<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $status = [
            'Editing',
            'Pending',
            'Approved',
        ];

        $targetTypes = [
            'All',
            'UserId',
            'MyId',
        ];

        $createdTypes = [
            'Unset',
            'Started',
            'Ended',
            'Both',
        ];

        Schema::create('adm_message_distribution_inputs', function (Blueprint $table) use ($status, $targetTypes, $createdTypes) {
            $table->id();
            $table->enum('create_status', $status);
            $table->string('opr_message_id', 255);
            $table->text('opr_messages_txt')->nullable();
            $table->text('opr_message_distributions_txt')->nullable();
            $table->text('opr_message_i18ns_txt')->nullable();
            $table->enum('target_type', $targetTypes);
            $table->text('target_ids_txt')->nullable();
            $table->enum('account_created_type', $createdTypes);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_message_distribution_inputs');
    }
};
