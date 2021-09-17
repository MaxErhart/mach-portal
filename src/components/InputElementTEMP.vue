<template>
  <div class="element-body" :class="{'focus': isFocused, 'has-error': showError}" @click="test()">
    <label class="element-label" :class="{'focus': isFocused, 'has-value': value !='' && value !=null, 'has-error': showError, 'has-placeholder': placeholder!='' && placeholder != null}">
      {{labelName}} <span class="required-icon" v-if="required">*</span>
    </label>
    <input v-model="value" :name="elementId" :placeholder="placeholder" :type="inputType" class="element-input" :class="{'focus': isFocused, 'has-error': showError}" @focus="focus()" @blur="blur()" ref="elementInput">
    <span class="element-error" :class="{'has-error': showError}">{{errorMessage}}</span>
  </div>
</template>

<script>
import {ERROR_TYPES, ERROR_MESSAGES, INPUT_TYPES, VALIDATION_REGEX} from '../validationSettings.js'

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
    validationType: Number, 
  },
  data() {
    return {
      ERROR_TYPES,
      ERROR_MESSAGES,

      INPUT_TYPES,
      VALIDATION_REGEX,
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
      if(this.requiredError()) {
        return ERROR_TYPES.REQUIRED
      } else if(this.valueError()) {
        return ERROR_TYPES.VALUE
      } else {
        return ERROR_TYPES.VALID
      }
    },
    showError() {
      if(this.deFocusedOnce && this.error!=ERROR_TYPES.VALID) {
        return true
      } else {
        return false
      }
    },
    errorMessage() {
      if(this.customErrorMessage) {
        return this.customErrorMessage
      } else {
        return ERROR_MESSAGES[this.error]
      }
    }
  },
  methods: {
    requiredError() {
      if(this.required && !this.value) {
        return true
      } else {
        return false
      }
    },
    valueError() {
      if(!VALIDATION_REGEX[this.validationType].test(this.value)) {
        return true
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
      // console.log(this.valueError())
      // console.log(reg.test("\""))
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
      opacity: 0;
      font-size: 12px;
      top: 10px;
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
  }
</style>