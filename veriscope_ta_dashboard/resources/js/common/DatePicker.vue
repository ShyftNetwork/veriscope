<template>
    <div class="form-control form-control--date-picker">
        <label 
            v-if="label || !required"
            :for=name
        >
            {{ label }}
            <span v-if="!required" class="label__optional">(optional)</span>
        </label>
        <div style="position: relative; background-color: #FFF;">
            <datepicker
                :value=value
                :id=name
                :name=name
                placeholder="eg. Feb 03, 1976"
                :required=required
                :class="{ error }"
                @input="onInput"
                use-utc
                typeable
            ></datepicker>
            <svg class="datepicker-calendar-icon" width="20px" height="19px" viewBox="0 0 20 19" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <g id="Calendar" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" stroke-linecap="square">
                    <g transform="translate(1.000000, 1.000000)" stroke="#404040" stroke-width="2">
                        <path d="M1.5,5.5 L16.5,5.5" id="Line-Copy-2"></path>
                        <path d="M1.5,1.5 L16.5,1.5" id="Line"></path>
                        <path d="M1.5,15.5 L16.5,15.5" id="Line-Copy"></path>
                        <path d="M1.5,1.5 L1.5,15.5" id="Line-4"></path>
                        <path d="M5,0 L5,2.5" id="Line-4"></path>
                        <path d="M13,0 L13,2.5" id="Line-4-Copy-2"></path>
                        <path d="M16.5,1.5 L16.5,15.5" id="Line-4-Copy"></path>
                    </g>
                </g>
            </svg>
        </div>
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
    import Datepicker from 'vuejs-datepicker';
    export default {
        components: {
            Datepicker
        },
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
                type: Date,
            },
            required: {
                type: Boolean,
                default: false,
            },
        },
        methods: {
            /**
             * Because the emitted value doesn't follow the use-utc attribute (only the UI selected does)
             * we need to catch the val and format it as such. So, rather than emitting the val directly,
             * we convert first using a timeshift seen here: 
             * https://praveenlobo.com/blog/how-to-convert-javascript-local-date-to-utc-and-utc-to-local-date/
             */
            onInput: function(val) {
                const valAsUtc = (val) ? new Date(val.getTime() + (val.getTimezoneOffset() * 60000)) : null;
                this.$emit('input', valAsUtc);
            },
        }
    }
</script>
