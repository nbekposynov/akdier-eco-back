<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Проверяет, имеет ли пользователь роль администратора
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            return response()->json([
                'message' => 'Доступ запрещен. Требуются права администратора.',
                'status' => 'error'
            ], 403);
        }

        return $next($request);
    }
}
