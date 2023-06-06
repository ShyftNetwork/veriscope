<template>
  <div>
    <div v-if="!hidesearch" class="form-control form-control--search mb-8 lg:max-w-xs">
      <input type="text" v-model="searchTerm" placeholder="Search">
      <img src="/images/icon-search.svg" class="icon-search" alt="Search Icon">
    </div>
    <vue-good-table
      mode="remote"
      @on-page-change="onPageChange"
      :totalRows="totalRecords"
      @on-sort-change="onSortChange"
      @on-per-page-change="onPerPageChange"
      @on-search="onSearch"
      @on-cell-click="onCellClick"
      :search-options="{
        enabled: true,
        externalQuery: searchTerm
      }"
      :pagination-options="{
        enabled: haspagination
      }"
      :rowStyleClass='rowStyleClassFn'
      :rows="rows"
      :columns="columns"
    >
      <template slot="table-row" slot-scope="props">
        <span v-if="props.column.field == 'active' && props.row.active != '-'">
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
  data(){
    return {
      searchTerm: '',
      serverParams: {
        // a map of column filters example: {name: 'john', age: '20'}
        columnFilters: {
        },
        sort: {
          field: '', // example: 'name'
          type: '' // 'asc' or 'desc'
        },
        page: 1, // what page I want to show
        perPage: 10, // how many items I'm showing per page
      },
      totalRecords: 0,
      rows: [],
    };
  },
  mounted: function() {
    if(this.initialSort) {
      this.serverParams.sort = this.initialSort;
    }
    this.loadItems();


  },
  props: {
      filter: {
          type: String,
          required: false
      },
      url: {
          type: String,
          required: false
      },
      columns: {
          type: Array,
          required: true
      },
      searchOptions: {
          type: Object,
          required: false
      },
      haspagination: {
        type: Boolean,
        default: true
      },
      whenUpdatedRefetch: {
        required: false
      },
      hidesearch: {
        type: Boolean,
        required: false,
        default: false
      },
      initialSort: {
        type: Object,
        required: false,
      },
  },
  methods: {
      updateParams(newProps) {
        //console.log('updateParams', newProps);
        this.serverParams = Object.assign({}, this.serverParams, newProps);
      },
      onPageChange(params) {
        //console.log('onPageChange', params);
        this.updateParams({page: params.currentPage});
        this.loadItems();
      },
      onPerPageChange(params) {
        //console.log('onPerPageChange', params);
        this.updateParams({perPage: params.currentPerPage});
        this.loadItems();
      },
      onSortChange(params) {
        //console.log('onSortChange', params);
        this.updateParams({
          sort: {
            type: params.sortType,
            field: this.columns[params.columnIndex].field,
          }
        });
        this.loadItems();
      },
      onColumnFilter(params) {
        //console.log('onColumnFilter', params);
        // disabled when search enabled
        this.updateParams(params);
        this.loadItems();
      },
      mySearchFn(row, col, cellValue, searchTerm) {
         return cellValue === 'my value';
      },
      onSearch(params) {
        this.updateParams({
          'searchTerm': this.searchTerm,
          'currentPage': 1,
        });
        this.loadItems();
      },
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
      // load items is what brings back the rows from server
      loadItems() {
        let params = this.serverParams;
        if(this.filter !== '') {
          params.filter = this.filter;
        }
        window.axios.get(this.url, { params: params })
            .then(({data}) => {
                //console.log(this.serverParams);
                this.totalRecords = data.totalRecords;
                data.rows.map(r => {
                  r['crypto_address'] = tableFormatter(r['crypto_address'], 'crypto_type');
                  r['shyft_creds'] = tableFormatter(r['shyft_creds'], 'credits');
                  r['bonus_creds'] = tableFormatter(r['bonus_creds'], 'credits');
                  r['usd'] = tableFormatter(r['usd'], 'currencyFormat');
                  r['address'] = tableFormatter({address:r['address'],type:r['type']}, 'addressLookup');
                });
                this.rows = data.rows;
            })
            .catch((response) => {
                //alert('fail');
            });
      },

      onCellClick: function(params) {
        this.$emit('on-cell-click', params);
      },

  },
  watch: {
    whenUpdatedRefetch() {
      this.loadItems();
    }
  }
};
</script>
