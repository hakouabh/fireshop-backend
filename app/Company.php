<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Company extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'name',
      'type_id'
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

    protected $casts = [
      'created_at' => 'datetime:Y-m-d H:i:s',
      'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    protected $hidden = [
      'type_id',
    ];

    public function type()
    {
      return $this->belongsTo('\App\CompanyType');
    }

    public function users()
    {
      return $this->hasMany('\App\User');
    }

    /**
     * Load related key models
     * @return HasMany
     */
    public function keys()
    {
        return $this->hasMany(Key::class);
    }

    /**
     * Load related key models
     * @return HasOne
     */
    public function activeKey()
    {
        return $this->hasOne(Key::class)->where('status', true)->latest();
    }
}
