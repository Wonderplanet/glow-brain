<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        # config(permission.table_names)の値が変更後の値の場合に備えてテーブル存在をチェックしてリネーム
        if(Schema::hasTable('data_controls')) Schema::rename('data_controls', 'adm_data_controls');
        if(Schema::hasTable('model_has_permissions')) Schema::rename('model_has_permissions', 'adm_model_has_permissions');
        if(Schema::hasTable('model_has_roles')) Schema::rename('model_has_roles', 'adm_model_has_roles');
        if(Schema::hasTable('permissions')) Schema::rename('permissions', 'adm_permissions');
        if(Schema::hasTable('personal_access_tokens')) Schema::rename('personal_access_tokens', 'adm_personal_access_tokens');
        if(Schema::hasTable('posts')) Schema::rename('posts', 'adm_posts');
        if(Schema::hasTable('role_has_permissions')) Schema::rename('role_has_permissions', 'adm_role_has_permissions');
        if(Schema::hasTable('roles')) Schema::rename('roles', 'adm_roles');
        if(Schema::hasTable('users')) Schema::rename('users', 'adm_users');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('adm_data_controls', 'data_controls');
        Schema::rename('adm_model_has_permissions', 'model_has_permissions');
        Schema::rename('adm_model_has_roles', 'model_has_roles');
        Schema::rename('adm_permissions', 'permissions');
        Schema::rename('adm_personal_access_tokens', 'personal_access_tokens');
        Schema::rename('adm_posts', 'posts');
        Schema::rename('adm_role_has_permissions', 'role_has_permissions');
        Schema::rename('adm_roles', 'roles');
        Schema::rename('adm_users', 'users');
    }
};
