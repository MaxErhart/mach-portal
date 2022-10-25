<template >
  <template v-if="data">
    <div id="element-body" ref="body" :class="{active: selectActive}">
      <div id="overlay-select" :style="overlayStyle" v-if="selectActive" @click="closeSelect()"></div>
      <InputElement @focus="openSelect()" @blur="blur()" :readonly="!typeToSearch" :data="{label: data.label, type: 'text', required: data.required, placeholder: data.placeholder, tooltip: data.tooltip, autocomplete: data.autocomplete ? data.autocomplete:'off'}" @valueChange="inputChanged($event)" :autocomplete="false" :height="height" class="input-select" :presetValue="selected ? nameCast(selected) : inputElementValue" @click="openSelect()" ref="input"/>
      <div id="open-select" :class="{active: selectActive}" @click="openSelect()">
        <svg width="32" height="18" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" version="1.1">
          <path stroke="null" d="m31.44697,0.76641c-0.56332,-0.56332 -1.47683,-0.56342 -2.04024,0.0001l-13.40638,13.40667l-13.40705,-13.40677c-0.56332,-0.56332 -1.47683,-0.56342 -2.04024,0.0001c-0.56342,0.56342 -0.56342,1.47683 0,2.04024l14.42722,14.42684c0.27055,0.27055 0.63747,0.42251 1.02007,0.42251s0.74962,-0.15206 1.02007,-0.42261l14.42646,-14.42684c0.56351,-0.56332 0.56351,-1.47683 0.0001,-2.04024z" id="XMLID_225_"/>
        </svg>      
      </div>
      <div id="select" :class="{active: selectActive}" :style="{height: (selectActive && options) ? `${Math.min(options.length*28, 150)}px` : '0px'}" ref="select">
        <div class="select-option" v-for="entry in filter(options)" :key="entry.id" @click="selectEntry(entry)">{{nameCast(entry)}}</div>
      </div>
    </div>
    <div class="display-selection-data" v-if="selected">
      <div class="data-element" v-for="id in data.displayFormIds" :key="id">
        <div class="data-element-label">{{selectedDisplayLabel(id)}}:</div>
        <div class="data-element-value">{{selectedDisplayValue(id)}}</div>
      </div>
    </div>    
    <input type="hidden" :name="name" :value="selected ? (nameAsValue ? nameCast(selected) : selected.id) : null" :style="{display: 'none'}">    
  </template>
</template>

<script>
import * as validationSettings from '@/validationSettings.json'
import InputElement from '@/components/inputs/InputElement.vue'
import axios from "axios";
export default {
  name: 'SelectReferenceElement',
  components: {
    InputElement,
  },
  emits: ['inputChanged', 'selectedEntry'],
  props: {
    id: Number,
    name: String,
    data: Object,
    formId: Number,
    displayId: Number,
    presetValue: String,
    nameAsValue: {
      default: true,
      type: Boolean,
    },
    clear: {
      default: false,
      type: Boolean,
    },
    emit: {
      default: true,
      type: Boolean,
    },    
    height: {
      default: 40,
      type: Number,
    },
    typeToSearch: {
      default: false,
      tpye: Boolean,
    }
  },
  data() {
    return {
      validationSettings,
      value: null,
      selected: null,
      selectActive: false,

      isFocused: false,
      deFocusedOnce: false,

      inputElementValue: '',
      submissions: null,
      form: null,
      display: null,
    }
  },
  mounted() {
    if(this.formId) {
      this.getForm(this.formId)
      this.getSubmissions(this.formId)
    }    
    if(this.presetValue) {
      var decode = this.options.filter(e=>e.name==this.presetValue)
      if(decode.legnth==0) {
        decode = this.options.filter(e=>e.id==this.presetValue)
      }
      if(decode.legnth==0) {
        decode = this.options.filter(e=>e==this.presetValue)
      }
      this.selected = decode[0]

    }
  },
  watch: {
    deFocusedOnce(to) {
      this.$refs.input.deFocusedOnce = to
    },
    formId(to) {
      if(to!==null) {
        this.getForm(to)
        this.getSubmissions(to)      
      }
    },
    // displayId(to) {
    //   if(to!==null && this.formId!=null) {
    //     this.display = this.form.form_elements.filter(e=>e.id==to)[0]
    //   }
    // },
    data() {
      if(this.presetValue) {
        var decode = this.options.filter(e=>e.name==this.presetValue)

        if(decode.length==0) {
          decode = this.options.filter(e=>e.id==this.presetValue)
        }
        if(decode.legnth==0) {
          decode = this.options.filter(e=>e==this.presetValue)
        }
        this.selected = decode[0]
      }      
    },
    presetValue(to) {
      var decode = this.options.filter(e=>e.name==to)

      if(decode.length==0) {
        decode = this.options.filter(e=>e.id==to)
      }
      if(decode.legnth==0) {
        decode = this.options.filter(e=>e==to)
      }
      this.selected = decode[0]
    },
  },
  computed: {
    apiUrl() {
      return this.$store.getters.getApiUrl;
    },    
    options() {
      var options = []
      const intRegex = new RegExp(/^[0-9]\d*$/)
      if(this.submissions==null) {
        return options
      }
      for(const [key, value] of Object.entries(this.submissions)) {
        if(intRegex.test(key)) {
          options.splice(Number(key), 0, {id: Number(key), ...value})
        }
      }
      console.log(this.data)
      console.log(this.selected)
      return options
    },
    hasError() {
      if(this.data.required && !this.selected) {
        return true;
      }
      return false;
    },    
    overlayStyle() {
      return {
        width: `${window.innerWidth}px`,
        height: `${window.innerHeight}px`,
        top: `${-this.$refs.body.getBoundingClientRect().y}px`,
        left: `${-this.$refs.body.getBoundingClientRect().x}px`,
      }
    },
  },
  methods: {
    selectedDisplayLabel(id) {
      if(this.id==null) {
        return ""
      }
      if(this.selected==null) {
        return ""
      }
      var name = ""
      this.selected.form_elements.forEach(el=>{
        if(el.id==id) {
          name=`${el.data.label}`
        }
      })
      return name
    },    
    selectedDisplayValue(id) {
      if(this.id==null) {
        return ""
      }
      if(this.selected==null) {
        return ""
      }
      var name = ""
      this.selected.form_elements.forEach(el=>{
        if(el.id==id) {
          name=`${el.pivot.data}`
        }
      })
      return name
    },
    getForm(id) {
      this.form=null;
      this.fetchedAllData = true;
      this.awaitData = true;
      const url = `${this.apiUrl}/forms/${id}`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.form = response.data;
        this.display = this.form.form_elements.filter(e=>e.id==this.displayId)[0]
        this.awaitData = false;
      })
    },    
    updateNumOptions(e){
      if(Number(e)>this.numOptions.length) {
        for(var i=this.numOptions.length; i<Number(e);i++){
          this.selections.push('')
        }
      }
      this.numOptions=Number(e)
    },    
    nameCast(submission) {
      if(this.display==null) {
        return ""
      }
      if(submission.form_elements==null) {
        return ""
      }
      var name = ""
      submission.form_elements.forEach(el=>{
        if(el.id==this.display.id) {
          name=el.pivot.data
        }
      })
      return name
    },
    getSubmissions(formId) {
      this.submissions = null;
      this.awaitData = true;
      const url = `${this.apiUrl}/submissions?form_id=${formId}`
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.submissions = response.data
        this.awaitData = false;
      })      
    },    
    inputChanged(value) {
      this.inputElementValue=value;
      var match = null;
      this.options.forEach(e=>{
        if(e.name==value) {
          match = e;
        }
      })
      this.selected = match;
      this.$emit('inputChanged', value);
      if(match && this.emit) {
        this.$emit('selectedEntry', match)
      }
    },
    selectEntry(entry) {
      this.selected = entry;
      this.selectActive = false;
      if(this.clear) {
        this.selected=null;
      }
      if(this.emit) {
        this.$emit('selectedEntry', entry)
      }

    },
    openSelect(){
      this.selectActive = true;
    },
    blur() {
      setTimeout(this.closeSelect, 500);
    },
    closeSelect() {
      if (this.selected==null) {
        this.$refs.input.value=null; // clear searchbar if closed without selecting
      }
      this.selectActive = false;
    },
    filter(OGoptions) {
      var options = JSON.parse(JSON.stringify(OGoptions)) //deep copy
      if (this.$refs.select) {
        this.$refs.select.scrollTo(0,0)
      }
      if(this.$refs.input==null || this.$refs.input.value==null || !this.typeToSearch || this.$refs.input.value=='') {
        return options
      }
      return options.sort((a, b)=>{
        if((this.nameCast(a).includes(this.$refs.input.value) || this.nameCast(a).toLowerCase().includes(this.$refs.input.value)) && !(this.nameCast(b).includes(this.$refs.input.value) || this.nameCast(b).toLowerCase().includes(this.$refs.input.value))){
          return -1;
        }
        if(!(this.nameCast(a).includes(this.$refs.input.value) || this.nameCast(a).toLowerCase().includes(this.$refs.input.value)) && (this.nameCast(b).includes(this.$refs.input.value) || this.nameCast(b).toLowerCase().includes(this.$refs.input.value))){
          return 1;
        }
        if(!(this.nameCast(a).includes(this.$refs.input.value) || this.nameCast(a).toLowerCase().includes(this.$refs.input.value)) && !(this.nameCast(b).includes(this.$refs.input.value) || this.nameCast(b).toLowerCase().includes(this.$refs.input.value))){
          return 0;
        }
        if((this.nameCast(a).includes(this.$refs.input.value) || this.nameCast(a).toLowerCase().includes(this.$refs.input.value)) && (this.nameCast(b).includes(this.$refs.input.value) || this.nameCast(b).toLowerCase().includes(this.$refs.input.value))){
          return 0;
        }                      
      });
    },
    sort(options) {
      options.forEach(e=>{
        this.nameCast(e).includes(this.$refs.input.value)
      })
    },  
  },
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.data-element {
  margin: 5px 0 5px 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: flex-start;
  .data-element-label {
    text-decoration: underline;
    margin: 0 0 2px 0;
  }
  .data-element-value {
    margin: 0 0 0 4px;
  }  
}
#overlay-select {
  z-index: 1;
  position:absolute;
}
#element-body {
  margin: 0px 0;
  position: relative;
  font-family: inherit;
  &:not(.active) {
    z-index: 0;
  }
}
.input-select {
  position: relative;
  z-index: 2;
}

#select {
  min-height: 48px;
  max-height: 156px;
  position: absolute;
  opacity: 0;
  bottom: 14px;
  transform: translateY(100%);
  background-color: #fff;
  z-index: 2;
  overflow-y: scroll;
  width: 100%;
  height: 0;
  padding: 5px;
  box-shadow: 0px 0px 6px 2px rgba(0,0,0,0.35);
  padding: 0px;
  pointer-events: none;
  &.active {
    transition: height 0.3s cubic-bezier(.4,0,.2,1), opacity 0.3s cubic-bezier(.4,0,.2,1);
    height: 94px;
    pointer-events: all;
    opacity: 1;
  }
}
.select-option {
  text-align: left;
  cursor: pointer;
  padding: 2px 4px 2px 4px;
  font-size: 20px;
  &:hover {
    background-color: $background_hover_dark;
    color: $text_light;
  }
  &.selected {
    background-color: $kit_green;
    color: $text_light;
  }
}
#open-select {
  position: absolute;
  cursor: pointer;
  >svg {
    transform: rotate(0deg) scale(0.4);
    transition: transform 0.3s cubic-bezier(.4,0,.2,1);
  }
  right: 0;
  bottom: 14px;
  &.active {
    > svg {
      transform: rotate(180deg) scale(0.4);
      transition: transform 0.3s cubic-bezier(.4,0,.2,1);

    }
  }
}
</style>