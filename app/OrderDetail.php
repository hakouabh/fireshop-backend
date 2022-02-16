<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class OrderDetail extends Model
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'order_id',
      'product_id',
      'amount',
      'discount',
      'total_order',
      'cost'
    ];
    
    protected $hidden = [
      'order_id',
      'product_id'
    ];
    protected $casts = [
      'created_at' => 'datetime:Y-m-d H:i:s',
      'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    public function product()
    {
      return $this->belongsTo('\App\Product')->withTrashed();
    }
}
