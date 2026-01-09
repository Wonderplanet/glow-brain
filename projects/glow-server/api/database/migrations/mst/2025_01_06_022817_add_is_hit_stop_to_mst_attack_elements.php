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
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->tinyInteger('is_hit_stop')->default(0)->after('hit_parameter2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->dropColumn('is_hit_stop');
        });
    }
};
