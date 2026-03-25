<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Organization;
use App\Models\PipelineStage;
use App\Models\PipelineTemplate;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ManagerDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::create([
            'name' => 'Kasetsart Innovation Hub',
            'slug' => 'ku-innovation',
            'size' => '11-50',
            'invite_code' => 'KU-' . strtoupper(Str::random(6)),
            'description' => 'Web3 and Software Solutions provider',
        ]);

        $now = Carbon::now();
        $monthsToGenerate = 3;

        for ($i = $monthsToGenerate; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $org->targets()->create([
                'amount' => rand(4500000, 6000000), 
                'type' => 'revenue',
                'month' => $date->month,
                'year' => $date->year,
                'description' => 'เป้าหมายรายได้รวมบริษัทเดือน ' . $date->format('M Y')
            ]);
        }

        $template = PipelineTemplate::create([
            'name' => 'Standard Sales Process',
            'industry' => 'Software',
            'is_default' => true
        ]);

        $stages = [
            ['name' => 'สนใจ (Prospect)', 'is_won' => false, 'pos' => 1],
            ['name' => 'ติดต่อแล้ว (Contacted)', 'is_won' => false, 'pos' => 2],
            ['name' => 'เสนอราคา (Quoted)', 'is_won' => false, 'pos' => 3],
            ['name' => 'เจรจาต่อรอง (Negotiation)', 'is_won' => false, 'pos' => 4],
            ['name' => 'ปิดการขาย (Won)', 'is_won' => true, 'pos' => 5],
            ['name' => 'สูญเสีย (Lost)', 'is_won' => false, 'pos' => 6],
        ];

        $stageModels = [];
        foreach ($stages as $s) {
            $stageModels[] = PipelineStage::create([
                'template_id' => $template->id,
                'name' => $s['name'],
                'position' => $s['pos'],
                'is_won' => $s['is_won'],
            ]);
        }

        $team = Team::create([
            'name' => 'Core Sales Team',
            'organization_id' => $org->id,
            'template_id' => $template->id,
        ]);

        $manager = User::create([
            'name' => 'Warit Manager',
            'email' => 'manager@flowcrm.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'organization_id' => $org->id,
        ]);

        $salesNames = ['Alice', 'Bob', 'Charlie', 'David'];
        $salesReps = [];
        foreach ($salesNames as $name) {
            $sales = User::create([
                'name' => $name,
                'email' => strtolower($name) . '@flowcrm.com',
                'password' => Hash::make('password'),
                'role' => 'sales',
                'organization_id' => $org->id,
                'team_id' => $team->id,
            ]);

            for ($i = $monthsToGenerate; $i >= 0; $i--) {
                $date = $now->copy()->subMonths($i);
                $sales->targets()->create([
                    'amount' => rand(800000, 1500000),
                    'type' => 'revenue',
                    'month' => $date->month,
                    'year' => $date->year,
                    'description' => 'เป้าหมายยอดขายส่วนบุคคลเดือน ' . $date->format('M Y')
                ]);
            }

            $salesReps[] = $sales;
        }

        foreach ($salesReps as $sales) {
            $customers = Customer::factory(15)->create([
                'organization_id' => $org->id,
                'team_id' => $team->id,
                'user_id' => $sales->id,
            ]);

            foreach ($customers as $customer) {
                $numDeals = rand(1, 3);
                for ($i = 0; $i < $numDeals; $i++) {
                    $randomStage = $stageModels[array_rand($stageModels)];
                    $isWon = $randomStage->is_won;
                    $dealDate = Carbon::now()->subDays(rand(0, 180));
                    $wonAt = $isWon ? $dealDate->copy()->addDays(rand(5, 30)) : null;
                    $lostAt = $randomStage->name === 'สูญเสีย (Lost)' ? $dealDate->copy()->addDays(rand(5, 30)) : null;

                    $deal = Deal::create([
                        'organization_id' => $org->id,
                        'customer_id' => $customer->id,
                        'user_id' => $sales->id,
                        'team_id' => $team->id,
                        'stage_id' => $randomStage->id,
                        'name' => 'Deal for ' . $customer->name,
                        'value' => rand(30000, 450000),
                        'expected_close_date' => $dealDate->copy()->addDays(rand(10, 45)),
                        'won_at' => $wonAt,
                        'lost_at' => $lostAt,
                        'created_at' => $dealDate,
                        'updated_at' => $wonAt ?? ($lostAt ?? $dealDate->copy()->addDays(rand(1, 15))),
                    ]);

                    Activity::create([
                        'deal_id' => $deal->id,
                        'customer_id' => $customer->id,
                        'user_id' => $sales->id,
                        'team_id' => $team->id,
                        'name' => 'Follow up with ' . $customer->nickname,
                        'activity_type' => ['call', 'line', 'meeting', 'email'][rand(0, 3)],
                        'priority' => rand(1, 3),
                        'is_completed' => (bool)rand(0, 1),
                        'created_at' => $dealDate->copy()->addDays(rand(0, 10)), 
                    ]);
                }
            }
        }
    }
}