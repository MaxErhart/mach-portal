<template>
  <div class="element-body" :class="{'focus': isFocused, 'has-error': showError, 'has-tooltip':tooltip}" @click="test()">
    <label class="element-label" :class="{'focus': isFocused, 'has-value': value !='' && value !=null, 'has-error': showError, 'has-placeholder': placeholder!='' && placeholder != null}">
      {{labelName}} <span class="required-icon" v-if="required">*</span>
    </label>
    <input v-model="value" :name="elementId" :placeholder="placeholder" :type="inputType" class="element-input" :class="{'focus': isFocused, 'has-error': showError}" @focus="focus()" @blur="blur()" ref="elementInput">
    <span class="tooltip-element">{{tooltip}}</span>
    <span class="element-error" :class="{'has-error': showError, 'has-tooltip':tooltip}">{{errorMessage}}</span>
  </div>
</template>

<script>
import * as validationSettings from '../validationSettings.json'
export default {
  name: 'InputeElement',
  props: {
    preset: Boolean,
    elementId: String,
    labelName: String,
    inputType: String,
    tag: String,
    tooltip: String,
    placeholder: String,
    required: Boolean,

    
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
    if(this.preset) {
      this.value = this.$store.getters.getFormSubmissionData.data[this.elementId];
    }
  },
  computed: {
    error() {
      const error_types = this.validationSettings.default.error_types
      const valid = this.validationSettings.default.valid
      for (const key in error_types) {
        if(this[key]()) {
          return error_types[key]
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
    required_error() {
      if(this.required && !this.value) {
        return true
      } else {
        return false
      }
    },
    value_error() {
      if(this.value) {
        const regex = new RegExp(this.validationSettings.default.input_types[this.inputType].regex)
        return !regex.test(this.value)
      } else {
        return false
      }
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
    test() {

    },   
  },
}
</script>


<style scoped lang="scss">
  .element-body {
    position: relative;
    display: flex;
    font-family: inherit;
    margin: 4px 12px 24px 12px;
    padding-top: 16px;
    height: 100%;
    min-height: 48px;
    &:before,:after {
      position: absolute;
      bottom: 0;
      right: 0;
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
    &.has-tooltip {
      margin: 4px 12px 36px 12px;
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
    display: block!important;
    left: 0;
    opacity: 0;
    transform: translate3d(0,-8px,0);
    pointer-events: none;
    height: 20px;
    position: absolute;
    bottom: -22px;
    font-size: 12px;
    transition: .3s cubic-bezier(.4,0,.2,1);
    &.has-error {
      color: #ff1744;
      opacity: 1;
      transform: translateZ(0);      
    }
    &.has-tooltip {
      bottom: -36px;
    }     
  }
.tooltip-element {
  display: block!important;
  left: 0;
  opacity: 1;
  transform: translate3d(0,-8px,0);
  pointer-events: none;
  height: 20px;
  position: absolute;
  bottom: -22px;
  font-size: 12px;
  transition: .3s cubic-bezier(.4,0,.2,1);
  transform: translateZ(0);
}  
</style>