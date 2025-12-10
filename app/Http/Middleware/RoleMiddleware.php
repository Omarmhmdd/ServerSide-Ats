<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle(Request $request, Closure $next, ...$roles): Response
{
    if (!auth()->check()) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    $user = auth()->user();
    
    // Load role relationship if not already loaded
    if (!$user->relationLoaded('role')) {
        $user->load('role');
    }

    // DEBUG: Log what we're checking
    Log::info('RoleMiddleware Debug', [
        'user_id' => $user->id,
        'role_id' => $user->role_id,
        'role_loaded' => $user->relationLoaded('role'),
        'role_object' => $user->role ? [
            'id' => $user->role->id,
            'name' => $user->role->name
        ] : 'NULL',
        'userRoleName' => $user->role ? strtolower($user->role->name) : null,
        'allowedRoles' => $roles,
        'allowedRoles_lowercase' => array_map('strtolower', $roles)
    ]);

    // Check if user has one of the required roles
    $userRoleName = $user->role ? strtolower($user->role->name) : null;
    $allowedRoles = array_map('strtolower', $roles);

    if (!$userRoleName || !in_array($userRoleName, $allowedRoles)) {
        Log::error('RoleMiddleware BLOCKED', [
            'userRoleName' => $userRoleName,
            'allowedRoles' => $allowedRoles,
            'match' => $userRoleName ? in_array($userRoleName, $allowedRoles) : false,
            'role_object_full' => $user->role
        ]);
        return response()->json([
            'message' => 'Unauthorized. Required role: ' . implode(' or ', $roles)
        ], 403);
    }

    Log::info('RoleMiddleware ALLOWED - User passed!');
    return $next($request);
}
}



