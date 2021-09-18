<template>
    <div class="container p-6 py-24 md:py-48 xl:pr-72">
        <page-intro
            title="Attestations Admin"
            intro="Discover components in your user attestations."
        ></page-intro>
        <div class="my-4 lg">
            <div class="flex flex-wrap items-center">
                <p>Search Attestations by User Address</p>
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
                <div class="w-full lg:w-1/3">
                    <select-input
                        v-model="attestation_user"
                        label="User Account"
                        placeholder="Choose the User Account"                        
                        name="attestation_user"
                        :options=taUserAccountsData
                        label-to-show="prefname"
                        v-validate="'required'"
                        @input="onTaUserSelected"
                        :error="errors.first('attestation_user')"
                        required
                    ></select-input> 
                </div>
            </div>
        </div>
        <!-- Attestations for Address -->
        <div class="my-4 lg">
            <div class="flex flex-wrap items-center">
                <strong class="mb-3 text-charcoal">Attestations for Address</strong>
            </div>
            <good-table
            :columns="ta_attestations_columns"
            :rows="ta_attestations_rows"
            :totalRows="totalRecords"
            url="ta-attestations"
            @toggle-active=toggleAttestationState
            ></good-table>
        </div>
        <!-- Components from Attestation -->
        <div class="my-4 lg">
            <div class="flex flex-wrap items-center">
                <strong class="mb-3 text-charcoal">Components from Attestation</strong>
            </div>
            <good-table
            :columns="ta_attestation_components_columns"
            :rows="ta_attestation_components_rows"
            :totalRows="totalRecords"
            url="ta-attestation-components"
            ></good-table>
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
    import DatePicker from '../../common/DatePicker';
    import SimpleButton from '../../common/SimpleButton';
    import PageIntro from '../../components/PageIntro';
    import SelectInput from '../../common/SelectInput';
    import {
        COMPLETE_ROUTE,
        TA_GET_USER_ATTESTATIONS,
        TA_GET_ATTESTATION_COMPONENTS,
        TA_GET_TAS,
        TA_GET_USERS,
        
    } from '../../store/mutation-types';

    export default {
        /**
         * Returns the localized state object for this component
         */
        data() {
            return {
                ta_attestations_columns: [],
                ta_attestation_components_columns: [],
                totalRecords: 0,
            }
        },
        created() {
            console.log('Component has been created!');
            this[TA_GET_TAS]().then(response => {
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
            VueGoodTable,
            GoodTable,
            SelectInput,
        },
        /**
         * mounted() - Vue Lifecycle Component method
         */
        mounted() {
            this[COMPLETE_ROUTE](this.$route.name);
            this.ta_attestations_columns =  [
                    {label: 'ID', field: 'id', sortable: false},
                    {label: 'Attestations Hash', field: 'hash', sortable: false},
                    {label: 'View Components', field: 'action', html: true, sortable: false}
                ];
            this.ta_attestations_rows = [];
            this.ta_attestation_components_columns =  [
                    {label: 'Field', field: 'field'},
                    {label: 'Data', field: 'data'} 
                ];
            this.ta_attestation_components_rows = [];
           
        },
        
        /**
         * computed object
         * Methods that are be cached based on state not changing.
         * Quick access to state as well.
         */
        computed: {
            // Auto applies getters/setters to form fields
            ...mapFields([
                'form.ta_user_address',
                'form.attestation_hash',
                'form.ta_attestation_hash',
                'form.attestation_ta_account',
                'form.attestation_user'
            ]),
            // Access globabl getters
            ...mapGetters([
                'isEditing',
            ]),
            // Slice out state properties
            ...mapState({
                formData: ({ attestations }) =>
                    attestations.form,
                ta_attestations_rows: ({ attestations }) =>
                    attestations.taGetUserAttestationsData,
                ta_attestation_components_rows: ({ attestations }) =>
                    attestations.taGetAttestationComponentsData,
                taUserAccountsData: ({ attestations }) =>
                        attestations.getTaUsersData,
                taAccountsData: ({ attestations }) =>
                        attestations.getTasData,
                    
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
            taGetUserAttestations() {
                this[TA_GET_USER_ATTESTATIONS]().then(response => {
                    console.log(response);
                });
            },
            taGetAttestationComponents() {
                this[TA_GET_ATTESTATION_COMPONENTS]().then(response => {
                    console.log(response);
                });
            },
            // Pull in required action methods
            ...mapActions([
                TA_GET_USER_ATTESTATIONS,
                TA_GET_ATTESTATION_COMPONENTS,
                TA_GET_TAS,
                TA_GET_USERS,
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
            onTaUserSelected: function() {
                console.log('here');
                console.log(this.formData);
                if(this.formData.attestation_user.id) {
                    this[TA_GET_USER_ATTESTATIONS](this.formData.attestation_user)
                    .then(response => {
                        console.log(response);
                    });
                }
            },
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
            toggleAttestationState: function({id=null}={}) {
                if(!id) return;
                this[TA_GET_ATTESTATION_COMPONENTS](id);
              },
        }

    };
</script>
