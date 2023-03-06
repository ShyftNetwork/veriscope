<template>
    <div>
        <div class="step-wizard">
            <div class="step-wizard__step">
                <a href="#toTA">Trust Anchor Account<span class="hidden xl:inline"></span></a>
            </div>
            <div class="step-wizard__step">
                <a href="#toTAststus">Verification Status<span class="hidden xl:inline"></span></a>
            </div>
            <div class="step-wizard__step">
                <a href="#toTAtoken">Token Balance<span class="hidden xl:inline"></span></a>
            </div>
            <div class="step-wizard__step">
                <a href="#toIVMStable">IVMS data<span class="hidden xl:inline"></span></a>
            </div>
            <div class="step-wizard__step" >
                <a href="#toKeyValues">On-chain Data<span class="hidden xl:inline"></span></a>
            </div>
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