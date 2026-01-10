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
        Schema::table('adm_users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->after('password');
            $table->string('first_name')->nullable()->after('google_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('avatar')->nullable()->after('last_name');
            $table->tinyInteger('active')->default(1)->after('avatar');
            $table->timestamp('last_login_at')->nullable()->after('active');
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_users', function (Blueprint $table) {
            $table->dropColumn('google_id');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('avatar');
            $table->dropColumn('active');
            $table->dropColumn('last_login_at');
            $table->string('password')->nullable(false)->change();
        });
    }
};
