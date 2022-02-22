<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use App\Traits\HasStatusAttribute;
use App\Traits\Uuids;

class Key extends Model
{
    use Uuids, HasStatusAttribute;

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
        'plan_type',
        'company_id',
        'lifetime',
        'activated_at'
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'activated_at' => 'datetime'
    ];

    /**
     * Whether the key has been activated
     * @return bool
     */
    public function isActivated()
    {
        return isset($this->activated_at);
    }

    /**
     * Getter for "expires_at" property
     * @return \Carbon\Carbon|null
     */
    public function getExpiresAtAttribute()
    {
        if ($this->isActivated()) {
            return $this->activated_at->addSeconds($this->lifetime);
        }

        return null;
    }

    /**
     * Related user model
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Activate the key
     * @return bool
     */
    public function activate()
    {
        return $this->update(['activated_at' => now()]);
    }

    /**
     * Deactivate the key
     * @return bool
     */
    public function deactivate()
    {
        return $this->update(['activated_at' => null]);
    }

    /**
     * Filter by activated property
     * @param Builder $query
     * @return Builder
     */
    public function scopeActivated(Builder $query)
    {
        return $query->whereNotNull('activated_at');
    }

    /**
     * Whether the key is valid
     * @return bool
     */
    public function isValid()
    {
        return $this->isActive()
            && $this->isActivated()
            && !$this->isExpired();
    }

    /**
     * Whether the key can be activated
     * @return bool
     */
    public function canActivate()
    {
        return $this->isActive()
            && !$this->isActivated()
            && $this->user instanceof Company;
    }

    /**
     * Whether the key already expired
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->activated_at->lt(now()->subSeconds($this->lifetime));
    }
}
