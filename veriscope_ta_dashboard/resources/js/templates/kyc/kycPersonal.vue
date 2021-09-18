<template>
    <div class="container p-6 py-24 md:py-48 xl:pr-72" :class="{ 'vue-data-loaded':formData.userLoaded }">
        <page-intro
            title="Let's get to know each other"
            intro="Please fill out the information requested in the following four steps as accurately as possible.  This is required for our KYC process in order to verify your identity, provide you with an initial attestation and allow you access to the Shyft portal. Personal information will be treated in accordance with the Shyft Privacy Policy."
        ></page-intro>

        <div class="my-4 lg:-mx-4">
            <div class="flex flex-wrap">
                <div class="w-full lg:w-1/3 lg:p-4">
                    <simple-input
                        v-model.trim="first_name"
                        label="First Name"
                        placeholder="Add your first name"
                        name="first_name"
                        v-validate="'required'"
                        :error="errors.first('first_name')"
                        required
                    ></simple-input>
                </div>
                <div class="w-full lg:w-1/3 lg:p-4">
                    <simple-input
                        v-model.trim="middle_name"
                        label="Middle Name"
                        placeholder="Add your middle name"
                        name="middle_name"
                    ></simple-input>
                </div>
                <div class="w-full lg:w-1/3 lg:p-4">
                    <simple-input
                        v-model.trim="last_name"
                        label="Last Name"
                        placeholder="Add your last name"
                        name="last_name"
                        v-validate="'required'"
                        :error="errors.first('last_name')"
                        required
                    ></simple-input>
                </div>
            </div>
            <div class="flex flex-wrap">
                <div class="w-full lg:w-1/2 lg:p-4">
                    <date-picker
                        v-model="dob"
                        label="Date of birth"
                        name="dob"
                        typeable
                        v-validate="'required'"
                        :error="errors.first('dob') || isDobValid"
                        @input="dobErrorHandler"
                        required
                    ></date-picker>
                </div>

                <div class="w-full lg:w-1/3 lg:p-4">
                    <select-input
                        v-model="gender"
                        label="Gender"
                        placeholder="Select your gender"
                        name="gender"
                        :options="genderData"
                        v-validate="'required'"
                        :error="errors.first('gender')"
                        required
                    ></select-input>
                </div>
            </div>

            <div class="flex flex-wrap">
                <div class="w-full lg:w-1/2 lg:p-4">
                    <international-phone
                        v-model="telephone"
                        label="Phone Number"
                        placeholder="Add your number"
                        name="telephone"
                        type="tel"
                        v-validate="'required'"
                        :error="errors.first('telephone') || isPhoneValid"
                        @error=phoneErrorHandler
                        required
                    >
                    </international-phone>
                </div>
            </div>

            <div class="flex flex-wrap">
                <div class="w-full lg:w-1/2 lg:p-4">
                    <simple-input
                        v-model.trim="occupation"
                        label="Your Occupation"
                        placeholder="What is your job?"
                        name="occupation"
                        v-validate="'required'"
                        :error="errors.first('occupation')"
                        required
                    ></simple-input>
                </div>
            </div>

            <div class="flex flex-wrap items-center">
                <div
                    v-if=isEditing
                    class="w-full lg:w-1/3 my-8 lg:my-12 lg:mx-4"
                >
                    <simple-button
                        class="min-w-full"
                        :disabled=formValidationCheck
                        :on-click="goTo('review')"
                    >
                        Done
                    </simple-button>
                </div>
                <div
                    v-else
                    class="w-full lg:w-1/3 my-8 lg:my-12 lg:mx-4"
                >
                    <simple-button
                        class="min-w-full"
                        :disabled=formValidationCheck
                        :on-click="goTo('location')"
                    >
                        Next Step
                    </simple-button>
                </div>
                <a v-if=!isEditing href="/auth/welcome" class="lg:mx-4"><strong>Cancel</strong></a>
            </div>
        </div>
    </div>
</template>
<script>
    import {
        mapMutations,
        mapGetters,
        mapState
    } from 'vuex';
    import { mapFields } from 'vuex-map-fields';
    import { formatNumber } from 'libphonenumber-js';
    import SimpleInput from '../../common/SimpleInput';
    import DatePicker from '../../common/DatePicker';
    import SelectInput from '../../common/SelectInput';
    import SimpleButton from '../../common/SimpleButton';
    import InternationalPhone from '../../common/InternationalPhone';
    import PageIntro from '../../components/PageIntro';
    import { formNotValid } from '../../utilities';
    import {
        COMPLETE_ROUTE
    } from '../../store/mutation-types';

    export default {
        /**
         * Returns the localized state object for this component
         */
        data() {
            return {
                isPhoneValid: '',
                isDobValid: ''
            }
        },
        /**
         * components object
         * Define components required by this component
         */
        components: {
            SimpleInput,
            DatePicker,
            SelectInput,
            SimpleButton,
            InternationalPhone,
            PageIntro,
        },
        /**
         * mounted() - Vue Lifecycle Component method
         */
        mounted() {
            this[COMPLETE_ROUTE](this.$route.name);
        },
        /**
         * computed object
         * Methods that are be cached based on state not changing.
         * Quick access to state as well.
         */
        computed: {
            // Auto applies getters/setters to form fields
            ...mapFields([
                'form.first_name',
                'form.middle_name',
                'form.last_name',
                'form.dob',
                'form.gender',
                'form.telephone',
                'form.occupation',
            ]),
            // Access globabl getters
            ...mapGetters([
                'isEditing',
            ]),
            // Slice out state properties
            ...mapState({
                formData: ({ kyc }) =>
                    kyc.form,
                genderData: ({ kyc }) =>
                    kyc.genderData,
            }),
            /**
             * Helper accessing utility method.
             * Used to disable and enable form submission CTA
             * @returns {Boolean}
             */
            formValidationCheck() {
                return formNotValid(this.fields) || this.isPhoneValid !== '' || this.isDobValid !== '';
            },
        },
        /**
         * methods object
         * Methods that are not cached based on state.
         */
        methods: {
            // Pull in required mutations
            ...mapMutations([
                COMPLETE_ROUTE
            ]),
            /**
             * Used to switch the router to a given route
             * @param {String} name - The name of the route
             */
            goTo: function(name) {
                return () => this.$router.push({ name });
            },
            /**
             * Used to determine if the phone number validates against its Country Code
             * @param {Boolean} isValid - Boolen emitted from input
             */
            phoneErrorHandler: function(isValid) {
                this.isPhoneValid = !isValid ? 'Please enter a valid phone' : '';
            },
             /**
             * Used to determine if user is 18 or older
             * @param {Date Object} date - Date emitted from input
             */
            dobErrorHandler: function(date) {
                if(!date) return;
                const oldEnough = new Date();
                oldEnough.setFullYear(oldEnough.getFullYear()-18);
                this.isDobValid = (this.dob.getTime() > oldEnough.getTime()) ? 'You must be 18yrs or older' : '';
            },
        },
    };
</script>