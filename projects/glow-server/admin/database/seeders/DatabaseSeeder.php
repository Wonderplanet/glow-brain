<?php

namespace Database\Seeders;

use App\Models\Adm\AdmUser;
use App\Models\Adm\AdmPermission;
use App\Models\Adm\AdmRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 初期ユーザーの作成
        $adminUser = AdmUser::create(['name' => 'admin', 'email' => 'admin@wonderpla.net', 'password' => bcrypt('admin')]);

        // ロールの作成
        $adminRole = AdmRole::create(['name' => 'Admin', 'description' => '管理者']);
        $developerRole = AdmRole::create(['name' => 'Developer', 'description' => 'エンジニアやプランナーなど']);

        // パーミッションの作成
        $adminPermission = AdmPermission::create(['name' => 'AdministratorAccess', 'description' => '全ての機能を許可']);
        $powerUserPermission = AdmPermission::create(['name' => 'PowerUserAccess', 'description' => '管理ツール自体の設定に関する操作を除いて許可']);

        // 初期ユーザーに管理者ロールを付与
        $adminUser->assignRole($adminRole);

        // ロールにパーミッションを付与
        $adminRole->givePermissionTo($adminPermission);
        $developerRole->givePermissionTo($powerUserPermission);
    }
}
