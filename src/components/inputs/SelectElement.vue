<template>
  <div class="root">
    <div class="select-element" :style="cursorStyle" >
      <InputElement @valueChange="handleValueChange($event)" :optional="optional" :customError="customError" ref="input" @click="openOptions()" @enter="inputEnter($event)" :tooltip="tooltip" :placeholder="placeholder" :required="required" :readonly="readonly" :readonlynostyle="!inputTypeable" class="input-element" type="text" :label="label" @focus="openOptions()" :presetValue="value"/>
      <div class="open-options" @click="openOptions()" :class="{active: optionsMenuOpen}">
        <svg viewBox="0 0 32.3 20">
          <path d="m31.44697,0.76641c-0.56332,-0.56332 -1.47683,-0.56342 -2.04024,0.0001l-13.40638,13.40667l-13.40705,-13.40677c-0.56332,-0.56332 -1.47683,-0.56342 -2.04024,0.0001c-0.56342,0.56342 -0.56342,1.47683 0,2.04024l14.42722,14.42684c0.27055,0.27055 0.63747,0.42251 1.02007,0.42251s0.74962,-0.15206 1.02007,-0.42261l14.42646,-14.42684c0.56351,-0.56332 0.56351,-1.47683 0.0001,-2.04024z"/>
        </svg>      
      </div>

      <div class="options" ref="options" v-if="optionsMenuOpen">
        <button class="option delete-option-buttion" @click.prevent="deleteSelection()" v-if="value!==null && !inputTypeable">
          <span>Reset Selection</span>
          <ion-icon class="icon" name="close-outline"></ion-icon>
        </button>
        <input class="search-options" type="text" v-if="search" placeholder="search..." v-model="searchString"/>
        <div class="option" v-for="option in filter(options)" :key="option" @click="selectOption(option)"><span>{{option.name}}</span></div>
        <div class="no-results" v-if="filter(options).length<=0 && searchString">
          No results found
        </div>
        <div class="option" v-if="filter(options).length<=0 && searchString && emptyEmptySearchOption && !awaiting" @click="emptySearchOption(emptyEmptySearchOption)">
          {{emptyEmptySearchOption.name}}
        </div>
        <DataPlaceholder v-if="awaiting" :side_length="38"/>
      </div>

    </div>
    <div class="overlay" v-if="optionsMenuOpen" @click="closeOptions()"></div>
    <input type="hidden" :value="inputValue" :name="name">
  </div>
</template>

<script>
import DataPlaceholder from '@/components/DataPlaceholder.vue'
import InputElement from '@/components/inputs/InputElement.vue'
export default {
  name: 'SelectElement',
  components: {
    DataPlaceholder,
    InputElement,
  },
  emits: ['selectedEntry', 'inputEnter', 'emptySearchOption','change','select'],
  props: {
    optional: {
      default: true,
      type: Boolean,
    },
    emptyEmptySearchOption: Object,
    inputTypeable: {
      default: false,
      type: Boolean,
    },
    label: String,
    data: [Array,Object],
    search: {
      default: true,
      type: Boolean,
    },
    presetValue: [Number,String],
    name: String,
    required: Boolean,
    tooltip: String,
    placeholder: String,
    readonly: Boolean,
    cast: {
      default: (option)=>{return {id: option.id, name: option.name}},
      type: Function,
    },
    nameAsValue: {
      default: false,
      type: Boolean,
    },
    valueCast: {
      default: (option)=>{return option.id},
      type: Function,
    }
  },
  data() {
    return {
      typed: null,
      optionsMenuOpen: false,
      value: null,
      searchString: null,
      selected : null,
      id: null,
      deFocusedOnce: false,
      customError: null,
      awaiting: false,
    }
  },
  mounted() {
    this.configurePresetValue(this.presetValue)
  },
  watch: {
    customError(to) {
      if(to) {
        this.$refs.input.customError = to
      }
    },
    deFocusedOnce(to) {
      if(to) {
        this.$refs.input.deFocusedOnce = true
      }
    },
    presetValue(to) {
      this.configurePresetValue(to)      
    },
    options() {
      this.configurePresetValue(this.presetValue)
    },
  },
  computed: {
    inputValue() {
      if(this.selected===null) {
        return null
      }
      if(this.nameAsValue) {
        return this.value
      }
      return this.valueCast(this.selected)
    },
    cursorStyle() {
      var style = {}
      if(this.readonly) {
        style['cursor'] = 'not-allowed'
      }
      return style
    },
    options() {
      if(!this.data) {
        return []
      }
      const options = []
      if(Array.isArray(this.data)) {
        if(!this.data || this.data.length<1) {
          return []
        }
        this.data.forEach((row,index)=>{
          var tmp = row
          if(typeof row=='string') {
            tmp = {id:index, name:row}
          }
          const option = this.cast(tmp)
          option['index'] = index
          options.push(option)
        })
      } else {
        const keys = Object.keys(this.data)
        if(!this.data || keys.length<1) {
          return []
        }
        keys.forEach(key=>{
          var tmp = this.data[key]
          if(typeof this.data[key]=='string') {
            tmp = {id:key, name:this.data[key]}
          }
          const option = this.cast(tmp)
          option['index'] = key
          options.push(option)
        })
      }
      return options
    },
    optionsHeight() {
      if(this.options.length*28+24+28>140) {
        return this.options.length*28+24+28-140
      }
      return 0
    },
  },

  methods: {
    handleValueChange(event) {
      if(this.inputTypeable) {
        this.$emit('change', event)
      } else {
        this.optionsMenuOpen=false
      }
    },
    blur() {
      this.optionsMenuOpen = false
      this.$refs.input.blur()
      this.clear()
    },
    setError(error) {
      this.customError = error
      this.deFocusedOnce = true
    },
    emptySearchOption(option) {
      this.awaiting = true
      this.$emit('emptySearchOption', {value: this.searchString, option})
    },
    inputEnter(value) {
      this.$emit('inputEnter', value)
    },
    deleteSelection() {
      this.value=null
      this.selected=null
      this.id=null
      this.$emit('selectedEntry', null)
      this.$emit('select', null)
      this.searchString = null
      this.closeOptions()
    },
    clear() {
      this.$refs.input.clear()
      this.value = null
      this.id = null
      this.selected = null
      this.deFocusedOnce = false
    },
    configurePresetValue(value) {
      if(value===null || value===undefined || value==='') {
        this.clear()
        return 
      }
      const option = this.options.find(option=>option.id==value)

      if(option) {
        this.selected = option
        this.id = option.id
        this.value = option.name
      } else if(this.inputTypeable) {
        this.$refs.input.value = value
      }
    },
    openOptions() {
      if(this.readonly) {
        return
      }
      this.$refs.input.focusActive = true
      this.$refs.input.lock = true
      this.optionsMenuOpen = true
    },
    closeOptions() {
      this.$refs.input.focusActive = false
      this.$refs.input.lock = false
      this.optionsMenuOpen = false
      this.deFocusedOnce = true
    },
    selectOption(option) {
      this.value = option.name
      this.selected = option
      this.id = option.id
      this.searchString = null
      this.closeOptions()
      this.$emit('selectedEntry', {id: option.id, name: option.name})
      this.$emit('select', {id: option.id, name: option.name})
    },
    filter(data) {
      if(!data || data.length<=0) {
        return []
      }
      
      var dataFiltered = data.filter(row=>{
        if(!this.searchString || row.name.match(new RegExp(this.searchString, "i"))) {
          return true
        }
        return false
      })
      if(!dataFiltered || dataFiltered.length<=0) {
        return []
      }
      return dataFiltered
    },
  },


}
</script>


<style scoped lang="scss">
.no-results {
  width: 100%;
  padding: 8px 16px;
  min-height: 32px;
  font-size: 16px;
  border-radius: 4px;

}
.delete-option-buttion {
  position: relative;
  box-sizing: border-box;
  width: calc(100% - 8px);
  right: 0;
  gap: 16px;
  border: 2px solid #dc3545 !important;
  * {color: #dc3545;}
  &:hover {
    * {
      color: white;
    }
  }
  .icon {
    font-size: 24px;
  }
}
.search-options {
  position: relative;
  min-height: 38px;
  font-size: 16px;
  width: calc(100% - 24px);
  margin: 8px 12px 8px 12px;
}

.select-element {
  position: relative;
  
}
.input-element {
  position: relative;
  z-index: 1;
}
.open-options {
  // cursor: pointer;
  top: 11px;
  right: 0;
  position: absolute;
  width: 32px;
  height: 20px;
  transform: scale(0.6) rotate(0);
  transition: transform .3s ease;
  &.active {
    transform: scale(0.6) rotate(180deg);
    transition: transform .3s ease;
  }
}
.options {
  box-sizing: border-box;
  left: 0;
  // padding: 4px 0px;
  position: absolute;
  display: inline-block;
  width: 100%;
  background-color: white;
  box-shadow: 0px 2px 4px 4px rgba(0,0,0,0.3);
  // border: 1px solid #00876c;
  top: 34px;
  overflow-y: auto;
  max-height: 230px;
  z-index: 101;
  border-top: none;
}

.option {
  position: relative;
  padding: 8px 2px;
  margin: 4px 12px;
  min-height: 32px;
  font-size: 16px;
  border: 2px solid #eeeeee;
  border-radius: 4px;
  display: flex;
  align-items: center;

  > span {
    width: 100%;
    word-wrap: break-word;
    text-align: start;
  }
  color: #2c3e50;
  cursor: pointer;

  &:hover {
    background-color: #2c3e50;
    color: white;
  }
}
.overlay {
  position: fixed;
  z-index: 100;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
}
</style>
