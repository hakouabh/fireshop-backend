<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;


class Operation extends Model
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */ 
    protected $fillable = [
        'company_id',
        'user_id',
        'customer_id',
        'total',
        'rest',
        'discount',
        'payment',
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
    // protected $dates = ['deleted_at'];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    protected $hidden = [
      'company_id'
    ];


    public function order()
    {
      return $this->hasMany('\App\Order');
    }

    public function customer()
    {
      return $this->belongsTo('\App\Customer')->withTrashed();
    }

    public function company()
    {
      return $this->belongsTo('\App\Company');
    }
}
