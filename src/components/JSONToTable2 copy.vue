<template>

  <div class="table-options">
    <button class="option download" @click.prevent="download()">Download</button>
  </div>

  <div class="searchbar">
    <input class="searchbar-input" placeholder="Search table..." type="text" v-model="searchString">
    <div>
      Displaying {{filterByCol(filter(data_decorated))?.length}} / {{data_decorated?.length}} rows.
    </div>
    <div>Selected {{Object.keys(selected)?.length}} / {{data_decorated?.length}} rows.</div>
  </div>

  <div class="table" :style="table_style" v-if="!loading">
    <div class="table-header-row-select" v-if="select" >
      <Checkbox class="all-select-box" :invert="true" :clean="true" @inputChange="selectAll($event)" :presetValue="all_selected"/>
      <div class="header-filter-wrapper">
        <div class="header-filter" :class="`header-filter-specific-${name}`" :style="header_filter_style('table_select')" @click="openColumnFilter($event, 'table_select')">
          <ion-icon class="icon" :style="header_filter_style_icon('table_select')" name="chevron-down"></ion-icon>
        </div>
      </div>
    </div>
    <div class="table-header-row" v-for="column in header" :key="column">
      <div class="header-name" @click="setDataSorting(column.id)" v-if="use_header_id">{{column.name}}</div>
      <div class="header-name" @click="setDataSorting(column)" v-else>{{column}}</div>
      <div class="header-filter-wrapper">
        <div class="header-filter" :class="`header-filter-specific-${name}`" :style="header_filter_style(column)" @click="openColumnFilter($event, column)">
          <ion-icon class="icon" :style="header_filter_style_icon(column)" name="chevron-down"></ion-icon>
        </div>
      </div>
    </div>

    <div class="table-row table-data-row" :class="{highlight: highlight_row_id==row.id}" v-for="(row,row_index) in sortData(filterByCol(filter(data_decorated)))" :key="row" @click.stop="test($event, row, row_index)" :ref="`row_id_${row.id}`">
      

      <div class="table-cell select-cell" :class="{active: row.id===active_row.id}" v-if="select">
        <Checkbox :clean="true" @inputChange="selectSingle($event, row)" :presetValue="isSelected(row)"/>
      </div>

      <div class="table-cell" :class="{active: row.id===active_row.id}" v-for="(column,index) in header" :key="column">

        <div class="row-notification" v-if="annotations && row.id in annotations && index===0" :style="row_notification_style">
          <ion-icon class="row-notification-icon" name="chatbox"></ion-icon>

          <span class="row-notification-text">
            {{notifications(annotations[row.id])}}
          </span>
        </div>

        <div class="cell-content" :style="{'font-size':fontsize+'px'}" v-html="getCellData(column,row)"></div>
      </div>
    </div>


    <div class="column-filter-window" :style="column_filter_window_style">
      <div class="header">
          <Checkbox @inputChange="checkAllFilters(column_filter_window.column)" :presetValue="allCheckedFilter(column_filter_window.column)" :clean="true" label="Select all"/>
      </div>
      <div class="body">
        <div class="option" v-for="(option) in column_filter_window_options" :key="option">
          <Checkbox @inputChange="checkSingleFilters(column_filter_window.column, option)" :presetValue="singleCheckedFilter(column_filter_window.column,option)" :clean="true" :label="option"/>
        </div>
      </div>
    </div>

    <div class="table-row-options-menu" v-if="active_row.options && filterPermissions(menuOptions)?.length>0" :style="table_row_options_menu_style">
      <div class="table-row-options-header">
        <ion-icon class="close-icon" name="close-outline" @click="closeTableRowOptions()"></ion-icon>
      </div>
      <div class="table-row-options-body">
        <div class="table-row-option" v-for="option in filterPermissions(menuOptions)" :key="option">

          <template v-if="((!(option.id in option_step) || option_step[option.id]===1) && option.twoStep) || !option.twoStep">
            <button @click.prevent.stop="handleOption(option)">
              <ion-icon class="option-icon" :name="option.icon"></ion-icon>
              <span>{{option.text}}</span>
            </button>
          </template>

          <template v-else>
            <button @click="handleOption(option)">
              <ion-icon class="option-icon" name="checkmark-outline"></ion-icon>
              <span>Confirm</span>
            </button>
            <button @click.prevent.stop="option_step[option.id]=1">
              <ion-icon class="option-icon" name="close-outline"></ion-icon>
              <span>Cancel</span>
            </button>
          </template>
        </div>
      </div>
    </div>
  </div>
  <template v-else>
    <DataPlaceholder animation="table"/>
  </template>



</template>

<script>
import Checkbox from '@/components/inputs/Checkbox.vue'
import DataPlaceholder from '@/components/DataPlaceholder.vue'
export default {
  name: 'JSONToTable',
  components: {
    Checkbox,
    DataPlaceholder,
  },
  emits: [
    // Emits Array of selected row from data Array
    'select',
    'deselect',
    'selected',
    /*
    Emits Object with keys
      option: Contains the option Object that has been clicked on
      row: Contains the row from the data Array that as been clicked on
    */
    'optionClick',

    // Emits the clicked on row from the data Array
    'rowClick',

  ],

  props: {
    dataset_permission: [String, Number],
    fontsize: {
      default: 16,
      type: Number,
    },
    use_header_id: {
      default: false,
      type: Boolean,
    },
    data: Array,
    header: Array,
    menuOptions: {
      default: () => [],
      type: Array,
    },
    loading: {
      default: false,
      type: Boolean,
    },
    select: {
      default: false,
      type: Boolean,
    },
    name: {
      defualt: '',
      type: String,
    },
    annotations: Object,
  },
  data() {
    return {
      dhref: null,
      searchString: '',
      active_row: {id: null,top:null,translateY: 0, left:null, options:false, data:null},
      selected: {},
      column_filters: {},
      acitve_filter: {column:null},
      option_step: {},
      column_filter_window: {column: null,x:0,y:0,width:256},
      highlight_row_id: null,
      sorting: {col:null,asc:false},
    }
  },
  mounted() {
    window.addEventListener("click", this.handleClick);
    
  },
  watch: {
    searchString() {
      const new_selected = {}
      this.filter(this.data_decorated).forEach(row=>{
        if(row.id in this.selected) {
          new_selected[row.id] = true
        }
      })
      this.selected = new_selected
    }
  },
  computed: {
    column_filter_window_options() {
      if(this.column_filter_window.column==='table_select') {
        return ['Selected', 'Unselected']
      }
      if(this.use_header_id) {
        const unique_values = this.filterByCol(this.filter(this.data_decorated),this.column_filter_window.column)?.map(row=>{
          if(typeof row[this.column_filter_window.column?.id]!=='string') {
            return JSON.stringify(row[this.column_filter_window.column?.id])
          }
          return row[this.column_filter_window.column?.id]
        }).filter((value,index,self) => self.indexOf(value)==index)
        return unique_values
      } else {
        const unique_values = this.filterByCol(this.filter(this.data_decorated),this.column_filter_window.column)?.map(row=>{
          if(typeof row[this.column_filter_window.column]!=='string') {
            return JSON.stringify(row[this.column_filter_window.column])
          }
          return row[this.column_filter_window.column]
        }).filter((value,index,self) => self.indexOf(value)==index)
        return unique_values
      }
    },
    column_filter_window_style() {
      return {
        'left': this.column_filter_window.x+'px',
        'top': this.column_filter_window.y+'px',
        'display': this.column_filter_window.column===null?'none':'block',
        'position': 'absolute',
        'width': this.column_filter_window.width+'px',
        'z-index': '100',
        'background-color': 'white',

      }
    },
    row_notification_style() {
      var offset = -12
      if(this.select) {
        offset = -120
      }
      return {
        'left': offset+'px'
      }
    },
    data_decorated() {
      return this.data
    },
    all_selected() {
      var all_selected = true
      console.log(this.data_decorated)
      this.filter(this.data_decorated).forEach(row=>{
        if(!(row.id in this.selected)) {
          all_selected=false
        }
      })
      return all_selected
    },
    table_style() {
      var n_cols = this.header?.length
      // var grid_templace_columns = `repeat(${n_cols}, auto)`
      var grid_templace_columns = `repeat(${n_cols}, max-content)`
      if(this.select) {
        // grid_templace_columns = `104px repeat(${n_cols}, auto)`
        grid_templace_columns = `104px repeat(${n_cols}, max-content)`
      }
      return {
        '--n_cols': n_cols,
        '--acitve_row_translateY': this.active_row.translateY+'px',
        'grid-template-columns': grid_templace_columns,
      }
    },
    table_row_options_menu_style() {
      return {
        'top': this.active_row.top + 'px',
        'left': this.active_row.left + 'px'
      }
    }
  },
  methods: {
    download() {
      if(this.use_header_id) {
      let csvContent = "data:text/csv;charset=utf-8,"
      csvContent += this.header.map(header=>header.name).join(";")+"\r\n"
      this.data.forEach(row=>{
        const vals = []
        this.header.forEach(header=>{
          vals.push(row[header.id])
        })
        csvContent += vals.join(";")+"\r\n"
      })
      const encodedURI = encodeURI(csvContent)
      window.open(encodedURI)
      } else {
        let csvContent = "data:text/csv;charset=utf-8,"
        csvContent += this.header.join(";")+"\r\n"
        this.data.forEach(row=>{
          const vals = []
          this.header.forEach(header=>{
            vals.push(row[header])
          })
          csvContent += vals.join(";")+"\r\n"
        })
        const encodedURI = encodeURI(csvContent)
        window.open(encodedURI)
      }
    },
    setDataSorting(column) {
      if(column===this.sorting.col) {
        this.sorting.asc = !this.sorting.asc
        return
      }
      this.sorting.col = column
    },
    sortData(data) {
      if(this.sorting.col===null) {
        return data
      }

      return data.sort((a,b)=> {
        if(a[this.sorting.col]===null || a[this.sorting.col]===undefined) {
          return 2*this.sorting.asc - 1
        }
        if(b[this.sorting.col]===null || b[this.sorting.col]===undefined) {
          return -(2*this.sorting.asc - 1)
        }
        if(typeof a[this.sorting.col]!=='string') {
          a[this.sorting.col] = `${a[this.sorting.col]}` 
        }
        if(typeof b[this.sorting.col]!=='string') {
          b[this.sorting.col] = `${b[this.sorting.col]}` 
        }
        return a[this.sorting.col].localeCompare(b[this.sorting.col],undefined, {numeric: true,sensitivity:'base'})*(2*this.sorting.asc - 1)
      })
    },
    async highlight(row) {

      var count = 0
      this.highlight_row_id = row.id

      while(!this.$refs[`row_id_${row.id}`]?.firstElementChild && count<4000) {
        count += 1
        await this.$nextTick()
      }

      this.$refs[`row_id_${row.id}`]?.firstElementChild.scrollIntoView({ behavior: "smooth", block: "end" });
    },
    filterByCol(data, skip) {
      if(this.use_header_id) {
        return data?.filter(row=>{
          var veto = false
          this.header?.forEach(column=>{
            if(veto) {
              return
            }
            if('table_select' in this.column_filters) {
              if('Selected' in this.column_filters['table_select']) {
                if(row.id in this.selected) {
                  veto = true
                }
              }
              if('Unselected' in this.column_filters['table_select']) {
                if(!(row.id in this.selected)) {
                  veto = true
                }
              }
            }
            if(column?.id==skip?.id) {
              return
            }
            if(!(column?.id in this.column_filters) || veto) {
              return
            }
            if(row[column?.id] in this.column_filters[column?.id]) {
              veto = true
            }
          })
          return !veto
        })
      } else {
        return data?.filter(row=>{
          var veto = false
          this.header?.forEach(column=>{
            if(veto) {
              return
            }
            if('table_select' in this.column_filters) {
              if('Selected' in this.column_filters['table_select']) {
                if(row.id in this.selected) {
                  veto = true
                }
              }
              if('Unselected' in this.column_filters['table_select']) {
                if(!(row.id in this.selected)) {
                  veto = true
                }
              }
            }
            if(column==skip) {
              return
            }
            if(!(column in this.column_filters) || veto) {
              return
            }
            if(row[column] in this.column_filters[column]) {
              veto = true
            }
          })
          return !veto
        })
      }

    },
    header_filter_style(column) {
      if(this.use_header_id) {
        if(column?.id in this.column_filters) {
          return {
            'color': '#00876c',
            'background-color': 'white',
          }
        }
      } else {
        if(column in this.column_filters) {
          return {
            'color': '#00876c',
            'background-color': 'white',
          }
        }
      }
      return {}
    },
    header_filter_style_icon(column) {
      if(this.use_header_id) {
        if(this.column_filter_window.column?.id===column?.id) {
          return {
            'transform': 'rotate(180deg)',
          }
        }
      } else {
        if(this.column_filter_window.column===column) {
          return {
            'transform': 'rotate(180deg)',
          }
        }
      }

      return {}
    },
    checkAllFilters(column) {
      if(this.use_header_id) {
        if(this.allCheckedFilter(column)) {
          this.column_filters[column?.id] = {}
          this.column_filter_window_options.forEach(option=>{
            this.column_filters[column?.id][option] = true
          })
        } else {
          delete this.column_filters[column?.id]
        }
      } else {
        if(this.allCheckedFilter(column)) {
          this.column_filters[column] = {}
          this.column_filter_window_options.forEach(option=>{
            this.column_filters[column][option] = true
          })
        } else {
          delete this.column_filters[column]
        }
      }

    },
    checkSingleFilters(column, option) {
      if(this.use_header_id) {
        if(this.singleCheckedFilter(column, option)) {
          if(column?.id in this.column_filters) {
            this.column_filters[column?.id][option] = true
          } else {
            this.column_filters[column?.id] = {}
            this.column_filters[column?.id][option] = true
          }
        } else {
          delete this.column_filters[column?.id][option]
          if(this.allCheckedFilter(column)) {
            delete this.column_filters[column?.id]
          }
        }
      } else {
        if(this.singleCheckedFilter(column, option)) {
          if(column in this.column_filters) {
            this.column_filters[column][option] = true
          } else {
            this.column_filters[column] = {}
            this.column_filters[column][option] = true
          }
        } else {
          delete this.column_filters[column][option]
          if(this.allCheckedFilter(column)) {
            delete this.column_filters[column]
          }
        }
      }

    },
    allCheckedFilter(column) {
      if(this.use_header_id) {
        if(!(column?.id in this.column_filters)) {
          return true
        }
        if(Object.keys(this.column_filters[column?.id]).length<=0) {
          return true
        }
        return false
      } else {
        if(!(column in this.column_filters)) {
          return true
        }
        if(Object.keys(this.column_filters[column]).length<=0) {
          return true
        }
        return false
      }

    },
    singleCheckedFilter(column,option) {
      if(this.use_header_id) {
        if(!(column?.id in this.column_filters)) {
          return true
        }
        if(!(option in this.column_filters[column?.id])) {
          return true
        }
        return false
      } else {
        if(!(column in this.column_filters)) {
          return true
        }
        if(!(option in this.column_filters[column])) {
          return true
        }
        return false
      }

    },
    filterPermissions(options) {
      if(options) {
        return options.filter(option=>option?.permission<=this.active_row.data?.permission).filter(option=>option?.dataset_permission<=this.dataset_permission)
      }
    },
    handleOption(option) {
      if(option.twoStep && (!(option.id in this.option_step) || this.option_step[option.id]==1)) {
        this.option_step[option.id] = 2
        return
      }
      this.closeTableRowOptions()
      this.$emit('optionClick', {option: option, row_id: this.active_row.id})
    },
    notifications(nNotifications) {
      if(nNotifications>100) {
        return '+99'
      }
      return nNotifications
    },
    unseen(row) {
      const user = this.$store.getters.getProfile
      return row.replies.filter(reply=>!reply.seen.includes(user?.id))
    },
    isSelected(row) {
      if(row.id in this.selected) {
        return true
      }
      return false
    },
    selectSingle(event, row) {
      if(event) {
        this.selected[row?.id] = true
        this.$emit('select', this.selected)
      } else {
        delete this.selected[row?.id]
        this.$emit('deselect', [row?.id])
      }
    },
    selectAll(event) {
      if(event) {
        this.selected = {}
        this.filterByCol(this.filter(this.data_decorated)).forEach(row=>{
          this.selected[row.id] = true
        })
        this.$emit('select', this.selected)
      } else {
        this.selected = {}
        this.$emit('deselect', this.filterByCol(this.filter(this.data_decorated)).map(row=>row.id))
      }
    },
    escapeCharacters(string) {
      string=string.replace('[', '\\[')
      string=string.replace(']', '\\]')
      string=string.replace('.', '\\.')
      return string
    },
    filter(data) {
      if(this.use_header_id) {
        return data?.filter(row=>{
          var veto = true
          if(this.searchString && this.searchString!=='') {
            this.header.forEach(column=>{
              const value = JSON.stringify(this.getCellData(column,row))
              if(value && value.match(new RegExp(this.escapeCharacters(this.searchString), "i"))) {
                veto = false
              }
            })
          } else {
            veto = false
          }

          return !veto
        })
      } else {
        return data?.filter(row=>{
          var veto = true
          if(this.searchString && this.searchString!=='') {
            this.header.forEach(column=>{
              const value = JSON.stringify(this.getCellData(column,row))
              if(value && value.match(new RegExp(this.escapeCharacters(this.searchString), "i"))) {
                veto = false
              }
            })
          } else {
            veto = false
          }

          return !veto
        })
      }

    },
    handleClick(event) {
      if(!this.ancestorContainsClass(event.target, 'table')) {
        this.closeTableRowOptions()
      }
      if(!this.ancestorContainsClass(event.target, 'column-filter-window') && !this.ancestorContainsClass(event.target, `header-filter-specific-${this.name}`)) {
        this.closeColumnFilter()
      }
    },
    getCellData(column,row) {
      if(this.use_header_id) {
        return row[column.id]
      }
      return row[column]
    },
    closeTableRowOptions() {
      this.active_row.options = false
      this.option_step = {}
    },
    ancestorContainsClass(target, className) {
      var element = target
      while(element && element.classList) {
        if(element.classList.contains(className)) {
          return true
        }
        element = element.parentNode
      }
      return false
    },
    getAncestor(target, className) {
      var element = target
      while(element && element.classList) {
        if(element.classList.contains(className)) {
          return element
        }
        element = element.parentNode
      }
      return null
    },
    test(event, row,row_index) {
      const target = this.getAncestor(event.target, 'table-cell')
      if(!target) {
        return
      }
      if(this.active_row.id && this.ancestorContainsClass(event.target, 'select-cell')) {
        this.closeTableRowOptions()
        return
      }
      if(this.active_row?.id==row?.id && this.active_row?.options) {
        this.closeTableRowOptions()
        return
      }
      this.closeColumnFilter()
      this.$emit('rowClick', {row_id: row?.id,row_index,row})
      const b_rect = target?.getBoundingClientRect()
      this.active_row.top = event.clientY - b_rect?.top + target?.offsetTop
      this.active_row.data = row
      const window_height = 16+25+this.filterPermissions(this.menuOptions).length*40+(this.filterPermissions(this.menuOptions).length-1)*4
      const window_width = 244
      if(event.clientY+window_height>window.innerHeight) {
        this.active_row.top -= event.clientY+window_height - window.innerHeight + 18
      }

      this.active_row.left = event.clientX - b_rect?.left + target?.offsetLeft
      if(event.clientX+window_width>window.innerWidth) {
        this.active_row.left -= event.clientX+window_width - window.innerWidth + 18
      }

      this.active_row.options = true

      this.active_row.height = b_rect?.height
      this.active_row.id = row?.id
    },
    closeColumnFilter() {
      this.column_filter_window.column=null
      this.column_filter_window.x=0
      this.column_filter_window.y=0
    },
    openColumnFilter(event, column) {
      var target = this.getAncestor(event.target, 'table-header-row')
      if(!target) {
        target = this.getAncestor(event.target, 'table-header-row-select')
      }
      this.getAncestor(event.target, 'table-header-row-select')
      this.closeTableRowOptions()
      const b_rect = target.getBoundingClientRect()
      var x = event.clientX - b_rect.left + target.offsetLeft
      if(event.clientX+this.column_filter_window.width>window.innerWidth) {
        x -= event.clientX+this.column_filter_window.width - window.innerWidth + 18
      }
      const y = event.clientY - b_rect.top + target.offsetTop
      this.column_filter_window.column=column
      this.column_filter_window.x=x
      this.column_filter_window.y=y
    },
  }
}
</script>

<style lang="scss" scoped>
.table-options {
  display: flex;
  flex-direction: row;
  gap: 8px;
  padding: 0.5rem 0;
  .option {
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
.header-name {
  cursor: pointer;
}
.highlight {
  > * {
    background-color: rgba(0, 135, 108,0.4) !important;

  }
}
.cell-content {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 4px;

}
.column-filter-window {
  overflow-y: auto;
  max-height: 412px;
  box-shadow: rgba(0, 0, 0, 0.25) 0px 54px 55px, rgba(0, 0, 0, 0.12) 0px -12px 30px, rgba(0, 0, 0, 0.12) 0px 4px 6px, rgba(0, 0, 0, 0.17) 0px 12px 13px, rgba(0, 0, 0, 0.09) 0px -3px 5px;
  .header {
    padding: 12px;
    margin-bottom: 12px;
    background-color: #ddd;
    // cursor: pointer;
    &:hover {
      background-color: #ccc;
    }
  }
  .body {
    display: flex;
    flex-direction: column;
    gap: 0px;
    .option {
      padding: 6px 12px;
      // cursor: pointer;
      &:hover {
        background-color: #ccc;
      }
    }
  }
}
.searchbar {
  align-items: flex-start;
  text-align: left;
  width: 100%;
  display:flex;
  flex-direction: column;
  gap: 4px;
  .searchbar-input {
    width: 100%;
    max-width: 210mm;
    font-size: 18px;
    padding: 8px 16px;
    border-radius: 50px;
    border: 1px solid black;
  }
}
.table-row-options-menu {
  position: absolute;
  background: #fff;
  box-shadow: rgba(0, 0, 0, 0.16) 0px 10px 36px 0px;
  outline: 1px solid #cccccc;
  z-index: 100;
  border-radius: 4px;
  .table-row-option {
    > .option-icon {
      font-size: 26px;
    }
    display: flex;
    flex-direction: row;
    align-items: stretch;
    > button {
      padding: 8px 16px;
      flex: 1;
      > .option-icon {
        font-size: 24px;
      }
      display: flex;
      flex-direction: row;
      align-items: center;
      cursor: pointer;
      gap: 8px;
      &:hover {
        background-color: #f4f4f4;
      }
    }

  }
  .table-row-options-body {
    padding: 8px 0 8px 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 244px;
  }
  .table-row-options-header {
    display: flex;
    flex-direction: row;
    border-bottom: 1px solid #cccccc;
    .close-icon {
      width: 40px;
      font-size: 24px;
      margin-left: auto;
      border-top-right-radius: 4px;
      cursor: pointer;

      &:hover {
        background-color: #dc3545;
        color: #FAF9F6;
      }
    }
  }

}
.table {
  position: relative;
  display: grid;
  // grid-template-columns: repeat(var(--n_cols), auto);
  .table-header-row {
    position: sticky;
    z-index: 99;
    top: 38px;

    background-color: #00876c;
    color: white;
    display: flex;
    // grid-template-columns: auto 42px;
    align-items: center;
    justify-content: flex-start;
    min-height: 38px;
    gap: 16px;
    padding: 0 8px;
    .header-name {
      grid-column-start: 2;
      grid-column-end: 3; 
      margin-right: auto;
      white-space: nowrap;
    }
    .header-filter-wrapper {
      display: flex;
      flex-direction: row;
      justify-content: flex-end;
      align-items: center;
      padding: 0 6px 0 0;
    }
    .header-filter {
      cursor: pointer;
      grid-column-start: 3;
      grid-column-end: 4;
      >.icon {
        pointer-events: none;
        transition: transform 0.225s ease-in;
      }
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px solid white;
      aspect-ratio: 4/3;
      border-radius: 2px;
      right: 6px;
      width: 32px;
      cursor: pointer;
      transition: transform 0.4s ease;
      &.active {
        color: #00876c;
        background-color: white;
        > * {
          transform: rotate(180deg);
        }
      }
    }
  }
  .table-row {
    position: relative;
  }
  .table-data-row {
    position: relative;
    transform: scaleX(1.15);
    display: contents;
    &:nth-child(2n+1) {
      >.table-cell {
        background-color: #f0f0f0;
      }
    }
    .table-cell:not(.select-cell) {
      position: relative;
      > * {
        position: relative;
        // pointer-events: none;
      }
    }
    .table-cell {
      > * {
        position: relative;
      }
      // word-wrap: break-word;
      // word-wrap: break-word;
      text-align: left;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: flex-start;
      padding: 4px;
      font-size: 1rem;
      &.active {
        transform: translateY(var(--acitve_row_translateY));
        background: rgb(97%,89%,74%) !important;
      }
    }
  }
}
.table-header-row-select {
  position: sticky;
  z-index: 99;
  top: 38px;
  background-color: #00876c;
  display: grid;
  grid-template-columns: minmax(42px, 1fr) max-content minmax(42px, 1fr);
  justify-content: center;
  align-items: center;
  .all-select-box {
    grid-column-start: 2;
    grid-column-end: 3;
  }
  .header-filter-wrapper {
    border: 1px solid white;
    width: 32px;
    cursor:pointer;
    aspect-ratio: 4/3;
    grid-column-start: 3;
    grid-column-end: 4;
      border-radius: 2px;
    .header-filter {
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      >.icon {
        pointer-events: none;
        transition: transform 0.225s ease-in;
      }
    }
  }
}
.row-notification {
  position: absolute;
  z-index: 3;
  left: 4px;
  top: -8px;
  .row-notification-icon {
    color: rgba(220, 53, 69, 1);
    transform: scaleX(-1);
    font-size: 2rem;
  }
  .row-notification-text {
    color: white;
    position: absolute;
    // border: 1px solid black;
    font-size: 0.9rem;
    text-align: center;
    width: 2rem;
    top: 4.5px;
    left: 0px;
  }
}
</style>