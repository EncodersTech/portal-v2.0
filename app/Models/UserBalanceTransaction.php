<?php

namespace App\Models;

use App\Traits\Excludable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBalanceTransaction extends Model
{
    use Excludable;

    protected $guarded = ['id'];

    protected $dates = ['clears_on'];

    public const BALANCE_TRANSACTION_TYPE_REGISTRATION_REVENUE = 1;
    public const BALANCE_TRANSACTION_TYPE_DWOLLA_VERIFICATION_FEE = 2;
    public const BALANCE_TRANSACTION_TYPE_ADJUSTMENT = 3;
    public const BALANCE_TRANSACTION_TYPE_REGISTRATION_PAYMENT = 4;
    public const BALANCE_TRANSACTION_TYPE_REGISTRATION_CHECK = 5;
    public const BALANCE_TRANSACTION_TYPE_WITHDRAWAL = 99;
    public const BALANCE_TRANSACTION_TYPE_ADMIN = 6;

    public const BALANCE_TRANSACTION_STATUS_PENDING = 1;
    public const BALANCE_TRANSACTION_STATUS_CLEARED = 2;
    public const BALANCE_TRANSACTION_STATUS_UNCONFIRMED = 3;
    public const BALANCE_TRANSACTION_STATUS_FAILED = 4;

    public const TRANSFER_REASON = [
        'R01' => 'Insufficient Funds',
        'R02' => 'Bank Account Closed',
        'R03' => 'No Account/Unable to Locate Account',
        'R04' => 'Invalid Bank Account Number Structure',
        'R05' => 'Unauthorized debit to consumer account',
        'R07' => 'Authorization Revoked by Customer',
        'R08' => 'Payment Stopped',
        'R09' => 'Uncollected Funds',
        'R10' => 'Customer Advises Originator is Not Known
        to Receiver and/or Originator is Not Authorized by Receiver to Debit Receiver’s Account',
        'R11' => 'Customer Advises Entry Not in Accordance with the Terms of the Authorization.',
        'R16' => 'Bank Account Frozen',
        'R20' => 'Non-Transaction Account',
        'R29' => 'Corporate Customer Advises Not Authorized',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sourceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'source_user_id');
    }

    public function destinationUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destination_user_id');
    }

    public function related()
    {
        return $this->morphTo();
    }
}
