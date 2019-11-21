<?php

namespace CarlosCGO\Google2fa\Http\Middleware;

use CarlosCGO\Google2fa\Google2fa;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        return resolve(Google2fa::class)->authorize($request) ? $next($request) : abort(403);
    }
}
