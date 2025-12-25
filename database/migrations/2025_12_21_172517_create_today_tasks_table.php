<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('today_tasks'); // Ensure no collision from failed runs

        Schema::create('today_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // User ID is UUID (char36) as confirmed by user
            $table->uuid('user_id');

            $table->string('task_type'); // checkin, stock, scrap, etc.
            $table->string('label')->nullable();
            $table->string('status')->default('pending'); // pending, completed
            $table->string('file_path')->nullable(); // for photo/file uploads
            $table->text('details')->nullable(); // for scrap notes
            $table->json('meta_data')->nullable(); // for checklists

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('today_tasks');
    }
};
