<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Deal;
use Illuminate\Database\Seeder;
use phpDocumentor\Reflection\Element;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deals = Deal::all();

        foreach ($deals as $deal) {
            $historyCount = fake()->numberBetween(1, 3);
            for ($i = 0; $i < $historyCount; $i++) {
                Activity::create([
                    'deal_id'       => $deal->id,
                    'customer_id'   => $deal->customer_id,
                    'user_id'       => $deal->user_id,
                    'team_id'       => $deal->team_id,
                    'name'          => fake()->randomElement(['ทักทายครั้งแรกผ่าน LINE', 'ส่งแค็ตตาล็อกสินค้า', 'โทรสอบถามความสนใจ', 'บันทึกบันทึกจากการคุย']),
                    'description'   => fake()->sentence(),
                    'activity_type' => fake()->randomElement(['line', 'call', 'note', 'message']), // เน้นประเภทที่ใช้บ่อยในไทย [3, 4]
                    'priority'      => 1,
                    'is_completed'  => true, // ทำเสร็จแล้ว
                    'created_at'    => now()->subDays(fake()->numberBetween(1, 10)),
                ]);
            }


            if ($deal->next_action) {
                Activity::create([
                    'name' => $deal->next_action,
                    'description'   => 'กิจกรรมที่ต้องทำตามแผนการขาย',
                    'activity_type' => fake()->randomElement(['task', 'call', 'line']),
                    'priority'      => fake()->numberBetween(1, 3), //
                    'is_completed'  => false, //
                    'created_at'    => now(),
                    'customer_id'   => $deal->customer_id,
                    'user_id'       => $deal->user_id,
                    'team_id'       => $deal->team_id,
                ]);
            }
        }
    }
}
