<template>
  <div class="checkbox-wrapper" :class="{readonly: readonly}">
    <div class="checkbox">
      <div class="checkbox-container" @click.stop="check()" :class="{active: checked, invert: invert}" ref="input"></div>
      <label for="checkbox" @click.stop="check()" :class="{'error': showError}">
        {{label}} <span class="required-icon" v-if="required">*</span>
      </label>
      <input type="hidden" :name="name" id="checkbox" v-model="checked">
    
    </div>
    <template v-if="!clean">
      <span class="tooltip-element">{{tooltip}}</span>   
      <span class="error-element" :class="{'error': showError, 'tooltip': tooltip}">{{errorMessage}}</span>
    </template>
  </div>

</template>

<script>
export default {
  name: 'Checkbox',
  props: {
    name: String,
    required: Boolean,
    label: String,
    tooltip: String,
    presetValue: Boolean,
    clean: Boolean,
    invert: Boolean,
    readonly: Boolean,
    streatch: Boolean,
  },
  data() {
    return {
      checked: false,
      deFocusedOnce: false,
      errorMessage: 'Field required',
    }
  },
  mounted() {
    if(this.presetValue) {
      this.checked=this.presetValue
    }
  },
  watch: {
    presetValue(to) {
      this.checked = to
    }
  },
  computed: {
    hasError() {
      if(this.required && !this.checked) {
        return true
      }
      return false
    },
    showError() {
      if(this.hasError && this.deFocusedOnce) {
        return true
      }
      return false
    }
  },
  methods: {
    clear() {
      this.checked = false
      this.deFocusedOnce = false
    },
    check() {
      if(this.readonly) {
        return
      }
      this.checked = !this.checked
      this.deFocusedOnce = true
      this.$emit('inputChange', this.checked)
    },
  }

}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.checkbox {
  box-sizing: border-box;
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 4px;


  cursor: pointer;
  > label {
    word-wrap: break-word;
    // word-break: break-all;
    user-select: none;
    cursor: pointer;
    &.error {
      color: #ff1744;
    }
  }
}
.checkbox-container {
  width: 20px;
  min-width: 20px;
  height: 20px;
  position: relative;
  border-radius: 2px;
  border: 2px solid rgba(0,0,0,.54);
  transition: .4s cubic-bezier(.25,.8,.25,1);
  &:not(.invert) {
    &:after {
      border: 2px solid white;
      border-top: 0;
      border-left: 0;
      position: absolute;
      content: " ";
      width: 6px;
      height: 13px;
      left: 5px;
      transform: rotate(45deg) scale3D(.15,.15,1);
      transition: transform .4s cubic-bezier(.25,.8,.25,1), opacity .4s cubic-bezier(.25,.8,.25,1);
      opacity: 0;
    }
  }
  &.invert{
    &:after {
      border: 2px solid $kit_green;
      border-top: 0;
      border-left: 0;
      position: absolute;
      content: " ";
      width: 6px;
      height: 13px;
      left: 5px;
      transform: rotate(45deg) scale3D(.15,.15,1);
      transition: transform .4s cubic-bezier(.25,.8,.25,1), opacity .4s cubic-bezier(.25,.8,.25,1);
      opacity: 0;
    }
  }
  &:not(.invert).active{
    background-color: $kit_green;
    border-color: $kit_green; 
  }
  &.invert.active {
    background-color: white;
    border-color: white;        
  }
  &.active:after{
    opacity: 1;
    transform: rotate(45deg) scale3D(1,1,1);
    transition: transform .4s cubic-bezier(.25,.8,.25,1);
  }
}
.checkbox-wrapper{
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  &.readonly {
    * {
      color: gray;
      cursor: not-allowed; 
    }
  }
}

.error-element {
  text-align: left;
  left: 0;
  opacity: 0;
  transform: translateY(-100%);
  pointer-events: none;
  position: relative;
  font-size: 12px;
  height: 12px;
  transition: .3s cubic-bezier(.4,0,.2,1);
  &.error {
    color: #ff1744;
    opacity: 1;
    transform: translateY(0);      
  }    
}
.tooltip-element {
  text-align: left;
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
