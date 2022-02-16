<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;

class Charge extends Model
{
    use  SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'amount',
      'type_id',
      'company_id',
      'user_id',
      'description'
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

    protected $hidden = [
      'type_id',
      'company_id'
    ];

    public function type()
    {
      return $this->belongsTo('\App\ChargeType');
    }

    public function company()
    {
      return $this->belongsTo('\App\Company');
    }
}
