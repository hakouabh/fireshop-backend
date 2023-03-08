<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Stock extends Model
{
    use  SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'cost',
      'product_id',
      'selling_price',
      'company_id',
      'quantity',
      'is_defect',
      'site_id',
      'initial_quantity'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    protected $casts = [
      'created_at' => 'datetime:Y-m-d H:i:s',
      'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    protected $hidden = [
      'company_id'
    ];

    public function product()
    {
      return $this->belongsTo('\App\Product')->withTrashed();
    }

    public function company()
    {
      return $this->belongsTo('\App\Company');
    }
    public function site()
    {
      return $this->belongsTo('\App\Site');
    }
}
