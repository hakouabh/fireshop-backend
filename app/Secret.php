<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasStatusAttribute;

class Secret extends Model
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
     * @var array
     */
    protected $fillable = [
        'id',
        'status'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'status' => 'boolean'
    ];

    /**
     * Enable the secret
     * @return bool
     */
    public function enable()
    {
        return $this->update(['status' => true]);
    }

    /**
     * Disable the secret
     * @return bool
     */
    public function disable()
    {
        return $this->update(['status' => false]);
    }
}
