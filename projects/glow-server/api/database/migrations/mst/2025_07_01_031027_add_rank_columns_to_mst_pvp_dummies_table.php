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
        Schema::table('mst_pvp_dummies', function (Blueprint $table) {
            $table->enum('rank_class_type', ['Bronze', 'Silver', 'Gold', 'Platinum'])->after('release_key');
            $table->unsignedInteger('rank_class_level')->default(1)->after('rank_class_type');
            $table->enum('matching_type', ['Upper', 'Same', 'Lower'])->after('rank_class_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_pvp_dummies', function (Blueprint $table) {
            $table->dropColumn(['rank_class_type', 'rank_class_level', 'matching_type']);
        });
    }
};
