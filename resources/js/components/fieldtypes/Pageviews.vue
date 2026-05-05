<template>
    <div>
        <input
            ref="input"
            class="input-text w-full"
            :type="editing ? 'number' : 'text'"
            :readonly="!editing"
            :value="pending"
            min="0"
            @input="updatePending($event.target.value)"
        />
        <div v-if="saving" class="mt-1 h-6 px-1 flex justify-end text-sm">
            <loading-graphic inline :text="__('Saving')" />
        </div>
        <div v-else-if="config.editable" class="mt-1 h-6 px-1 flex justify-between text-sm">
            <button
                v-if="!editing"
                class="
                    text-button text-blue
                    hover:text-grey-80
                    mr-3
                    flex
                    items-center
                    outline-none
                "
                @click="edit"
            >
                {{ __("Edit") }}
            </button>

            <button
                v-if="editing"
                class="
                    text-button text-blue
                    hover:text-grey-80
                    mr-3
                    flex
                    items-center
                    outline-none
                "
                @click="cancel"
            >
                {{ __("Cancel") }}
            </button>

            <button
                v-if="editing"
                class="
                    text-button text-blue
                    hover:text-grey-80
                    flex
                    items-center
                    outline-none
                "
                @click="confirm"
            >
                {{ __("Confirm") }}
            </button>
        </div>
    </div>
</template>

<script>
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';

export default {
    mixins: [Fieldtype],

    data() {
        return {
            editing: false,
            saving: false,
            pending: this.value,
        };
    },

    computed: {
        input() {
            return this.$refs.input;
        },

        entry() {
            return this.publishContainer.values.id;
        },
    },

    watch: {
        value(newValue) {
            if (!this.editing) {
                this.pending = newValue;
            }
        },
    },

    methods: {
        edit() {
            this.editing = true;

            this.input.focus();
        },

        stopEditing() {
            this.editing = false;
        },

        confirm() {
            this.stopEditing();

            this.saving = true;

            this.$axios
                .patch(cp_url(`popular/pageviews/${this.entry}`), {
                    views: this.pending,
                })
                .then(() => {
                    this.update(this.pending);

                    this.$toast.success(__("Pageviews updated"));
                })
                .catch(() => {
                    this.resetPending();

                    this.$toast.error(__("Something went wrong"));
                })
                .finally(() => {
                    this.saving = false;
                });
        },

        cancel() {
            this.stopEditing();

            this.resetPending();
        },

        updatePending(value) {
            this.pending = parseInt(value, 10) || 0;

            if (this.pending < 0) this.pending = 0;
        },

        resetPending() {
            this.pending = this.value;
        }
    },
};
</script>