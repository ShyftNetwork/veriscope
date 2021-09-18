/**
 * Usage
        <pie-chart :datasets="{
            datasets: [{
                data: [10, 90],
                backgroundColor: [
                    '#FFF',
                    '#333'
                ],
                borderWidth: [ 0,0 ]
            }],
            labels: [
                'Red',
                'Yellow',
            ],
        }"></pie-chart>
        <pie-chart :percentage="100" color="#333"></pie-chart>
 */

import Vue from 'vue'
import VueCharts from 'vue-chartjs'

Vue.use(VueCharts)

Vue.component('pie-chart', {
    extends: VueCharts.Pie,
    props: {
        datasets: {
            type: Object,
            required: false,
            default: null
        },
        percentage: {
            type: Number,
            required: false,
            default: 100,
            validator: function (value) {
                return (value <= 100) && (value >= 0);
            }
        },
        fill: {
            type: String,
            required: false,
            default: '#000'
        }

    },
    mounted () {
        /**
         * Initialization
         */
        const datasets = this.buildDataSets();
        const options = this.buildDataOptions();
        this.init(datasets, options);
    },

    computed: {
        /**
         * Return the percentage remaining since we need
         * to hollow out the Pie Chart with whatever remains
         * in the overall Pie value
         */
        percentageRemaining() {
            return 100 - this.percentage;
        },
        /**
         * Return the cutour colour based on the percentage.
         * When 100%, we don't want to see a white line
         */
        cutoutColour() {
            return (this.percentageRemaining === 0) ? this.fill : '#fff';
        }
    },

    methods: {
        /**
         * Used to build out the required dataset
         * prop for the underlying Pie Chart
         */
        buildDataSets() {
            if(!this.datasets) {
                return {
                    datasets: [{
                        data: [this.percentageRemaining, this.percentage],
                        backgroundColor: [this.cutoutColour, this.fill],
                        borderWidth: [0, 0],
                        
                    }],
                    labels: ['Purchased', 'Remaining'],

                }
            }
            return this.datasets;
        },
        /**
         * Used to build out the options object
         * prop for the underlying Pie Chart
         */
        buildDataOptions() {
            if(!this.datasets) {
                return {
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled: false
                    }
                }
            }
        },

        /**
         * 
         * @param {Object} datasets - Signature for Pie Chart lib 
         */
        init(datasets={}, options={}) {
            this.renderChart(datasets, options);
        }
    }
});
