<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MenuService;

class UiController extends Controller
{
    public function menu(Request $request)
    {
        $user = $request->user();
        $tenantId = $request->attributes->get('tenant_id');
        return response()->json(MenuService::forUser($user->id, $tenantId));
    }
}
