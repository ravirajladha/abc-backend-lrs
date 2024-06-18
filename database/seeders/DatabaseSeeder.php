<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Http\Constants\AuthConstants;
use App\Models\Auth;

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
        
        $this->call(SectionSeeder::class);
        $this->call(EbookElementTypeSeeder::class);
        $this->call(SchoolSeeder::class);
    }
}
