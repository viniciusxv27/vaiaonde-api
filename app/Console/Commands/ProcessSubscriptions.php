<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PartnerSubscription;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process subscription renewals and suspensions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing subscriptions...');

        // Process auto-renewals
        $this->processRenewals();

        // Process expired subscriptions
        $this->processExpirations();

        $this->info('Subscription processing completed.');
    }

    /**
     * Process subscription renewals
     */
    private function processRenewals()
    {
        $subscriptions = PartnerSubscription::where('status', 'active')
            ->where('auto_renew', true)
            ->whereDate('next_payment_date', '<=', Carbon::today())
            ->with(['user', 'plan'])
            ->get();

        $renewed = 0;
        $suspended = 0;

        foreach ($subscriptions as $subscription) {
            $user = $subscription->user;
            $plan = $subscription->plan;

            if ($user->wallet_balance >= $plan->price) {
                DB::beginTransaction();
                try {
                    // Debit from wallet
                    $user->wallet_balance -= $plan->price;
                    $user->save();

                    // Create transaction
                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'debit',
                        'amount' => $plan->price,
                        'description' => "Renovação de assinatura: {$plan->name}",
                        'status' => 'approved',
                        'payment_method' => 'wallet',
                    ]);

                    // Renew subscription
                    $subscription->renew();

                    DB::commit();
                    $renewed++;
                    $this->info("Renewed subscription #{$subscription->id} for user {$user->name}");
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Failed to renew subscription #{$subscription->id}: {$e->getMessage()}");
                }
            } else {
                // Insufficient balance - suspend subscription
                $subscription->suspend();
                $suspended++;
                $this->warn("Suspended subscription #{$subscription->id} for user {$user->name} - insufficient balance");
            }
        }

        $this->info("Renewals processed: {$renewed} renewed, {$suspended} suspended");
    }

    /**
     * Process expired subscriptions
     */
    private function processExpirations()
    {
        $expiredSubscriptions = PartnerSubscription::where('status', 'active')
            ->whereDate('ends_at', '<', Carbon::today())
            ->get();

        $expired = 0;

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->suspend();
            $expired++;
            $this->warn("Expired subscription #{$subscription->id}");
        }

        $this->info("Expirations processed: {$expired} subscriptions expired");
    }
}
