<?php

namespace App\Models;

use App\Traits\Excludable;
use Illuminate\Database\Eloquent\Model;

class DwollaVerificationAttempt extends Model
{
    use Excludable;

    protected $guarded = ['id'];

    public const STATUS_PENDING = 1;
    public const STATUS_FAILED = 2;
    public const STATUS_SUCCEEDED = 3;

    public const DOCUMENT_TYPE_PASSPORT = 'passport';
    public const DOCUMENT_TYPE_LICENSE = 'license';
    public const DOCUMENT_TYPE_ID = 'idCard';

    public const DOCUMENT_TYPE_STRINGS = [
        self::DOCUMENT_TYPE_ID => 'ID Card',
        self::DOCUMENT_TYPE_LICENSE => 'Driver\'s License',
        self::DOCUMENT_TYPE_PASSPORT => 'Passport',
    ];

    public const INFO_RULES = [
        'date_of_birth' => ['required', 'date_format:Y-m-d', 'before:-18 years'],
        'ssn' => ['required', 'string', 'max:10'],
        'addr_1' => ['required', 'string', 'max:255'],
        'addr_2' => ['nullable', 'string', 'max:255'],
        'city' => ['required', 'string', 'max:255'],
        'state' => ['required', 'string', 'size:2'],
        'zipcode' => ['required', 'regex:/^\d{5}([ \-]\d{4})?$/'],
    ];

    public const ALLOWED_DOCUMENT_TYPES = ['jpeg', 'png', 'jpg', 'pdf'];

    public static function getVerificationDocumentRules() {
        return [
            'type' => [
                'required',
                'in:' . implode(',', array_keys(self::DOCUMENT_TYPE_STRINGS))
            ],
            'document' => [
                'required',
                'file',
                'mimes:' . implode(',', self::ALLOWED_DOCUMENT_TYPES),
                'max:' . config('services.dwolla.verification_document_size')]
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function user_balance_transaction()
    {
        return $this->morphOne(UserBalanceTransaction::class, 'related');
    }
}
