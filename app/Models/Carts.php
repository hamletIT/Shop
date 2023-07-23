<?php

namespace App\Models;
use Session;

use Illuminate\Database\Eloquent\Model;

class Carts extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'random_number',
        'status',
        'sessionStartDate',
        'sessionEndDate',
        'totalQty',
        'product_id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the phone record associated with the user.
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Products','product_id');
    }
}