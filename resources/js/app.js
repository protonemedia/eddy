import "./bootstrap";
import "../css/app.css";
import "@protonemedia/laravel-splade/dist/style.css";

import { createApp, defineAsyncComponent } from "vue/dist/vue.esm-bundler.js";
import { renderSpladeApp, SpladePlugin } from "@protonemedia/laravel-splade";


const el = document.getElementById("app");

createApp({
    render: renderSpladeApp({ el })
})
    .use(SpladePlugin, {
        "max_keep_alive": 10,
        "transform_anchors": false,
        "progress_bar": {
            "color": "#309dc7",
        },
    })
    .component('Ansicolor', defineAsyncComponent(() => import("./Ansicolor.vue")))
    .component('CopyToClipboard', defineAsyncComponent(() => import("./CopyToClipboard.vue")))
    .component('PrismEditor', defineAsyncComponent(() => import("./PrismEditor.vue")))
    .mount(el);
