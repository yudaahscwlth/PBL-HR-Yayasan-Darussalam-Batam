<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Login user and return token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();
        
        // Create token
        /** @var \Laravel\Sanctum\HasApiTokens $user */
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // Load user relationships
        $user->load(['roles', 'permissions', 'profilePribadi', 'profilePekerjaan.jabatan', 'profilePekerjaan.departemen', 'profilePekerjaan.tempatKerja']);
        
        // Get user roles and permissions
        $roles = $user->roles->pluck('name')->toArray();
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        
        // Determine redirect path based on role
        $redirectTo = $this->getRedirectPath($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                ...(new UserResource($user))->toArray(request()),
                'roles' => $roles,
                'permissions' => $permissions,
            ],
            'token' => $token,
            'redirect_to' => $redirectTo,
        ]);
    }

    /**
     * Logout user and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Load user relationships
        $user->load(['roles', 'permissions', 'profilePribadi', 'profilePekerjaan.jabatan', 'profilePekerjaan.departemen', 'profilePekerjaan.tempatKerja']);
        
        // Get user roles and permissions
        $roles = $user->roles->pluck('name')->toArray();
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        return response()->json([
            'success' => true,
            'message' => 'User data retrieved successfully',
            'data' => [
                ...(new UserResource($user))->toArray(request()),
                'roles' => $roles,
                'permissions' => $permissions,
            ],
        ]);
    }

    /**
     * Refresh user token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        /** @var \Laravel\Sanctum\HasApiTokens $user */
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'token' => $token,
        ]);
    }

    /**
     * Get redirect path based on user role
     */
    private function getRedirectPath($user): string
    {
        $roles = $user->roles->pluck('name')->toArray();
        
        // Admin roles (only superadmin)
        if (in_array('superadmin', $roles)) {
            return '/admin/dashboard';
        }
        
        // Kepala Yayasan
        if (in_array('kepala yayasan', $roles)) {
            return '/kepala-yayasan/dashboard';
        }
        
        // Direktur Pendidikan
        if (in_array('direktur pendidikan', $roles)) {
            return '/direktur-pendidikan/dashboard';
        }
        
        // HRD roles
        if (in_array('kepala hrd', $roles) || in_array('staff hrd', $roles)) {
            return '/hrd/dashboard';
        }
        
        // Kepala Departemen
        if (in_array('kepala departemen', $roles)) {
            return '/kepala-departemen/dashboard';
        }
        
        // Kepala Sekolah
        if (in_array('kepala sekolah', $roles)) {
            return '/kepala-sekolah/dashboard';
        }
        
        // Tenaga Pendidik
        if (in_array('tenaga pendidik', $roles)) {
            return '/tenaga-pendidik/dashboard';
        }
        
        return '/dashboard';
    }
}
