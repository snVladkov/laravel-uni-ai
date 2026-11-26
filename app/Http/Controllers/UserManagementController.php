<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\SyncUserRolesRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /**
     * Browse and filter the application users.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $search = trim($request->string('q')->toString());
        $role = $request->string('role')->toString();

        $users = User::query()
            ->with('roles')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%")
                    ->orWhere('teacher_code', 'like', "%{$search}%");
            }))
            ->when($role !== '', fn ($query) => $query->role($role))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::query()->where('guard_name', 'web')->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles', 'search', 'role'));
    }

    /**
     * Show the form for creating a user.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = Role::query()->where('guard_name', 'web')->orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a new user with the selected roles.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $roles = $request->validated('roles');

        $user = DB::transaction(function () use ($request, $roles): User {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'department' => $request->needsDepartment() ? $request->validated('department') : null,
                'teacher_code' => in_array('teacher', $roles, true) ? $request->validated('teacher_code') : null,
                'student_code' => in_array('student', $roles, true) ? $request->validated('student_code') : null,
            ]);

            $user->syncRoles($roles);

            return $user;
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('users.flash.created', ['name' => $user->name]));
    }

    /**
     * Sync the roles and department of the given user.
     */
    public function syncRoles(SyncUserRolesRequest $request, User $user): RedirectResponse
    {
        $this->authorize('syncRoles', $user);

        $roles = $request->validated('roles');

        DB::transaction(function () use ($request, $roles, $user): void {
            $user->syncRoles($roles);

            $user->update([
                'department' => $request->needsDepartment() ? $request->validated('department') : null,
            ]);
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('users.flash.roles_updated', ['name' => $user->name]));
    }
}
