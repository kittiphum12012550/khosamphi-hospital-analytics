<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user account is deactivated
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'บัญชีผู้ใช้นี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
            ]);
        }

        // Roles can be passed like 'admin,staff' or as multiple params
        $allowedRoles = [];
        foreach ($roles as $roleGroup) {
            foreach (explode(',', $roleGroup) as $r) {
                $allowedRoles[] = trim($r);
            }
        }

        if (empty($allowedRoles) || in_array($user->role, $allowedRoles)) {
            return $next($request);
        }

        // If user does not have sufficient role
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้ (Forbidden)',
            ], 403);
        }

        abort(403, 'ขออภัย คุณไม่มีสิทธิ์เข้าถึงหน้านี้ (Role Required: ' . implode('/', $allowedRoles) . ')');
    }
}

