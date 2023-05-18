<script>
import { h } from "vue";

// import Prism Editor
import { PrismEditor } from "vue-prism-editor";
import "vue-prism-editor/dist/prismeditor.min.css"; // import the styles somewhere

// Highlighting library
import pkg from "prismjs/components/prism-core.js";
const { languages, highlight } = pkg;
import "prismjs/themes/prism.css";
import "prismjs/components/prism-markup";
import "prismjs/components/prism-markup-templating";

import "prismjs/components/prism-bash";
import "prismjs/components/prism-clike";
import "prismjs/components/prism-json";
import "prismjs/components/prism-nginx";
import "prismjs/components/prism-php";

export default {
    props: {
        language: {
            type: String,
            default: "clike",
        },

        modelValue: {
            type: String,
            default: "",
        },

        name: {
            type: String,
            default: "",
        },

        disabled: {
            type: Boolean,
            default: false,
        },

        lineNumbers: {
            type: Boolean,
            default: false,
        },
    },

    render() {
        const language = languages[this.language]
            ? languages[this.language]
            : languages["plain"];

        return this.$slots.default({
            prism: h(PrismEditor, {
                class: "rounded-md border border-gray-300 disabled:opacity-50 text-gray-700 font-mono px-3 py-2 bg-white shadow-sm leading-7 text-sm !mt-0 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 break-all",
                highlight: (code) => {
                    return highlight(code, language);
                },
                lineNumbers: this.lineNumbers,
                modelValue: this.modelValue,
                readonly: this.disabled,
                "onUpdate:modelValue": (value) =>
                    this.$emit("update:modelValue", value),
                onVnodeMounted: (ctx) => {
                    // Set the name attribute on the textarea so Dusk can find it
                    ctx.el
                        .querySelector("textarea")
                        .setAttribute("name", this.name);
                },
                ...this.$attrs,
            }),
        });
    },

    emits: ["update:model-value"],
};
</script>