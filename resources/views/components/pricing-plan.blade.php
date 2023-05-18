@php $default = $attributes->get('default', false); @endphp

<div class="relative rounded-2xl ring-1 @if($default) z-10 bg-white shadow-xl ring-gray-900/10 @else bg-gray-800/80 ring-white/10 lg:bg-transparent lg:pb-14 lg:ring-0 @endif">
    <div class="p-8 lg:pt-12 xl:p-10 xl:pt-14">
        <h2 id="tier-{{ $name }}" class="text-sm font-semibold leading-6 @if($default) text-gray-900 @else text-white @endif">{{ $name }}</h2>

        <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between lg:flex-col lg:items-stretch">
            <div class="mt-2 flex items-center gap-x-4">
                <p class="text-4xl font-bold tracking-tight @if($default) text-gray-900 @else text-white @endif">
                    <span v-if="!toggled">€{{ $monthlyPrice }}</span>
                    <span v-else>€{{ $yearlyPrice }}</span>
                </p>

                <div class="text-sm leading-5">
                    <p class="@if($default) text-gray-900 @else text-white @endif">EUR</p>
                    <p class="@if($default) text-gray-500 @else text-gray-400 @endif">
                        <span v-if="!toggled">Billed monthly</span>
                        <span v-else>Billed annually</span>
                    </p>
                </div>
            </div>

            <a href="/dashboard" aria-describedby="tier-{{ $name }}"
                class="rounded-md py-2 px-3 text-center text-sm font-semibold leading-6 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 @if($default) bg-indigo-600 shadow-sm hover:bg-indigo-500 focus-visible:outline-indigo-600 @else bg-white/10 hover:bg-white/20 focus-visible:outline-white @endif"
            >Get Started</a>
        </div>

        <div class="mt-8 flow-root sm:mt-10">
            <ul role="list" class="-my-2 divide-y border-t text-sm leading-6 lg:border-t-0 @if($default) divide-gray-900/5 border-gray-900/5 text-gray-600 @else divide-white/5 border-white/5 text-white @endif">
                @foreach($features as $feature)
                    <li class="flex gap-x-3 py-2">
                        @svg('heroicon-s-check', 'h-6 w-5 flex-none ' . ($default ? 'text-green-500' : 'text-gray-500'))

                        {{ $feature }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>