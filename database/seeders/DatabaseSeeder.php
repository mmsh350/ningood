<?php

namespace Database\Seeders;

use App\Models\ClaimCount;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        SiteSetting::truncate();
        Service::truncate();
        ClaimCount::truncate();

        User::updateOrCreate(
            ['email' => 'admin@idenfy.com.ng'],
            [
                'name' => 'Idenfy Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('@passwd12345'),
                'role' => 'admin',
            ]
        );

        SiteSetting::factory(1)->create();

        foreach (Service::factory()->withCustomData() as $data) {
            Service::create($data);
        }

        ClaimCount::factory(1)->create();

        $this->call([
            ReferralBonusTableSeeder::class,
            BvnModificationSeeder::class,
        ]);
    }
}
