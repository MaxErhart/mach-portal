<template>
  <div class="element-background" @click="closeMenu()" :class="{'active': isActive}"></div>
  <div class="element-body" :class="{'isActive': isActive, 'focus': isFocused}" ref="element_body">
    <label :for="elementId" class="element-label" :class="{'isActive': isActive, 'has-value': value !='' && value !=null,  'focus': isFocused, 'error': showError}">
      {{labelName}} <span class="required-icon" v-if="required">*</span>
    </label>
    <div class="element-input-container" role="button" @click="openMenu()" ref="input_select_container" :class="{'error': showError}">
      <input type="text" :name="elementId" :id="elementId" readonly class="element-selected" @blur="blur()" ref="input_selection" v-model="value">
      <span class="element-suffix-icon" :class="{'isActive': isActive}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" class="element-svg" :class="{'isActive': isActive}"><path d="M7,10L12,15L17,10H7Z"></path></svg>
      </span>
    </div>
    <span class="element-error" :class="{'error': showError}">Field required</span>    
  </div>
  <transition name="fade">
    <div class="select-menu-body" v-if="isActive" :class="{'active': isActive}" :style="menuStyle" >
        <div class="list-item" @click="selectItem(item)" v-for="item in options" :key="item">{{item}}</div>
    </div>
  </transition>  

</template>

<script>
import * as validationSettings from '../validationSettings.json'
export default {
  name: 'SelectElement',
  props: {
    preset: Boolean,
    elementId: String,
    labelName: String,
    tooltip: String,
    required: Boolean,
    numOptions: String,
    options: Array,
    
    tag: String,
  },
  data() {
    return {
      validationSettings,
      value: null,
      deFocusedOnce: false,
      isActive: false,
      isFocused: false,
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
    menuStyle() {
      return {'top': this.$refs.element_body.offsetTop+18+'px', 'left': this.$refs.element_body.offsetLeft+'px', 'width': this.$refs.element_body.offsetWidth+'px'}
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
      return false
    },    
    openMenu() {
      this.isFocused = true
      this.isActive = true
      this.$refs.input_selection.focus()
    },
    closeMenu() {
      if(this.isActive) {
        this.isActive = false
        this.blur()
      }
    },
    blur() {
      if(!this.isActive) {
        this.isFocused = false
        this.deFocusedOnce = true
      } else {
        this.$refs.input_selection.focus()
      }
    },
    selectItem(item) {
      this.value = item
      this.isActive = false
    },
    test() {

    }, 
     
  },
  mounted() {
    if(this.preset) {
      this.value = this.$store.getters.getFormSubmissionData.data[this.elementId];
    }    
  }
}
</script>


<style scoped lang="scss">
.element-background {
  position:absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  // background-color: rgba(0,0,0,0.5);
  z-index: 0;
  pointer-events: none;
  &.active {
    z-index: 3;
    pointer-events: auto;
  }
}
.element-body {
  position: relative;
  display: flex;
  font-family: inherit;
  margin: 4px 12px 24px 12px;
  padding-top: 16px;
  height: 100%;
  min-height: 48px;
  z-index: 1;
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
  &.isActive{
    &:before {     
      opacity: 1;
      transform: scaleX(1);
      transition: .3s cubic-bezier(.4,0,.2,1);
      transition-property: border, opacity, transform;
    }      
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
  &.isActive {
    color: #00876c;
    top: 0px;
    font-size: 12px;  
    opacity: 1;    
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
  &.error:not(.focus) {
    color: #ff1744;
  }   
}
.element-input-container {
  cursor: pointer;
  border-bottom: 1px solid rgba(0,0,0,.54);
  display: flex;
  flex-direction: row;
  width: 100%;
  // box-shadow: 0 3px 1px -2px rgba(0,0,0,.2),0 2px 2px 0 rgba(0,0,0,.14),0 1px 5px 0 rgba(0,0,0,.12);
  &.error {
    border-bottom: 1px solid #ff1744;
  }  
}
.element-selected {
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
  pointer-events: none;
}
.element-suffix {
  align-items: center;
  display: inline-flex;
  height: 24px;
  flex: 1 0 auto;
  justify-content: center;
  min-width: 24px;
  width: 24px;  
}
.element-suffix-icon {
  align-items: center;
  display: inline-flex;
  justify-content: center;
  position: relative;
  transition: .3s cubic-bezier(.25,.8,.5,1),visibility 0s;
  vertical-align: middle;
  user-select: none;
  &.isActive {
    transform: rotate(180deg);
    transition: .3s cubic-bezier(.25,.8,.5,1),visibility 0s;
  }
}
.element-svg {
  widows: 24px;
  height: 24px;
  fill: currentColor;
  padding: 0;
  margin: 0;
  &.isActive {
    color:#00876c;    
  }
}
.select-menu-body {
  padding: 4px 0;
  position: absolute;
  background-color: #fff;
  display: inline-block;
  overflow-y: auto;
  overflow-x: hidden;
  contain: content;
  box-shadow: 0 5px 5px -3px rgba(0,0,0,.2),0 8px 10px 1px rgba(0,0,0,.14),0 3px 14px 2px rgba(0,0,0,.12);
  border-radius: 4px;
  z-index: 8;
  pointer-events: none;
  top: 0;
  max-height: 210px;
  min-width: 275px;
  top: 16px;
  &.active {
    pointer-events: auto;  
  }
}
.fade-enter-active  {
  opacity: 1;
  transition: .3s cubic-bezier(.4,0,.2,1);
  transition-delay: .1s;
  transition-property: opacity;
}
.fade-leave-active {
  opacity: 1;
  transition: .1s cubic-bezier(.4,0,.2,1);
  transition-property: opacity;    
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.list-item {
  position: relative;
  padding: 10px 16px;
  font-size: 16px;
  cursor: pointer;
  overflow: hidden;
  &:hover {
    background-color: #f2f2f2;
  }
}
span.ripple {
  position: absolute; /* The absolute position we mentioned earlier */
  border-radius: 50%;
  transform: scale(0);
  animation: ripple 600ms linear;
  background-color: rgba(255, 255, 255, 0.7);  
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
  &.error {
    color: #ff1744;
    opacity: 1;
    transform: translateZ(0);      
  }  
}
</style>