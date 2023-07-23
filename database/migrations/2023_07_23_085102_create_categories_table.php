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
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('big_store_id')->unsigned();
            $table->foreign('big_store_id')->references('id')->on('big_stores')->onDelete('cascade');    
            $table->string('title')->nullable();
            $table->string('status')->nullable();
            $table->string('photoFileName')->nullable();
            $table->string('photoFilePath')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
