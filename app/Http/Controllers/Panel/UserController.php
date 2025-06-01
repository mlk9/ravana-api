<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{

    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', [User::class]);

        $request->validate([
            'order' => ['nullable', 'in:first_name,last_name,email,created_at'],
            'dir' => ['nullable', 'in:asc,desc']
        ]);

        $users = User::query();

        if ($request->filled('search')) {
            $users->whereHas(function ($query) use($request){
                $query->orWhereLike('first_name', '%'.$request->input('search').'%')
                    ->orWhereLike('last_name', '%'.$request->input('search').'%')
                    ->orWhereLike('email', '%'.$request->input('search').'%');
            });
        }

        if ($request->filled('dir') && $request->filled('order')) {
            $users->orderBy($request->input('order'), $request->input('dir'));
        }

        $users = $users->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1));

        return $this->success(['data' => $users->toArray()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', User::class);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'min:3'],
            'last_name' => ['required', 'string', 'min:3'],
            'email' => ['required', 'email', 'unique:users,email'],
            'email_verify' => ['required', 'boolean'],
            'password' => ['required', 'string', Password::min(8)
                ->mixedCase()     // حروف بزرگ و کوچک
                ->letters()       // شامل حروف باشد
                ->numbers()       // شامل عدد باشد
                ->symbols()       // شامل نمادها باشد
                ->uncompromised() // در دیتابیس رمزهای لو رفته نباشد
            ],
            'suspend' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $data['password'] = Hash::make($request->input('password'));
        $data['email_verified_at'] = $request->input('email_verify',false) == true ? now() : null;
        $data['suspended_at'] = $request->input('suspend',false) == true ? now() : null;

        $user = User::query()->create($data);

        if ($request->filled('roles')) {
            $user->syncRoles($request->input('roles'), []);
        }

        if ($request->filled('permissions')) {
            $user->syncPermissions($request->input('permissions', []));
        }

        return $this->success(['data' => $user->toArray(), 'code' => 201]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid) : JsonResponse
    {
        $s_user = User::query()->where('uuid', $uuid)->firstOrFail();
        Gate::authorize('view', $s_user);
        return $this->success(['data' => $s_user->toArray()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['nullable', 'string', 'min:3'],
            'last_name' => ['nullable', 'string', 'min:3'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($uuid, 'uuid')],
            'email_verified_at' => ['nullable', 'integer', 'min:946684800'],
            'password' => ['nullable', 'string', Password::min(8)
                ->mixedCase()     // حروف بزرگ و کوچک
                ->letters()       // شامل حروف باشد
                ->numbers()       // شامل عدد باشد
                ->symbols()       // شامل نمادها باشد
                ->uncompromised() // در دیتابیس رمزهای لو رفته نباشد
            ],
            'suspend' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $user = User::query()->where('uuid', $uuid)->firstOrFail();
        $s_user=$user;
        Gate::authorize('update', $s_user);

        if ($request->filled('roles')) {
            $user->syncRoles($request->input('roles'), []);
        }

        if ($request->filled('permissions')) {
            $user->syncPermissions($request->input('permissions', []));
        }


        $user->update([
            'first_name' => $request->input('first_name',$user->first_name),
            'last_name' => $request->input('last_name',$user->last_name),
            'email' => $request->input('email',$user->email),
            'email_verified_at' => $request->input('email_verify',false) == true ? now() : null,
            'suspended_at' => $request->input('suspend',false) == true ? now() : null,
            'password' => $request->has('password') ? Hash::make($request->input('password')) : $user->password,
        ]);

        $user->refresh();

        return $this->success(['data' => $user->toArray()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid) : JsonResponse
    {
        $s_user = User::query()->where('uuid', $uuid)->firstOrFail();
        Gate::authorize('delete', $s_user);
        $res = $s_user->delete();

        if($res)
        {
            return $this->success();
        }

       return $this->error();
    }
}
