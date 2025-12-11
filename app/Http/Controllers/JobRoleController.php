<?php

namespace App\Http\Controllers;

use App\Services\JobRoleServices;
use Exception;
use Illuminate\Http\Request;

class JobRoleController extends Controller
{
    function getJobRoles(int $id = 0){
         try{
            $roles = JobRoleServices::getRoles($id);
            return $this->successResponse($roles);
        }catch (Exception $e) {
            return $this->errorResponse( 'Server Error: ' . $e->getMessage());
        }

    }

    function getLevels(){
         try{
            $levels = JobRoleServices::getLevels();
            return $this->successResponse($levels);
        }catch (Exception $e) {
            return $this->errorResponse( 'Server Error: ' . $e->getMessage());
        }
    }
    
    function addOrUpdateJobRole(Request $request,int $id = 0){
         try{
            $roles = JobRoleServices::addOrUpdateRole($request,$id);
            return $this->successResponse($roles);
        }catch (Exception $e) {
            return $this->errorResponse( 'Server Error: ' . $e->getMessage());
        }
    }

    function deleteJobRole(int $id = 0){
         try{
            JobRoleServices::deleteJobRole($id);
            return $this->successResponse();
        }catch (Exception $e) {
            return $this->errorResponse( 'Server Error: ' . $e->getMessage());
        }
    }
}
