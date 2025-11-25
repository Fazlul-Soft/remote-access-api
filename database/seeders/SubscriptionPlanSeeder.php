<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubscriptionPlan::create(['name' => 'Plan A', 'description' => '<h11>Plan A description</h11>', 'max_devices' => 2, 'price' => 9.99]);
        SubscriptionPlan::create(['name' => 'Plan B', 'description' => '<h11>Plan B description</h11>', 'max_devices' => 3, 'price' => 14.99]);
    }
}
