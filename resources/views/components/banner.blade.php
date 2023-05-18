<x-splade-toggle :data="true">
    <x-splade-state>
        <div v-if="state.shared.jetstreamBanner && toggled" :class="{
            'bg-indigo-500': state.shared.jetstreamBanner.bannerStyle != 'danger',
            'bg-red-700': state.shared.jetstreamBanner.bannerStyle == 'danger'
        }">
            <div class="max-w-screen-xl mx-auto py-2 px-3 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between flex-wrap">
                    <div class="w-0 flex-1 flex items-center min-w-0">
                        <span class="flex p-2 rounded-lg" :class="{
                            'bg-indigo-600': state.shared.jetstreamBanner.bannerStyle != 'danger',
                            'bg-red-600': state.shared.jetstreamBanner.bannerStyle == 'danger'
                        }">
                            <svg v-if="state.shared.jetstreamBanner.bannerStyle != 'danger'" class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>

                            <svg v-if="state.shared.jetstreamBanner.bannerStyle == 'danger'" class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </span>

                        <p class="ml-3 font-medium text-sm text-white truncate" v-text="state.shared.jetstreamBanner.banner" />
                    </div>

                    <div class="shrink-0 sm:ml-3">
                        <button
                            type="button"
                            class="-mr-1 flex p-2 rounded-md focus:outline-none sm:-mr-2 transition"
                            :class="{
                                'hover:bg-indigo-600 focus:bg-indigo-600': state.shared.jetstreamBanner.bannerStyle != 'danger',
                                'hover:bg-red-600 focus:bg-red-600': state.shared.jetstreamBanner.bannerStyle == 'danger'
                            }"
                            aria-label="Dismiss"
                            @click="toggle"
                        >
                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </x-splade-state>
</x-splade-toggle>