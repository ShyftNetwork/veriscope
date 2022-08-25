<template>
    <div>
        <div class="step-wizard">
            <div class="step-wizard__step" 
                :class="{ 
                    'step-wizard__step--complete': completedRoutes.includes('attestations-manage-organization'),
                    'step-wizard__step--active': currentRoute === 'attestations-manage-organization' 
                }"
            ><a href="manage-organization">Manage Organization<span class="hidden xl:inline"></span></a></div>
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

    export default {
        
        /**
         * computed object
         * Methods that are be cached based on state not changing.
         * Quick access to state as well.
         */
        computed: {
            // Slice out state properties
            ...mapState({
                completedRoutes: ({ attestations }) =>
                    attestations.completedRoutes,
                currentRoute: ({ attestations }) =>
                    attestations.currentRoute,
            }),
            // Access globabl getters
            ...mapGetters([
                'percentageComplete'
            ]),
        },
       
    }
</script>