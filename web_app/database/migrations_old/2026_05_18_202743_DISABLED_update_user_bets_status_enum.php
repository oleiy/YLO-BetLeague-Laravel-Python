<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE user_bets
            MODIFY status ENUM(
                'pending',
                'active',
                'settling',
                'won',
                'lost',
                'cancelled'
            ) DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE user_bets
            MODIFY status ENUM(
                'pending',
                'won',
                'lost',
                'cancelled'
            ) DEFAULT 'pending'
        ");
    }
};
