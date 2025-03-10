<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ModeratorOrAdminMiddleware
{
    /**
     * Проверяет, имеет ли пользователь роль модератора или администратора
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !(auth()->user()->hasRole('moderator') || auth()->user()->hasRole('admin'))) {
            return response()->json([
                'message' => 'Доступ запрещен. Требуются права модератора или администратора.',
                'status' => 'error'
            ], 403);
        }

        return $next($request);
    }
}
