<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // Convert user_id columns to native uuid type (assumes values are valid UUIDs)
            DB::statement("ALTER TABLE oauth_access_tokens ALTER COLUMN user_id TYPE uuid USING user_id::uuid;");
            DB::statement("ALTER TABLE oauth_clients ALTER COLUMN user_id TYPE uuid USING user_id::uuid;");
        } else {
            // For non-postgres, keep as varchar(36) — nothing to do
            // Logging for visibility
            logger()->info('Skipping uuid conversion: non-postgres driver '.$driver);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE oauth_access_tokens ALTER COLUMN user_id TYPE varchar USING user_id::text;");
            DB::statement("ALTER TABLE oauth_clients ALTER COLUMN user_id TYPE varchar USING user_id::text;");
        }
    }
};
