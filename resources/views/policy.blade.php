@seoTitle(__('Privacy Policy'))

<div class="font-sans text-gray-900 antialiased">
    <div class="bg-gray-100 pt-4">
        <div class="flex min-h-screen flex-col items-center pt-6 sm:pt-0">
            <div>
                <x-authentication-card-logo />
            </div>

            <x-splade-content class="prose mt-6 w-full overflow-hidden bg-white p-6 shadow-md sm:max-w-2xl sm:rounded-lg" :html="$policy" />
        </div>
    </div>
</div>
