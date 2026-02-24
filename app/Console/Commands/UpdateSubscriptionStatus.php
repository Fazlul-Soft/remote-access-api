<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class UpdateSubscriptionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-subscription-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Set 'active' to 'expired' if expires_at has passed
        Subscription::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        // 2. Set 'expired' to 'locked' if data_hidden_at has passed
        $toLock = Subscription::where('status', 'expired')
            ->where('data_hidden_at', '<=', now())
            ->get();

        foreach ($toLock as $sub) {
            $sub->update(['status' => 'locked']);

            // Disable the user's data access
            $sub->user->update([
                'is_active' => false,
                'subscription_plan_id' => null,
            ]);
        }

        $this->info('Subscription statuses synced successfully.');
    }
}
