<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateOldUsers extends Command
{
    protected $signature = 'users:migrate-old';

    protected $description = 'Migrate users from old users table to new users table';

    public function handle()
    {
        $oldUsers = DB::table('old_users')->get(); // change table name if needed

        $inserted = 0;
        $skipped = 0;

        foreach ($oldUsers as $old) {
            $exists = DB::table('users')->where('email', $old->email)->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            // Insert into users table
            DB::table('users')->insert([
                'name' => $old->firstname.' '.$old->lastname,
                'email' => $old->email,
                'password' => bcrypt($old->password),
                'old_balance' => $old->balance,
                'phone_number' => $old->number,
                'profile_pic' => $old->image,
                'wallet_is_created' => 0, // default value
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $userId = DB::getPdo()->lastInsertId();

            $walletExists = DB::table('wallets')->where('user_id', $userId)->exists();

            if (! $walletExists) {
                DB::table('wallets')->insert([
                    'user_id' => $userId,
                    'balance' => $old->balance,
                    'deposit' => $old->balance,
                    'bonus' => 0.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // ✅ Mark wallet as created
                DB::table('users')->where('id', $userId)->update([
                    'wallet_is_created' => 1,
                    'has_moved' => 1,
                ]);
            }

            $inserted++;
        }

        $this->info("✅ Migration complete. Inserted: $inserted | Skipped (existing): $skipped");
    }
}
