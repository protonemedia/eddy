<x-splade-data store="mobileNavigation" default="{ open: false }" />

<x-splade-rehydrate on="refresh-navigation-menu, profile-information-updated">
    <nav class="border-b border-gray-100 bg-white">
        <!-- Primary Navigation Menu -->
        <div class="mx-auto max-w-7xl px-4 sm:px-3 md:px-6 lg:px-8">
            <div class="flex flex-wrap justify-between">
                <div class="order-1 flex h-16">
                    <!-- Logo -->
                    <div class="flex shrink-0 items-center">
                        <Link href="{{ route('dashboard') }}">
                            <x-application-mark class="block h-9 w-auto" />
                        </Link>
                    </div>
                </div>

                <nav class="order-3 hidden sm:flex sm:w-screen sm:space-x-5 sm:border-t sm:py-3 md:order-2 md:w-auto md:border-t-0 md:py-2" aria-label="Global">
                    <x-nav-link :href="route('servers.index')" :active="request()->routeIs('servers*')">
                        {{ __('Servers') }}
                    </x-nav-link>

                    <x-nav-link :href="route('credentials.index')" :active="request()->routeIs('credentials*')">
                        {{ __('Credentials') }}
                    </x-nav-link>

                    <x-nav-link :href="route('disks.index')" :active="request()->routeIs('disks*')">
                        {{ __('Backup Disks') }}
                    </x-nav-link>

                    <x-nav-link :href="route('ssh-keys.index')" :active="request()->routeIs('ssh-keys*')">
                        {{ __('SSH Keys') }}
                    </x-nav-link>
                </nav>

                <div class="order-2 hidden sm:flex sm:items-center md:order-3 md:ml-6">
                    <div class="relative ml-2">
                        @if (\Laravel\Jetstream\Jetstream::hasTeamFeatures())
                            <x-splade-dropdown>
                                <x-slot:trigger>
                                    <span class="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-transparent bg-white px-2 py-2 text-sm font-medium leading-4 text-indigo-900 transition duration-150 ease-in-out hover:bg-indigo-50 focus:bg-indigo-50 focus:outline-none active:bg-indigo-50"
                                        >
                                            @svg('heroicon-o-building-office', 'h-6 w-6')
                                        </button>
                                    </span>
                                </x-slot>

                                <div class="mt-2 w-60 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                                    <!-- Team Management -->
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ __('Manage Team') }}
                                    </div>

                                    <!-- Team Settings -->
                                    <x-dropdown-link :href="route('teams.show', auth()->user()->currentTeam)">
                                        {{ __('Team Settings') }}
                                    </x-dropdown-link>

                                    @if (config('eddy.subscriptions_enabled') &&
                                         auth()->user()->ownsTeam(auth()->user()->currentTeam))
                                        <x-dropdown-link href="/billing" away>
                                            {{ __('Manage Subscription') }}
                                        </x-dropdown-link>
                                    @endif

                                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                        <x-dropdown-link :href="route('teams.create')">
                                            {{ __('Create New Team') }}
                                        </x-dropdown-link>
                                    @endcan

                                    <div class="border-t border-gray-200" />

                                    <!-- Team Switcher -->
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ __('Switch Teams') }}
                                    </div>

                                    @foreach (auth()->user()->allTeams() as $team)
                                        <x-splade-form method="PUT" :action="route('current-team.update')" :default="['team_id' => $team->getKey()]">
                                            <x-dropdown-link as="button">
                                                <div class="flex items-center">
                                                    @if ($team->is(auth()->user()->currentTeam))
                                                        <svg
                                                            class="mr-2 h-5 w-5 text-green-400"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke-width="1.5"
                                                            stroke="currentColor"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                            />
                                                        </svg>
                                                    @endif

                                                    <div>{{ $team->name }}</div>
                                                </div>
                                            </x-dropdown-link>
                                        </x-splade-form>
                                    @endforeach
                                </div>
                            </x-splade-dropdown>
                        @endif
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="relative ml-2">
                        <x-splade-dropdown>
                            <x-slot:trigger>
                                @if (\Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <button class="flex rounded-full border-2 border-transparent text-sm transition focus:border-gray-300 focus:outline-none">
                                        <img
                                            class="h-8 w-8 rounded-full object-cover"
                                            src="{{ auth()->user()->profile_photo_url }}"
                                            alt="{{ auth()->user()->name }}"
                                        />
                                    </button>
                                @else
                                    <span class="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-transparent bg-white px-2 py-2 text-sm font-medium leading-4 text-indigo-900 transition duration-150 ease-in-out hover:bg-indigo-50 focus:bg-indigo-50 focus:outline-none active:bg-indigo-50"
                                        >
                                            @svg('heroicon-o-user-circle', 'h-6 w-6')
                                        </button>
                                    </span>
                                @endif
                            </x-slot>

                            <div class="mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                                <!-- Account Management -->
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __('Manage Account') }}
                                </div>

                                <x-dropdown-link :href="route('profile.show')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                @if (\Laravel\Jetstream\Jetstream::hasApiFeatures())
                                    <x-dropdown-link :href="route('api-tokens.index')">
                                        {{ __('API Tokens') }}
                                    </x-dropdown-link>
                                @endif

                                <div class="border-t border-gray-200" />

                                <!-- Authentication -->
                                <x-splade-form :action="route('logout')">
                                    <x-dropdown-link as="button">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </x-splade-form>
                            </div>
                        </x-splade-dropdown>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="order-4 -mr-2 flex items-center sm:hidden">
                    <button
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                        @click="mobileNavigation.open = ! mobileNavigation.open"
                    >
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path
                                :class="{ 'hidden': mobileNavigation.open, 'inline-flex': !mobileNavigation.open }"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                :class="{ 'hidden': !mobileNavigation.open, 'inline-flex': mobileNavigation.open }"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div :class="{ 'block': mobileNavigation.open, 'hidden': !mobileNavigation.open }" class="sm:hidden">
            <div class="space-y-1 pb-3 pt-2">
                <x-responsive-nav-link :href="route('servers.index')" :active="request()->routeIs('servers*')">
                    {{ __('Servers') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('credentials.index')" :active="request()->routeIs('credentials*')">
                    {{ __('Credentials') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('disks.index')" :active="request()->routeIs('disks*')">
                    {{ __('Backup Disks') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('ssh-keys.index')" :active="request()->routeIs('ssh-keys*')">
                    {{ __('SSH Keys') }}
                </x-responsive-nav-link>
            </div>

            <!-- Responsive Settings Options -->
            <div class="border-t border-gray-200 pb-1 pt-4">
                <div class="flex items-center px-4">
                    @if (\Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <div class="mr-3 shrink-0">
                            <img class="h-10 w-10 rounded-full object-cover" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" />
                        </div>
                    @endif

                    <div>
                        <div class="text-base font-medium text-gray-800">
                            {{ auth()->user()->name }}
                        </div>
                        <div class="text-sm font-medium text-gray-500">
                            {{ auth()->user()->email }}
                        </div>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.show')" :active="request()->routeIs('profile.show')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    @if (\Laravel\Jetstream\Jetstream::hasApiFeatures())
                        <x-responsive-nav-link :href="route('api-tokens.index')" :active="request()->routeIs('api-tokens.index')">
                            {{ __('API Tokens') }}
                        </x-responsive-nav-link>
                    @endif

                    <!-- Authentication -->
                    <x-splade-form method="POST" :action="route('logout')">
                        <x-responsive-nav-link as="button">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </x-splade-form>

                    <!-- Team Management -->
                    @if (\Laravel\Jetstream\Jetstream::hasTeamFeatures())
                        <div class="border-t border-gray-200" />

                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Manage Team') }}
                        </div>

                        <!-- Team Settings -->
                        <x-responsive-nav-link :href="route('teams.show', auth()->user()->currentTeam)" :active="request()->routeIs('teams.show')">
                            {{ __('Team Settings') }}
                        </x-responsive-nav-link>

                        @if (config('eddy.subscriptions_enabled') &&
                             auth()->user()->ownsTeam(auth()->user()->currentTeam))
                            <x-responsive-nav-link away href="/billing">
                                {{ __('Manage Subscription') }}
                            </x-responsive-nav-link>
                        @endif

                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                            <x-responsive-nav-link :href="route('teams.create')" :active="request()->routeIs('teams.create')">
                                {{ __('Create New Team') }}
                            </x-responsive-nav-link>
                        @endcan

                        <div class="border-t border-gray-200" />

                        <!-- Team Switcher -->
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Switch Teams') }}
                        </div>

                        @foreach (auth()->user()->allTeams() as $team)
                            <x-splade-form method="PUT" :action="route('current-team.update')" :default="['team_id' => $team->getKey()]">
                                <x-responsive-nav-link as="button">
                                    <div class="flex items-center">
                                        @if ($team->is(auth()->user()->currentTeam))
                                            <svg
                                                class="mr-2 h-5 w-5 text-green-400"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke-width="1.5"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                        @endif

                                        <div>{{ $team->name }}</div>
                                    </div>
                                </x-responsive-nav-link>
                            </x-splade-form>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </nav>
</x-splade-rehydrate>
