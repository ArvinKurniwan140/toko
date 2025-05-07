<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Encryptable;

class Pembayaran extends Model
{
    use HasFactory;

    // protected $encryptable = [
    //     'card_holder_name',
    //     'card_number',
    //     'card_expiry',
    //     'card_cvv'
    // ];

    protected $fillable = [
        'user_id',
        'card_type',
        'card_holder_name',
        'card_number',
        'card_expiry',
        'card_cvv',
        'is_default'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tampilkan hanya 4 digit terakhir kartu kredit
     */
    public function getLastFourDigitsAttribute()
    {
        $decrypted = $this->getDecryptedAttribute('card_number');
        if ($decrypted) {
            return substr($decrypted, -4);
        }
        return null;
    }
}