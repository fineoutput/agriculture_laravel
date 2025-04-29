<?php
namespace App\Http\Middleware;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
class DynamicJWTUser
{
    public function handle($request, Closure $next)
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $type = $payload->get('type');
        $modelMap = [
            'farmer' => \App\Models\Farmer::class,
            'doctor' => \App\Models\Doctor::class,
            'vendor' => \App\Models\Vendor::class,
        ];
        if (isset($modelMap[$type])) {
            config(['auth.guards.api.provider' => 'users']);
            config(['auth.providers.users.model' => $modelMap[$type]]);
        }
        return $next($request);
    }
}