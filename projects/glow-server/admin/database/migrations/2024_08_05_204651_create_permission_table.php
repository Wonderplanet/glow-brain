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
        Schema::create('adm_permission_features', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('permission_id');
            $table->string('feature_name');
            $table->unique(['permission_id', 'feature_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_permission_features');
    }
};
