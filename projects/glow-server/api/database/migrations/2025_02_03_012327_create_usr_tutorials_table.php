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
        Schema::create('usr_tutorials', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_tutorial_id', 255)->comment('mst_tutorials.id');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_tutorial_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_tutorials');
    }
};
