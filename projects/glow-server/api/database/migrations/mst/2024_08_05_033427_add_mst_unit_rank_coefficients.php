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
        Schema::create('mst_unit_rank_coefficients', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedInteger('rank')->comment('ユニットのランク');
            $table->integer('coefficient')->comment('係数');
            $table->bigInteger('release_key')->default(1);
        });
        Schema::table('mst_unit_grade_coefficients', function (Blueprint $table) {
            $table->enum('unit_label', [
                'DropN',
                'DropR',
                'DropSR',
                'DropSSR',
                'PremiumN',
                'PremiumR',
                'PremiumSR',
                'PremiumSSR',
                'PremiumUR'
            ])->default('DropN')->after('id')->comment('ユニットラベル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_unit_rank_coefficients');
        Schema::table('mst_unit_grade_coefficients', function (Blueprint $table) {
            $table->dropColumn('unit_label');
        });
    }
};
