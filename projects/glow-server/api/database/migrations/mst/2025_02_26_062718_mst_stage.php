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
        //
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->timestampTz('start_at')->comment('ステージ公開開始日時')->after('sort_order');
        });

        Schema::table('mst_stages', function (Blueprint $table) {
            $table->timestampTz('end_at')->comment('ステージ公開終了日時')->after('start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('mst_stages', function (Blueprint $table) {
            $table->dropColumn('start_at');
        });

        Schema::table('mst_stages', function (Blueprint $table) {
            $table->dropColumn('end_at');
        });
    }
};
