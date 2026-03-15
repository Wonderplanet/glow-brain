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
        Schema::create('mst_artwork_grade_up_costs', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->string('mst_artwork_grade_up_id')->comment('mst_artwork_grade_ups.id');
            $table->enum('resource_type', ['Item'])->comment('リソースタイプ');
            $table->string('resource_id')->nullable()->comment('リソースID');
            $table->unsignedInteger('resource_amount')->comment('リソース数量');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');

            $table->index(['mst_artwork_grade_up_id']);
            $table->comment('原画グレードアップコストマスタ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_artwork_grade_up_costs');
    }
};
