import {
    SUBMIT_USER_SUCCESS,
    SUBMIT_USER_FAIL,
    SUBMIT_ERROR_MESSAGE_CLEAR,
    COMPLETE_ROUTE,
    CURRENT_ROUTE,
    LOAD_COUNTRIES_SUCCESS,
    LOAD_COUNTRIES_FAIL,
    LOAD_STATES_SUCCESS,
    LOAD_STATES_FAIL,
    LOAD_USER_SUCCESS,
    LOAD_USER_FAIL,
    SET_UI_COUNTRY,
    SET_UI_STATE,
    UPDATE_USER_COUNTRY,
    UPDATE_USER_STATE,
    LOAD_BA_PROVIDERS_SUCCESS,
    LOAD_BA_PROVIDERS_FAIL,
    LOAD_BA_PROVIDERS_NETWORKS_SUCCESS,
    LOAD_BA_PROVIDERS_NETWORKS_FAIL,
    CREATE_BA_REPORT_SUCCESS
} from '../../mutation-types';

export const mutations = {
    /**
     * Called when user information successfully saved
     * @param {object} state
     */
    [SUBMIT_USER_SUCCESS] (state) {
        state.userStatus.userNotification = {
            message: '',
            errors: []
        }
    },
    /**
     * Called when user information fails saving
     * @param {object} state
     */
    [SUBMIT_USER_FAIL] (state, { message='', errors=[] } = {}) {
        state.userStatus.userNotification = {
            message,
            errors
        }
    },
    /**
     * Called when user information fails saving
     * @param {object} state
     */
    [SUBMIT_ERROR_MESSAGE_CLEAR] (state) {
        state.userStatus.userNotification = {
            message: '',
            errors: []
        }
    },
    /**
     * Used to track completed route steps
     *
     */
    [COMPLETE_ROUTE] (state, payload=null) {
        if(!payload || typeof payload !== 'string') return;
        state.completedRoutes = [
            ...new Set([
                ...state.completedRoutes,
                payload
            ])
        ]
    },
    /**
     * Used to track current route step
     *
     */
    [CURRENT_ROUTE] (state, { currentRoute='' }={}) {
        state.currentRoute = currentRoute;
    },
    /**
     * Used to load the Countries data
     *
     */
    [LOAD_COUNTRIES_SUCCESS] (state, payload=[]) {
        if(!Array.isArray(payload)) return;
        state.countryData = payload;
    },
    /**
     * Used when loading the Countries data fails
     *
     */
    [LOAD_COUNTRIES_FAIL] (state) {
        //TODO: Might want to commit error
        console.log('Country data failed to load');
    },
    /**
     * Used to load the States data
     *
     */
    [LOAD_STATES_SUCCESS] (state, payload=[]) {
        if(!Array.isArray(payload)) return;
        state.stateData = payload;
    },
    /**
     * Used when loading the States data fails
     *
     */
    [LOAD_STATES_FAIL] (state) {
        //TODO: Might want to commit error
        console.log('State data failed to load');
    },
    /**
     * Used to load a user profile. Only used when debugging.
     *
     */
    [LOAD_USER_SUCCESS] (state, {user=null}={}) {
        if(!user) return;
        // If wrappers prevent the form from immediate validation errors
        if(user.first_name) {
            state.form.first_name = user.first_name;
        }
        if(user.middle_name) {
            state.form.middle_name = user.middle_name;
        }
        if(user.last_name) {
            state.form.last_name = user.last_name;
        }
        if(user.email) {
            state.form.email = user.email;
        }
        if(user.dob) {
            // Safari and IE don't recognize yyyy-mm-dd format so need to format
            // DB value with a quick regex before outputting to Vuex data.
            const dobConverted = user.dob.replace(/-/g, '/');
            state.form.dob = new Date(dobConverted);
        }
        if(user.gender && state.genderData) {
            state.form.gender = state.genderData.find(({ value }) => value === user.gender) || null;
        }
        if(user.telephone) {
            state.form.telephone = user.telephone;
        }
        if(user.occupation) {
            state.form.occupation = user.occupation;
        }
        if(user.address) {
            state.form.address = user.address;
        }
        if(user.suite) {
            state.form.suite = user.suite;
        }
        if(user.country) {
            state.userCountryValue = user.country;
        }
        if(user.state) {
            state.userStateValue = user.state;
        }
        if(user.city) {
            state.form.city = user.city;
        }
        if(user.zip) {
            state.form.zip = user.zip;
        }
        if(user.status && state.statusData) {
            state.form.status = state.statusData.find(({ value }) => value === user.status) || null;
        }
        if(user.tranche_id) {
            state.form.tranche_id = user.tranche_id.toString();
        }
        // Adds user loaded when successful
        state.form.userLoaded = true;
    },
    /**
     * Used when loading an initial user fails
     *
     */
    [LOAD_USER_FAIL] (state) {
        console.log('user load fail');
    },
    /**
     * Selects the value of the Country select form element
     * @param {object} state
     */
    [SET_UI_COUNTRY](state) {
        state.form.country = state.countryData.find(({ name }) => name === state.userCountryValue) || null;
    },
    /**
     * Sets the value of the State select form element
     * @param {object} state
     */
    [SET_UI_STATE](state) {
        state.form.state = state.stateData.find(({ name }) => name === state.userStateValue) || null;
    },
    /**
     * Updates the user stored country value as reference for UI element
     * Triggered on form selection updates
     * @param {string} userCountryValue
     * @param {string} value
     */
    [UPDATE_USER_COUNTRY](state, value=null) {
        state.userCountryValue = (value && value.name) ? value.name : null;
    },
    /**
     * Updates the user stored state value as reference for UI element
     * Triggered on form selection updates
     * @param {string} userStateValue
     * @param {string} value
     */
    [UPDATE_USER_STATE](state, value=null) {
        state.userStateValue = (value && value.name) ? value.name : null;
    },
    /**
     * Used to load the blockchain analytics providers data
     *
     */
     [LOAD_BA_PROVIDERS_SUCCESS] (state, payload=[]) {
        if(!Array.isArray(payload)) return;
        state.blockchainAnalyticsProviders = payload;
    },
    /**
     * Used when loading the blockchain analytics providers data fails
     *
     */
    [LOAD_BA_PROVIDERS_FAIL] (state) {
        //TODO: Might want to commit error
        console.log('Blockchain analytics providers data failed to load');
    },
    /** 
     * Used to load the blockchain analytics provider networks data
     *
     */
    [LOAD_BA_PROVIDERS_NETWORKS_SUCCESS] (state, payload=[]) {
        if(!Array.isArray(payload)) return;
        state.blockchainAnalyticsProvidersNetworks = payload;
    },
    /**
     * Used when loading the blockchain analytics provider networks data fails
     *
     */
    [LOAD_BA_PROVIDERS_NETWORKS_FAIL](state) {
        //TODO: Might want to commit error
        console.log('Blockchain analytics provider networks data failed to load');
    },

    /**
     * Updates the user stored state value as reference for UI element
     * Triggered on form selection updates
     * @param {object} state
     * @param {object} payload
     */

    [CREATE_BA_REPORT_SUCCESS] (state, payload) {
        console.log('CREATE_BA_REPORT_SUCCESS')
        console.log(state)
        console.log(payload)
        state.ba_provider_report_submitted = false
        state.ba_provider_report = `/backoffice/blockchain-analytics-addresses/${payload.report_id}/view`

    }
};