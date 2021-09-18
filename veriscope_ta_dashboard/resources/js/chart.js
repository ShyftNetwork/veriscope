import Vue from 'vue'
import VueCharts from 'vue-chartjs'

Vue.use(VueCharts)

Vue.component('line-chart', {
  extends: VueCharts.Line,
  props: {
      datasets: {
          type: Array,
          required: true
      },
  },
  mounted () {
    this.renderChart({
      datasets: this.datasets,
    }, {responsive: true, maintainAspectRatio: false})
  }
})
