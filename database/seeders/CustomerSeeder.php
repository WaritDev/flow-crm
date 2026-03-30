<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Database\Support\ThaiDemoNames;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'sales')->get();
        $provinces = ['กรุงเทพฯ', 'นนทบุรี', 'ปทุมธานี', 'ชลบุรี', 'เชียงใหม่'];
        $pairs = ThaiDemoNames::pairs();
        $idx = 0;

        foreach ($users as $user) {
            for ($i = 0; $i < 5; $i++) {
                $p = $pairs[$idx % count($pairs)];
                $idx++;

                Customer::create([
                    'organization_id' => $user->organization_id,
                    'team_id' => $user->team_id,
                    'user_id' => $user->id,
                    'name' => $p['name'],
                    'nickname' => $p['nickname'],
                    'line_id' => 'line_u'.$user->id.'_c'.$idx,
                    'province' => fake()->randomElement($provinces),
                    'status' => 'active',
                ]);
            }
        }
    }
}
