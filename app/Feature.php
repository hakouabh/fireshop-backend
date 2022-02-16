<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasStatusAttribute;

class Feature extends Model
{
    use HasStatusAttribute;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'id',
        'status',
        'title',
        'text'
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
    ];
}
