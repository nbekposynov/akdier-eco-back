<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserDataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('admin');
    }

    public function getUsers(Request $request)
    {
        $query = User::query()
            ->select([
                'id',
                'name',
                'email',
                'role',
                'bin_company',
                'description',
                'moderator_id',
                'created_at',
            ])
            ->with('moderator:id,name,email'); // Загружаем данные о модераторе

        // Фильтрация по роли
        if ($request->has('role')) {
            $query->where('role', $request->role);
        } else {
            $query->whereIn('role', ['moderator', 'company']);
        }

        // Поиск
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('bin_company', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Сортировка
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Пагинация
        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage);

        // Форматируем ответ
        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'links' => [
                'first' => $users->url(1),
                'last' => $users->url($users->lastPage()),
                'prev' => $users->previousPageUrl(),
                'next' => $users->nextPageUrl(),
            ],
            'filters' => [
                'search' => $request->search ?? null,
                'role' => $request->role ?? 'all',
                'sort_by' => $sortField,
                'sort_direction' => $sortDirection
            ]
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'role' => 'sometimes|in:moderator,company,user,admin',
            'bin_company' => 'sometimes|string|nullable',
            'description' => 'sometimes|string|nullable',
            'moderator_id' => 'sometimes|nullable|exists:users,id', // Добавляем валидацию ID модератора
        ]);

        // Проверяем, что выбранный модератор действительно имеет роль "модератор"
        if (isset($validated['moderator_id']) && $validated['moderator_id'] !== null) {
            $moderator = User::find($validated['moderator_id']);
            if (!$moderator || $moderator->role !== 'moderator') {
                return response()->json([
                    'message' => 'Указанный пользователь не является модератором',
                    'errors' => ['moderator_id' => ['Указанный ID не соответствует модератору']]
                ], 422);
            }
        }

        $user->update($validated);

        // Загружаем связанного модератора для возврата в ответе
        if ($user->moderator_id) {
            $user->load('moderator:id,name,email');
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Получить список всех модераторов для выбора
     */
    public function getModerators()
    {
        $moderators = User::where('role', 'moderator')
            ->select(['id', 'name', 'email'])
            ->orderBy('name')
            ->get();

        return response()->json($moderators);
    }
}
