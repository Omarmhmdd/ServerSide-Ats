<?php

namespace App\Http\Controllers;

use App\Services\GithubService;

class GithubController extends Controller
{
    public function analyze($username, GithubService $github)
    {
      
        return $this->successResponse([
            "profile" => $profile,
            "repositories" => $repos
        ]);
    }
}

