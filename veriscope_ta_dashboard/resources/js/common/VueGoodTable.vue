<template>
  <div>
    <vue-good-table
      :rowStyleClass='rowStyleClassFn'
      :rows="rows"
      :columns="columns"
    >
      <template slot="table-row" slot-scope="props">
        <span v-if="props.column.field == 'action'">
          <simple-button :on-click=setupActiveToggleEmit(props.row) class="btn--sm">{{props.row.active}}</simple-button>
        </span>
        <span v-else-if="props.column.html">
          <span v-html=props.formattedRow[props.column.field]>{{props.formattedRow[props.column.field]}}</span>
        </span>
        <span v-else>
          {{props.formattedRow[props.column.field]}}
        </span>
      </template>
    </vue-good-table>
  </div>
</template>

<script>
import { VueGoodTable } from 'vue-good-table';
import SimpleButton from './SimpleButton';

import {
  tableFormatter,
} from '../utilities';

// import the styles
//import 'vue-good-table/dist/vue-good-table.css'

export default {
  components: {
    VueGoodTable,
    SimpleButton
  },
  name: 'good-table',
  props: {
      columns: {
          type: Array,
          required: true
      },
      url: {
          type: String,
          required: true
      },
      rows: {
          type: Array,
          required: true
      },
  },
  methods: {
      rowStyleClassFn(row) {
        return row.last_state == 'Rejected' ? 'danger' : '';
      },
      /**
       * A very specific method which, when the column indicates
       * it is an Active toggle, it will bind a click event and emit
       * the row's data. Used specifically for adding callback functionality
       * when an Active Toggle button is clicked.
       */
      setupActiveToggleEmit(row) {
        return () => this.$emit('toggle-active',row);
      },

    
  },
};
</script>
