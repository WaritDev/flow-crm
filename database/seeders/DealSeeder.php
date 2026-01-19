<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\PipelineStage;
use App\Models\Deal;

class DealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $stages = PipelineStage::all();
        $actions = ['โทรนัดวันดูห้อง', 'ส่งใบเสนอราคาทาง LINE', 'ตามเรื่องเอกสารกู้', 'ทักทายหลังเงียบไป 2 วัน'];

        foreach ($customers as $customer) {
            $stage = $stages->random();
            Deal::create(
                [
                    'name' => 'ขาย'.$customer->name,
                    'customer_id' => $customer->id,
                    'user_id' => $customer->user_id,
                    'team_id' => $customer->team_id,
                    'stage_id' => $stage->id,
                    'next_action' => fake()->randomElement($actions),
                ]
            );
        }
    }
}
