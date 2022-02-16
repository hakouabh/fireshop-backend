<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Order extends Model
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'operation_id',
      'company_id',
      'user_id',
      'type'
    ];
    
    protected $hidden = [
      'operation_id',
      'company_id'
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

    public function operation()
    {
      return $this->belongsTo('\App\Operation')->withTrashed();
    }

    public function order_detail()
    {
      return $this->hasOne('\App\OrderDetail');
    }

    public function company()
    {
      return $this->belongsTo('\App\Company');
    }
}
