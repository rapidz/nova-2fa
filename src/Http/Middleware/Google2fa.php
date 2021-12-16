<?php

namespace CarlosCGO\Google2fa\Http\Middleware;

use Closure;
use CarlosCGO\Google2fa\Google2FAAuthenticator;
use CarlosCGO\Google2fa\Models\User2fa;
use PragmaRX\Google2FA\Google2FA as G2fa;
use PragmaRX\Recovery\Recovery;

/**
 * Class Google2fa
 * @package CarlosCGO\Google2fa\Http\Middleware
 */
class Google2fa
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \PragmaRX\Google2FA\Exceptions\InsecureCallException
     */
    public function handle($request, Closure $next)
    {
        if (!config('google2fa.enabled')) {
            return $next($request);
        }
        if ($request->path() === 'los/2fa/confirm' || $request->path() === 'los/2fa/authenticate'
            || $request->path() === 'los/2fa/register' ||
            // No 2fa to logout...
            $request->path() === ltrim(route('nova.logout', [], false), '/')) {
            return $next($request);
        }
        $authenticator = app(Google2FAAuthenticator::class)->boot($request);

        if (auth()->guest() || $authenticator->isAuthenticated()) {
            return $next($request);
        }
        if (empty(auth()->user()->user2fa) || auth()->user()->user2fa->google2fa_enable === 0) {


            $google2fa = new G2fa();
            $recovery = new Recovery();
            $secretKey = $google2fa->generateSecretKey();
            $data['recovery'] = $recovery
                ->setCount(config('google2fa.recovery_codes.count'))
                ->setBlocks(config('google2fa.recovery_codes.blocks'))
                ->setChars(config('google2fa.recovery_codes.chars_in_block'))
                ->toArray();

            User2fa::where('user_id', auth()->user()->id)->delete();

            $user2fa = new User2fa();
            $user2fa->user_id = auth()->user()->id;
            $user2fa->google2fa_secret = $secretKey;
            $user2fa->recovery = json_encode($data['recovery']);
            $user2fa->save();

            return response(view('nova-google2fa::recovery', $data));
        }

        return response(view('nova-google2fa::authenticate'));
    }
}
