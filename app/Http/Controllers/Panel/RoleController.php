<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Role::class);
        $roles = Role::query()->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1));

        return $this->success([
            'data' => [
                $roles->toArray()
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Role::class);
        $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name']
        ]);

        $role = Role::query()->create([
            'name' => $request->input('name'),
            'guard_name' => 'web'
        ]);

        $permissionNames = $request->input('permissions', []);

        $permissionModels = collect($permissionNames)->map(function ($permissionName) {
            $permission = Permission::query()->where('name', $permissionName)->first();
            if (is_null($permission)) {
                throw ValidationException::withMessages([
                    'permissions' => ["Permission `{$permissionName}` not found."]
                ]);
            }
            return $permission;
        });

        $role->syncPermissions($permissionModels);

        return $this->success([
            'data' => [
                'role' => $role->toArray(),
                'permissions' => $permissionModels->toArray()
            ],
            'code' => 201
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $role = Role::query()->where('id', $id)
            ->orWhere('name', $id)
            ->firstOrFail();
        Gate::authorize('view', $role);
        return $this->success([
            'data' => [
                'role' => $role->toArray(),
                'permissions' => $role->permissions->toArray(),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::query()->where('id', $id)
            ->orWhere('name', $id)
            ->firstOrFail();
        Gate::authorize('update', $role);
        $request->validate([
            'name' => ['nullable', 'required_without:permissions', 'string', 'max:80', Rule::unique('roles', 'name')->ignore($role)],
            'permissions' => ['nullable', 'required_without:name', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name']
        ]);

        if ($request->filled('permissions')) {
            $permissionNames = $request->input('permissions', []);
            $role->syncPermissions([]);
            $role->permissions()->detach();
            $role->save();
            $role->refresh();

            $permissionModels = collect($permissionNames)->map(function ($permissionName) {
                $permission = Permission::query()->where('name', $permissionName)->first();
                if (is_null($permission)) {
                    throw ValidationException::withMessages([
                        'permissions' => ["Permission `{$permissionName}` not found."]
                    ]);
                }
                return $permission;
            });

            $role->syncPermissions($permissionModels);
        }

        $role->update(['name' => $request->input('name', $role->name)]);
        $role->refresh();

        return $this->success([
            'data' => [
                'role' => $role->toArray(),
                'permissions' => $role->permissions->toArray()
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $role = Role::query()->where('id', $id)
            ->orWhere('name', $id)
            ->firstOrFail();
        Gate::authorize('delete', $role);
        $res = $role->delete();
        return $res ? $this->success() : $this->error();
    }
}
