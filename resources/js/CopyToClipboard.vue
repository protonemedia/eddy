<script>
import { useClipboard } from "@vueuse/core";
const { copy, isSupported } = useClipboard();

export default {
    props: {
        source: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            copied: false,
            timeout: null,
        };
    },

    render() {
        return this.$slots.default({
            copied: this.copied,
            copy: () => {
                if (!isSupported) {
                    return;
                }

                clearTimeout(this.timeout);
                this.copied = true;
                copy(this.source);

                this.timeout = setTimeout(() => {
                    this.copied = false;
                }, 2000);
            },
        });
    },
};
</script>
