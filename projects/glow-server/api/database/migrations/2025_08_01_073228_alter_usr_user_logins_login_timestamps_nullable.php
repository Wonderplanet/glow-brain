<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_user_logins', function (Blueprint $table) {
            $table->timestampTz('first_login_at')->nullable()->comment('初回ログイン日時')->change();
            $table->timestampTz('last_login_at')->nullable()->comment('最終ログイン日時')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_user_logins', function (Blueprint $table) {
            $table->timestampTz('first_login_at')->nullable(false)->comment('初回ログイン日時')->change();
            $table->timestampTz('last_login_at')->nullable(false)->comment('最終ログイン日時')->change();
        });
    }
};
