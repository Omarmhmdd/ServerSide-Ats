<?php

namespace App\Services;

use App\Models\CustomStage;
use App\Models\JobRole;
use App\Models\JobSkill;
use App\Models\Level;
use Error;
use Illuminate\Support\Facades\DB;

class JobRoleServices
{
    static function getLevels(){
        $levels = Level::all('id','name');
        return $levels;
    }

    public static function getRoles($id = null)
{
    $query = JobRole::with(['recruiter', 'hiringManager', 'level', 'skills']);

    $roles = $id
        ? $query->where('id', $id)->get()
        : $query->get();

    return $roles->map(function ($role) {
        return [
            'id'          => $role->id,
            'title'       => $role->title,
            'description' => $role->description,
            'location'    => $role->location,
            'is_remote'   => $role->is_remote,
            'is_on_site'  => $role->is_on_site,

            'recruiter_id'   => $role->recruiter->id ?? null,
            'recruiter_name' => $role->recruiter->name ?? null,

            'hiring_manager_id'   => $role->hiringManager->id ?? null,
            'hiring_manager_name' => $role->hiringManager->name ?? null,

            'level_id'   => $role->level->id ?? null,
            'level_name' => $role->level->name ?? null,

            'skills_list' => $role->skills->map(function ($skill) {
                return [
                    'name'         => $skill->name,
                    'nice_to_have' => $skill->nice_to_have,
                ];
            }),
        ];
    });
}

    static function convertSkills($roles)
    {
        foreach ($roles as $role) {
            $skills = [];

            if (!empty($role->skills_list)) {
                $pairs = explode(',', $role->skills_list);

                foreach ($pairs as $pair) {
                    if (strpos($pair, ':') !== false) {
                        list($name, $nice) = explode(':', $pair);
                        $skills[] = [
                            'name' => $name,
                            'nice_to_have' => (int)$nice
                        ];
                    }
                }
            }

            $role->skills = $skills;
            unset($role->skills_list);
        }
    }



    public static function addOrUpdateRole($request, $id = 0)
    {
        $id = $id ?: $request->input('id', 0);
        return DB::transaction(function () use ($request, $id) {

            $role = self::saveJobRole($request, $id);

            self::saveSkills($role->id, $request->input('skills', []));

            self::saveStages($role->id, $request->input('stages', []));

            return $role;
        });
    }

    public static function getStatistics(int $recruiterId){
        return DB::select("
            SELECT j.id,j.title,j.is_remote,j.is_on_sight,COUNT(c.id) AS candidate_count
            FROM job_roles j
            LEFT JOIN candidates c 
                ON c.job_role_id = j.id
            WHERE j.recruiter_id = ?
            GROUP BY 
                j.id,
                j.title,
                j.is_remote,
                j.is_on_sight
        ", [$recruiterId]);
    }

    public static function getCandidatesInStages(int $recruiter_id): array{
       $globalStages = self::getNumberInGlobalStages($recruiter_id);
       $customStages = self::getNumberInCustomStages($recruiter_id);       

        return self::formatStatisticalReturn($globalStages , $customStages);
    }

    private static function getNumberInGlobalStages($recruiter_id){
         return  DB::select("
            SELECT j.title, p.global_stages, COUNT(p.candidate_id) AS count
            FROM job_roles j
            LEFT JOIN pipelines p ON p.job_role_id = j.id
            WHERE j.recruiter_id = ?
            GROUP BY j.title, p.global_stages
        ", [$recruiter_id]);
    }

    private static function getNumberInCustomStages($recruiter_id){
        return DB::select("
            SELECT j.title, cs.name AS custom_stage_name, COUNT(p.candidate_id) AS count
            FROM job_roles j
            JOIN pipelines p ON p.job_role_id = j.id
            JOIN custom_stages cs ON cs.id = p.custom_stage_id
            WHERE j.recruiter_id = ?
            GROUP BY j.title, cs.name
        ", [$recruiter_id]);
    }

    private static function formatStatisticalReturn($globalStages , $customStages){
        $formatted = [];

        foreach ($globalStages as $row) {
            if ($row->global_stages !== null) {
                $formatted[$row->title][$row->global_stages] = (int) $row->count;
            }
        }

        foreach ($customStages as $row) {
            $formatted[$row->title][$row->custom_stage_name] = (int) $row->count;
        }

        return $formatted;
    }


    private static function saveJobRole($request, $id)
    {
        if ($id == 0) {
            $role = new JobRole();
        } else {
            $role = JobRole::find($id);
            if (!$role && $id != 0) {
                throw new \Exception("No Job Role Found.");
            }
        }

        $role->fill($request->all());

        if ($role->save()) {
            return $role;
        }

        throw new \Exception("Error Saving Role.");
    }

    private static function saveSkills($job_role_id, $skillsArray)
    {
        JobSkill::where('job_role_id', $job_role_id)->delete();

        if (!empty($skillsArray)) {
            foreach ($skillsArray as $skillData) {
                $skill = new JobSkill();
                $skill->fill([
                    'job_role_id' => $job_role_id,
                    'name' => $skillData['name'],
                    'nice_to_have' => $skillData['nice_to_have']
                ]);
                $skill->save();
            }
        }
    }

    private static function saveStages($job_role_id, $stagesArray)
    {
        CustomStage::where('job_role_id', $job_role_id)->delete();

        if (!empty($stagesArray)) {
            foreach ($stagesArray as $index => $stage_name) {
                $stage = new CustomStage();

                $stage->fill([
                    'job_role_id' => $job_role_id,
                    'name' => $stage_name,
                    'order' => $index
                ]);

                $stage->save();
            }
        }
    }

    static function deleteJobRole($id){
        $role = JobRole::findOrFail($id);
        $role->delete();
    }
}
