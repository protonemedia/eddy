<CopyToClipboard :source="@js(trim($slot))" #default="{ copy, copied }">
    <button type="button" @click="copy" {{ $attributes }}>
        @svg('heroicon-o-clipboard-document', 'w-full h-full text-gray-700', ['v-show' => '!copied'])
        @svg('heroicon-o-clipboard-document-check', 'w-full h-full text-green-600', ['v-show' => 'copied'])
    </button>
</CopyToClipboard>
