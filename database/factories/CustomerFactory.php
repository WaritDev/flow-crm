<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nickname' => fake()->firstName(),
            'phone_num' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'line_id' => 'line_' . fake()->userName(),
            'province' => fake()->randomElement(['กรุงเทพฯ', 'ปทุมธานี', 'นนทบุรี', 'เชียงใหม่', 'ชลบุรี']),
            'address' => fake()->address(),
            'status' => 'active',
            'business_type' => fake()->randomElement(['Retail', 'Wholesale', 'Service']),
            'tags' => json_encode(['VIP', 'New Customer']),
        ];
    }
}