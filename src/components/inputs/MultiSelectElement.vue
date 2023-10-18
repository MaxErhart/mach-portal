<template>
  <div class="multiselect">

    <div class="element-body" :class="{'focus': isFocused, 'has-error': showError, 'has-tooltip':tooltip}" :style="{'height': `${height}px`, 'margin-bottom': `${marginBottom}px`}">
      <label class="element-label" :class="{'focus': isFocused, 'has-value': (value !='' && value !=null) || selected.length>0, 'has-error': showError, 'has-placeholder': placeholder!='' && placeholder != null}">
        {{label}} <span class="required-icon" v-if="required">*</span>
      </label>
      <input :style="inputStyle" v-model="value" :autocomplete="autocomplete ? 'on':'off'" @keyup="emitTyping()" :placeholder="placeholder" type="text" class="element-input" :class="{'focus': isFocused, 'has-error': showError}" @focus="focus()" @blur="blur()" ref="elementInput">
      
      <div class="selections" ref="selections">
        <div class="selected" v-for="(selection, index) in selected" :key="selection">
          {{nameCast(selection)}}
          <div class="remove-select" @click="removeSelection(selection)">
            <svg width="26" height="26" xmlns="http://www.w3.org/2000/svg" version="1.1" xml:space="preserve">
                <path d="M1,1,24,24 M24,1,1,24" stroke-width="4" />
            </svg>
          </div>
          <input type="hidden" :name="`${name}_${index}`" :value="valueCast(selection)">
        </div>
      </div>
      <span class="tooltip-element">{{tooltip}}</span>
      <span class="element-error" :class="{'has-error': showError, 'has-tooltip':tooltip}">{{errorMessage}}</span>
    </div>  

    <div id="open-select" :class="{active: isFocused}" @click="openSelect()">
      <svg width="32" height="18" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" version="1.1">
        <path stroke="null" d="m31.44697,0.76641c-0.56332,-0.56332 -1.47683,-0.56342 -2.04024,0.0001l-13.40638,13.40667l-13.40705,-13.40677c-0.56332,-0.56332 -1.47683,-0.56342 -2.04024,0.0001c-0.56342,0.56342 -0.56342,1.47683 0,2.04024l14.42722,14.42684c0.27055,0.27055 0.63747,0.42251 1.02007,0.42251s0.74962,-0.15206 1.02007,-0.42261l14.42646,-14.42684c0.56351,-0.56332 0.56351,-1.47683 0.0001,-2.04024z" id="XMLID_225_"/>
      </svg>      
    </div>

    <div id="select" v-if="data != null && data != undefined" :class="{active: isFocused}" :style="{height: (isFocused && data) ? `${Math.min(data.length*28, 150)}px` : '0px'}">
      <div class="select-option" v-for="entry in filter(data)" :key="entry" @mousedown="selectEntry(entry)">{{nameCast(entry)}}</div>
    </div>


  </div>
</template>

<script>
export default {
  name: 'MultiSelectElement',
  props: {
    label: String,
    required: Boolean,
    placeholder: String,
    tooltip: {
      default: '',
      type: String,
    },
    name: String,
    autocomplete: {
      default: false,
      type: Boolean,
    },
    height: {
      default: 47,
      type: Number,
    },
    marginBottom: {
      default: 16,
      type: Number,
    },
    nameCast: {
      default: (e)=>`${e.data.label}`,
      type: Function,
    },
    valueCast: {
      default: (e)=>`${e.id}`,
      type: Function,
    },      
    data: Object,
    preset: Object,
  },
  data() {
    return {
      isFocused: false,
      value: '',

      selected: [],

      inputOffset: 0,
    }
  },
  mounted() {
    if(this.preset) {
      this.selected = []
      this.selected = this.selected.concat(this.preset.map(e=>e.name))
    }
  },
  watch: {
    preset(to) {
      this.selected = []
      this.selected = this.selected.concat(to.map(e=>e.name))
    }
  },  
  computed: {
    inputStyle() {
      return {
        'padding-left': `${this.inputOffset+4}px`
      }
    },
    showError() {
      return false
    },
    errorMessage() {
      return ''
    }
  },
  methods: {
    openSelect() {
      this.isFocused=true
    },
    filter(data) {
      var notInputs = ['SectionElement', 'HeaderElement']
      var filtered = data.filter(e=>!this.selected.includes(e))
      filtered = filtered.filter(e=>!notInputs.includes(e.component))
      if(this.value==null) {
        return filtered
      }
      return filtered.sort((a, b)=>{
        if(this.nameCast(a).includes(this.value) && !this.nameCast(b).includes(this.value)){
          return -1;
        }
        if(!this.nameCast(a).includes(this.value) && this.nameCast(b).includes(this.value)){
          return 1;
        }
        if(!this.nameCast(a).includes(this.value) && !this.nameCast(b).includes(this.value)){
          return 0;
        }
        if(this.nameCast(a).includes(this.value) && this.nameCast(b).includes(this.value)){
          return 0;
        }                      
      });      
    },
    removeSelection(selection) {
      const index = this.selected.indexOf(selection)
      this.selected.splice(index, 1)
      this.$nextTick(()=>{
        this.inputOffset = this.$refs.selections.getBoundingClientRect().width
      })      
    },
    selectEntry(entry) {
      this.selected.push(entry)
      this.$nextTick(()=>{
        this.inputOffset = this.$refs.selections.getBoundingClientRect().width
      })
    },
    focus() {
      this.isFocused = true
    },
    blur() {
      if(this.isFocused) {
        this.deFocusedOnce = true
      }
      this.isFocused = false
    },
    emitTyping() {
      this.$emit('typing', this.value)
    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';

.multiselect {
  position: relative;
}

.element-body {
  // outline: 1px solid black;
  position: relative;
  display: flex;
  flex-direction: column;
  font-family: inherit;
  margin-top: 0px;
  padding-top: 16px;
  &.has-tooltip {
    &:before,:after {
      bottom: 28px;
    } 
  }
  &:before,:after {
    position: absolute;
    right: 0;
    bottom: 0px;
    left: 0;
    z-index: 1;
    transition: border .3s cubic-bezier(.4,0,.2,1),opacity .3s cubic-bezier(.4,0,.2,1),transform 0s cubic-bezier(.4,0,.2,1) .3s;
    will-change: border,opacity,transform;
    content: " ";
  }
  &:before {
    background-color: #00876c;
    height: 2px;
    z-index: 2;
    opacity: 0;
    transform: scaleX(.12);      
  }
  &.focus{
    &:before {
      opacity: 1;
      transform: scaleX(1);
      transition: .3s cubic-bezier(.4,0,.2,1);
      transition-property: border, opacity, transform;
    }      
  }   
}
.element-label {
  position: absolute;
  top: 23px;
  left: 0px;
  pointer-events: none;
  transition: .4s cubic-bezier(.25,.8,.25,1);
  transition-duration: .3s;
  font-size: 16px;
  line-height: 20px;
  
  color: rgba(0,0,0,.54);
  &.has-placeholder {
    opacity: 1;
    font-size: 12px;
    top: 0px;
  }
  &.focus {
    color: #00876c;
    top: 0px;
    font-size: 12px;  
    opacity: 1;    
  }
  &.has-value {
    top: 0px;
    font-size: 12px;
    opacity: 1;      
  }
  &.has-error:not(.focus) {
    color: #ff1744;
  }    
}
.element-input {
  position: absolute;
  height: 100%;
  width: 100%;
  height: 32px;
  padding: 0;
  display: block;
  flex: 1;
  border: none;
  background: none;
  transition: .4s cubic-bezier(.25,.8,.25,1);
  transition-property: font-size,padding-top,color;
  font-family: inherit;
  font-size: 16px;
  line-height: 32px;
  border-bottom: 1px solid rgba(0,0,0,.54);
  outline: none;
  &.has-error {
    border-bottom: 1px solid #ff1744;
  }
}
.element-error {
  text-align: left;
  display: block!important;
  left: 0;
  opacity: 0;
  transform: translate3d(0,-8px,0);
  pointer-events: none;
  position: relative;
  font-size: 12px;
  transition: .3s cubic-bezier(.4,0,.2,1);
  &.has-error {
    color: #ff1744;
    opacity: 1;
    transform: translateZ(0);      
  }    
}
.tooltip-element {
text-align: left;
display: block!important;
left: 0;
opacity: 1;
transform: translate3d(0,-8px,0);
pointer-events: none;
position: relative;
font-size: 12px;
transition: .3s cubic-bezier(.4,0,.2,1);
transform: translateZ(0);
}


#select {
  min-height: 48px;
  max-height: 156px;
  position: absolute;
  opacity: 0;
  bottom: 0px;
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
  bottom: 1px;
  &.active {
    > svg {
      transform: rotate(180deg) scale(0.4);
      transition: transform 0.3s cubic-bezier(.4,0,.2,1);

    }
  }
}
.selections {
  position: absolute;
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  // height: 40px;
  bottom: 2px;
}
.selected {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  user-select: none;
  background-color: #dfdfdf;
  border-radius: 16px;  
  font-size: 16px;
  padding: 0px 2px 0px 14px;
  margin: 0 2px;
}
.remove-select {
  display: flex;
  top: 0;
  right: 0;
  transform: scale(0.5);
  cursor: pointer;
  > svg {
    stroke: $text_dark;
  }
}
</style>
