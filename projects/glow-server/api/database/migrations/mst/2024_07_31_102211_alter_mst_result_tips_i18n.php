<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_result_tips_i18n', function (Blueprint $table) {
            $table->renameColumn('userLevel', 'user_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_result_tips_i18n', function (Blueprint $table) {
            $table->renameColumn('user_level', 'userLevel');
        });
    }
};
