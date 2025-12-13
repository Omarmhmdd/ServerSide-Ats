<?php

namespace App\Http\Controllers;

use App\Services\JobSkillServices;
use Exception;
use Illuminate\Http\Request;

class JobSkillController extends Controller
{
    function addOrUpdateJobSkill(Request $request,int $id = 0){
         try{
            $skills = JobSkillServices::addOrUpdateSkill($request,$id);
            return $this->successResponse($skills);
        }catch (Exception $e) {
            return $this->errorResponse( 'Server Error: ' . $e->getMessage());
        }
    }
}
