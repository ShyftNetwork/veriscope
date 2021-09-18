import { getField, updateField } from 'vuex-map-fields';
import { actions } from './actions';
import { mutations } from './mutations';
import { getters } from './getters';

export default {
    state: {
        form: {
            first_name: '',
            middle_name: '',
            last_name: '',
            dob: null,
            gender: null,
            telephone: '',
            occupation: '',
            email: '',
            address: '',
            suite: '',
            country: null,
            state: null,
            status: null,
            city: '',
            zip: '',
            userLoaded: false,
        },
        userCountryValue: null,
        userStateValue: null,
        userStatus: {
            userNotification: {
                message: '',
                errors: []
            },
        },
        completedRoutes: [],
        currentRoute: '',
        countryData: [],
        stateData: [],
        genderData: [
            {label: 'Male', value: 'male'},
            {label: 'Female', value: 'female'},
            {label: 'I\'d rather not say', value: 'none'}
        ], 
        statusData: [
            {label: 'Active', value: 'active'},
            {label: 'Suspended', value: 'suspended'},
            {label: 'Terminated', value: 'terminated'}
        ]
    },
    getters: {
        getField,
        ...getters
    },
    actions,
    mutations: {
        updateField,
        ...mutations
    },
}
