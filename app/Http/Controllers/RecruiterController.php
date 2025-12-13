<?php

namespace App\Http\Controllers;

use App\Services\RecruiterServices;
use Exception;

class RecruiterController extends Controller
{
    function getRecruiters(int $id = 0){
         try{
            $roles = RecruiterServices::getRecuiters($id);
            return $this->successResponse($roles);
        }catch (Exception $e) {
            return $this->errorResponse( 'Server Error: ' . $e->getMessage());
        }

    }
}
