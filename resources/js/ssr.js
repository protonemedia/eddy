import { createServer } from "http";
import { createSSRApp } from "vue";
import { renderToString } from "@vue/server-renderer";
import { renderSpladeApp, SpladePlugin, startServer } from "@protonemedia/laravel-splade";

import Ansicolor from './Ansicolor.vue';
import CopyToClipboard from './CopyToClipboard.vue';
import PrismEditor from './PrismEditor.vue';

startServer(createServer, renderToString, (props) => {
    return createSSRApp({
        render: renderSpladeApp(props)
    })
        .component('Ansicolor', Ansicolor)
        .component('CopyToClipboard', CopyToClipboard)
        .component('PrismEditor', PrismEditor)
        .use(SpladePlugin);
});