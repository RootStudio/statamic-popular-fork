<template>
    <div>
        <div v-if="loading" class="loading p-2">
            <loading-graphic />
        </div>

        <ul v-else-if="entries.length" class="px-2 pb-2">
            <li
                v-for="(entry, index) in entries"
                :key="entry.id"
                class="py-1 flex justify-between items-center"
            >
                <div class="flex-1 flex items-center min-w-0">
                    <div class="mr-2 px-1 bg-grey-30 text-grey-80 rounded-full">
                        {{ index + 1 }}
                    </div>
                    <a class="truncate" :href="entry.edit_url">{{ entry.title }}</a>
                </div>
                <div class="flex-0 ml-2" :title="`${entry.pageviews ?? 0} views`">
                    {{ shorten(entry.pageviews) }} {{ __("views") }}
                </div>
            </li>
        </ul>

        <p
            v-else
            class="p-2 pt-1 text-sm text-grey-50"
        >
            {{ __("There are no entries in this collection") }}
        </p>
    </div>
</template>

<script>
export default {
    props: {
        collection: String,
        initialPerPage: {
            type: [Number, String],
            default: 5,
        },
    },

    data() {
        return {
            loading: true,
            entries: [],
        };
    },

    mounted() {
        this.fetchEntries();
    },

    methods: {
        fetchEntries() {
            this.loading = true;

            this.$axios
                .get(cp_url(`collections/${this.collection}/entries`), {
                    params: {
                        sort: 'pageviews',
                        order: 'desc',
                        page: 1,
                        perPage: Number(this.initialPerPage) || 5,
                    },
                })
                .then((response) => {
                    const data = response?.data?.data ?? {};
                    this.entries = Object.values(data);
                })
                .catch(() => {
                    this.entries = [];
                    this.$toast.error(__("Something went wrong"));
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        shorten(number) {
            if (!number) {
                return 0;
            }

            if (number < 1E3) {
                return number;
            }

            let suffix = 'K';
            for (const currentSuffix of ['K', 'M', 'B', 'T']) {
                suffix = currentSuffix;
                number /= 1E3;
                if (number < 1E3) {
                    break;
                }
            }

            return `${Number(number.toFixed(number < 10 ? 1 : 0))}${suffix}`;
        },
    },
};
</script>
