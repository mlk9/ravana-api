<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $writer = [
            'articles.index',
            'articles.create',
            'articles.edit',
            'articles.show',
            'articles.destroy',

            'categories.index',
            'categories.create',
            'categories.edit',
            'categories.show',
            'categories.destroy',
        ];

        $all = [
            ...$writer,
            'articles.control-other'
        ];

        $roles = [
            'ceo' => $all,
            'writer' => $writer
        ];

        foreach ($roles as $role => $permissions) {
            $roleM = Role::query()->firstOrCreate(['name' => $role]);

            $permissionModels = collect($permissions)->map(function ($permission) {
                return Permission::query()->firstOrCreate(['name' => $permission]);
            });

            $roleM->syncPermissions($permissionModels);
        }
    }
}
