<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_messages', function (Blueprint $table) {
            $table->renameColumn('opr_message_id', 'mng_message_id');
            $table->string('mng_message_id')->nullable()->comment('mng_messages.id')->change();
        });

        Schema::table('usr_temporary_individual_messages', function (Blueprint $table) {
            $table->renameColumn('opr_message_id', 'mng_message_id');
            $table->string('mng_message_id')->comment('mng_messages.id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_messages', function (Blueprint $table) {
            $table->renameColumn('mng_message_id', 'opr_message_id');
        });

        Schema::table('usr_temporary_individual_messages', function (Blueprint $table) {
            $table->renameColumn('mng_message_id', 'opr_message_id');
        });
    }
};
