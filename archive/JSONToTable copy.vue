<template>
  <div v-if="data!==null && data.length>0" class="json-to-table" :style="tableStyle" ref="table">



    <div class="header" v-if="select">
      <Checkbox @inputChange="checkAll($event)" :presetValue="allChecked" label="All" :clean="true" :invert="true"/>
    </div>


    <div class="header" v-for="header in headers" :key="header" >
      <div class="header-name" :class="{desc: sort.type=='desc' && sort.column==header,asc: sort.type=='asc' && sort.column==header}" @click="setSort(header)">{{header}}</div>
      <div class="header-filter" @click="openFilter(header, $event)" :class="{active: filterWindowActive(header), beingFiltered: columnFiltered(header)}">
        
        <svg width="32" height="18" xmlns="http://www.w3.org/2000/svg" version="1.1" >
          <path stroke="null" d="m31.44697,0.76641c-0.56332,-0.56332 -1.47683,-0.56342 -2.04024,0.0001l-13.40638,13.40667l-13.40705,-13.40677c-0.56332,-0.56332 -1.47683,-0.56342 -2.04024,0.0001c-0.56342,0.56342 -0.56342,1.47683 0,2.04024l14.42722,14.42684c0.27055,0.27055 0.63747,0.42251 1.02007,0.42251s0.74962,-0.15206 1.02007,-0.42261l14.42646,-14.42684c0.56351,-0.56332 0.56351,-1.47683 0.0001,-2.04024z" id="XMLID_225_"/>
        </svg>        
      </div>
    </div>

    <div class="rows" v-for="(row,index) in sorting(filter(dataDecorated))" :key="index" :class="{'active': index===activeRow.index}">

      <div class="row-item" v-if="select">
        <Checkbox @inputChange="checkSingle($event, row.component_id)" :presetValue="isChecked(row.component_id)" label="" :clean="true"/>
      </div>

      <div class="row-item" :class="{warning: error && error[index][headerIdx]==2, error: error && error[index][headerIdx]==1, pointer: (rowMenu && rowMenu.length>0) || itemClickable}" :style="index>activeRow.index && activeRow.index!=null ? rowStyle : null" v-for="(header,headerIdx) in headers" :key="header" @click="handleItemClick($event, index)">
        <template v-if="Array.isArray(row[header])">
          <template v-for="entry in row[header]" :key="entry">
            <a v-if="entry['type']=='file'" :href="entry.url" class="item-link">{{entry.name}} </a>
            <template v-else>{{entry}}</template>
          </template>
        </template>
        <template v-else>{{row[header]}}</template>
        <div class="row-notification" v-if="headerIdx==0 && row.replies && unseen(row).length>0"><span>{{unseen(row).length}}</span></div>
      </div>
      
    </div>
    <div class="row-options" :style="activeRow.index!==null ? optionsStyle : null" v-if="rowMenu && rowMenu.length>0">
      <IconButton v-for="item in getOptions(rowMenu, sorting(filter(data))[activeRow.index])" :key="item" :icon="item.icon" :text="item.text" :width="item.width" :height="item.height" @buttonClicked="emitMenuClicked(item.name, sorting(filter(data))[activeRow.index])" />
    </div>
  </div>

  <div class="overlay" v-if="filterWindow.active" @click="closeFilter()"></div>
  <div class="filter-window" ref="filterWindow" v-if="filterWindow.active" :style="{'left': `${filterWindow.x}px`, 'top': `${filterWindow.y}px`}">
    <InputElement :presetValue="filterWindow.column in filters ? filters[filterWindow.column].searchString : null" label="filter" :required="false" type="text" @valueChange="updateFilters($event, filterWindow.column)"/>
    <Checkbox :presetValue="filterAllChecked(filterWindow.column)" label="Select All" @inputChange="updateAllFilter($event, filterWindow.column)"/>
    <div class="value-item" v-for="value in filterWindowValues(filterWindow.column)" :key="value">
      <Checkbox :presetValue="filterSingleChecked(filterWindow.column, value)" :label="value" @inputChange="updateSingleFilter($event, filterWindow.column, value)"/>
    </div>
  </div>


</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
import IconButton from '@/components/IconButton.vue'
import { nextTick } from 'vue'
export default {
  name: 'JSONToTable',
  components: {
    Checkbox,
    IconButton,
    InputElement,
  },
  props: {
    data: Object,
    error: Array,
    select: Boolean,
    align: {
      default: 'left',
      type: String,
    },
    rowMenu: Array,
    itemClickable: Boolean,
    columnSettings: Object,
  },
  data() {
    return {
      activeRow: {index: null, top: null, optionsHeight: this.rowMenu && this.rowMenu.length>0 ? 40*this.rowMenu.length : 0},
      sort: {type: null, column: null},
      filterWindow: {active: false, x: 0, y: 0, column: null},
      filters: {},
      checkboxElementData_rowSelect: {label: "", disabled: false, text: "Save"},
      bodyRect: {top: null},
      checked: [],
      dataDecorated: null,
    }
  },
  emits: ['menuItemClicked', 'itemClicked'],
  mounted() {
    if(this.data) {
      this.dataDecorated = this.decorate(this.data)
    }
  },
  watch: {
    data(to) {
      if(to) {
        this.dataDecorated = this.decorate(this.data)
      } else {
        this.dataDecorated = null
      }
    }
  },
  computed: {
    rowStyle() {
      return {
        'top': `${this.activeRow.optionsHeight}px`,
      }
    },
    optionsStyle() {
      return {
        'top': `${this.activeRow.top}px`,
        'height': `${this.activeRow.optionsHeight}px`,
        // 'width': `${this.$refs.table.scrollWidth}px`,
        'border-bottom': '1px solid black',
        'border-left': '1px solid black',
        'border-right': '1px solid black',
        'z-index': 0,
      }
    },
    tableStyle() {
      var ncols = this.headers.length-1
      if(this.select) {
        ncols += 1
      }
      return {
        'display': 'grid',
        'grid-template-columns': `max-content repeat(${ncols}, minmax(max-content, auto))`
      }
    },
    allChecked() {
      if(this.checked.length==this.filter(this.data).length) {
        return true
      }
      return false
    },
    headers() {
      if(this.data===null || this.data.length<=0) {
        return []
      }
      var headers = []
      if(this.columnSettings.order && this.columnSettings.order.length>0) {
        headers=this.columnSettings.order
      }
      for (const [key, ] of Object.entries(this.data[0])) {
        if(this.columnSettings.order && this.columnSettings.order.length>0 && this.columnSettings.order.includes(key)) {
          continue
        }
        if(this.columnSettings && this.columnSettings.items && this.columnSettings.items.length>0) {
          if(this.columnSettings.type=='blacklist') {
            if(!this.columnSettings.items.includes(key)) {
              headers.push(key)
            }
          } else if(this.columnSettings.type=='whitelist') {
            if(this.columnSettings.items.includes(key)) {
              headers.push(key)
            }
          }
        } else {
          headers.push(key)
        }
      }
      return headers
    }
  },
  methods: {
    updateChecked() {
      const visibleIds = this.filter(this.dataDecorated).map(entry=>entry.component_id)
      this.checked = this.checked.filter(checkedId=>visibleIds.includes(checkedId))
    },
    decorate(data) {
      var returnVal = []
      data.forEach((entry,index)=>{
        var tmp = JSON.parse(JSON.stringify(entry))
        tmp["component_id"] = index
        returnVal.push(tmp)
      })
      return returnVal
    },
    checkSingle(value, id) {
      if(!this.checked.includes(id) && value) {
        this.checked.push(id)
      } else if(this.checked.includes(id) && !value) {
        this.checked.splice(this.checked.indexOf(id), 1)
      }
    },
    isChecked(id) {
      if(this.checked.includes(id)) {
        return true
      }
      return false
    },
    checkAll(value) {
      if(value) {
        this.checked = this.filter(this.dataDecorated).map(entry=>entry.component_id)
      } else {
        this.checked = []
      }
    },
    unseen(row) {
      const user = this.$store.getters.getUser
      return row.replies.filter(reply=>!reply.seen.includes(user.id))
    },
    getOptions(options, entry) {
      if(this.activeRow.index==null){
        return
      }
      return options.filter(option=>option.permission!=null?option.permission<=entry.permission:true)
    },
    handleItemClick(event, index) {
      if(event.target.tagName.toLowerCase() === 'a') {
        return
      }
      if(this.itemClickable) {
        this.$emit('itemClicked', this.sorting(this.filter(this.data))[index])
      } else if(this.rowMenu && this.rowMenu.length>0) {
        if(index!=this.activeRow.index) {
          this.openRowOptions(event, index)
        } else {
          this.closeRowOptions()
        }
      }
    },
    emitMenuClicked(name, data) {
      const trueIndex = this.data.indexOf(data)
      this.closeRowOptions()
      this.$emit('menuItemClicked', {name: name, data: data, index: trueIndex})
    },
    openRowOptions(event, index) {
      if(!this.rowMenu || this.rowMenu.length<=0) {
        return
      }
      var correction = 0
      if(this.bodyRect.top==null) {
        this.bodyRect.top = this.$refs.table.getBoundingClientRect().top
      }
      if(index>this.activeRow.index && this.activeRow.index!=null) {
        correction = -this.activeRow.optionsHeight
      }
      this.indexOpen = index
      const top = window.top.scrollY + event.target.getBoundingClientRect().top + event.target.getBoundingClientRect().height - this.bodyRect.top + correction
      this.activeRow.index = index
      this.activeRow.top = top
      this.activeRow.optionsHeight = this.getOptions(this.rowMenu, this.sorting(this.filter(this.data))[this.activeRow.index]).length*40
    },
    closeRowOptions() {
      if(!this.rowMenu || this.rowMenu.length<=0) {
        return
      }
      this.activeRow = {index: null, top: null, optionsHeight: 80}
    },
    sorting(data) {
      if(this.sort.column) {
        return data.sort((a,b) => {
          if(a[this.sort.column]==null || a[this.sort.column]==undefined) {
            a[this.sort.column] = ''
          }
          if(b[this.sort.column]==null || b[this.sort.column]==undefined) {
            b[this.sort.column] = ''
          }
          if(this.sort.type=='asc') {
            return -a[this.sort.column].localeCompare(b[this.sort.column], undefined, {
              numeric: true,
              sensitivity: 'base'
            })
          } else {
            return a[this.sort.column].localeCompare(b[this.sort.column], undefined, {
              numeric: true,
              sensitivity: 'base'
            })            
          }

        })
      }
      return data
    },
    setSort(header) {
      if(this.sort.column==header && this.sort.type=='desc') {
        this.sort.type = 'asc'
      } else if(this.sort.column==header && this.sort.type=='asc') {
        this.sort.type = 'desc'
      } else {
        this.sort.type = 'asc'
        this.sort.column = header
      }
    },
    updateFilters(value, header) {
      if(!value && header in this.filters && this.filters[header].checkboxes.length<1) {
        delete this.filters[header]
      }
      if(header in this.filters && value!=null && value!=undefined && value!='') {
        this.filters[header].searchString = value
      } else if(value!=null && value!=undefined && value!=''){
        this.filters[header] = {checkboxes: [], searchString: value}
      }
      this.updateChecked()
    },
    columnFiltered(header) {
      if(header in this.filters) {
        return true
      }
      return false
    },     
    filterWindowActive(header) {
      if(header==this.filterWindow.column) {
        return true
      }
      return false
    },
    filter(data) {
      if(!data || data.length<=0) {
        return []
      }
      var dataFiltered = data.filter(row=>{
        var veto = false
        this.headers.forEach(header=>{
          if(header in this.filters && this.filters[header].checkboxes.includes(row[header])) {
            veto = true
          }
          if(header in this.filters &&  this.filters[header].searchString!=null && this.filters[header].searchString!='' && this.filters[header].searchString!=undefined && !row[header].match(new RegExp(this.filters[header].searchString, "i"))) {
            veto = true
          }
        })
        return !veto
      })
      if(!dataFiltered || dataFiltered.length<=0) {
        return []
      }
      return dataFiltered
    },
    filterSingleChecked(column, value) {
      if(!(column in this.filters)) {
        return true
      }

      if(this.filters[column].checkboxes.includes(value)) {
        return false
      }
      return true
    },    
    updateSingleFilter(checked, column, value) {
      if(checked && column in this.filters && this.filters[column].checkboxes.includes(value)) {
        var index = this.filters[column].checkboxes.indexOf(value)
        this.filters[column].checkboxes.splice(index, 1)        
      } else if(!checked && column in this.filters && !this.filters[column].checkboxes.includes(value)) {
        this.filters[column].checkboxes.push(value)
      } else if(!checked && !(column in this.filters)) {
        this.filters[column] = {checkboxes: [value], searchString: null}
      }
      if(column in this.filters && this.filters[column].checkboxes.length==0 && !this.filters[column].searchString) {
        delete this.filters[column]
      }
      this.updateChecked()
    },
    updateAllFilter(checked, column) {
      if(checked && column in this.filters && !this.filters[column].searchString) {
        delete this.filters[column]
      } else if(checked && column in this.filters && this.filters[column].searchString) {
        this.filters[column].checkboxes = []
      } else if(!checked && column in this.filters) {
        this.filters[column].checkboxes = this.filterWindowValues(this.filterWindow.column)
      } else if(!checked) {
        this.filters[column] = {checkboxes: this.filterWindowValues(this.filterWindow.column), searchString: null}
      }
      this.updateChecked()

    },    
    filterAllChecked(column) {
      if(column in this.filters && this.filters[column].checkboxes.length!=0) {
        return false
      }
      return true
    },    
    openFilter(column, event) {
      this.filterWindow.column = column
      this.filterWindow.active = true
      this.filterWindow.x = event.target.getBoundingClientRect().x - this.$refs.table.getBoundingClientRect().x
      this.filterWindow.y = event.target.getBoundingClientRect().height + event.target.getBoundingClientRect().y - this.$refs.table.getBoundingClientRect().y
      nextTick(()=>{
        if(this.$refs.filterWindow.getBoundingClientRect().x+this.$refs.filterWindow.getBoundingClientRect().width>window.innerWidth) {
          this.filterWindow.x = this.filterWindow.x - this.$refs.filterWindow.getBoundingClientRect().x - this.$refs.filterWindow.getBoundingClientRect().width + window.innerWidth - 18
        }
      })
    },
    closeFilter() {
      this.filterWindow.active = false
      this.filterWindow.x = 0
      this.filterWindow.y = 0
      this.filterWindow.column = null
    },
    filterWindowValues(column) {
      return this.data.map(row=>{
        return row[column]
      }).filter((value,index,self) => self.indexOf(value)==index)
      // 
    },    
  }
}
</script>


<style scoped lang="scss">
.item-link{
  display: block;
}
.row-notification {
  position: absolute;
  left: 8px;
  top: -5px;
  background-color: #C53803;
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: .8em;
  width: 18px;
  height: 18px;
  border-radius: 100%;
}
.row-options {
  position: absolute;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  width: 100%;
  z-index: -1;
  background-color: white;
  height: 0;
  transition: height .4s ease;
  box-shadow: 0px 0px 4px 0px inset rgba(0, 0, 0, 0.2);
  overflow: hidden;
  border-bottom-right-radius: 4px;
  border-bottom-left-radius: 4px;
  > * {
    padding: 4px;
    width: 100%;
  }
}

.header-name {
  padding: 0 36px;
  cursor: pointer;
}
.header-name:not(.desc):not(.asc)::after {
  content: '';
  border: solid rgba(0,0,0,0);
  border-width: 0 2px 2px 0;
  display: inline-block;
  padding: 2px;
  margin: 0 0 0 6px;
  transform: rotate(-135deg);
  -webkit-transform: rotate(-135deg);
}
.header-name.desc::after {
  content: '';
  border: solid black;
  border-width: 0 2px 2px 0;
  display: inline-block;
  padding: 2px;
  margin: 0 0 0 6px;
  transform: rotate(-135deg);
  -webkit-transform: rotate(-135deg);
}
.header-name.asc::after {
  content: '';
  border: solid black;
  border-width: 0 2px 2px 0;
  display: inline-block;
  padding: 2px;
  margin: 0 0 0 6px;
  transform: rotate(45deg);
  -webkit-transform: rotate(45deg);
}
.header-filter {
  position: absolute;
  border: 1px solid white;
  border-radius: 2px;
  cursor: pointer;
  margin-left: auto;
  right: 4px;
  > * {
    pointer-events: none;
    position: relative;
    top: 2px;
    transform: rotate(0deg) scale(0.4);
    transition: transform 0.3s cubic-bezier(.4,0,.2,1);
    stroke: white;
    fill: white;
  }
  &:hover{
    background-color: #007755;
  }
  &.beingFiltered {
    background-color: white;
    > svg {
      fill: #00876C;
      stroke: #00876C;
    }    
  }
  &.active {
    > svg {
      transform: rotate(180deg) scale(0.4);
      transition: transform 0.3s cubic-bezier(.4,0,.2,1);
    }
  }  
}
.json-to-table {
  position:relative;
  .header {
    position: relative;
    padding: 8px 18px;
    background-color: #00876C;
    color: white;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
  }
  .rows {
    position: relative;
    display: contents;
    &.active {
      > *{border-top: 1px solid black;}
      >:first-child {
        border-top-left-radius: 4px;
        border-left: 1px solid black;
      }
      >:last-child {
        border-top-right-radius: 4px;
        border-right: 1px solid black;
      }
    }

    &:nth-child(2n) {
      > * {
        background-color: #eee;
        &.warning {
          background-color: rgba(255, 193, 7, 0.9);
        }
        &.error {
          background-color: rgba(220, 53, 69, 0.9);
        }
      }
    }
    &:nth-child(2n+1) {
      > * {
        background-color: #f9f9f9;
        &.warning {
          background-color: rgba(255, 193, 7, 0.6);
        }
        &.error {
          background-color: rgba(220, 53, 69, 0.6);
        }
      }
    }    
  }
  .row-item {
    gap: 8px;
    &.pointer{
      cursor: pointer;
    }
    position: relative;
    top: 0;
    transition: top .4s ease;
    padding: 8px;
    display: flex;
    align-content: center;
    align-items: center;
    justify-content: center;
    justify-items: center;
  }
}
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
}
.filter-window {
  z-index: 2;
  padding: 15px;
  border-radius: 2px;
  background-color: white;
  box-shadow: 0px 0px 6px 2px rgba(0,0,0,0.35);
  position: absolute;
  border: 1px solid black;
  .value-item {
    padding: 0 0 0 16px;
  }
}
</style>
