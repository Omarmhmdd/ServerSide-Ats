<?php

namespace App\Http\Controllers;

use App\Services\HiringManagerServices;
use Exception;

class HiringManagerController extends Controller
{
    function getHiringManagers(){
         try{
            $roles = HiringManagerServices::getHiringMangers();
            return $this->successResponse($roles);
        }catch (Exception $e) {
            return $this->errorResponse( 'Server Error: ' . $e->getMessage());
        }

    }
}
