import {
    SUBMIT_USER,
    SUBMIT_USER_SUCCESS,
    SUBMIT_USER_FAIL,
    UPDATE_USER,
    LOAD_COUNTRIES,
    LOAD_COUNTRIES_SUCCESS,
    LOAD_COUNTRIES_FAIL,
    LOAD_STATES,
    LOAD_STATES_SUCCESS,
    LOAD_STATES_FAIL,
    LOAD_USER,
    LOAD_USER_SUCCESS,
    LOAD_USER_FAIL
} from '../../mutation-types';

export const actions = {
    /**
     * Submit the user details
     * @param {object} context - Context provided by vuex
     * @param {object} payload - Payload to submit
     * @returns {promise}
     */
    [SUBMIT_USER]({ commit, getters }, payload) {
        return axios.put(`user/${getters.UID}/verify`, payload)
            .then(response => {
                commit(SUBMIT_USER_SUCCESS);
                return response;
            })
            .catch(error => {
                let submissionResponse = {
                    message: '',
                    errors: []
                };
                // Error
                if (error.response) {
                    const { errors, message } = error.response.data;
                    submissionResponse.message = message;
                    if(errors) {
                        for (var prop in errors) {
                            const errorProp = errors[prop];
                            if(errorProp && errorProp[0]) {
                                submissionResponse.errors.push(errorProp[0]);
                            }
                        }
                    }
                    // The request was made and the server responded with a status code
                    // that falls out of the range of 2xx
                    //console.log(error.response.data);
                    //console.log(error.response.status);
                    //console.log(error.response.headers);
                } else if (error.request) {
                    // The request was made but no response was received
                    // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                    // http.ClientRequest in node.js
                    //console.log(error.request);
                } else {
                    // Something happened in setting up the request that triggered an Error
                    //console.log(error.message);
                }
                commit(SUBMIT_USER_FAIL, submissionResponse);
            });
    },
    /**
     * Get the Country Data Load
     * @param {object} context - Context provided by vuex
     * @returns {promise}
     */
    [LOAD_COUNTRIES]({ commit }) {
        return axios.get('countries')
            .then(({ data }) => {
                commit(LOAD_COUNTRIES_SUCCESS, data);
                return data;
            })
            .catch((response) => {
                commit(LOAD_COUNTRIES_FAIL);
            });
    },
    /**
     * Get the Country Data Load
     * @param {object} context - Context provided by vuex
     * @returns {promise}
     */
    [LOAD_STATES]({ commit }, {id}) {
        return axios.get(`countries/${id}`)
            .then(({ data }) => {
                commit(LOAD_STATES_SUCCESS, data);
                return data;
            })
            .catch(() => {
                commit(LOAD_STATES_FAIL);
            });
    },
    /**
     * Load the User Data
     * @param {object} context - Context provided by vuex
     * @returns {promise}
     */
    [LOAD_USER]({ commit }, id=null) {
        if(!id) return;
        return window.axios.get(`user/${id}`)
            .then(({ data }) => {
                //console.log('200 response on user load');
                commit(LOAD_USER_SUCCESS, data);
                return data;
            })
            .catch(error => {
                //console.log('error', error.response);
                commit(LOAD_USER_FAIL);
            });
    },
    /**
     * Submit the user details
     * @param {object} context - Context provided by vuex
     * @param {object} payload - Payload to submit
     * @returns {promise}
     */
    [UPDATE_USER]({ commit, getters }, payload) {
        console.log('here');
        console.log(payload);
        return axios.put(`user/${getters.EDITID}`, payload)
            .then(response => {
                commit(SUBMIT_USER_SUCCESS);
                return response;
            })
            .catch(error => {
                let submissionResponse = {
                    message: '',
                    errors: []
                };
                // Error
                if (error.response) {
                    const { errors, message } = error.response.data;
                    submissionResponse.message = message;
                    if(errors) {
                        for (var prop in errors) {
                            const errorProp = errors[prop];
                            if(errorProp && errorProp[0]) {
                                submissionResponse.errors.push(errorProp[0]);
                            }
                        }
                    }
                    // The request was made and the server responded with a status code
                    // that falls out of the range of 2xx
                    //console.log(error.response.data);
                    //console.log(error.response.status);
                    //console.log(error.response.headers);
                } else if (error.request) {
                    // The request was made but no response was received
                    // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                    // http.ClientRequest in node.js
                    //console.log(error.request);
                } else {
                    // Something happened in setting up the request that triggered an Error
                    //console.log(error.message);
                }
                commit(SUBMIT_USER_FAIL, submissionResponse);
            });
    }
};
