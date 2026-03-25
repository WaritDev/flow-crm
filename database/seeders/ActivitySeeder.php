<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Deal;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deals = Deal::all();
        $todayStart = now()->startOfDay();

        foreach ($deals as $deal) {
            // ประวัติกิจกรรม (ทำเสร็จแล้ว) เพื่อให้ demo ดูมีความสมจริงบนประวัติ/สถิติ
            $historyCount = fake()->numberBetween(1, 2);
            for ($i = 0; $i < $historyCount; $i++) {
                $createdAt = now()->subDays(fake()->numberBetween(1, 10))->setTime(
                    fake()->numberBetween(9, 17),
                    fake()->numberBetween(0, 59)
                );

                Activity::create([
                    'deal_id' => $deal->id,
                    'customer_id'   => $deal->customer_id,
                    'user_id'       => $deal->user_id,
                    'team_id'       => $deal->team_id,
                    'name'          => fake()->randomElement(['ทักทายครั้งแรกผ่าน LINE', 'ส่งแค็ตตาล็อกสินค้า', 'โทรสอบถามความสนใจ', 'บันทึกบันทึกจากการคุย']),
                    'description'   => fake()->sentence(),
                    'activity_type' => fake()->randomElement(['line', 'call', 'note', 'message']), // เน้นประเภทที่ใช้บ่อยในไทย [3, 4]
                    'priority'      => 1,
                    'is_completed'  => true, // ทำเสร็จแล้ว
                    'created_at'    => $createdAt,
                ]);
            }


            // งานต่อไป (ทำยังไม่เสร็จ) สำหรับ Action Stream
            $dueDate = $deal->next_action_date;
            if (!$dueDate) {
                continue;
            }

            // ลดจำนวนงานที่ยังไม่เสร็จให้เหมาะกับ demo (ไม่ให้ล้นเกินไป)
            if (fake()->numberBetween(1, 100) > 65) {
                continue;
            }

            $isOverdue = $dueDate->lt($todayStart);
            $isToday = $dueDate->isSameDay($todayStart);

            $priority = $isOverdue ? 3 : ($isToday ? 2 : 1);

            $activityType = 'task';
            $stageName = $deal->stage?->name ?? '';
            if (str_contains($stageName, 'สนใจ') || str_contains($stageName, 'Prospect') || str_contains($deal->next_action, 'LINE')) {
                $activityType = 'line';
            } elseif (str_contains($stageName, 'เจรจา') || str_contains($stageName, 'Negotiation')) {
                $activityType = 'call';
            }

            $createdAt = $dueDate->copy()->setTime(
                fake()->numberBetween(9, 17),
                fake()->numberBetween(0, 59)
            );

            Activity::create([
                'deal_id' => $deal->id,
                'name' => $deal->next_action,
                'description' => 'กิจกรรมที่ต้องทำตามแผนการขาย',
                'activity_type' => $activityType,
                'priority' => $priority,
                'is_completed' => false,
                'created_at' => $createdAt,
                'customer_id' => $deal->customer_id,
                'user_id' => $deal->user_id,
                'team_id' => $deal->team_id,
            ]);
        }
    }
}
