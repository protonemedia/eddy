<footer {{ $attributes->class('mx-auto flex max-w-sm flex-col items-center text-center') }}>
    <div class="flex flex-row space-x-3 font-medium underline">
        <Link href="{{ route('policy.show') }}">{{ __('Privacy Policy') }}</Link>
        <Link href="{{ route('terms.show') }}">{{ __('Terms of Service') }}</Link>
        <a href="https://twitter.com/pascalbaljet" target="_blank">Twitter</a>
        <a href="https://blog.eddy.management" target="_blank">Blog</a>
    </div>

    <p class="mt-1">
        &copy; {{ date('Y') }} - {{ config('app.name') }} is an open-source
        <a href="https://protone.media" target="_blank" class="underline">Protone Media B.V.</a>
        product powered by
        <a href="https://splade.dev" class="underline" target="_blank">Splade.dev</a>
    </p>
</footer>
