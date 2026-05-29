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
        Schema::create('fixture_statistics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fixture_id')
                ->unique()
                ->constrained('fixtures')
                ->cascadeOnDelete();

            $table->integer('home_goals')->default(0);
            $table->integer('away_goals')->default(0);

            $table->integer('home_corners')->default(0);
            $table->integer('away_corners')->default(0);

            $table->integer('home_yellow_cards')->default(0);
            $table->integer('away_yellow_cards')->default(0);

            $table->integer('home_red_cards')->default(0);
            $table->integer('away_red_cards')->default(0);

            $table->integer('home_shots_on_goal')->default(0);
            $table->integer('away_shots_on_goal')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixture_statistics');
    }
};
