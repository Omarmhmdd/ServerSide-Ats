<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Interview;
use App\Models\JobRoles;
use App\Models\Pipeline;
use Carbon\Carbon;
use Http;

class InterviewService
{

    public static function scheduleInterviews($list_of_emails){
        // first get the user's job_role_id and recruiter_id
        $required_ids = self::getRequiredIds($list_of_emails);

        // then select best next time for the interviews
        $list_of_interviews = self::chooseNextBestSchedule($list_of_emails , $required_ids); 
        
        // move users in pipeline to next stage => screening

        return true;
    }

    private static function getRequiredIds($list_of_emails){
        $first_email = reset($list_of_emails);
        $required_ids = Candidate::where('email' , $first_email)
                        ->select([
                            'job_role_id'
                        ])->first();

        // then get the hiring_manager_id of this job
        $hiring_manager_id = JobRoles::where('id' , $required_ids["job_role_id"])
                                    ->select("hiring_manager_id")
                                    ->first();
        return[
            ...$required_ids,
            "hiring_manager_id" => $hiring_manager_id
        ];
    }
    private static function chooseNextBestSchedule($list_of_emails , $required_ids){

        $last_interview_time = self::getLastInterviewTime($required_ids["hiring_manager_id"]);

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

        return self::createSchedules($list_of_emails , $next_interview_time  ,$required_ids["hiring_manager_id"] , $end_time , $required_ids["job_role_id"]);
       
    }
    private static function getLastInterviewTime($hiring_manager_id){
         return Interview::where('interviewer_id', $hiring_manager_id)
            ->orderBy('schedule', 'desc') 
            ->value('schedule');
    }
    private static function determineNextInterviewTime($last_interview_time , $end_time){
        $next_interview_time = Carbon::parse($last_interview_time)->addMinutes(20);

        // ensure interviews start at least 1 week from today
        $oneWeekFromNow = now()->addWeek();
        if ($next_interview_time->lt($oneWeekFromNow)) {
            $next_interview_time = $oneWeekFromNow
                ->startOfDay()
                ->setTime(8, 0);
        }

        // 4) Check if time exceeds 2 PM
        if ($next_interview_time->format('H:i') > $end_time->format('H:i')) {
            // Move to next day at 8 AM
            $next_interview_time = $next_interview_time->addDay()->setTime(8, 0);
        }

        return $next_interview_time;
    }
    private static function createSchedules($list_of_emails , $next_interview_time , $hiring_manager_id , $end_time , $job_role_id){
        $list_of_new_interviews = [];
         foreach ($list_of_emails as $candidate_id => $email) {

            $new_interview = new Interview([
                'candidate_id'   => $candidate_id,
                'interviewer_id' => $hiring_manager_id,
                'schedule'       => $next_interview_time,
                'job_rol_id' => $job_role_id,
                'type' => 'screen',
                'duration' => 20,
                'meeting_link' => null,
                'rubric' => null,
                'notes' => null,
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

}
