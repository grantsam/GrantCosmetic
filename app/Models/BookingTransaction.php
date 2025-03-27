<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_trx_id',
        'name',
        'phone',
        'email',
        'quantity',
        'proof',
        'total_amount',
        'total_tax_amount',
        'is_paid',
        'address',
        'post_code',
        'city',
        'sub_total_amount',
    ];

    public function generateUniqueTrxId()
    {
        $prefix = 'GRANT-';
        do {
            $randomstring = $prefix . mt_rand(100000, 999999);
        }
        while (self::where('booking_trx_id', $randomstring)->exists());

        return $randomstring;
    }

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
