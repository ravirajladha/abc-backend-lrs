<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Http\Constants\AuthConstants;
use App\Models\Auth;
use App\Models\Trainer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $admin = [
            'email' => 'admin@abc.com',
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'phone_number' => '9495331232',
            'type' => AuthConstants::TYPE_ADMIN,
            'status' => AuthConstants::STATUS_ACTIVE,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ];

        Auth::insert($admin);

        // Seed trainer user
        $trainerAuth = [
            'email' => 'trainer@abc.com',
            'username' => 'trainer',
            'password' => Hash::make('abc123'),
            'phone_number' => '9876543210',
            'type' => AuthConstants::TYPE_TRAINER,
            'status' => AuthConstants::STATUS_ACTIVE,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ];

        // Insert the trainer data into the `auth` table
        $trainerAuthId = DB::table('auth')->insertGetId($trainerAuth);

        // Insert related data into the `trainer` table
        $trainer = [
            'auth_id' => $trainerAuthId,
            'name' => 'trainer',
            'emp_id' => 'emp123',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ];

        Trainer::insert($trainer); // Insert into the trainer table



        $this->call(EbookElementTypeSeeder::class);

        $this->call(FeeSeeder::class);
    }
}
