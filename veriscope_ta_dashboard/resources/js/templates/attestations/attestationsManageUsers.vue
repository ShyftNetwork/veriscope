<template>
    <div class="container p-6 py-24 md:py-48 xl:pr-72">
        <page-intro
            title="Manage Users"
            intro="Onboard your platform users and set attestations to their crypto withdrawal requests."
        ></page-intro>
        <!-- 01. Create a new user Shyft account -->

        <div class="my-4 lg">
            <!-- 01. TA User List -->
            <div class="my-4 lg">
                <div class="flex flex-wrap items-center">
                    <strong class="mb-3 text-charcoal">Your Users in your Organization</strong>
                </div>
                <good-table
                :columns="ta_get_user_columns"
                :rows="ta_get_user_rows"
                :totalRows="totalRecords"
                url="ta-accounts"
                ></good-table>
            </div>
            <div class="my-4 lg">
                <div class="flex flex-wrap items-center">
                    <strong class="mb-3 text-charcoal">Your Users with Crypto Wallet Addresses</strong>
                </div>
                <good-table
                :columns="ta_get_user_wallet_address_columns"
                :rows="ta_get_user_wallet_address_rows"
                :totalRows="totalRecords"
                url="ta_get_user_wallet_address"
                ></good-table>
            </div>
            <br/>
            <div class="flex flex-wrap items-center">
                <h2>01. Generate Random User</h2>
            </div>
            <div class="flex flex-wrap items-center">
                <p>Choose the TA to onboard this new user</p>
            </div>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <select-input
                        v-model="attestation_ta_account"
                        label="TA Account"
                        placeholder="Choose the TA Account"
                        name="attestation_ta_account"
                        :options=taAccountsData
                        label-to-show="ta_prefname"
                        v-validate="'required'"
                        :error="errors.first('attestation_ta_account')"
                        required
                    ></select-input>
                </div>
            </div>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3 my-8">
                    <simple-button class="min-w-full"
                        :on-click=taCreateRandomUsers
                        >

                        Create Random User
                    </simple-button>
                </div>
            </div>
            <div class="flex flex-wrap items-center" :style="{ display:show_ta_create_user_result }">
                <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">User Account Address: {{ ta_create_user_result }}</strong></p>
            </div>
            <br/>
        </div>
        <!-- 02. Set Wallet Attestation for your User -->
        <br/>
        <div class="my-4 lg">
            <div class="flex flex-wrap items-center">
                <h2>02. Set Wallet Attestation for your User</h2>
            </div>
            <div class="flex flex-wrap items-center">
                <p>Choose the TA for this action</p>
            </div>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <select-input
                        v-model="attestation_ta_account"
                        label="TA Account"
                        placeholder="Choose the TA Account"
                        name="attestation_ta_account"
                        :options=taAccountsData
                        label-to-show="ta_prefname"
                        v-validate="'required'"
                        @input="onTaSelected"
                        :error="errors.first('attestation_ta_account')"
                        required
                    ></select-input>
                </div>
            </div>
            <div class="flex flex-wrap items-center">
                <p>Choose the User for this action</p>
            </div>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <select-input
                        v-model="attestation_user"
                        label="User Account"
                        placeholder="Choose the User Account"
                        name="attestation_user"
                        :options=taUserAccountsData
                        label-to-show="prefname"
                        v-validate="'required'"
                        :error="errors.first('attestation_user')"
                        required
                    ></select-input>
                </div>
            </div>
            <div class="flex flex-wrap items-center">
                <p>Enter the crypto wallet type</p>
            </div>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <select-input
                        v-model="user_crypto_address_type"
                        label="Crypto Address Type"
                        placeholder="eg BTC or ETH"
                        name="user_crypto_address_type"
                        :options=taWalletTypeData
                        label-to-show="wallet_type"
                        v-validate="'required'"
                        @input="onTaWalletTypeSelected"
                        :error="errors.first('user_crypto_address_type')"
                        required
                    ></select-input>
                </div>
            </div>
            <div class="flex flex-wrap items-center">
                <p>Enter the crypto address</p>
            </div>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model="attestation_document_matrix"
                        label="Crypto Address"
                        placeholder="Crypto Address"
                        name="attestation_document_matrix"
                        v-validate="'required'"
                        :error="errors.first('attestation_document_matrix')"
                        required
                        disabled
                    ></simple-input>
                </div>
            </div>
            <div class="flex flex-wrap items-center">
                <p>Choose the jurisdiction for this user</p>
            </div>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <select-input
                        v-model="attestation_jurisdiction"
                        label="Jurisdiction"
                        placeholder="Choose The Jurisdiction"
                        name="attestation_jurisdiction"
                        :options=taCountryData
                        label-to-show="name"
                        v-validate="'required'"
                        :error="errors.first('attestation_jurisdiction')"
                        required
                    ></select-input>
                </div>
            </div>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3 my-8">
                    <simple-button class="min-w-full"
                        :on-click=taSetWalletAttestation
                        >
                        Set Wallet Attestation
                    </simple-button>
                </div>
            </div>
            <div class="flex flex-wrap items-center" :style="{ display:show_ta_set_wallet_attestation_result }">
                <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">{{ta_set_wallet_attestation_result}}</strong></p>
            </div>
            <div class="flex flex-wrap items-center" :style="{ display:show_ta_set_wallet_attestation_hash }">
                <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">Attestation Hash: {{ta_set_wallet_attestation_hash}}</strong></p>
            </div>
            <div class="flex flex-wrap items-center" :style="{ display:show_ta_set_wallet_attestation_error }">
                <p class="md:flex md:items-center"><img src="/images/icon-error.svg" alt="Error" class="mr-2"> <strong class="mr-2">{{ta_set_wallet_attestation_error}}</strong></p>
            </div>
            <div class="flex flex-wrap items-center" :style="{ display:show_fatf_button }">
                <div class="flex flex-wrap items-center">
                    <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">Attention - New FATF Report <a href="fatf-travel-rule-reports">Available</a></strong></p>
                </div>
            </div>
        </div>
        </div>
    </div>
</template>
<script>
    import {
        mapActions,
        mapMutations,
        mapGetters,
        mapState
    } from 'vuex';
    import { mapFields } from 'vuex-map-fields';
    import SimpleInput from '../../common/SimpleInput';
    import { VueGoodTable } from 'vue-good-table';
    import GoodTable from '../../common/VueGoodTable';
    import SelectInput from '../../common/SelectInput';
    import DatePicker from '../../common/DatePicker';
    import SimpleButton from '../../common/SimpleButton';
    import PageIntro from '../../components/PageIntro';
    import {

        COMPLETE_ROUTE,
        TA_CREATE_USER,
        TA_CREATE_RANDOM_USERS,
        TA_SET_ATTESTATION,
        TA_GET_TAS,
        TA_GET_USERS,
        TA_LOAD_COUNTRIES,
        TA_LOAD_WALLET_TYPES,
        TA_LOAD_WALLET_ADDRESSES,
        TA_ASSIGN_CRYPTO_ADDRESS,
        TA_GET_USER_WALLET_ADDRESSES,

    } from '../../store/mutation-types';

    export default {

        /**
         * Returns the localized state object for this component
         */
        data() {
            return {
                totalRecords: 0,
                ta_get_user_columns: [],
                ta_get_user_wallet_address_columns: [],
                isDobValid: '',
            }
        },

        created() {
            console.log('Component has been created!');
            this[TA_GET_TAS]().then(response => {

                });
            this[TA_LOAD_COUNTRIES]().then(response => {

                });
            this[TA_LOAD_WALLET_TYPES]().then(response => {

                });
            this[TA_GET_USERS]().then(response => {

                });
            this[TA_GET_USER_WALLET_ADDRESSES]().then(response => {
                    console.log(response);
                });
          },
        /**
         * components object
         * Define components required by this component
         */
        components: {
            SimpleInput,
            DatePicker,
            SimpleButton,
            PageIntro,
            SelectInput,
            VueGoodTable,
            GoodTable,
        },
        /**
         * mounted() - Vue Lifecycle Component method
         */
        mounted() {
            this[COMPLETE_ROUTE](this.$route.name);
            this.ta_get_user_columns =  [
                    {label: 'User Full Name', field: 'prefname'},
                    {label: 'User Shyft Account', field: 'account_address'},
                    {label: 'Primary Identifier', field: 'primary_identifier'},
                    {label: 'Secondary Identifier', field: 'secondary_identifier'},
                    {label: 'Name Identifier Type', field: 'name_identifier_type'},
                    {label: 'Address Type', field: 'address_type'},
                    {label: 'Street Name', field: 'street_name'},
                    {label: 'Building Number', field: 'building_number'},
                    {label: 'Postcode', field: 'postcode'},
                    {label: 'Town Name', field: 'town_name'},
                    {label: 'Country Sub Division', field: 'country_sub_division'},
                    {label: 'Country', field: 'country'},
                    {label: 'National Identifier', field: 'national_identifier'},
                    {label: 'National Identifier Type', field: 'national_identifier_type'},
                    {label: 'Country of Issue', field: 'country_of_issue'},
                    {label: 'Registration Authority', field: 'registration_authority'},
                    {label: 'Date of Birth', field: 'date_of_birth'},
                    {label: 'Place of Birth', field: 'place_of_birth'},
                    {label: 'Country of Residence', field: 'country_of_residence'},

                ];
            this.ta_get_user_rows = [];
            this.ta_get_user_wallet_address_columns =  [
                    {label: 'Prefname', field: 'user_prefname'},
                    {label: 'Wallet Type', field: 'user_wallet_type'},
                    {label: 'Wallet Deposit Address', field: 'user_wallet_address'}
                ];
            this.ta_get_user_wallet_address_rows = [];
        },

        /**
         * computed object
         * Methods that are be cached based on state not changing.
         * Quick access to state as well.
         */
        watch: {
            ta_create_user_result(newValue, oldValue) {
              console.log(`Updating from ${oldValue} to ${newValue}`);
              this[TA_GET_USERS]();
              this[TA_GET_USER_WALLET_ADDRESSES]();
            },
        },
        computed: {
            // Auto applies getters/setters to form fields
            ...mapFields([
                'form.user_prefname',
                'form.user_password',
                'form.attestation_user',
                'form.attestation_document_matrix',
                'form.attestation_public_data',
                'form.attestation_availability_address',
                'form.attestation_jurisdiction',
                'form.attestation_kyc_data',
                'form.attestation_ta_account',
                'form.user_crypto_address',
                'form.user_crypto_address_type',
                'form.dob',
                'form.gender',
            ]),
            // Access globabl getters
            ...mapGetters([
                'isEditing',
            ]),
            // Slice out state properties
            ...mapState({
                formData: ({ attestations }) =>
                    attestations.form,
                ta_create_user_result: ({ attestations }) =>
                    attestations.ta_temp_user,
                ta_set_kyc_attestation_result: ({ attestations }) =>
                    attestations.taSetKycAttestationOptions[attestations.taSetKycAttestationData],
                ta_set_kyc_attestation_error: ({ attestations }) =>
                    attestations.taSetKycAttestationError,
                ta_set_kyc_attestation_hash: ({ attestations }) =>
                    attestations.taSetKycAttestationHashData,
                ta_set_wallet_attestation_result: ({ attestations }) =>
                    attestations.taSetWalletAttestationOptions[attestations.taSetWalletAttestationData],
                ta_set_wallet_attestation_error: ({ attestations }) =>
                    attestations.taSetWalletAttestationError,
                ta_set_wallet_attestation_hash: ({ attestations }) =>
                    attestations.taSetWalletAttestationHashData,
                taAccountsData: ({ attestations }) =>
                        attestations.getTasData,
                taUserAccountsData: ({ attestations }) =>
                        attestations.getTaUsersData,
                taWalletTypeData: ({ attestations }) =>
                        attestations.taWalletTypeData,
                taWalletAddressData: ({ attestations }) =>
                        attestations.taWalletAddressData,
                taCountryData: ({ attestations }) =>
                    attestations.taCountryData,
                taSetAttestationTypeOptions: ({ attestations }) =>
                    attestations.taSetAttestationTypeOptions,
                ta_assign_crypto_address_result: ({ attestations }) =>
                    attestations.taAssignCryptoAddressData,
                ta_get_user_rows: ({ attestations }) =>
                    attestations.getTaUsersData,
                ta_get_user_wallet_address_rows: ({ attestations }) =>
                    attestations.taGetUserWalletAddressData,
                genderData: ({ attestations }) =>
                    attestations.genderData,
                show_fatf_button: ({ attestations }) =>
                    attestations.showFatfButton,
                show_ta_create_user_result: ({ attestations }) =>
                    attestations.showTaCreateUserResult,

                show_ta_set_kyc_attestation_result: ({ attestations }) =>
                    attestations.showTaSetKycAttestationResult,
                show_ta_set_kyc_attestation_hash: ({ attestations }) =>
                    attestations.showTaSetKycAttestationHash,
                show_ta_set_kyc_attestation_error: ({ attestations }) =>
                    attestations.showTaSetKycAttestationError,

                show_ta_set_wallet_attestation_result: ({ attestations }) =>
                    attestations.showTaSetWalletAttestationResult,
                show_ta_set_wallet_attestation_hash: ({ attestations }) =>
                    attestations.showTaSetWalletAttestationHash,
                show_ta_set_wallet_attestation_error: ({ attestations }) =>
                    attestations.showTaSetWalletAttestationError,
            }),
        },
        /**
         * methods object
         * Methods that are not cached based on state.
         */
        methods: {

            /**
             * Used to submit entire form payload to API
             */
            taCreateUser() {
                this[TA_CREATE_USER]().then(response => {
                    console.log(response);
                });
            },
            taCreateRandomUsers() {
                this[TA_CREATE_RANDOM_USERS]().then(response => {
                    console.log(response);
                });
            },
            taSetAttestation() {
                this[TA_SET_ATTESTATION]().then(response => {
                    console.log(response);
                });
            },
            taSetKycAttestation() {
                this[TA_SET_ATTESTATION]({type:"KYC"}).then(response => {
                    console.log(response);
                });
            },
            taSetWalletAttestation() {
                this[TA_SET_ATTESTATION]({type:"WALLET"}).then(response => {
                    console.log(response);
                });
            },
            taAssignCryptoAddress() {
                this[TA_ASSIGN_CRYPTO_ADDRESS]().then(response => {
                    console.log(response);
                });
            },
            // Pull in required action methods
            ...mapActions([
                TA_CREATE_USER,
                TA_CREATE_RANDOM_USERS,
                TA_SET_ATTESTATION,
                TA_GET_TAS,
                TA_GET_USERS,
                TA_LOAD_COUNTRIES,
                TA_LOAD_WALLET_TYPES,
                TA_LOAD_WALLET_ADDRESSES,
                TA_ASSIGN_CRYPTO_ADDRESS,
                TA_GET_USER_WALLET_ADDRESSES,
            ]),
            // Pull in required mutations
            ...mapMutations([
                COMPLETE_ROUTE,

            ]),
            /**
             * A callback for the country selector when an item is selected
             */
            onTaSelected: function() {
                console.log('here');
                console.log(this.formData);
                if(this.formData.attestation_ta_account.id) {
                    this[TA_GET_USERS]()
                    .then(response => {
                        console.log(response);
                    });
                }
            },
            onTaWalletTypeSelected: function() {
                if(this.formData.user_crypto_address_type.id) {
                    this[TA_LOAD_WALLET_ADDRESSES]()
                    .then(response => {
                    });
                }
            },
            /**
             * Used to go back a step in the router history
             */
            goToFATF: function() {
                console.log('goToFATF');
                window.location.href = 'fatf-travel-rule-reports';
            },
            goBack: function() {
                this.$router.go(-1);
            },
            /**
             * Used to switch the router to a given route
             * @param {String} name - The name of the route
             */
            goTo: function(name) {
                return () => this.$router.push({ name });
            },
            dobErrorHandler: function(date) {
                if(!date) return;
                const oldEnough = new Date();
                oldEnough.setFullYear(oldEnough.getFullYear()-18);
                this.isDobValid = (this.dob.getTime() > oldEnough.getTime()) ? 'You must be 18yrs or older' : '';
            },
        }
    };
</script>
