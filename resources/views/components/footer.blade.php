<footer {{ $attributes->class('flex flex-col items-center max-w-xs sm:max-w-full text-center mx-auto') }}>
    <div class="font-medium underline flex flex-row space-x-3">
        <Link href="{{ route('policy.show') }}">{{ __('Privacy Policy') }}</Link>
        <Link href="{{ route('terms.show') }}">{{ __('Terms of Service') }}</Link>
        <a href="https://twitter.com/pascalbaljet" target="_blank">Twitter</a>
    </div>

    <p class="mt-1">&copy; {{ date('Y') }} - {{ config('app.name') }} is an open-source <a href="https://protone.media" target="_blank" class="underline">Protone Media B.V.</a> product powered by <a href="https://splade.dev" class="underline" target="_blank">Splade.dev</a>.</p>
</footer>