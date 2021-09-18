<template>
    <div>
        <div class="step-wizard">
            <div class="step-wizard__step" 
                :class="{ 
                    'step-wizard__step--complete': completedRoutes.includes('personal'),
                    'step-wizard__step--active': currentRoute === 'personal' 
                }"
            >Personal<span class="hidden xl:inline"> Information</span></div>
            <div class="step-wizard__step" 
                :class="{ 
                    'step-wizard__step--complete': completedRoutes.includes('location'),
                    'step-wizard__step--active': currentRoute === 'location'  
                }"
            >Location<span class="hidden xl:inline">  Information</span></div>
            <div class="step-wizard__step" 
                :class="{ 
                    'step-wizard__step--complete': completedRoutes.includes('review'),
                    'step-wizard__step--active': currentRoute === 'review'  
                }"
            >Review<span class="hidden xl:inline">  Information</span></div>
            <div class="step-wizard__step" 
                :class="{ 
                    'step-wizard__step--complete': completedRoutes.includes('complete'),
                    'step-wizard__step--active': currentRoute === 'complete'  
                }"
            >Complete</div>
        </div>
        <div class="step-wizard-progress md:invisible" :style="{ width:percentageComplete }"></div>
        <transition name="fade">
            <router-view></router-view>
        </transition>
    </div>
</template>
<script>
    // Import Vuex helpers
    import { 
        mapState,
        mapActions,
        mapGetters,
        mapMutations
    } from 'vuex';
    // Import Vuex Mutation constants
    import {
        LOAD_USER,
        LOAD_COUNTRIES,
        SET_UI_COUNTRY
    } from '../../store/mutation-types';
    
    export default {
        /**
         * mounted() - Vue Lifecycle Component method
         * Converted to async await in order to assure both country and user
         * data have been loaded before making a UI selection
         */
        async mounted() {
            const loadUser = this[LOAD_USER](this.UID);
            const loadCountries = this[LOAD_COUNTRIES]();
            await loadUser;
            await loadCountries;
            this[SET_UI_COUNTRY]();
        },
        /**
         * computed object
         * Methods that are be cached based on state not changing.
         * Quick access to state as well.
         */
        computed: {
            // Slice out state properties
            ...mapState({
                completedRoutes: ({ kyc }) =>
                    kyc.completedRoutes,
                currentRoute: ({ kyc }) =>
                    kyc.currentRoute,
            }),
            // Access globabl getters
            ...mapGetters([
                'UID',
                'percentageComplete'
            ]),
        },
        /**
         * methods object
         * Methods that are not cached based on state.
         */
        methods: {
            // Pull in required action methods
            ...mapActions([
                LOAD_USER,
                LOAD_COUNTRIES,
            ]),
            // Pull in required mutations
            ...mapMutations([
                SET_UI_COUNTRY
            ])
        },
    }
</script>