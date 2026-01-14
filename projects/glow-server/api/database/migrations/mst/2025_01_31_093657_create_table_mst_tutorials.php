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
        Schema::create('mst_tutorials', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->enum('type', ['Intro', 'Main', 'Free'])->default('Intro')->comment('チュートリアルタイプ');
            $table->integer('sort_order')->default(0)->comment('各チュートリアルコンテンツの順番');
            $table->string('function_name')->default('')->unique()->comment('チュートリアル名');
            $table->string('condition_type')->default('')->comment('フリーパートの開放条件種別');
            $table->string('condition_value')->default('')->comment('フリーパートの開放条件値');
            $table->timestampTz('start_at');
            $table->timestampTz('end_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_tutorials');
    }
};
