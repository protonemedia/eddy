@seoTitle(__('Terms of Service'))

<div class="font-sans text-gray-900 antialiased">
    <div class="bg-gray-100 pt-4">
        <div class="flex min-h-screen flex-col items-center pt-6 sm:pt-0">
            <div>
                <x-authentication-card-logo />
            </div>

            <x-splade-content class="prose mt-6 w-full overflow-hidden bg-white p-6 shadow-md sm:max-w-2xl sm:rounded-lg" :html="$terms" />

            <div class="mt-8 flex items-center justify-center space-x-4">
                <a
                    download
                    class="rounded-md border border-transparent bg-indigo-500 px-4 py-2 font-bold text-white shadow-sm hover:bg-indigo-700 focus:border-indigo-300 focus:outline-none focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    href="{{ Storage::url('nl-digital-voowaarden-en.pdf') }}"
                    target="_blank"
                >
                    {{ __('Download PDF') }}
                </a>

                <a
                    class="rounded-md border border-transparent bg-indigo-500 px-4 py-2 font-bold text-white shadow-sm hover:bg-indigo-700 focus:border-indigo-300 focus:outline-none focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    rel="noopener noreferrer"
                    href="https://get.adobe.com/reader/?loc=en"
                    target="_blank"
                >
                    {{ __('Download Acrobat Reader') }}
                </a>
            </div>

            <div class="relative mt-8 w-full sm:max-w-5xl" style="padding-top: 137%">
                <iframe
                    class="absolute inset-0 h-full w-full"
                    src="/pdf/web/viewer.html?file={{ Storage::url('nl-digital-voowaarden-en.pdf') }}"
                    scrolling="no"
                ></iframe>
            </div>
        </div>
    </div>
</div>
