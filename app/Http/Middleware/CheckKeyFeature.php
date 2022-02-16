<?php

namespace App\Http\Middleware;
use App\User;
use Closure;

class CheckKeyFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $featureId)
    {
        $user = $request->user();

        abort_unless($user instanceof User
            && method_exists($user, 'hasKeyFeature')
            && $user->hasKeyFeature($featureId), 403);

        return $next($request);
    }
}
