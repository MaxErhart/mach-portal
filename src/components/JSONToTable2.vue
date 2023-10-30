<template>
  <div class="json-to-table">
    


    <div class="options-bar">
      <div class="selected" v-if="select">
        Selected: {{selected?.length}}/{{data?.length}}
      </div>
      <div class="showing">
        Showing: {{filter(decorate(data))?.length}}/{{data?.length}}
      </div>
      <div class="download">
        <button @click.prevent="download()">Download</button>
      </div>
      <div class="search">
        <input type="text" placeholder="Search table..." v-model="filter_string">
      </div>
    </div>

    <div class="table" :style="table_style" ref="table">


      <div class="table-column" v-if="select" :class="{active:'select'===active_column_filter_id}">
        <div class="column-left column-left-checkbox"></div>
        <div class="column-center column-center-checkbox">
          <Checkbox label="" @change="selectAll($event)" :presetValue="allSelected" fill="#fff" stroke="#00876c"/>
        </div>
        <div class="column-right" v-if="col_filter">

          <button class="column-filter-button" @click="openColumnFilter($event, {id:'select'})" :class="{active:'select' in column_filter_values,'popup-open': 'select'===active_column_filter_id}">
            <ion-icon class="icon" name="chevron-down"></ion-icon>
          </button>

          <div class="popup-overlay" @click="closeColumnFilter()" v-if="'select'===active_column_filter_id"></div>


          <div class="column-filter-popup" v-if="'select'===active_column_filter_id" :style="column_filter_popup_style">
            <div class="column-filter-popup-head">
              <button class="close" @click="closeColumnFilter()">
                <ion-icon name="close-outline"></ion-icon>
              </button>
            </div>
            <div class="column-filter-popup-body">
              <div class="column-filter-popup-all">
                <Checkbox label="Select all" @change="selectAllFilter($event,{id:'select'})" :presetValue="allSelectedFilter({id:'select'})"/>
              </div>
              <div class="column-filter-popup-single">
                <Checkbox :presetValue="singleSelectedFilter({id:'select'},value)" v-for="value in ['selected', 'unselected']" :key="value" :label="value" @change="selectSingleFilter($event,{id:'select'},value)"/>
              </div>
            </div>
          </div>


        </div>
      </div>
      <div class="table-column" v-for="(column) in columns.filter(column=>!('show' in column) || column.show)" :key="column.id" :class="{active:column.id===active_column_filter_id}">

        <div class="column-left" @click="setSortByColumn(column)">

          <div class="icon-wrapper" :class="{active: sort_by_col===column.id && !sort_asc}">
            <ion-icon name="chevron-up-outline" ></ion-icon>
          </div>
          <div class="icon-wrapper" :class="{active: sort_by_col===column.id && sort_asc}">
            <ion-icon name="chevron-down-outline" ></ion-icon>
          </div>

        </div>

        <div class="column-center" >
          {{column.name}}
        </div>

        <div class="column-right" v-if="col_filter">
          <button class="column-filter-button" @click="openColumnFilter($event, column)" :class="{active:column.id in column_filter_values,'popup-open': column.id===active_column_filter_id}">
            <ion-icon class="icon" name="chevron-down"></ion-icon>
          </button>


          <div class="popup-overlay" @click="closeColumnFilter()" v-if="column.id===active_column_filter_id"></div>


          <div class="column-filter-popup" v-if="column.id===active_column_filter_id" :style="column_filter_popup_style">
            <div class="popup-head">
              <button class="close" @click="closeColumnFilter()">
                <ion-icon name="close-outline"></ion-icon>
              </button>
            </div>
            <div class="column-filter-popup-body">
              <div class="column-filter-popup-all">
                <Checkbox label="Select all" @change="selectAllFilter($event,column)" :presetValue="allSelectedFilter(column)"/>
              </div>
              <div class="column-filter-popup-single">
                <Checkbox :presetValue="singleSelectedFilter(column,value)" v-for="value in unique_values(column)" :key="value" :label="value" @change="selectSingleFilter($event,column,value)"/>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="table-row" :ref="`row_${row.index}`" :class="{'highlight-1':highlight_1_row_index==row.index,'highlight-2':highlight_2_row_index==row.index}" v-for="(row) in sort(filter(decorate(data)))" :key="row.index" @click="handleRowClick($event,row)">
        <div class="table-entry" v-if="select">
          <Checkbox label="" @change="selectSingle($event,row.index)" :presetValue="singleSelected(row.index)"/>
        </div>
        <div class="table-entry" :class="`error-${errors?.[row.index]?.[index]}`" v-for="(column,index) in columns.filter(column=>!('show' in column) || column.show)" :key="column.id">
          <div class="content" v-html="getRowData(row,column.id)" ></div>
        </div>
      </div>

      <!-- <div class="popup-overlay" @click="closeRowmenu()" v-if="active_rowmenu_index!==null"></div> -->

      <div class="table-row-menu" v-if="active_rowmenu_index!==null" :style="rowmenu_style">
        <div class="popup-head">
          <button class="close" @click="closeRowmenu()">
            <ion-icon name="close-outline"></ion-icon>
          </button>
        </div>
        <div class="popup-body">
          <div class="popup-option" v-for="option in filterByPermission(rowmenu, data[active_rowmenu_index])" :key="option.id">
            <div class="first-step" v-if="option.two_step && (option_steps[option.id]===0 || !(option.id in option_steps))">
              <button class="table-row-option"  @click="option_steps[option.id]=1">
                <ion-icon :name="option.icon"></ion-icon>
                <span>{{option.text}}</span>
              </button>
            </div>
            <div class="first-step" v-if="!option.two_step">
              <button class="table-row-option"  @click="handleRowOption(option)">
                <ion-icon :name="option.icon"></ion-icon>
                <span>{{option.text}}</span>
              </button>
            </div>
            <div class="second-step" v-if="option.two_step && option_steps[option.id]===1">
              <button class="table-row-option" @click="handleRowOption(option);option_steps[option.id]=0">
                <ion-icon name="checkmark-outline"></ion-icon>
                <span>Confirm</span>
              </button>
              <button class="table-row-option" @click="option_steps[option.id]=0">
                <ion-icon name="close-outline"></ion-icon>
                <span>Cancel</span>
              </button>
            </div>
          </div>


        </div>
      </div>

    </div>
    

  </div>
</template>

<script>
import Checkbox from '@/components/inputs_23/Checkbox.vue' 
export default {
  name: 'JSONToTable',


  components: {
    Checkbox,
  },


  props: {
    col_filter: Boolean,
    // Array of table data each
    // Each entry is of the form {id1: val1, id2: val2, ...}
    // Each id uniquely identifies the column
    // Only values with an id corresponding to an id in the columns prop are displayed
    data: Array,
    
    
    // Array of the tables header
    // Columns are displayed in the order they are listed
    // Each entry is of the form {id: column_id, name: column_name, show: Boolean}
    columns: Array,

    // Show / Hide column for row selection
    select: Boolean,

    // permission for the dataset effects rowmenu options
    dataset_permission: [String, Number],

    // Array of menu options when clicking on a row
    // Rows must be an object containing:
    //  - id: unique option identifyer
    //  - icon: icon from the ion icons set
    //  - text: text to be displayed for the option
    //  - emit: name of the emit
    //  - permission: a level of permission for an option to be visible dependant on the permission of the row clicked (row must contain a permission key)
    //  - dataset_permission: a level of permission for an option to be visible dependant on the permission for the dataset
    rowmenu: Array,


    errors: Array,

    // Determines if element inside the data array are nested
    // If true the column.id will be split at "." and each fragment treated as a nested layer from where data is retrieved
    nested: {
      default: false,
      type: Boolean,
    },

  },

  

  data() {
    return {
      acitve_column_filter_settings: {width: 310, height: 400},
      active_column_filter_id: null,
      acitve_column_filter_offset_left: 0,
      column_filter_values: {},
      filter_string: null,

      sort_by_col: null,
      sort_asc: true,

      selected: [],

      active_rowmenu_index: null,
      active_rowmenu_offset_left: 0,
      active_rowmenu_offset_top: 0,
      highlight_1_row_index: null,
      highlight_2_row_index: null,
      rowmenu_settings: {width: 310, height: 400},

      option_steps: {},
    }
  },

  computed: {

    selected_rows() {
      const rows = []
      this.selected.forEach((index)=>{
        rows.push(this.data[index])
      })
      return rows
    },

    allSelected() {
      if(this.filter(this.decorate(this.data)).length===this.selected.length) {
        return true
      }
      return false
    },
  

    rowmenu_style() {
      return {
        'left': this.active_rowmenu_offset_left+'px',
        'top': this.active_rowmenu_offset_top+'px',
        'width': this.rowmenu_settings.width+'px',
        'max-height': this.rowmenu_settings.height+'px',
      }
    },

    column_filter_popup_style() {
      return {
        'left': this.acitve_column_filter_offset_left+'px',
        'width': this.acitve_column_filter_settings.width+'px',
        'max-height': this.acitve_column_filter_settings.height+'px',
      }
    },


    table_style() {
      if(!this.columns?.filter(column=>!('show' in column) || column.show)?.length>0) {
        return {}
      }
      var grid_template_columns = `repeat(${this.columns.filter(column=>!('show' in column) || column.show).length}, max-content)`
      
      if(this.select) {
        grid_template_columns = `132px repeat(${this.columns.filter(column=>!('show' in column) || column.show).length}, max-content)`
      }
      return {
        'grid-template-columns': grid_template_columns,
      }
    }
  },



  methods: {
    getRowData(row,column_id) {
      if(!this.nested) {
        return row[column_id]
      }
      const fragments = column_id.split(".")
      var tmp_value = row[fragments[0]]
      for(let i=1;i<fragments.length;i++) {
        tmp_value = tmp_value[fragments[i]]
      }
      return tmp_value
      
    },
    getOptionStep(option) {
      console.log(option)
      return true
    },
    async highlight(index) {
      this.highlight_1_row_index = null
      this.highlight_2_row_index = index
      var count = 0
      while(count<100) {
        count += 1
        await this.$nextTick()
      }
      this.$refs[`row_${index}`].firstElementChild.scrollIntoView({ behavior: "smooth", block: "end" })


    },

    handleRowOption(option) {
      this.$emit('option', {option, index:this.active_rowmenu_index,row:this.data[this.active_rowmenu_index]})
      this.closeRowmenu()

    },


    filterByPermission(rowmenu,row) {
      // const row = this.data[this.active_rowmenu_index]
      if(!rowmenu) {
        return []
      }
      return rowmenu.filter(option=>{
        if(row.permission===null || row.permission===undefined || row.permission<option.permission) {
          return false
        }
        if(this.dataset_permission!==null && this.dataset_permission!==undefined && option.dataset_permission>this.dataset_permission) {
          return false
        }
        return true 
      })
    },

    closeRowmenu() {
      this.active_rowmenu_index = null
      this.active_rowmenu_offset_left = 0
    },

    handleRowClick(event,row) {
      if(this.active_rowmenu_index!==row.index) {
        this.option_steps = {}
      }
      this.$emit('rowClick', row)
      var dom_element = event.target

      while(!dom_element.classList.contains('checkbox')) {
        if(dom_element.classList.contains('json-to-table') || dom_element.classList.contains('table-row') || dom_element.classList.contains('table-entry') || dom_element.classList.contains('content')) {
          break
        }
        dom_element = dom_element.parentNode
        return
      }
      this.highlight_1_row_index = row.index
      if(this.filterByPermission(this.rowmenu,row)?.length<1) {
        return
      }
      this.active_rowmenu_offset_left = event.x - this.$refs.table.getBoundingClientRect().x
      this.active_rowmenu_offset_top = event.target.getBoundingClientRect().y + event.target.getBoundingClientRect().height - this.$refs.table.getBoundingClientRect().y
      const window_overflow = window.innerWidth - event.x - this.rowmenu_settings.width
      if(window_overflow<0) {
        this.active_rowmenu_offset_left += window_overflow
      }
      this.active_rowmenu_index = row.index
    },


    singleSelected(index) {
      if(this.selected.indexOf(index)!==-1) {
        return true
      }
      return false
    },
    selectSingle(event, index) {
      if(event) {
        this.selected.push(index)
      } else {
        this.selected.splice(this.selected.indexOf(index),1)
      }
      this.$emit('select', {indices:this.selected,rows:this.selected_rows})
    },

    selectAll(event) {
      if(event) {
        this.selected=[]
        this.filter(this.decorate(this.data)).forEach(row=>{
          this.selected.push(row.index)
        })
      } else {
        this.selected = []
      }

      this.$emit('select', {indices:this.selected,rows:this.selected_rows})
    },

    sort(data) {
      if(this.sort_by_col===null) {
        return data
      }
      return data.sort((a,b)=>{
        if(a[this.sort_by_col]===null || a[this.sort_by_col]===undefined) {
          return 2*this.sort_asc - 1
        }
        if(b[this.sort_by_col]===null || b[this.sort_by_col]===undefined) {
          return -(2*this.sort_asc - 1)
        }
        if(typeof a[this.sort_by_col]!=='string') {
          a[this.sort_by_col] = `${a[this.sort_by_col]}` 
        }
        if(typeof b[this.sort_by_col]!=='string') {
          b[this.sort_by_col] = `${b[this.sort_by_col]}` 
        }
        return a[this.sort_by_col].localeCompare(b[this.sort_by_col],undefined, {numeric: true,sensitivity:'base'})*(2*this.sort_asc - 1)

      })
    },

    setSortByColumn(column) {
      if(this.sort_by_col!==column.id) {
        this.sort_asc = true
        this.sort_by_col = column.id
        return
      }
      this.sort_asc = !this.sort_asc
    },

    download() {
      var csv_content = "data:text/csv;charset=utf-8,"
      const delimiter = ";"
      const newline = "\r\n"
      const filter = true
      csv_content += this.columns.map(column=>column.name).join(delimiter)+newline
      var data = this.data
      if(filter) {
        data = this.filter(this.decorate(this.data))
      }
      data.forEach(row=>{
        const values = []
        this.columns.forEach(column=>{
          values.push(row[column.id])
        })
        csv_content += values.join(delimiter)+newline
      })
      const encodedURI = encodeURI(csv_content)
      window.open(encodedURI)
    },
    
    escapeCharacters(string) {
      string=string.replace('[', '\\[')
      string=string.replace(']', '\\]')
      string=string.replace('.', '\\.')
      return string
    },
    decorate(data) {
      return JSON.parse(JSON.stringify(data)).map((row,index)=>{
        row['index'] = index
        return row
      })
    },
    filter(data, skip=null) {
      var selected_changed = false
      const filtered_data = data.filter(row=>{
        var veto_column = false
        var veto_string = true
        if(skip!==null && skip in this.column_filter_values && row[skip] in this.column_filter_values[skip]) {
          return true
        }
        this.columns.filter(column=>!('show' in column) || column.show).forEach(column=>{
          if(veto_column) {
            return
          }
          if(skip!==null && skip==column.id) {
            return
          }
          if(this.filter_string=='' || !this.filter_string || (row[column.id] && `${row[column.id]}`.match(new RegExp(this.escapeCharacters(this.filter_string), "i")))) {
            veto_string = false
          }
          if(!(column.id in this.column_filter_values)) {
            return
          }
          const col_values = Object.keys(this.column_filter_values[column.id])
          for(var j=0;j<col_values.length;j++) {
            if(row[column.id] == col_values[j] || (row[column.id]===null && col_values[j]==="null")) {

              veto_column = true
            }
            if(veto_column) {
              break
            }
          }
        })

        if(veto_column || veto_string) {
          const selected_index = this.selected.indexOf(row.index)
          if(selected_index!==-1) {
            this.selected.splice(selected_index,1)
            selected_changed = true
          }
        }
        if('select' in this.column_filter_values && this.select && !veto_column) {
          if('selected' in this.column_filter_values['select'] && this.selected.indexOf(row.index)!==-1) {
            veto_column = true
          }
          if('unselected' in this.column_filter_values['select'] && this.selected.indexOf(row.index)===-1) {
            veto_column = true
          }
        }
        return !veto_column && !veto_string
      })
      if(selected_changed) {
        this.$emit('select', {indices:this.selected,rows:this.selected_rows})
      }
      return filtered_data
    },

    singleSelectedFilter(column, value) {
      if(column.id in this.column_filter_values && value in this.column_filter_values[column.id]) {
        return false
      }
      return true
    },

    allSelectedFilter(column) {
      if(!(column.id in this.column_filter_values)) {
        return true
      }
      return false
    },

    selectAllFilter(event,column) {
      if(event) {
        if(!(column.id in this.column_filter_values)) {
          return
        }
        delete this.column_filter_values[column.id]
        return
      }

      this.column_filter_values[column.id] = {}
      this.data.forEach(row=>{
        this.column_filter_values[column.id][row[column.id]] = true
      })
    },
    selectSingleFilter(event, column, value) {
      if(event) {
        if(!(column.id in this.column_filter_values)) {
          return
        }
        if(value in this.column_filter_values[column.id]) {
          delete this.column_filter_values[column.id][value]
        }
        if(Object.keys(this.column_filter_values[column.id]).length<1) {
          delete this.column_filter_values[column.id]
        }
        return
      }

      if(!(column.id in this.column_filter_values)) {
        this.column_filter_values[column.id] = {}
      }
      this.column_filter_values[column.id][value] = true
    },

    unique_values(column) {
      const t = this.filter(this.data, column.id).map(row=>row[column.id]).filter((value,index,array)=>{
        return array.indexOf(value) === index
      })
      return t
    },


    openColumnFilter(event, column) {
      this.active_column_filter_id = column.id
      const offset_left = window.innerWidth-event.target.getBoundingClientRect().left - this.acitve_column_filter_settings.width
      if(offset_left<0) {
        this.acitve_column_filter_offset_left = offset_left
      }
    },
    closeColumnFilter() {
      this.active_column_filter_id = null
      this.acitve_column_filter_offset_left = 0
    }
  },
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.column-left-checkbox {
  display: none;
}
.column-center-checkbox {
  grid-column-start:0;
  grid-column-end:2;
}
.options-bar{
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 32px;
  .search {
    input {
      width: 100%;
      max-width: 210mm;
      min-width: 100mm;
      font-size: 18px;
      padding: 8px 16px;
      border-radius: 50px;
      border: 1px solid black;
    }
  }
  .download {
    button {
      border: 1px solid #2C3E50;
      padding: 0.5rem;
      aspect-ratio: 16/9;
      cursor: pointer;
      &:hover {
        background-color: #2C3E50;
        color: white;
      }
    }
  }
}
.popup-overlay {
  position: fixed;
  top:0;
  right:0;
  left:0;
  bottom:0;
  z-index: 1000;
  background-color: rgba(0,0,0,0.02);
}
.table-row-menu {
  position: absolute;
  border: 1px solid black;
  border-radius: 4px;
  background-color: white;
  // top: 100%;
  z-index: 1001;
}
.popup-body {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.popup-option {
  width: 100%;
}
.first-step {
  width: 100%;
}
.second-step {
  display: grid;
  grid-template-columns: 1fr 1fr;
}
.table-row-option {
  display: flex;
  flex-direction: row;
  align-items: center;
  width: 100%;
  padding: 4px;
  gap: 8px;
  font-size: 32px;
  cursor: pointer;
  &:hover {
    background-color: $text-dark;
    color: white;
  }
  > span {
    font-size: 16px;
  }
}
.popup-head {
  width: 100%;
  height: 24px;
  display: flex;
  justify-content: flex-end;
  background-color: #ccc;
  border-top-right-radius: 4px;
  border-top-left-radius: 4px;

  .close {
    font-size: 24px;
    width: 32px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    border-top-right-radius: 4px;
    color: $text_dark;
    &:hover {
      background-color: $error;
      color: white;
    }
    * {
      pointer-events: none;
    }
  }
}
.table {
  position: relative;
  display: grid;
  .table-row {
    position: relative;
    display: contents;
    &.highlight-1{
      > .table-entry {
        background-color: #f7e3bd !important;
      }
    }
    &.highlight-2{
      > .table-entry {
        background-color: rgba(0, 135, 108,0.4) !important;
      }
    }
    
    &:nth-child(2n+1) {
      .table-entry {
        background-color: #f0f0f0;

      }
    }
    .table-entry {
      position: relative;
      display: flex;
      align-items: center;
      font-size: 14px;
      max-width: 620px;
      padding: 1px 8px;
      &.error-0 {
        background-color: rgba(40, 167, 69, 0.5);
      }
      &.error-1 {
        background-color: rgba(255, 193, 7,0.5);
      }
      &.error-2 {
        background-color: rgba(255, 23, 68,0.5);
      }

      .content {
        text-align: left;
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2; /* number of lines to show */

      }
    }
  }
  .table-column {
    position: sticky;
    top:38px;
    background-color: $kit_green;
    border: 1px solid $kit_green;
    color: white;
    display: grid;
    grid-template-columns: 16px auto 32px;
    gap: 16px;
    padding: 5px 7px;
    align-items: center;
    max-width: 620px;
      z-index: 999;
    &.active {
      z-index: 1000;
    }
    .column-left {
      cursor: pointer;
      .icon-wrapper {
        color: #bbb;
        height: 16px;
        &.active {
          color: white;
        }
      }
    }
    .column-center {
      white-space: nowrap;
      // text-align: start;
    }
    .column-right {
      position: relative;

      .column-filter-button {
        position: relative;
        width: 100%;
        height: 24px;

        border: 1px solid white;
        border-radius: 2px;

        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        * {
          pointer-events: none;
        }
        .icon {
          transition: transform 0.225s ease;
        }
        &.popup-open {
          .icon {
            transform: rotate(180deg);
          }
        }
        &.active {
          color: $kit_green;
          background-color: white;
        }
      }

      .column-filter-popup {
        position: absolute;
        z-index: 1001;
  
        overflow-y:auto;
        border: 1px solid black;
        border-radius: 4px;
        background: white;


        .column-filter-popup-body {
          .column-filter-popup-all {
            color: $text_dark;
              padding: 8px;
            // font-size: 32px;
            border-bottom: 1px solid gray;

          }
          .column-filter-popup-single {
            padding: 8px;
            color: $text_dark;
            display: flex;
            flex-direction: column;
            gap: 8px;
          }
        }
      }
    }
  }
}
</style>
