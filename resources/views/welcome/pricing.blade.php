<x-splade-toggle>
    <div class="flow-root bg-gradient-to-t from-gray-700 to-gray-800 py-16 sm:pt-32 lg:pb-48">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="relative z-10">
                <h1 class="mx-auto max-w-4xl text-center text-5xl font-bold tracking-tight text-white">Eddy's Pricing</h1>

                <p class="mx-auto mt-4 max-w-2xl text-center text-lg leading-8 text-white/80">
                    Discover Eddy today with our
                    <b class="text-white">5-day free trial</b>
                    and choose a plan that fits your needs. Support open-source development while enjoying hassle-free server management and deployment. Join
                    Eddy and Splade's community today!
                </p>

                <div class="mt-16 flex justify-center">
                    <fieldset class="grid grid-cols-2 gap-x-1 rounded-full bg-white/5 p-1 text-center text-xs font-semibold leading-5 text-white">
                        <legend class="sr-only">Payment frequency</legend>

                        <label @click="setToggle(false)" class="cursor-pointer rounded-full px-2.5 py-1" :class="{'bg-indigo-500': !toggled}">
                            <span>Monthly</span>
                        </label>

                        <label @click="setToggle(true)" class="cursor-pointer rounded-full px-2.5 py-1" :class="{'bg-indigo-500': toggled}">
                            <span>Annually</span>
                        </label>
                    </fieldset>
                </div>
            </div>

            <div class="relative mx-auto mt-10 grid max-w-md grid-cols-1 gap-y-8 lg:mx-0 lg:-mb-14 lg:max-w-none lg:grid-cols-3">
                <div
                    class="hidden lg:absolute lg:inset-x-px lg:bottom-0 lg:top-4 lg:block lg:rounded-2xl lg:bg-gray-800/80 lg:ring-1 lg:ring-white/10"
                    aria-hidden="true"
                ></div>

                <x-pricing-plan
                    name="Compact"
                    monthly-price="7.99"
                    yearly-price="84.99"
                    :features="
                        [
                            '3 Servers',
                            '10 Sites per Server',
                            'Deployment log limited to 5 releases per site',
                            'Unlimited cron, daemons, firewall rules, etc.',
                            'No team support',
                            'You support the development of Eddy and Splade!',
                        ]
                    "
                />

                <x-pricing-plan
                    default
                    name="Turbo"
                    monthly-price="11.99"
                    yearly-price="129.99"
                    :features="
                        [
                            '10 Servers',
                            'Unlimited Sites per Server',
                            'Unlimited cron, daemons, firewall rules, etc.',
                            'Deployment log limited to 15 releases per site',
                            'Up to 5 team members',
                            'You support the development of Eddy and Splade!',
                        ]
                    "
                />

                <x-pricing-plan
                    name="Platinum"
                    monthly-price="20.99"
                    yearly-price="229.99"
                    :features="
                        [
                            'Unlimited Servers',
                            'Unlimited Sites per Server',
                            'Unlimited Deployment logs',
                            'Unlimited cron, daemons, firewall rules, etc.',
                            'Unlimited team members',
                            'Access to upcoming features',
                            'You support the development of Eddy and Splade!',
                        ]
                    "
                />
            </div>
        </div>
    </div>
</x-splade-toggle>
