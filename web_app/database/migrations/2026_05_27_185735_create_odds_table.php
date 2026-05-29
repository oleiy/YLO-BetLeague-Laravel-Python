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
        Schema::create('odds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fixture_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('team_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('market_name', 100);

            $table->string('outcome_name', 50);

            $table->decimal('specifier', 10, 2)
                ->nullable();

            $table->decimal('value', 5, 2);

            $table->decimal('probability', 5, 4)
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odds');
    }
};
