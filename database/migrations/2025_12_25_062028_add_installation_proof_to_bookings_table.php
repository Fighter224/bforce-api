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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('alternator_image')->nullable();
            $table->string('starter_image')->nullable();
            $table->string('odometer_image')->nullable();
            $table->string('plate_image')->nullable();
            $table->string('battery_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'alternator_image',
                'starter_image',
                'odometer_image',
                'plate_image',
                'battery_image'
            ]);
        });
    }
};
