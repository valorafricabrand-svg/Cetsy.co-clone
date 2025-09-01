<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Payout;
use App\Models\Bank;
use App\Models\WithdrawalRequest;
use App\Models\AccountBalance;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Business;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Helpers\SafaricomDarajaHelper;

class ProcessApprovedPayouts extends Command
{
    protected $signature = 'process:approved-payouts';
    protected $description = 'Process and update approved payouts to sent or failed, and handle refunds if necessary.';

    public function handle()
    {
        $approvedPayouts = Payout::where('status', 'approved')->get();

        if ($approvedPayouts->isEmpty()) {
            $this->info("No approved payouts found to update.");
            return 0;
        }

        foreach ($approvedPayouts as $payout) {
            $utility = AccountBalance::where('account_name', 'Utility Account')->first();

            if (!$utility) {
                Log::error("Utility Account not found.");
                $this->markAsFailedAndRefund($payout, "Utility account missing");
                continue;
            }

            if ($utility->available_balance < $payout->Amount) {
                Log::error("Insufficient funds for payout ID {$payout->id}. Required: {$payout->Amount}, Available: {$utility->available_balance}");
                $this->markAsFailedAndRefund($payout, "Insufficient utility balance");
                continue;
            }

            $withdrawalRequest = WithdrawalRequest::find($payout->withdrawal_request_id);

            if (!$withdrawalRequest) {
                Log::error("WithdrawalRequest not found for payout ID {$payout->id}.");
                $this->markAsFailedAndRefund($payout, "Missing withdrawal request");
                continue;
            }

            try {
                $response = null;

                switch ($payout->payment_type) {
                    case 'mpesa_number':
                        $response = SafaricomDarajaHelper::initiateB2CPayment(
                            $payout->MobileNumber,
                            (int) $payout->Amount,
                            $payout->PurposeOfPayment
                        );
                        break;

                    case 'paybill_number':
                        $response = SafaricomDarajaHelper::initiateB2BPayment(
                            $withdrawalRequest->paybill_number,
                            (int) $payout->Amount,
                            $payout->PurposeOfPayment,
                            $withdrawalRequest->paybill_account
                        );
                        break;

                    case 'till_number':
                        $response = SafaricomDarajaHelper::initiateB2BTillPayment(
                            $withdrawalRequest->till_number,
                            (int) $payout->Amount,
                            $payout->PurposeOfPayment
                        );
                        break;

                    case 'bank':
                        $bank = Bank::find($withdrawalRequest->bank_id);
                        if (!$bank || !$bank->paybill_number) {
                            Log::error("Bank paybill number missing for payout ID {$payout->id}.");
                            $this->markAsFailedAndRefund($payout, "Missing bank paybill");
                            continue 2;
                        }

                        $response = SafaricomDarajaHelper::initiateB2BPayment(
                            $bank->paybill_number,
                            (int) $payout->Amount,
                            $payout->PurposeOfPayment,
                            $withdrawalRequest->account_number
                        );
                        break;

                    default:
                        Log::error("Unknown payment type '{$payout->payment_type}' for payout ID {$payout->id}.");
                        $this->markAsFailedAndRefund($payout, "Unknown payment type");
                        continue 2;
                }

                if (isset($response['status']) && $response['status'] === 'success') {
                    $payout->update(['status' => 'sent']);
                    Log::info("Payout ID {$payout->id} marked as sent. Response: " . json_encode($response));

  

// Fetch the business for this payout
$business = Business::find($payout->business_id);

if (! empty($business->phone)) {
    $now     = Carbon::now();
    $balance = wallet($business->id); // your helper to get current wallet balance

    $msg = sprintf(
        "Withdrawal confirmation\n" .
        "You have withdrawn KES %s from your Fedhatrac Wallet on %s at %s. New Wallet Balance: KES %s Ref: %s\n" .
        "If you did not authorise this, please contact support immediately.",
        number_format($payout->Amount, 2),
        $now->format('d F Y'),
        $now->format('g:i A'),
        number_format($balance, 2),
        $payout->TransID   // or whatever your payout reference field is
    );

    sendsms($business->phone, $msg);
}

                } else {
                    $this->markAsFailedAndRefund($payout, "Daraja response error", $response);
                }
            } catch (\Exception $e) {
                $this->markAsFailedAndRefund($payout, "Exception: " . $e->getMessage());
            }
        }

        $this->info("Processed {$approvedPayouts->count()} approved payouts.");
        return 0;
    }

    /**
     * Mark payout as failed and refund amount to user's wallet.
     */
    private function markAsFailedAndRefund(Payout $payout, string $reason, $response = null)
    {
        $payout->update(['status' => 'failed']);
        Log::error("Payout ID {$payout->id} failed. Reason: {$reason}. Response: " . json_encode($response));

        $account = Account::where('name', 'Cash in Wallet')
            ->where('business_id', $payout->business_id)
            ->first();

if($payout->payout_type == 'single'){
      $total_amount = get_charge($payout->Amount) + $payout->Amount;  
  } else{
    $total_amount = 35 + $payout->Amount;
  }

      

        $payment = Payment::create([
            'business_id'     => $payout->business_id,
            'user_id'         => $payout->user_id,
            'payout_id'       => $payout->id,
            'amount'          => $total_amount,
            'description'     => "Refund for failed payout ID {$payout->id}",
            'payment_source'  => 'Refund',
            'status'          => '1',
            'currency'        => 'KES',
            'balance'         => 0,
            'TransactionDate' => now(),
        ]);

        if ($account) {
            Transaction::create([
                'amount'           => $payment->amount,
                'description'      => "Refund: Payment ID {$payment->id}",
                'transaction_type' => 'credit',
                'date'             => now(),
                'account_id'       => $account->id,
                'payment_id'       => $payment->id,
                'business_id'      => $payment->business_id,
                'currency'         => 'KES',
            ]);
        }
    }
}
