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
            // Cast existing bigint user_id to text so we can store UUIDs
            DB::statement("ALTER TABLE oauth_access_tokens ALTER COLUMN user_id TYPE varchar USING user_id::text;");
            DB::statement("ALTER TABLE oauth_clients ALTER COLUMN user_id TYPE varchar USING user_id::text;");
        } else {
            // MySQL / SQLite: change column to VARCHAR(36)
            // For MySQL we can use MODIFY, for SQLite we'll attempt a safe fallback using Schema facade
            try {
                if ($driver === 'mysql') {
                    DB::statement("ALTER TABLE oauth_access_tokens MODIFY user_id varchar(36) NULL;");
                    DB::statement("ALTER TABLE oauth_clients MODIFY user_id varchar(36) NULL;");
                } else {
                    Schema::table('oauth_access_tokens', function ($table) {
                        $table->string('user_id', 36)->nullable()->change();
                    });
                    Schema::table('oauth_clients', function ($table) {
                        $table->string('user_id', 36)->nullable()->change();
                    });
                }
            } catch (\Exception $e) {
                // Best-effort: if change() is not supported (SQLite without doctrine/dbal)
                // we silently continue; developer can run a manual migration if needed.
                logger()->warning('Could not alter oauth user_id columns automatically: '.$e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // Try to cast back to bigint where possible (may fail if UUIDs are present).
            DB::statement("ALTER TABLE oauth_access_tokens ALTER COLUMN user_id TYPE bigint USING (CASE WHEN user_id ~ '^[0-9]+$' THEN user_id::bigint ELSE NULL END);");
            DB::statement("ALTER TABLE oauth_clients ALTER COLUMN user_id TYPE bigint USING (CASE WHEN user_id ~ '^[0-9]+$' THEN user_id::bigint ELSE NULL END);");
        } else {
            try {
                if ($driver === 'mysql') {
                    DB::statement("ALTER TABLE oauth_access_tokens MODIFY user_id bigint NULL;");
                    DB::statement("ALTER TABLE oauth_clients MODIFY user_id bigint NULL;");
                } else {
                    Schema::table('oauth_access_tokens', function ($table) {
                        $table->unsignedBigInteger('user_id')->nullable()->change();
                    });
                    Schema::table('oauth_clients', function ($table) {
                        $table->unsignedBigInteger('user_id')->nullable()->change();
                    });
                }
            } catch (\Exception $e) {
                logger()->warning('Could not revert oauth user_id columns automatically: '.$e->getMessage());
            }
        }
    }
};
