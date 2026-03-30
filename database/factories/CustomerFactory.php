<?php

namespace Database\Factories;

use Database\Support\ThaiDemoNames;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        $p = fake()->randomElement(ThaiDemoNames::pairs());

        return [
            'name' => $p['name'],
            'nickname' => $p['nickname'],
            'phone_num' => fake()->numerify('08########'),
            'email' => fake()->unique()->safeEmail(),
            'line_id' => 'line_'.strtolower(preg_replace('/\s+/', '_', $p['nickname'])).'_'.fake()->unique()->numerify('####'),
            'province' => fake()->randomElement(['กรุงเทพฯ', 'ปทุมธานี', 'นนทบุรี', 'เชียงใหม่', 'ชลบุรี']),
            'address' => fake()->address(),
            'status' => 'active',
            'business_type' => fake()->randomElement(['Retail', 'Wholesale', 'Service']),
            'tags' => json_encode(['VIP', 'New Customer']),
        ];
    }
}
