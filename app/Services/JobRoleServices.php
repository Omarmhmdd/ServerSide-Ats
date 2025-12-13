<?php

namespace App\Services;

use App\Models\JobRole;
use App\Models\JobSkill;
use App\Models\Level;
use Error;
use Illuminate\Support\Facades\DB;

class JobRoleServices
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }

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
