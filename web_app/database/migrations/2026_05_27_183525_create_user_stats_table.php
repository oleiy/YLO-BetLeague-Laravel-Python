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
        Schema::create('user_stats', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->integer('balance_points')
                ->default(1000);

            $table->integer('total_bets')
                ->default(0);

            $table->integer('won_bets')
                ->default(0);

            $table->integer('lost_bets')
                ->default(0);

            $table->decimal('accuracy_rate', 5, 2)
                ->default(0);

            $table->decimal('yield', 5, 2)
                ->default(0);

            $table->integer('current_streak')
                ->default(0);

            $table->integer('best_streak')
                ->default(0);

            $table->integer('referral_count')
                ->default(0);

            $table->boolean('is_banned')
                ->default(false);

            $table->dateTime('ban_until')
                ->nullable();

            $table->integer('daily_login_streak')
                ->default(0);

            $table->date('last_daily_reward')
                ->nullable();

            $table->integer('total_referral_earned')
                ->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
