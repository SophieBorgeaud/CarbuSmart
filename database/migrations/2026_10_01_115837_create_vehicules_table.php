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
        Schema::create('vehicules', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('utilisateur_id');
            $table->unsignedBigInteger('type_carburant_id');

            $table->string('nom_vehicule');
            $table->decimal('conso_moyenne', 5, 2);
            $table->string('marque');
            $table->string('modele');
            $table->string('type_vehicule');
            $table->string('num_plaque');

            $table->boolean('est_defaut')->default(false);
            $table->boolean('est_actif')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicules');
    }
};
