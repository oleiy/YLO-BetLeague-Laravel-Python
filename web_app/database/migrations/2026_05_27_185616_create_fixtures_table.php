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
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();

            $table->integer('api_id');

            $table->foreignId('league_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('home_team_id')
                ->constrained('teams')
                ->cascadeOnDelete();

            $table->foreignId('away_team_id')
                ->constrained('teams')
                ->cascadeOnDelete();

            $table->dateTime('match_date');

            $table->string('status', 20)
                ->default('NS');

            $table->integer('home_score')
                ->nullable();

            $table->integer('away_score')
                ->nullable();

            $table->boolean('is_settled')
                ->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
