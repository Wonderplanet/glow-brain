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
        // mst_quests_i18nのcategory_name追加
        Schema::table('mst_quests_i18n', function (Blueprint $table) {
            $table->string('category_name')->default('')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // mst_quests_i18nのcategory_name削除
        Schema::table('mst_quests_i18n', function (Blueprint $table) {
            $table->dropColumn('category_name');
        });
    }
};
