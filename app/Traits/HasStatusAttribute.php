<?php
namespace App\Traits;
use Illuminate\Database\Eloquent\Builder;

trait HasStatusAttribute
{
     /**
     * Filter by status
     * @param Builder $query
     * @param bool $status
     * @return Builder
     */
    public function scopeOfStatus(Builder $query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Filter by active status
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query)
    {
        return $this->scopeOfStatus($query, true);
    }

    /**
     * Whether the plan is active
     * @return bool
     */
    public function isActive()
    {
        return !empty($this->status);
    }
}
