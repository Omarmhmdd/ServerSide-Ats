<?php

namespace App\Services;

use App\Http\Requests\AIRequest;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\JobRole;
use App\Models\Pipeline;
use App\Models\ScoreCard;
use Carbon\Carbon;
use Exception;
use Http;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http as FacadesHttp;
use Laravel\Pail\ValueObjects\Origin\Http as OriginHttp;


class InterviewService {
    public static function scheduleInterviews($list_of_emails){

        $required_ids = self::getRequiredIds($list_of_emails);


        $list_of_interviews = self::chooseNextBestSchedule($list_of_emails , $required_ids);


        $payload = [
            "emails" => $list_of_emails,
            "interviews" => $list_of_interviews
        ];
    $url = env('N8N_SEND_EMAIL_ENDPOINT', 'http://localhost:5678/webhook-test/');
       FacadesHttp::post($url, $payload);
        self::moveToScreeningStageInPipeline($list_of_emails , $required_ids , $list_of_interviews);

        return true;
    }
    private static function getRequiredIds(array $list_of_emails){
        if (empty($list_of_emails)) {
            throw new Exception("Empty list of emails");
        }

        $first_email = reset($list_of_emails);

        $candidate = Candidate::where('email', $first_email)
            ->select('job_role_id')
            ->first();

        if (!$candidate) {
           throw new Exception("Candidate not found");
        }

        $jobRole = JobRole::where('id', $candidate->job_role_id)
            ->select('interviewer_id')
            ->first();

        if (!$jobRole) {
            throw new Exception("Job role not found");
        }

        return [
            'job_role_id' => $candidate->job_role_id,
            'interviewer_id' => $jobRole->interviewer_id
        ];
    }
    private static function chooseNextBestSchedule($list_of_emails , $required_ids){

        $last_interview_time = self::getLastInterviewTime($required_ids["interviewer_id"]);

        // interview window from 8 am -> 2 pm
        $start_time = Carbon::parse('08:00');
        $end_time   = Carbon::parse('14:00');

        // if no interviews, set interview to a week from now at 8 am
        if(!$last_interview_time){
            $next_interview_time = now()
                ->addWeek()
                ->startOfDay()
                ->setTime(8, 0);
        }else{// continue from last interview
           $next_interview_time = self::determineNextInterviewTime($last_interview_time , $end_time);
        }

        return self::createSchedules($list_of_emails , $next_interview_time  ,$required_ids["interviewer_id"] , $end_time , $required_ids["job_role_id"]);

    }
    private static function getLastInterviewTime($interviewer_id){
         return Interview::where('interviewer_id', $interviewer_id)
            ->orderBy('schedule', 'desc')
            ->value('schedule');
    }
    private static function determineNextInterviewTime($last_interview_time , $end_time){
        $next_interview_time = Carbon::parse($last_interview_time)->addMinutes(20);


        $oneWeekFromNow = now()->addWeek();
        if ($next_interview_time->lt($oneWeekFromNow)) {
            $next_interview_time = $oneWeekFromNow
                ->startOfDay()
                ->setTime(8, 0);
        }


        if ($next_interview_time->format('H:i') > $end_time->format('H:i')) {

            $next_interview_time = $next_interview_time->addDay()->setTime(8, 0);
        }

        return $next_interview_time;
    }
    private static function createSchedules($list_of_emails , $next_interview_time , $interviewer_id , $end_time , $job_role_id){
        $list_of_new_interviews = [];
         foreach ($list_of_emails as $candidate_id => $email) {

            $new_interview = new Interview([
                'candidate_id'   => $candidate_id,
                'interviewer_id' => $interviewer_id,
                'schedule'       => $next_interview_time,
                'job_role_id' => $job_role_id,
                'type' => 'screen',
                'duration' => 20,
                'meeting_link' => '',
                'rubric' => '',
                'notes' => '',
                'status' => 'pending',
            ]);

            $new_interview->save();

            $list_of_new_interviews[] = $new_interview;
            // update time
            $next_interview_time->addMinutes(20);

            // check if it exceeds 2 pm, if yes move for next day at 8 am
            if ($next_interview_time->format('H:i') > $end_time->format('H:i')) {
                $next_interview_time = $next_interview_time->addDay()->setTime(8, 0);
            }
        }

        return $list_of_new_interviews;
    }
    private static function moveToScreeningStageInPipeline($list_of_emails , $required_ids , $list_of_interviews){
        $interview_index = 0;
        foreach($list_of_emails as $candidate_id => $email){
            $user_pipeline = Pipeline::where('candidate_id' , $candidate_id)
                                    ->where('job_role_id' , $required_ids["job_role_id"])
                                    ->first();

            if (!$user_pipeline) {
                $user_pipeline = new Pipeline();
                $user_pipeline->candidate_id = $candidate_id;
                $user_pipeline->job_role_id = $required_ids["job_role_id"];
            }

            $user_pipeline->global_stages = "screen";

            if (isset($list_of_interviews[$interview_index])) {
                $user_pipeline->interview_id = $list_of_interviews[$interview_index]->id;
            }

            $user_pipeline->save();

            $interview_index++;
        }

        self::callN8nToSendEmails($list_of_emails , $list_of_interviews);
    }

    private static function callN8nToSendEmails($list_of_emails , $list_of_interviews){
        $payload = [
            "emails" => $list_of_emails,
            "interviews" => $list_of_interviews
        ];
       FacadesHttp::post("http://localhost:5678/webhook-test/sendEmails" , $payload);
    }


    private static function canAccessInterview(Interview $interview): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Load role relationship if not already loaded
        if (!isset($user->role)) {
            $user->load('role');
        }

        // Admin can access everything
        if ($user->isAdmin()) {
            return true;
        }

        // Recruiter can only access their own job roles
        if ($user->isRecruiter()) {
            // Load jobRole relationship if not loaded
            if (!isset($interview->jobRole)) {
                $interview->load('jobRole');
            }

            // Check if jobRole exists and belongs to recruiter
            if (!$interview->jobRole) {
                return false;
            }

            return $interview->jobRole->recruiter_id === $user->id;
        }

        // Interviewer can view interviews (read-only)
        return true;
    }


    private static function getRecruiterJobRoleIds(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        // Load role relationship if not already loaded
        if (!isset($user->role)) {
            $user->load('role');
        }

        if (!$user->isRecruiter()) {
            return [];
        }

        return JobRole::where('recruiter_id', $user->id)
            ->pluck('id')
            ->toArray();
    }

    public static function getAllInterviews(): Collection{
        $user = Auth::user();

        $query = Interview::with(['interviewer', 'jobRole', 'candidate']);

        // Filter by recruiter's job roles if not admin
        if ($user) {
            /** @var \App\Models\User $user */
            if (!isset($user->role)) {
                $user->load('role');
            }

            if ($user->isRecruiter()) {
                $jobRoleIds = self::getRecruiterJobRoleIds();
                if (!empty($jobRoleIds)) {
                    $query->whereIn('job_role_id', $jobRoleIds);
                } else {
                    return Interview::whereRaw('1 = 0')->get();
                }
            }
        }

        return $query->latest()->get();
    }

    public static function getInterviewById(int $id): Interview{
        $interview = Interview::with(['interviewer', 'jobRole', 'candidate'])
            ->find($id);

        if (!$interview) {
            throw new ModelNotFoundException('Interview not found');
        }

        // Check access permission
        if (!self::canAccessInterview($interview)) {
            throw new ModelNotFoundException('Interview not found');
        }

        return $interview;
    }

    public static function createInterview(array $data): Interview{
        $user = Auth::user();

        // Check if job role belongs to recruiter
        if ($user) {
            /** @var \App\Models\User $user */
            if (!isset($user->role)) {
                $user->load('role');
            }

            if ($user->isRecruiter()) {
                $jobRole = JobRole::find($data['job_role_id']);
                if (!$jobRole || $jobRole->recruiter_id !== $user->id) {
                    throw new ModelNotFoundException('Job role not found');
                }
            }
        }

        $interview = Interview::create($data);
        $interview->load(['interviewer', 'jobRole', 'candidate']);

        // Automatically find or create pipeline and link interview
        $pipeline = Pipeline::where('candidate_id', $data['candidate_id'])
            ->where('job_role_id', $data['job_role_id'])
            ->first();

        if (!$pipeline) {
            // Create pipeline if it doesn't exist
            $pipeline = Pipeline::create([
                'candidate_id' => $data['candidate_id'],
                'job_role_id' => $data['job_role_id'],
                'global_stages' => 'screen', // Interview means they're in screening stage
                'custom_stage_id' => null,
                'interview_id' => $interview->id,
            ]);
        } else {
            // Link interview to existing pipeline
            $pipeline->interview_id = $interview->id;
            // If pipeline is in 'applied' stage, move to 'screen' when interview is created
            if ($pipeline->global_stages === 'applied') {
                $pipeline->global_stages = 'screen';
                $pipeline->custom_stage_id = null;
            }
            $pipeline->save();
        }

        return $interview;
    }

    public static function updateInterview(int $id, array $data): Interview{
        $interview = Interview::find($id);

        if (!$interview) {
            throw new ModelNotFoundException('Interview not found');
        }

        // Check access permission
        if (!self::canAccessInterview($interview)) {
            throw new ModelNotFoundException('Interview not found');
        }

        $interview->update($data);
        $interview->load(['interviewer', 'jobRole', 'candidate']);

        return $interview;
    }

    public static function deleteInterview(int $id): bool{
        $interview = Interview::find($id);

        if (!$interview) {
            throw new ModelNotFoundException('Interview not found');
        }

        // Check access permission
        if (!self::canAccessInterview($interview)) {
            throw new ModelNotFoundException('Interview not found');
        }

        return $interview->delete();
    }

    public static function getInterviewsByCandidate(int $candidateId): Collection{
        $user = Auth::user();

        $query = Interview::with(['interviewer', 'jobRole', 'candidate'])
            ->where('candidate_id', $candidateId);

        // Filter by recruiter's job roles if not admin
        if ($user) {
            /** @var \App\Models\User $user */
            if (!isset($user->role)) {
                $user->load('role');
            }

            if ($user->isRecruiter()) {
                $jobRoleIds = self::getRecruiterJobRoleIds();
                if (!empty($jobRoleIds)) {
                    $query->whereIn('job_role_id', $jobRoleIds);
                } else {
                    return Interview::whereRaw('1 = 0')->get();
                }
            }
        }

        return $query->latest()->get();
    }

    public static function getInterviewsByInterviewer(int $interviewerId): Collection{
        $user = Auth::user();

        $query = Interview::with(['interviewer', 'jobRole', 'candidate'])
            ->where('interviewer_id', $interviewerId);

        // Filter by recruiter's job roles if not admin
        if ($user) {
            /** @var \App\Models\User $user */
            if (!isset($user->role)) {
                $user->load('role');
            }

            if ($user->isRecruiter()) {
                $jobRoleIds = self::getRecruiterJobRoleIds();
                if (!empty($jobRoleIds)) {
                    $query->whereIn('job_role_id', $jobRoleIds);
                } else {
                    return Interview::whereRaw('1 = 0')->get();
                }
            }
        }

        return $query->latest()->get();
    }

    public static function updateInterviewStatus(int $id, string $status): Interview{
        $validStatuses = ['no show', 'completed', 'canceled', 'posptponed', 'pending'];

        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Invalid status provided');
        }

        $interview = Interview::find($id);

        if (!$interview) {
            throw new ModelNotFoundException('Interview not found');
        }

        // Check access permission
        if (!self::canAccessInterview($interview)) {
            throw new ModelNotFoundException("You can't access this interview.");
        }

        $interview->update(['status' => $status]);
        $interview->load(['interviewer', 'jobRole', 'candidate']);

        return $interview;
    }

    public static function MarkAsComplete($id){

        $interview = self::getInterviewById($id);
        $interview = self::updateInterviewStatus($id,'completed');

        $url = "http://localhost:5678/webhook-test/complete_interview";
        FacadesHttp::post($url,[
            'candidate_id' => $interview->candidate_id,
            'interview_id' => $interview->id,
            'candidate_email' => $interview->candidate->email,
            'interviewer_email' => $interview->interviewer->email,
            'recruiter_email' => $interview->jobRole->recruiter->em,
            'interviewer_name' => $interview->interviewer->name,
            'type' => $interview->type,
            'candidate_name' => $interview->candidate->first_name,
            'role_title' => $interview->jobRole->title,
            'interview_type' => $interview->type,
            'interview_notes' => $interview->notes,
        ]);
        return $interview;
    }

    public static function createScoreCard($request)
    {
        return DB::transaction(function () use ($request) {

            $input = $request->validated();

            $data_to_save = [
                'interview_id'              => $input['interview_id'],
                'candidate_id'              => $input['candidate_id'],
                'criteria'                   => json_encode($input['scorecard']),
                'summary'                   => $input['summary'],
                'written_evidence'          => $input['scorecard']['communication']['evidence'] ?? 'See criteria for details',
                'overall_recommendation' => $input['overall_recommendation'],
            ];

            return ScorecardServices::saveScorecard($data_to_save, 0);
        });
    }


}




