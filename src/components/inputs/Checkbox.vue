<template>
  <div class="checkbox-wrapper" :style="!clean ? cleanStyle : {}">
  <!-- <div class="checkbox-wrapper" :style="cleanStyle"> -->
    <div class="checkbox">
      <div class="checkbox-container" @click.prevent="check()" :class="{active: checked}"></div>
      <label for="checkbox" @click.prevent="check()">
        {{data.label}} <span class="required-icon" v-if="data.required">*</span>
      </label>
      <input type="hidden" :name="name" id="checkbox" v-model="checked">
    
    </div>
    <template v-if="!clean">
      <span class="tooltip-element">{{data.tooltip}}</span>   
      <span class="error-element" :class="{'error': showError, 'tooltip': data.tooltip}">{{errorMessage}}</span>
    </template>
  </div>

</template>

<script>
export default {
  name: 'Checkbox',
  props: {
    name: String,
    data: Object,
    presetValue: Boolean,
    clean: Boolean,
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
    cleanStyle() {
      return {
        'margin': '0'
      }
    },
    hasError() {
      if(this.data.required && !this.checked) {
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
    check() {
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
    user-select: none;
    cursor: pointer;
  }
}
.checkbox-container {
  width: 20px;
  min-width: 20px;
  height: 20px;
  position: relative;
  border-radius: 2px;
  border: 2px solid transparent;
  transition: .4s cubic-bezier(.25,.8,.25,1);
  border-color: rgba(0,0,0,.54);




  &:after {
    border: 2px solid #fff;
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
  &.active {
    background-color: $kit_green;
    border-color: $kit_green;        
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
  margin: 5px 0 5px 0;
}

.error-element {
  text-align: left;
  display: block!important;
  left: 0;
  opacity: 0;
  transform: translate3d(0,-8px,0);
  pointer-events: none;
  position: relative;
  font-size: 12px;
  transition: .3s cubic-bezier(.4,0,.2,1);
  &.error {
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
