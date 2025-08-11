<?php
namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class ResolveTenant {
    public function handle(Request $request, Closure $next) {
        $slug = $request->header('X-Tenant') ?? $request->get('tenant');
        if (!$slug) {
            return response()->json(['message' => 'Tenant header missing'], 400);
        }
        $tenant = Tenant::where('slug', $slug)->first();
        if (!$tenant || $tenant->status !== 'active') {
            return response()->json(['message' => 'Tenant invalid or inactive'], 403);
        }
        $request->attributes->set('tenant_id', $tenant->id);
        return $next($request);
    }
}
