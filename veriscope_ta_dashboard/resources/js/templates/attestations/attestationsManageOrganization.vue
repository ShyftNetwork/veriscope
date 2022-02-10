<template>
    <div class="container p-6 py-24 md:py-48 xl:pr-72">
        <page-intro
            title="Manage Your Organization"
        ></page-intro>
        <!-- 01. Create a new trust anchor account -->
        <div class="flex flex-wrap items-center">
                <h2>01. Load trust anchor (TA) account</h2>
        </div>
        <div class="flex flex-wrap items-center">
            <div class="w-full lg:w-1/3 my-8">
                <simple-button class="min-w-full"
                    :on-click=createTaAccount
                >
                    Load TA Account
                </simple-button>
            </div>
        </div>
        <div class="flex flex-wrap items-center" :style="{ display:show_ta_create_account }">
            <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">TA Account Address is: {{ta_create_account_result}}</strong></p>
        </div>
        <div class="my-4 lg">
            <!-- 00. TA List -->
            <div class="my-4 lg">
                <div class="flex flex-wrap items-center">
                    <strong class="mb-3 text-charcoal">Your TA for your Organization</strong>
                </div>
                <p>Note: Your TA account is loaded from the .env in the veriscope_ta_node directory.</p>
                <good-table
                :columns="ta_account_columns"
                :rows="ta_account_rows"
                :totalRows="totalRecords"
                url="ta-accounts"
                ></good-table>
            </div>
            <br/>
        </div>
        <div class="my-4 lg">
            <!-- 00. TA IVMS List -->
            <div class="my-4 lg">
                <div class="flex flex-wrap items-center">
                    <strong class="mb-3 text-charcoal">IVMS for your Organization</strong>
                </div>
                <p>Note: You are required to register your VASP entity information so it is included in the KYC Template/IVMS schema.  This information is not persisted in the blockchain.  See IVMS-101 documentation for details.</p>
                <p>Please Set or Update your Entity information in the form below.</p>
                <good-table
                :columns="ta_ivms_columns"
                :rows="ta_ivms_rows"
                :totalRows="totalRecords"
                url="ta-accounts"
                ></good-table>
            </div>
            <br/>
        </div>
        <br/>
        <!-- 02. Save Entity Information for IVMS -->
        <div class="my-4 lg">
            <div class="flex flex-wrap items-center">
                <h2>02. Set or Update Entity Information for IVMS</h2>
            </div>
            <b>Legal Person Name</b>
            <br/>
            <i>Legal Entity Name</i>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model="ivms_legal_person_name"
                        placeholder="Legal Person Name"
                        name="ivms_legal_person_name"
                        v-validate="'required'"
                        :error="errors.first('ivms_legal_person_name')"
                        required
                        disabled
                        style="padding-top:5px"
                    ></simple-input>
                </div>
            </div>
            <b>Legal Person Name Identifier Type</b>
            <br/>
            <i>LEGL - Legal name, SHRT - Short Name, TRAD - Trading name</i>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model="ivms_legal_person_name_identifier_type"
                        placeholder="Legal Person Name Identifier Type"
                        name="ivms_legal_person_name_identifier_type"
                        v-validate="'required'"
                        :error="errors.first('ivms_legal_person_name_identifier_type')"
                        required
                        disabled
                        style="padding-top:5px"
                    ></simple-input>
                </div>
            </div>
            <b>Address Type</b>
            <br/>
            <i>(Set as either HOME, BIZZ, or GEOG)</i>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model="ivms_address_type"
                        placeholder="Address Type (HOME, BIZZ, GEOG)"
                        name="ivms_address_type"
                        v-validate="'required'"
                        :error="errors.first('ivms_address_type')"
                        required
                        disabled
                        style="padding-top:5px"
                    ></simple-input>
                </div>
            </div>
            <b>Street Name</b>
            <br/>
            <i>Name of a street or thoroughfare.</i>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model="ivms_street_name"
                        placeholder="Street Name"
                        name="ivms_street_name"
                        v-validate="'required'"
                        :error="errors.first('ivms_street_name')"
                        required
                        disabled
                        style="padding-top:5px"
                    ></simple-input>
                </div>
            </div>
            <b>Building Number</b>
            <br/>
            <i>Number that identifies the position of a building on a street.</i>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model="ivms_building_number"
                        placeholder="Building Number"
                        name="ivms_building_number"
                        v-validate="'required'"
                        :error="errors.first('ivms_building_number')"
                        required
                        disabled
                        style="padding-top:5px"
                    ></simple-input>
                </div>
            </div>
            <b>Building Name</b>
            <br/>
            <i>Name of the building or house.</i>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model="ivms_building_name"
                        placeholder="Building Name"
                        name="ivms_building_name"
                        v-validate="'required'"
                        :error="errors.first('ivms_building_name')"
                        required
                        disabled
                        style="padding-top:5px"
                    ></simple-input>
                </div>
            </div>
            <b>Postcode</b>
            <br/>
            <i>Identifier consisting of a group of letters and/or numbers that is added to a postal address to assist the sorting of mail.</i>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model="ivms_postcode"
                        placeholder="Postcode"
                        name="ivms_postcode"
                        v-validate="'required'"
                        :error="errors.first('ivms_postcode')"
                        required
                        disabled
                        style="padding-top:5px"
                    ></simple-input>
                </div>
            </div>
            <b>Town Name</b>
            <br/>
            <i>Name of a built-up area, with defined boundaries, and a local government.</i>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model="ivms_town_name"
                        placeholder="Town Name"
                        name="ivms_town_name"
                        v-validate="'required'"
                        :error="errors.first('ivms_town_name')"
                        required
                        disabled
                        style="padding-top:5px"
                    ></simple-input>
                </div>
            </div>
            <b>Country Sub Division</b>
            <br/>
            <i>Identifies a subdivision of a country for example, state, region, province, départment or county.</i>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model="ivms_country_sub_division"
                        placeholder="Country Sub Division"
                        name="ivms_country_sub_division"
                        v-validate="'required'"
                        :error="errors.first('ivms_country_sub_division')"
                        required
                        disabled
                        style="padding-top:5px"
                    ></simple-input>
                </div>
            </div>
            <b>Country Code</b>
            <br/>
            <i>The value used for the field country must be present on the ISO-3166-1 alpha-2 codes or the value XX.</i>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model="ivms_country"
                        placeholder="Country Code"
                        name="ivms_country"
                        v-validate="'required'"
                        :error="errors.first('ivms_country')"
                        required
                        disabled
                        style="padding-top:5px"
                    ></simple-input>
                </div>
            </div>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3 my-8">
                    <simple-button class="min-w-full"
                        :on-click=taSaveIvms
                        >
                        Set or Update Entity IVMS
                    </simple-button>
                </div>
            </div>
        </div>
        <!-- 03. Is Trust Anchor Verified? -->
        <div class="my-4 lg">
            <div class="flex flex-wrap items-center">
                <h2>03. Is Trust Anchor Verified?</h2>
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
                        :on-click=taIsVerified
                        >
                        
                        Is Verified?
                    </simple-button>
                </div>
            </div>
            <div class="flex flex-wrap items-center" :style="{ display:show_ta_is_verified_data_result }">
                <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">{{ta_is_verified_data_result}}</strong></p>
            </div>
        </div>
         <!-- 04. Get Shyft Tokens Balance -->
        <br/>
        <div class="my-4 lg">
            <div class="flex flex-wrap items-center">
                <h2>04. Get Shyft Tokens Balance</h2>
            </div>
            <div class="flex flex-wrap items-center">
                <p>Get Shyft Tokens Balance for TA Account</p>
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
                <div class="w-full lg:w-1/3 my-4">
                    <simple-button class="min-w-full"
                        :on-click=taGetBalance
                        >
                        
                        Get Balance
                    </simple-button>
                </div>
            </div>
            <div class="flex flex-wrap items-center" :style="{ display:show_ta_get_balance_result }">
                <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">{{ta_get_balance_result}}</strong></p>
            </div>
        </div>
        <div class="my-4 lg">
            <div class="flex flex-wrap items-center">
                <h2>05. Register Jurisdication</h2>
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
                        :on-click=taRegisterJurisdiction
                        >
                        
                        Register Jurisdication
                    </simple-button>
                </div>
            </div>
            <div class="flex flex-wrap items-center">
                <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">{{ta_register_jurisdiction_result}}</strong></p>
            </div>
        </div>
        <br/>
        <!-- 06 Setup Discovery Layer for Data Transfer -->
        <div class="my-4 lg">
            <div class="flex flex-wrap items-center">
                <h2>06. Setup Discovery Layer for Data Transfer</h2>
            </div>
            <div class="flex flex-wrap items-center">
                <p>Choose TA Account for Discovery Layer</p>
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
                        :on-click=taSetUniqueAddress
                        >
                        
                        Set TA Unique Address
                    </simple-button>
                </div>
            </div>
            <div class="flex flex-wrap items-center">
                <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">{{ta_set_unique_address_result}}</strong></p>
            </div>
        </div>
        <!-- 07 Add Key Value Pair to Discovery Layer -->
        <div class="my-4 lg">
            <div class="flex flex-wrap items-center">
                <h2>07. Add Key Value Pair to Discovery Layer</h2>
            </div>
            <div class="flex flex-wrap items-center">
                <p>Choose TA Account for Discovery Layer</p>
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
                <p>Choose a Key Name </p>
            </div>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <select-input
                        v-model="ta_key_name"
                        label="Key Name"
                        placeholder="Choose the Key Name"                        
                        name="ta_key_name"
                        :options=taGetDiscoveryLayerKeysData
                        label-to-show="key"
                        v-validate="'required'"
                        :error="errors.first('ta_key_name')"
                        required
                    ></select-input> 
                </div>
            </div>
            <div class="flex flex-wrap items-center">
                <p>Enter a Key Value </p>
            </div>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model.trim="ta_key_value"
                        label="Key Value"
                        placeholder="Add Key Value"
                        name="ta_key_value"
                        v-validate="'required'"
                        :error="errors.first('ta_key_value')"
                        required
                        disabled
                    ></simple-input>
                </div>
            </div>
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3 my-8">
                    <simple-button class="min-w-full"
                        :on-click=taSetKeyValuePair
                    >
                        Save Key Value Pair
                    </simple-button>
                </div>
            </div>
            <div class="flex flex-wrap items-center">
                <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">{{ta_set_key_value_pair_result}}</strong></p>
            </div>
        </div>
        <br/>
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
    import DatePicker from '../../common/DatePicker';
    import SimpleButton from '../../common/SimpleButton';
    import PageIntro from '../../components/PageIntro';
    import SelectInput from '../../common/SelectInput';
    import {
        COMPLETE_ROUTE,
        TA_LOAD_COUNTRIES,
        CREATE_TA_ACCOUNT,
        TA_SAVE_IVMS,
        TA_IS_VERIFIED,
        TA_GET_BALANCE, 
        TA_REGISTER_JURISDICTION,
        TA_SET_UNIQUE_ADDRESS,
        TA_SET_KEY_VALUE_PAIR,
        TA_GET_TAS,
        TA_GET_UNIQUE_ADDRESS,
        TA_GET_DISCOVERY_LAYER_KEYS,
        
        
    } from '../../store/mutation-types';

    export default {
        /**
         * Returns the localized state object for this component
         */
        data() {
            return {
                ta_account_columns:[],
                totalRecords: 0,
                ta_ivms_columns:[],
            }
        },
        created() {
            console.log('Component has been created!');
            this[TA_GET_TAS]().then(response => {

                });
            this[TA_GET_DISCOVERY_LAYER_KEYS]().then(response => {
        
                });
            this[TA_LOAD_COUNTRIES]().then(response => {
          
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
            VueGoodTable,
            GoodTable,
            SelectInput,
        },
        /**
         * mounted() - Vue Lifecycle Component method
         */
        mounted() {
            this[COMPLETE_ROUTE](this.$route.name);
            this.ta_account_columns =  [
                    {label: 'TA Prefname', field: 'ta_prefname'},
                    {label: 'TA Account', field: 'account_address'},
                    {label: 'TA Public Key', field: 'public_key'},
                    {label: 'TA Signature', field: 'signature'},
                    {label: 'TA Signature Hash', field: 'signature_hash'},
                ];
            this.ta_account_rows = [];
            this.ta_ivms_columns =  [
                    {label: 'Legal Person Name', field: 'legal_person_name'},
                    {label: 'Legal Person Name Identifier Type', field: 'legal_person_name_identifier_type'},
                    {label: 'Address Type', field: 'address_type'},
                    {label: 'Street Name', field: 'street_name'},
                    {label: 'Building Number', field: 'building_number'},
                    {label: 'Building Name', field: 'building_name'},
                    {label: 'Postcode', field: 'postcode'},
                    {label: 'Town Name', field: 'town_name'},
                    {label: 'Country Sub Division', field: 'country_sub_division'},
                    {label: 'Country', field: 'country'},
                ];
            this.ta_ivms_rows = [];
        },
        
        /**
         * computed object
         * Methods that are be cached based on state not changing.
         * Quick access to state as well.
         */
        computed: {
            // Auto applies getters/setters to form fields
            ...mapFields([
                'form.ta_prefname',
                'form.attestation_jurisdiction',
                'form.ta_token_amount',
                'form.attestation_ta_account',
                'form.ta_unique_account',
                'form.ta_key_name',
                'form.ta_key_value',
                'form.ivms_legal_person_name',
                'form.ivms_legal_person_name_identifier_type',
                'form.ivms_address_type',
                'form.ivms_street_name',
                'form.ivms_building_number',
                'form.ivms_building_name',
                'form.ivms_postcode',
                'form.ivms_town_name',
                'form.ivms_country_sub_division',
                'form.ivms_country',
            ]),
            // Access globabl getters
            ...mapGetters([
                'isEditing',
            ]),
            // Slice out state properties
            ...mapState({
                formData: ({ attestations }) =>
                    attestations.form,
                ta_create_account_result: ({ attestations }) =>
                    attestations.ta_temp_account,
                ta_register_jurisdiction_result: ({ attestations }) =>
                    attestations.taRegisterJurisdictionOptions[attestations.taRegisterJurisdictionData],
                taCountryData: ({ attestations }) =>
                    attestations.taCountryData,
                ta_is_verified_data_result: ({ attestations }) =>
                    attestations.taIsVerifiedData,
                ta_get_balance_result: ({ attestations }) =>
                    attestations.taGetBalanceData,
                ta_account_rows: ({ attestations }) =>
                    attestations.getTasData,
                ta_ivms_rows: ({ attestations }) =>
                    attestations.getTasData,
                taAccountsData: ({ attestations }) =>
                        attestations.getTasData,
                taGetDiscoveryLayerKeysData: ({ attestations }) =>
                        attestations.taGetDiscoveryLayerKeysData,
                show_ta_create_account: ({ attestations }) =>
                    attestations.showTaCreateAccount,
                show_ta_is_verified_data_result: ({ attestations }) =>
                    attestations.showTaIsVerifiedData,
                show_ta_get_balance_result: ({ attestations }) =>
                    attestations.showTaGetBalanceResult,
                ta_set_unique_address_result: ({ attestations }) =>
                    attestations.taSetUniqueAddressOptions[attestations.taSetUniqueAddressData], 
                ta_get_unique_address_result: ({ attestations }) =>
                    attestations.taGetUniqueAddressData,
                ta_set_key_value_pair_result: ({ attestations }) =>
                    attestations.taSetKeyValuePairData
                    
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
            createTaAccount() {
                this[CREATE_TA_ACCOUNT]().then(response => {
                    console.log(response);
                });
            },
            taIsVerified() {
                this[TA_IS_VERIFIED]().then(response => {
                    console.log(response);
                });
            },
            taSaveIvms() {
                this[TA_SAVE_IVMS]().then(response => {
                    console.log(response);
                });
            },
            taGetBalance() {
                this[TA_GET_BALANCE]().then(response => {
                    console.log(response);
                });
            },
            taRegisterJurisdiction() {
                this[TA_REGISTER_JURISDICTION]().then(response => {
                    console.log(response);
                });
            },
            taSetUniqueAddress() {
                this[TA_SET_UNIQUE_ADDRESS]().then(response => {
                    console.log('assign tam address to tas');
                    console.log(response);
                });
            },
            taGetUniqueAddress() {
                this[TA_GET_UNIQUE_ADDRESS]().then(response => {
                    console.log('get generic address from tam');
                    console.log(response);
                });
            },
            taSetKeyValuePair() {
                this[TA_SET_KEY_VALUE_PAIR]().then(response => {
                    console.log('ta set key value pair');
                    console.log(response);
                });
            },
    
            // Pull in required action methods
            ...mapActions([
                TA_LOAD_COUNTRIES,
                CREATE_TA_ACCOUNT,
                TA_SAVE_IVMS,
                TA_IS_VERIFIED,
                TA_GET_BALANCE,
                TA_REGISTER_JURISDICTION,
                TA_GET_TAS,
                TA_SET_UNIQUE_ADDRESS,
                TA_SET_KEY_VALUE_PAIR,
                TA_GET_UNIQUE_ADDRESS,                
                TA_GET_DISCOVERY_LAYER_KEYS,
            ]),

            // Pull in required mutations
            ...mapMutations([
                COMPLETE_ROUTE,
            ]),
            
            /**
             * Used to go back a step in the router history
             */
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
        }

    };
</script>