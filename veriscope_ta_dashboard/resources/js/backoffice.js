import Vue from 'vue';
import Vuex from 'vuex';
import VeeValidate from 'vee-validate';
import VTooltip from 'v-tooltip';
import { VueGoodTable } from 'vue-good-table';
import GoodTable from './common/VueUrlGoodTable';
import { mapFields } from 'vuex-map-fields';
import { formNotValid } from './utilities';
import SimpleInput from './common/SimpleInput';
import SelectInput from './common/SelectInput';
import InternationalPhone from './common/InternationalPhone';
import DatePicker from './common/DatePicker';
import SimpleButton from './common/SimpleButton';
import CheckboxInput from './common/CheckboxInput';
import Notification from './common/Notification';
import LoadingOverlay from './common/LoadingOverlay'
import Dropzone from './components/Dropzone';
import Modal from './common/Modal';
import store from './store';
import {
  mapActions,
  mapGetters,
  mapMutations,
  mapState,
} from 'vuex';
import {
  LOAD_USER,
  LOAD_COUNTRIES,
  LOAD_STATES,
  UPDATE_USER,
  SUBMIT_ERROR_MESSAGE_CLEAR,
  SET_UI_COUNTRY,
  SET_UI_STATE,
  UPDATE_USER_COUNTRY,
  UPDATE_USER_STATE,
  LOAD_BA_PROVIDERS,
  LOAD_BA_PROVIDERS_NETWORKS,
  CREATE_BA_REPORT,
  CREATE_BA_REPORT_SUCCESS,
  ATTESTATIONS_SOCKET_CONNECTION_SUCCESS
} from './store/mutation-types';

Vue.use(VeeValidate);
Vue.use(VTooltip);

if (document.getElementById('backoffice')) {
  const app = new Vue({
    el: '#backoffice',
    store,
    data() {
      return {
          isPhoneValid: '',
          isDobValid: '',
          notification: null,
          loading: false,
          loadingMessage: ''
      }
    },
    components: {
      VueGoodTable,
      GoodTable,
      VTooltip,
      SelectInput,
      InternationalPhone,
      DatePicker,
      SimpleInput,
      SimpleButton,
      CheckboxInput,
      Notification,
      Modal,
      Dropzone,
      LoadingOverlay
    },
    created() {
      window.Echo.connector.pusher.connection.bind('connected', function () {
        var socketId = window.Echo.socketId();
        store.commit(ATTESTATIONS_SOCKET_CONNECTION_SUCCESS, socketId);
      });

      var userId = null;
      if (document.head.querySelector('meta[name="user-id"]')
      && document.head.querySelector('meta[name="user-id"]').content) {
          userId = document.head.querySelector('meta[name="user-id"]').content;
          console.log(userId);
      }

      Echo.private(`user.${userId}`)
      .listen('ContractsInstantiate', (event) => {

          if (event.data.message == 'report-created') {
            this.ba_provider_report = `/backoffice/blockchain-analytics-addresses/${event.data.report_id}/view`
            store.commit(CREATE_BA_REPORT_SUCCESS, event.data);
        }
      })
    },
    async mounted() {
      const loadUser = this[LOAD_USER](this.EDITID);
      const loadCountries = this[LOAD_COUNTRIES]();
      const loadBAProviders = this[LOAD_BA_PROVIDERS]();

      await loadUser;
      await loadCountries;
      await loadBAProviders;
      
      this[SET_UI_COUNTRY]();
    },
    computed: {
      ...mapGetters([
          'EDITID',
          'UID',
          'userDobValue',
          'userGenderValue',
          'userStatusValue',
          'userLoaded',
          'addressValidationObject'
      ]),
      ...mapFields([
        'form.first_name',
        'form.middle_name',
        'form.last_name',
        'form.email',
        'form.telephone',
        'form.status',
        'form.dob',
        'form.gender',
        'form.occupation',
        'form.address',
        'form.suite',
        'form.city',
        'form.zip',
        'form.country',
        'form.state',
        'form.role',
        'ba_provider',
        'ba_provider_network',
        'ba_provider_report',
        'ba_provider_report_submitted',
        'walletAddress'
      ]),
      ...mapState({
        countryData: ({ kyc }) =>
          kyc.countryData,
        stateData: ({ kyc }) =>
          kyc.stateData,
        statusData: ({ kyc }) =>
          kyc.statusData,
        genderData: ({ kyc }) =>
          kyc.genderData,
        formData: ({ kyc }) =>
          kyc.form,
        kyc: ({kyc}) =>
          kyc,
        submissionErrorTitle: ({ kyc }) =>
          kyc.userStatus.userNotification.message,
        submissionErrors: ({ kyc }) =>
          kyc.userStatus.userNotification.errors,
        blockchainAnalytics: ({ kyc }) =>
          kyc.blockchainAnalytics,
        blockchainAnalyticsProviders: ({ kyc }) =>
          kyc.blockchainAnalyticsProviders,
        blockchainAnalyticsProvidersNetworks: ({ kyc }) =>
          kyc.blockchainAnalyticsProvidersNetworks
      }),
      /**
       * Used to validate all associated fields and release the main kyc update submit
       */
      formValidationCheck() {
        return formNotValid(this.fields) || this.isPhoneValid !== '' || this.isDobValid !== '';
      }
    },
    methods: {
      ...mapActions([
          LOAD_USER,
          LOAD_COUNTRIES,
          LOAD_STATES,
          UPDATE_USER,
          LOAD_BA_PROVIDERS,
          LOAD_BA_PROVIDERS_NETWORKS,
          CREATE_BA_REPORT
      ]),
      ...mapMutations([
        SUBMIT_ERROR_MESSAGE_CLEAR,
        SET_UI_COUNTRY,
        SET_UI_STATE,
        UPDATE_USER_COUNTRY,
        UPDATE_USER_STATE,
        
      ]),
      /**
       * Used to get transaction button
       */
        getBackofficeTransactionUrl: function({ tx_url=null, tx_edit_url=null }={}) {
          let txUrlViewButton = '';
          let txUrlEditButton = '';
          if(tx_url) {
            txUrlViewButton=`<a href="${tx_url}" class="btn btn--sm" target="_blank">View TX</a>`;
          }
          if(tx_edit_url) {
            txUrlEditButton=`<a href="${tx_edit_url}" class="btn btn--sm">Edit</a>`;
          }
          return `${txUrlViewButton} ${txUrlEditButton}`;
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
      /**
       * Callback when a BA provider is selected in the form
       */
      onBAProviderSelected: function() {
        
        if(this.ba_provider) {
          console.log(this)
            this[LOAD_BA_PROVIDERS_NETWORKS](this.ba_provider)
            .then(() => this[SET_UI_STATE]());
        }
      },
      /**
       * Callback when a country is selected in the form
       */
      onCountrySelected: function() {
        if(this.country) {
            this[UPDATE_USER_COUNTRY](this.country);
            this[LOAD_STATES](this.country)
            .then(() => this[SET_UI_STATE]());
        }
      },
      /**
       * Callback when a state is selected in the form
       */
      onStateSelected: function() {
          this[UPDATE_USER_STATE](this.state);
      },
      /**
       * Used to clear the Vuex submission message
       */
      clearMessageHandler() {
        this[SUBMIT_ERROR_MESSAGE_CLEAR]();
      },
      /**
       * Clears any notification message
       */
      removeNotification() {
        this.notification = null;
      },
      /**
       * Used to generate full image object from thumbnail.
       * For the most part this would be used by Good Table
       * when displaying thumbnails.
       */
      getThumbnail: function({ thumbnail } = null) {
        if(!thumbnail) return;
        return `<img src="${thumbnail}" width="100" class="block cursor-pointer">`;
      },
      /**
       * Used as callback for when table rows are clicked. Specifically
       * for Photo rows to help in triggering a modal opener.
       */
      onCellClick: function({ column, row } = null) {
        if(!column || !row) return;
      },
      /**
       * Used to format and submit kyc form updates to API
       */
      submitForm() {
        window.scrollTo(0,0);
        this[UPDATE_USER]({
          first_name: this.formData.first_name,
          last_name: this.formData.last_name,
          email: this.formData.email,
          // middle_name: this.formData.middle_name,
          // dob: Date.parse(this.userDobValue),
          // gender: this.userGenderValue,
          // telephone: this.formData.telephone,
          // occupation: this.formData.occupation,
          // address: this.formData.address,
          // suite: this.formData.suite,
          // country: this.kyc.userCountryValue,
          // state: this.kyc.userStateValue,
          // city: this.formData.city,
          // zip: this.formData.zip,
          status: this.userStatusValue,
        }).then(response => {
          if(response && response.status === 200) {
              this.notification = {
                type: 'success',
                message: 'This profile has been updated.'
              };
          } else {
            this.notification = {
              type: 'error',
              message: 'Whoops, something went wrong. Please try again',
            };
          }
        });
      },
      createBlockchainAnalyticsReport() {
      
       
        if (!this.ba_provider || !this.ba_provider_network || !this.walletAddress) return
        this.ba_provider_report_submitted = true
        if (this.ba_provider_report) this.ba_provider_report = null
        this[CREATE_BA_REPORT]({
          ba_provider: this.ba_provider,
          network: this.ba_provider_network,
          wallet: this.walletAddress
        })
        .then(response => {
         

        });
      }
    },
  });
}
