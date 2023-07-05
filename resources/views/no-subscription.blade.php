@seoTitle(__('Servers'))

<x-app-layout>
    <x-slot:header>
        {{ __('Subscription Required') }}
    </x-slot>

    <x-slot:description>
        <p class="text-lg text-red-500">{{ __('Whoops! It looks like you don\'t have an active subscription or your trial has expired.') }}</p>
    </x-slot>

    <x-panel>
        <x-empty-state href="/billing" away icon="heroicon-o-receipt-percent">
            <span>{{ __('Manage Subscription') }}</span>
            @svg('heroicon-s-arrow-right-circle', 'ml-1 w-5 h-5 text-gray-400')
        </x-empty-state>
    </x-panel>
</x-app-layout>
