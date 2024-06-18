<?php 
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Auth;
use App\Models\School;
use Illuminate\Support\Facades\Hash;
use App\Http\Constants\AuthConstants;

class SchoolSeeder extends Seeder
{
    public function run()
    {
        $auth = Auth::create([
            'email' => 'public_school@abc.com',
            'username' => 'Public School',
            'password' => Hash::make('abc123'),
            'phone_number' => '1234567890',
            'type' => AuthConstants::TYPE_SCHOOL, // Adjust if necessary
            'status' => AuthConstants::STATUS_ACTIVE,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        School::create([
            'auth_id' => $auth->id,
            'name' => 'AV Public School',
            'phone_number' => '1234567890',
            'school_type'=> 0,
            'type' => '1',
        ]);
    }
}
