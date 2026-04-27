<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_details')->insert([
            [
                'id' => 1,
                'payment_method' => 'bkash',
                'merchant_no' => '01834793155',
                'details' => 'Please send payment through this number then send trx ID.',
                'note' => null,
                'logo' => '1765164362_biQlWKRRuJ.png',
                'is_active' => 1,
                'created_at' => Carbon::parse('2025-12-07 21:26:02'),
                'updated_at' => Carbon::parse('2025-12-08 11:50:54'),
            ],
        ]);
    }
}
