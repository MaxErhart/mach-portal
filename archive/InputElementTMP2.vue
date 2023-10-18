<template >
  <template v-if="data">
    <div class="element-body" :class="{'focus': isFocused, 'has-error': showError, 'has-tooltip':data.tooltip}" :style="{'min-height': `${height}px`, 'margin-bottom': `${0}px`}" @click="test()">
      <label class="element-label" :class="{'focus': isFocused, 'has-value': value !='' && value !=null, 'has-error': showError, 'has-placeholder': hasPlaceholder}">
        {{data.label}} <span class="required-icon" v-if="data.required">*</span>
      </label>
      <input :readonly="readonly" v-model="value" :autocomplete="data.autocomplete ? data.autocomplete:'off'" @keyup="emitChange()" @change="emitChange()" :name="name" :placeholder="data.placeholder" :type="data.type" class="element-input" :class="{'focus': isFocused, 'has-error': showError}" @focus="focus()" @blur="blur()" ref="elementInput">
      <span class="tooltip-element">{{data.tooltip}}</span>
      <span class="element-error" :class="{'has-error': showError, 'has-tooltip':data.tooltip}">{{errorMessage}}</span>
    </div>
  </template>
</template>

<script>
import * as validationSettings from '@/validationSettings.json'
import moment from 'moment'
export default {
  name: 'InputeElement',
  props: {
    name: String,
    data: Object,

    presetValue: String,
    height: {
      default: 40,
      type: Number,
    },
    marginBottom: {
      default: 0,
      type: Number,
    },
    readonly: {
      default: false,
      type: Boolean,
    },
    customValid: Object,

  },
  data() {
    return {
      validationSettings,
      value: null,
      

      isFocused: false,
      deFocusedOnce: false,
    }
  },
  mounted() {
    if(this.presetValue) {
      if(this.data && this.data.type=="date") {
        this.value = moment(this.presetValue).format("yyyy-MM-DD");
      } else {
        this.value = this.presetValue
      }
    }
  },
  watch: {
    presetValue(to) {
      if(this.data && this.data.type=="date") {
        this.value = moment(to).format("yyyy-MM-dd");
      } else {
        this.value = to
      }      
    }
  },
  computed: {
    hasPlaceholder() {
      return (this.data.placeholder && this.data.placeholder!='') || this.data.type=="date" ||  this.data.type=="time"
    },
    error() {
      // const error_types = this.validationSettings.default.error_types
      const valid = this.validationSettings.default.valid
      // for (const key in error_types) {
      //   if(this[key]()) {
      //     return error_types[key]
      //   }
      // }
      if(this.customValid) {
        const regex = new RegExp(this.customValid.regex)
        if(!regex.test(this.value)) {
          return this.customValid
        }
      }
      return valid
    },
    hasError() {
      const valid = this.validationSettings.default.valid      
      if(this.error != valid) {
        return true
      } else {
        return false
      }
    },
    showError() {     
      if(this.deFocusedOnce && this.hasError) {
        return true
      } else {
        return false
      }
    },
    errorMessage() {
      return this.error.message
    }
  },
  methods: {
    emitChange() {
      this.$emit('valueChange', this.value);
    },
    required_error() {
      if(this.data.required && !this.value) {
        return true
      } else {
        return false
      }
    },
    value_error() {
      if(this.value) {
        const regex = new RegExp(this.validationSettings.default.input_types[this.data.type].regex)
        return !regex.test(this.value)
      } else {
        return false
      }
    },
    focus() {
      this.isFocused = true
      this.$emit('focus')
    },
    blur() {
      if(this.isFocused) {
        this.deFocusedOnce = true
      }
      this.$emit('blur')
      this.isFocused = false
    },
    test() {

    },   
  },
}
</script>


<style scoped lang="scss">
  .element-body {
    position: relative;
    display: flex;
    flex-direction: column;
    font-family: inherit;
    margin: 0px;
    padding-top: 16px;
    &.has-tooltip {
      &:before,:after {
        bottom: 28px;
      } 
    }
    &:before,:after {
      position: absolute;
      right: 0;
      bottom: 13px;
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
    // box-shadow: 0 3px 1px -2px rgba(0,0,0,.2),0 2px 2px 0 rgba(0,0,0,.14),0 1px 5px 0 rgba(0,0,0,.12);    
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
    outline: none;
    border-bottom: 1px solid rgba(0,0,0,.54);
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
</style>