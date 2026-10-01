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
        Schema::create('favoris', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('utilisateur_id');
            $table->string('station_strapi_id');

            $table->timestamps();

            $table->unique(
                ['utilisateur_id', 'station_strapi_id'],
                'uq_favori_utilisateur_station'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favoris');
    }
};
