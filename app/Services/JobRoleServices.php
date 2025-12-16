<?php

namespace App\Services;

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

    static function getRoles($id){
        if(!$id){
            $roles = DB::select('SELECT
                recruiter.id AS recruiter_id,
                recruiter.name AS recruiter_name,
                levels.id AS level_id,
                levels.name AS level_name,
                manager.id AS hiring_manager_id,
                manager.name AS hiring_manager_name,
                job_roles.location,
                job_roles.title,
                job_roles.description,
                job_roles.is_remote,
                job_roles.is_on_site
            FROM job_roles
            INNER JOIN users AS recruiter ON job_roles.recruiter_id = recruiter.id
            INNER JOIN users AS manager ON job_roles.hiring_manager_id = manager.id
            INNER JOIN levels ON job_roles.level_id = levels.id;');
            return $roles;
        }

        $role = DB::select('SELECT
                recruiter.id AS recruiter_id,
                recruiter.name AS recruiter_name,
                levels.id AS level_id,
                levels.name AS level_name,
                manager.id AS hiring_manager_id,
                manager.name AS hiring_manager_name,
                job_roles.location,
                job_roles.title,
                job_roles.description,
                job_roles.is_remote,
                job_roles.is_on_site
            FROM job_roles
            INNER JOIN users AS recruiter ON job_roles.recruiter_id = recruiter.id
            INNER JOIN users AS manager ON job_roles.hiring_manager_id = manager.id
            INNER JOIN levels ON job_roles.level_id = levels.id WHERE job_roles.id = ?;',[$id]);
        return $role;
    }

    public static function addOrUpdateRole($request, $id = 0)
    {
        return DB::transaction(function () use ($request, $id) {

            $role = self::saveJobRole($request, $id);

            self::saveSkills($role->id, $request->input('skills', []));

            //self::saveStages($role->id, $request->input('stages', []));

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
            if (!$role) {
                throw new \Exception("No Job Role Found.");
            }
        }

        $role->fill($request->all());

        if ($role->save()) {
            return $role;
        }

        throw new \Exception("Error Saving Role.");
    }

    private static function saveSkills($role_id, $skillsArray)
    {
        JobSkill::where('role_id', $role_id)->delete();

        if (!empty($skillsArray)) {
            foreach ($skillsArray as $skillData) {
                $skill = new JobSkill();
                $skill->fill([
                    'role_id' => $role_id,
                    'name' => $skillData['name'],
                    'nice_to_have' => $skillData['is_nice_to_have']
                ]);
                $skill->save();
            }
        }
    }

    /*private static function saveStages($role_id, $stagesArray)
    {
        Stage::where('job_role_id', $role_id)->delete();

        if (!empty($stagesArray)) {
            foreach ($stagesArray as $stageName) {
                $stage = new Stage();

                $stage->fill([
                    'job_role_id' => $role_id,
                    'name' => $stageName
                ]);

                $stage->save();
            }
        }
    }*/

    static function deleteJobRole($id){
        $role = JobRole::findOrFail($id);
        $role->delete();
    }
}
