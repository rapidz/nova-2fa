<?php

namespace CarlosCGO\Google2fa;

use Laravel\Nova\Tool;
use CarlosCGO\Google2fa\Models\User2fa;
use PragmaRX\Google2FA\Google2FA as G2fa;
use PragmaRX\Recovery\Recovery;
use Request;

class Google2fa extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * @return bool
     */
    protected function is2FAValid()
    {
        $secret = Request::get('secret');
        if (empty($secret)) {
            return false;
        }

        $google2fa = new G2fa();

        return $google2fa->verifyKey(auth()->user()->user2fa->google2fa_secret, $secret, config('google2fa.window'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \PragmaRX\Google2FA\Exceptions\InsecureCallException
     */
    public function confirm()
    {
        if ($this->is2FAValid()) {
            auth()->user()->user2fa->google2fa_enable = 1;
            auth()->user()->user2fa->save();
            $authenticator = app(Google2FAAuthenticator::class);
            $authenticator->login();

            return response()->redirectTo(config('nova.path'));
        }

        $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());

        $google2fa_url = $google2fa->getQRCodeInline(
            config('app.name'),
            auth()->user()->email,
            auth()->user()->user2fa->google2fa_secret
        );

        $data['google2fa_url'] = $google2fa_url;
        $data['error'] = 'Secret is invalid.';

        return view('nova-google2fa::register', $data);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \PragmaRX\Google2FA\Exceptions\InsecureCallException
     */
    public function register()
    {
        $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());

        $google2fa_url = $google2fa->getQRCodeInline(
            config('app.name'),
            auth()->user()->email,
            auth()->user()->user2fa->google2fa_secret
        );

        $data['google2fa_url'] = $google2fa_url;

        return view('nova-google2fa::register', $data);

    }

    private function isRecoveryValid($recover, $recoveryHashes)
    {
        foreach ($recoveryHashes as $recoveryHash) {
            if (password_verify($recover, $recoveryHash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function authenticate()
    {
        if ($recover = Request::get('recover')) {
            if ($this->isRecoveryValid($recover, json_decode(auth()->user()->user2fa->recovery, true)) === false) {
                $data['error'] = 'Recovery key is invalid.';

                return view('nova-google2fa::authenticate', $data);
            }

            $google2fa = new G2fa();
            $recovery = new Recovery();
            $secretKey = $google2fa->generateSecretKey();
            $data['recovery'] = $recovery
                ->setCount(config('google2fa.recovery_codes.count'))
                ->setBlocks(config('google2fa.recovery_codes.blocks'))
                ->setChars(config('google2fa.recovery_codes.chars_in_block'))
                ->toArray();

            $recoveryHashes = $data['recovery'];
            array_walk($recoveryHashes, function (&$value) {
                $value = password_hash($value, config('google2fa.recovery_codes.hashing_algorithm'));
            });

            User2fa::where('user_id', auth()->user()->id)->delete();

            $user2fa = new User2fa();
            $user2fa->user_id = auth()->user()->id;
            $user2fa->google2fa_secret = $secretKey;
            $user2fa->recovery = json_encode($recoveryHashes);
            $user2fa->save();

            return response(view('nova-google2fa::recovery', $data));
        }
        if ($this->is2FAValid()) {
            $authenticator = app(Google2FAAuthenticator::class);
            $authenticator->login();

            return response()->redirectTo(config('nova.path'));
        }
        $data['error'] = 'One time password is invalid.';

        return view('nova-google2fa::authenticate', $data);
    }
}
