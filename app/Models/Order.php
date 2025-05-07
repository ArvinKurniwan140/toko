<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptsAttributes;
use Illuminate\Database\Eloquent\SoftDeletes;


class Order extends Model
{
    use HasFactory, SoftDeletes, EncryptsAttributes;

    /**
     * Atribut yang akan dienkripsi dengan AES-256
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'first_name',
        'last_name',
        'company',
        'address',
        'city',
        'province',
        'zipcode',
        'country',
        'phone',
        'email',
        'notes',
        'subtotal',
        'shipping',
        'total_amount',
        'payment_method',
        'status',
    ];

    protected $encrypts = [
        'address' => 'personal',
        'city' => 'personal',
        'province' => 'personal',
        'zipcode' => 'personal',
        'country' => 'personal',
        'phone' => 'personal',
        'email' => 'personal',
        'payment_method' => 'payment',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the order items for this order.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the user that placed the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
