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
        Schema::table('mst_dummy_outposts', function (Blueprint $table) {
            $table->dropUnique('mst_dummy_outposts_mst_dummy_user_id_unique');
            $table->unique(['mst_dummy_user_id', 'mst_outpost_enhancement_id'], 'mst_dummy_user_outpost_enhancement_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_dummy_outposts', function (Blueprint $table) {
            $table->dropUnique('mst_dummy_user_outpost_enhancement_unique');
            $table->unique('mst_dummy_user_id');
        });
    }
};
