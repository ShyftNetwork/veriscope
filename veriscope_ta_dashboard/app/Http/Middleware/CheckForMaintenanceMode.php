<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Constant;

class CheckForMaintenanceMode {

    public function handle(Request $request, Closure $next)
    {
        $maintenance = Constant::where('name', 'maintenance')->first();

        if (!empty($maintenance) && $maintenance->value == 1 && config('backoffice.enabled') != 'true')
        {
            return abort(503);
        }

        return $next($request);
    }

}
