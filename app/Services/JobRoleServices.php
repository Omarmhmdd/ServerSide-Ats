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
