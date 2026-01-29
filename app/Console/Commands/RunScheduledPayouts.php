<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\PayoutRequest;
use App\Models\Activity;
use App\Helpers\PayPalHelper;
use App\Helpers\SafaricomDarajaHelper;
use App\Helpers\WiseHelper;

class RunScheduledPayouts extends Command
{
    protected $signature = 'payouts:run-schedule {--force : Run regardless of schedule}';
    protected $description = 'Auto-approve and/or auto-disburse payouts based on schedule settings.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $schedule = (string) (function_exists('setting') ? setting('payout_schedule', 'manual') : 'manual');
        $weekday = (int) (function_exists('setting') ? setting('payout_weekday', 5) : 5);
        $monthDay = (int) (function_exists('setting') ? setting('payout_month_day', 15) : 15);
        $autoApprove = (bool) (int) (function_exists('setting') ? setting('payout_auto_approve', 0) : 0);
        $autoDisburse = (bool) (int) (function_exists('setting') ? setting('payout_auto_disburse', 0) : 0);

        $now = now();

        if (!$force && !$this->isDue($schedule, $weekday, $monthDay, $now)) {
            $this->info('Not due based on schedule.');
            return 0;
        }

        if ($autoApprove) {
            $pending = PayoutRequest::where('status', 'pending')->get();
            foreach ($pending as $payout) {
                $payout->update([
                    'status'      => 'approved',
                    'approved_at' => now(),
                    'approved_by' => null,
                ]);
            }
            $this->info('Approved '.$pending->count().' pending payouts.');
        }

        if ($autoDisburse) {
            $eligible = PayoutRequest::with(['paymentMethod.paymentType','user'])
                ->whereIn('status', ['approved'])
                ->get();

            $sent = 0;
            foreach ($eligible as $payout) {
                if ($this->autoSend($payout)) {
                    $sent++;
                }
            }
            $this->info('Auto-disbursed '.$sent.' payouts.');
        }

        return 0;
    }

    private function isDue(string $schedule, int $weekday, int $monthDay, $now): bool
    {
        $schedule = strtolower(trim($schedule));
        if ($schedule === 'manual') {
            return false;
        }
        if ($schedule === 'weekly') {
            return (int) $now->dayOfWeek === $weekday;
        }
        if ($schedule === 'biweekly') {
            if ((int) $now->dayOfWeek !== $weekday) {
                return false;
            }
            // Use ISO week parity for simple bi-weekly cadence
            return ((int) $now->isoWeek % 2) === 0;
        }
        if ($schedule === 'monthly') {
            return (int) $now->day === $monthDay;
        }
        return false;
    }

    private function autoSend(PayoutRequest $payout): bool
    {
        if (!$payout->paymentMethod || !$payout->paymentMethod->paymentType) {
            return false;
        }

        $typeName = strtolower($payout->paymentMethod->paymentType->name);
        $account  = (string) $payout->paymentMethod->account_number;
        $amount   = (float) $payout->amount;
        $note     = 'Seller payout #'.$payout->id;
        $meta     = $payout->meta ?? [];

        try {
            if (str_contains($typeName, 'paypal')) {
                $resp = PayPalHelper::createPayout($account, $amount, $note, 'payout_'.$payout->id);
                if (($resp['status'] ?? 'error') !== 'success') {
                    Log::warning('scheduled_payouts.paypal_failed', ['payout_id' => $payout->id, 'message' => $resp['message'] ?? null]);
                    return false;
                }
                $data = $resp['data'] ?? [];
                $meta['paypal'] = $data;
                $meta['txn_reference'] = $meta['txn_reference'] ?? ($data['batch_header']['payout_batch_id'] ?? null);
            } elseif (str_contains($typeName, 'wise')) {
                $recipientId = (string) ($payout->paymentMethod->wise_recipient_id ?? '');
                $profileId = $payout->paymentMethod->wise_profile_id ?? null;
                $currency = (string) ($payout->paymentMethod->bank_currency ?? (function_exists('setting') ? setting('default_currency', 'USD') : 'USD'));
                $resp = WiseHelper::createPayout($recipientId, $amount, $currency, $note, 'payout_'.$payout->id, $profileId);
                if (($resp['status'] ?? 'error') !== 'success') {
                    Log::warning('scheduled_payouts.wise_failed', ['payout_id' => $payout->id, 'message' => $resp['message'] ?? null]);
                    return false;
                }
                $data = $resp['data'] ?? [];
                $meta['wise'] = $data;
                $meta['txn_reference'] = $meta['txn_reference'] ?? ($data['transfer']['id'] ?? null);
            } elseif (str_contains($typeName, 'mpesa') || str_contains($typeName, 'm-pesa')) {
                $msisdn = preg_replace('/\D+/', '', $account);
                if (str_starts_with($msisdn, '0') && strlen($msisdn) === 10) {
                    $msisdn = '254'.substr($msisdn,1);
                } elseif (str_starts_with($msisdn, '7') && strlen($msisdn) === 9) {
                    $msisdn = '254'.$msisdn;
                }
                if (!preg_match('/^2547\d{8}$/', $msisdn)) {
                    Log::warning('scheduled_payouts.mpesa_invalid', ['payout_id' => $payout->id]);
                    return false;
                }
                $ref = 'PAYOUT-'.$payout->id;
                $resp = SafaricomDarajaHelper::initiateB2CPayment($msisdn, $amount, $ref);
                if (($resp['status'] ?? 'error') !== 'success') {
                    Log::warning('scheduled_payouts.mpesa_failed', ['payout_id' => $payout->id, 'message' => $resp['message'] ?? null]);
                    return false;
                }
                $meta['mpesa'] = $resp['data'] ?? [];
                $meta['txn_reference'] = $meta['txn_reference'] ?? ($meta['mpesa']['ConversationID'] ?? $ref);
            } else {
                return false; // unsupported for auto-disburse
            }
        } catch (\Throwable $e) {
            Log::error('scheduled_payouts.send_failed', ['payout_id' => $payout->id, 'error' => $e->getMessage()]);
            return false;
        }

        $meta['sent_at'] = $meta['sent_at'] ?? now()->toISOString();
        $payout->status  = 'sent';
        $payout->meta    = $meta;
        $payout->save();

        Activity::create([
            'user_id'      => $payout->user_id,
            'is_read'      => false,
            'description'  => 'Your payout request of $' . number_format($payout->amount, 2) . ' has been sent and is awaiting confirmation',
            'type'         => \App\Models\Activity::TYPE_PAYOUT,
            'related_id'   => $payout->id,
            'related_type' => 'payout',
        ]);

        return true;
    }
}
