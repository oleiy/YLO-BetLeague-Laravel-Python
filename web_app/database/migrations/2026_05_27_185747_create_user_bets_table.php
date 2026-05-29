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
        Schema::create('user_bets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('fixture_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('total_odd', 10, 2);

            $table->integer('stake');

            $table->decimal('potential_win', 10, 2);

            $table->text('analysis')
                ->nullable();

            $table->boolean('is_betbuilder')
                ->default(true);

            $table->enum('status', [
                'pending',
                'active',
                'settling',
                'won',
                'lost',
                'cancelled'
            ])->default('pending');

            $table->dateTime('settled_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bets');
    }
};
