<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'sales')->get();
        $provinces = ['กรุงเทพฯ', 'นนทบุรี', 'ปทุมธานี', 'ชลบุรี', 'เชียงใหม่'];

        foreach ($users as $user) {
            for ($i = 0; $i < 5; $i++) {
                Customer::create([
                    'organization_id' => $user->organization_id,
                    'team_id' => $user->team_id,
                    'user_id' => $user->id,
                    'name' => fake()->name(),
                    'nickname' => fake()->firstName(), // ชื่อเล่นคนไทย
                    'line_id' => 'line_'.fake()->userName(),
                    'province' => fake()->randomElement($provinces),
                    'status' => 'active',
//                    'tags' => ''
                ]);
            }
        }
    }
}
