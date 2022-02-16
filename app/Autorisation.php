<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Autorisation extends Model
{
    use  SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'access_product',
        'product_list',
        'product_add',
        'product_update',
        'stock_add',
        'stock_update',
        'operations_list',
        'operations_view',
        'charge_list',
        'charge_add',
        'charge_update',
        'counter_discount',
        'counter_return',
        'counter_synthesis',
        'corbeille',
        'dashboard'
    ];
    protected $casts = [
      'published' => 'boolean',
      'access_product' => 'boolean',
      'product_list' => 'boolean',
      'product_add' => 'boolean',
      'product_update' => 'boolean',
      'stock_add' => 'boolean',
      'stock_update' => 'boolean',
      'operations_list' => 'boolean',
      'operations_view' => 'boolean',
      'charge_list' => 'boolean',
      'charge_add' => 'boolean',
      'charge_update' => 'boolean',
      'counter_discount' => 'boolean',
      'counter_return' => 'boolean',
      'counter_synthesis' => 'boolean',
      'corbeille' => 'boolean',
      'dashboard' => 'boolean'
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

    protected $hidden = [
      'user_id',
    ];

    public function user()
    {
      return $this->belongsTo('\App\User');
    }
}
