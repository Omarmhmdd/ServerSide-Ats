<?php

namespace App\Http\Controllers;

use App\Services\ScorecardServices;
use Exception;
use Illuminate\Http\Request;

class ScorecardController extends Controller
{
    function getJobRoles(int $id){
         try{
            $roles = ScorecardServices::getScorecards($id);
            return $this->successResponse($roles);
        }catch (Exception $e) {
            return $this->errorResponse( 'Server Error: ' . $e->getMessage());
        }

    }

    function addOrUpdateJobRole(Request $request,int $id = 0){
         try{
            $roles = ScorecardServices::addOrUpdateScorecard($request,$id);
            return $this->successResponse($roles);
        }catch (Exception $e) {
            return $this->errorResponse( 'Server Error: ' . $e->getMessage());
        }
    }

    function deleteScoreCard(int $id = 0){
         try{
            ScorecardServices::deleteScoreCard($id);
            return $this->successResponse();
        }catch (Exception $e) {
            return $this->errorResponse( 'Server Error: ' . $e->getMessage());
        }
    }
}
