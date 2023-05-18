<?php

namespace App\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Policy;
use Symfony\Component\HttpFoundation\Response;

class Csp extends Policy
{
    /**
     * Indicates whether the CSP header should be added to the response.
     */
    public function shouldBeApplied(Request $request, Response $response): bool
    {
        if ($request->is('horizon*')) {
            return false;
        }

        if ($request->is('billing*')) {
            return false;
        }

        return config('csp.enabled');
    }

    /**
     * Configures the CSP.
     */
    public function configure()
    {
        $vite = Vite::isRunningHot() ? Vite::asset('') : '';

        $this
            ->addDirective(Directive::BASE, Keyword::SELF)
            ->addDirective(Directive::CONNECT, [
                Keyword::SELF,
                'wss://ws-eu.pusher.com',
                '*.usefathom.com',
            ])
            ->addDirective(Directive::DEFAULT, Keyword::SELF)
            ->addDirective(Directive::FORM_ACTION, Keyword::SELF)
            ->addDirective(Directive::FONT, Keyword::SELF)
            ->addDirective(Directive::IMG, [
                Keyword::SELF, 'data:', 'https://ui-avatars.com/api/', 'https://gravatar.com/avatar/',
                '*.usefathom.com',
            ])
            ->addDirective(Directive::MEDIA, Keyword::SELF)
            ->addDirective(Directive::OBJECT, Keyword::NONE)
            ->addDirective(Directive::SCRIPT, [
                Keyword::SELF,
                Keyword::UNSAFE_EVAL,   // Vue
                Keyword::UNSAFE_INLINE, // Splade Server error modal
                '*.usefathom.com',
            ])
            ->addDirective(Directive::STYLE, [Keyword::SELF, Keyword::UNSAFE_INLINE])
            ->addDirective(Directive::STYLE_ELEM, [Keyword::SELF, Keyword::UNSAFE_INLINE]);

        if ($vite) {
            // Allow connections to the local Vite server.
            $this->addDirective(Directive::CONNECT, str_replace(['http://', 'https://'], ['ws://', 'wss://'], $vite));
            $this->addDirective(Directive::SCRIPT, $vite);
        }

        if (config('debugbar.enabled')) {
            // Laravel Debugbar
            $this->addDirective(Directive::FONT, ['data:']);
        }
    }
}
