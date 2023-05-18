@seoTitle(__('Terms of Service'))

<div class="font-sans text-gray-900 antialiased">
    <div class="pt-4 bg-gray-100">
        <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
            <div>
                <x-authentication-card-logo />
            </div>

            <x-splade-content class="w-full sm:max-w-2xl mt-6 p-6 bg-white shadow-md overflow-hidden sm:rounded-lg prose" :html="$terms" />

            <div class="flex justify-center items-center space-x-4 mt-8">
                <a
                    download
                    class="border rounded-md shadow-sm font-bold py-2 px-4 focus:outline-none focus:ring focus:ring-opacity-50 bg-indigo-500 hover:bg-indigo-700 text-white border-transparent focus:border-indigo-300 focus:ring-indigo-200"
                    href="{{ Storage::url('nl-digital-voowaarden-en.pdf') }}"
                    target="_blank">{{ __('Download PDF') }}</a>

                <a
                    class="border rounded-md shadow-sm font-bold py-2 px-4 focus:outline-none focus:ring focus:ring-opacity-50 bg-indigo-500 hover:bg-indigo-700 text-white border-transparent focus:border-indigo-300 focus:ring-indigo-200"
                    rel="noopener noreferrer"
                    href="https://get.adobe.com/reader/?loc=en"
                    target="_blank">{{ __('Download Acrobat Reader') }}</a>
            </div>

            <div class="relative w-full sm:max-w-5xl mt-8" style="padding-top: 137%">
                <iframe
                    class="w-full h-full absolute inset-0"
                    src="/pdf/web/viewer.html?file={{ Storage::url('nl-digital-voowaarden-en.pdf') }}"
                    scrolling="no"></iframe>
            </div>
        </div>
    </div>
</div>