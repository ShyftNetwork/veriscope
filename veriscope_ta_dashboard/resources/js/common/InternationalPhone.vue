<template>
    <div class="form-control">
        <label
            v-if="label || !required"
            :for=name
        >
            {{ label }}
            <span v-if="!required" class="label__optional">(optional)</span>
        </label>
        <vue-tel-input
            :value="value"
            :placeholder=placeholder
            @onInput="onInput"
        >
        </vue-tel-input>
        <span v-if="error" class="error-message mt-1">
            <svg class="mr-3" style="min-width:16px" width="16px" height="16px" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <g id="Shyft-Dashboards" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <g id="Dropdown-Fields" transform="translate(-488.000000, -385.000000)">
                        <g id="Error" transform="translate(0.000000, 378.000000)">
                            <g transform="translate(488.000000, 7.000000)">
                                <circle id="Oval-2" fill="#F90E28" cx="8" cy="8" r="8"></circle>
                                <rect id="Rectangle-7" fill="#FFFFFF" x="7" y="4" width="2" height="5" rx="1"></rect>
                                <rect id="Rectangle-7-Copy" fill="#FFFFFF" x="7" y="10" width="2" height="2" rx="1"></rect>
                            </g>
                        </g>
                    </g>
                </g>
            </svg>
            {{ error }}
        </span>
    </div>
</template>
<script>
    import Vue from 'vue';
    import VueTelInput from 'vue-tel-input';
    Vue.use(VueTelInput);

    export default {
        props: {
            name: {
                type: String,
                required: true
            },
            label: {
                type: String,
            },
            error: {
                type: String,
            },
            value: {
                type: String,
            },
            required: {
                type: Boolean,
                default: false,
            },
            placeholder: {
                type: String,
                default: 'Enter a value...',
            },
        },
        methods: {
            onInput({ number, isValid, country }) {
                this.$emit('input', number);
                this.$emit('error', isValid);
            },
        }
    }
</script>
