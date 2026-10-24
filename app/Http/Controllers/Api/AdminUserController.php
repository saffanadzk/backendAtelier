<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminUserController extends Controller
{
    /**
     * Admin: List users.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->ensureAdmin($request);

        $limit = min((int) $request->input('limit', 20), 50);

        $users = User::withCount('articles')
            ->latest()
            ->paginate($limit);

        return UserResource::collection($users);
    }

    /**
     * Admin: Update user role.
     */
    public function updateRole(Request $request, int $id): JsonResponse
    {
        $this->ensureAdmin($request);

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:reader,contributor,editor,admin'],
        ]);

        // 1. Prevent demoting the primary system admin account
        if ($user->email === 'admin@atelier.local' && $validated['role'] !== 'admin') {
            return response()->json([
                'message' => 'Akun Administrator Utama (admin@atelier.local) dilindungi dan tidak dapat diturunkan rolenya.',
            ], 422);
        }

        // 2. Prevent admin from removing their own admin status
        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak dapat menurunkan role akun Administrator Anda sendiri.',
            ], 422);
        }

        // 3. Require explicit confirmation when promoting a non-admin to administrator
        if ($validated['role'] === 'admin' && $user->role !== 'admin' && !$request->boolean('confirm_admin_promotion')) {
            return response()->json([
                'message' => 'Konfirmasi pengangkatan Administrator diperlukan.',
                'requires_confirmation' => true,
            ], 422);
        }

        $user->role = $validated['role'];
        $user->save();

        return response()->json([
            'message' => "Role {$user->name} berhasil diperbarui menjadi {$user->role}.",
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Admin: Approve contributor application.
     */
    public function approveApplication(Request $request, int $id): JsonResponse
    {
        $this->ensureAdmin($request);

        $user = User::findOrFail($id);
        $user->role = 'contributor';
        $user->application_status = 'approved';
        $user->save();

        return response()->json([
            'message' => "{$user->name} has been approved as a Contributor.",
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Admin: Reject contributor application.
     */
    public function rejectApplication(Request $request, int $id): JsonResponse
    {
        $this->ensureAdmin($request);

        $user = User::findOrFail($id);
        $user->application_status = 'rejected';
        $user->save();

        return response()->json([
            'message' => "Application for {$user->name} was marked as rejected.",
            'user' => new UserResource($user),
        ]);
    }

    private function ensureAdmin(Request $request): void
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Administrator access required.');
        }
    }
}
