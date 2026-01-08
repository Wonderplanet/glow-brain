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
            $table->renameColumn('mst_outpost_enhancement_Id', 'mst_outpost_enhancement_id')
                ->comment('mst_outpost_enhancements.id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_dummy_outposts', function (Blueprint $table) {
            $table->renameColumn('mst_outpost_enhancement_id', 'mst_outpost_enhancement_Id')
                ->comment('mst_outpost_enhancements.id');
        });
    }
};
