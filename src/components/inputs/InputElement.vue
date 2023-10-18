<template>
  <div class="input-element" :style="[dynamicStyle, {'--color': color}]" :class="{active: focusActive}" >
    <div class="optional" v-if="!required && optional">(optional)</div>
    <label :for="name" :style="labelDynamicStyle" class="input-label" :class="{active: labelActive, 'has-error': showError, focused: focusActive}" >
      <span>{{label}}
        <span v-if="required">*</span>
      </span>
    </label>
    <!-- <input ref="input" class="input-field" @keydown.enter.prevent @keyup.prevent="change($event)" @change.prevent="change($event)" :style="inputDynamicStyle" :class="{'has-error': showError}" @focus="focus()" @blur="blur()" :readonly="readonly || readonlynostyle" v-model="value" :autocomplete="autocomplete ? autocomplete:'off'" :name="name" :placeholder="placeholder" :type="type"> -->
    <input :step="getStep(type)" v-model="value" ref="input" class="input-field" @keydown.enter.prevent @keydown="blockInvalidCharacter($event)" @keyup.prevent="change($event)" @change.prevent="change($event)" :style="inputDynamicStyle" :class="{'has-error': showError}" @focus="focus()" @blur="blur()" :readonly="readonly || readonlynostyle" :autocomplete="autocomplete ? autocomplete:'off'" :name="name" :placeholder="placeholder" :type="getType(type)">
    <div class="input-tooltip" v-if="tooltip">{{tooltip}}</div>
    <Transition name="slide">
      <div class="input-error" v-if="showError">{{error}}</div>
    </Transition>
  </div>
</template>

<script>
import * as validationSettings from '@/validationSettings.json'
import moment from 'moment'
export default {
  name: 'InputElement',
  components: {
  },
  props: {
    color: {
      default: '#00876c',
      type: String,
    },
    optional: {
      default: true,
      type: Boolean,
    },
    presetValue: [String,Number],
    readonly: Boolean,
    name: String,
    placeholder: String,
    type: {
      default: 'text',
      type: String,
    },
    autocomplete: String,
    label: String,
    tooltip: String,
    required: Boolean,
    validationType: String,
    readonlynostyle: Boolean,
    clean: Boolean,
  },
  data() {
    return {
      value: null,
      focusActive: false,
      deFocusedOnce: false,
      validationSettings,
      customError: null,
      lock: false,
    }
  },
  mounted() {
    this.configurePresetValue(this.presetValue)
  },
  watch: {
    presetValue(to) {
      this.configurePresetValue(to)      
    }
  },
  computed: {
    labelDynamicStyle() {
      const style = {
        // 'cursor': this.readonly || this.readonlynostyle ? 'pointer':'text'
      }
      if(this.readonly && !this.value && this.value!==0) {
        style['color'] = 'gray';
      }
      return style
    },
    inputDynamicStyle() {
      const style = {
        'cursor': this.readonly ? 'not-allowed' : this.readonlynostyle ? 'pointer':'text'
      }
      if(this.readonly && !this.focusActive) {
        style['color'] = 'gray';
        style['border-bottom'] = '1px solid gray'
      }
      return style
    },
    error() {
      if(this.customError) {
        return this.customError
      }
      if(this.required && !this.value) {
        return this.validationSettings.default.error_types.required_error.message
      }
      if(!this.required && !this.value) {
        return null
      }
      var type = undefined
      if(this.validationType && this.validationSettings.default.input_types[this.validationType]) {
        type = this.validationType
      } else if(this.type && this.validationSettings.default.input_types[this.type]) {
        type = this.type
      }
      const regex = new RegExp(this.validationSettings.default.input_types[type].regex)
      if(!regex.test(this.value)) {
        return this.validationSettings.default.error_types[type].message
      }
      return null
    },
    showError() {
      if(!this.deFocusedOnce) {
        return false
      }
      if(this.error) {
        return true
      }
      return false
    },
    errorMessage() {
      return 'as'
    },
    labelActive() {
      return (this.focusActive && !this.readonly) || this.value || this.value===0 || this.placeholder || this.type=='date' || this.type=='time'
    },
    dynamicStyle() {
      var height = 26+12+12
      if(this.tooltip) {
        height += 12
      }
      if(this.clean) {
        height = 26+12
      }
      return {
        'height': `${height}px` 
      }
    }
  },
  methods: {
    setError(error) {
      this.customError = error
      this.deFocusedOnce = true
    },
    getStep(type) {
      if(type==='integer') {
        return '1'
      }
      return "any"
    },
    getType(type) {
      if(type==='integer' || type==='float') {
        return 'number'
      }
      return type
    },
    configurePresetValue(presetValue) {
      if(!presetValue && presetValue!==0) {
        this.clear()
        return
      }
      if(this.type=='date') {
        const formats = ['YYYY-MM-DD', 'D.M.YYYY','M/D/YYYY']
        var date = null
        formats.forEach(format=>{
          const tmp = moment(presetValue, format,true)
          if(tmp.isValid()) {
            date = tmp
          }
        })
        this.value = date.format("YYYY-MM-DD");
      } else {
        this.value = presetValue
      }
    },
    focus() {
      if(!this.readonly) {
        this.focusActive = true
      }
      if(this.type=="date" && !this.readonly) {
        this.$refs.input.showPicker()
      }
      this.$emit('focus')
    },
    blur() {
      if(this.lock || this.readonly) {
        return
      }
      this.$refs.input.blur()
      this.$emit('blur')
      this.deFocusedOnce = true
      this.focusActive = false
    },
    clear() {
      this.value = null
      this.deFocusedOnce = false
      this.focusActive = false
    },
    blockInvalidCharacter(event) {
      if(this.type==='integer') {
        if(['e', 'E', '+', ',','.'].includes(event.key)) {
          event.preventDefault()
        }
      } else if(this.type==='float') {
        if(['e', 'E', '+',].includes(event.key)) {
          event.preventDefault()
        }
      }

    },
    change(event) {
      if(event.key=='Enter') {
        this.$emit('enter', this.value)
      }
      this.$emit('valueChange', this.value)
    }
  }

}
</script>


<style scoped lang="scss">
.input-element {
  position: relative;
  display: flex;
  flex-direction: column;
  padding-top: 12px;
  z-index: 0;
  &:before {
    position: absolute;
    top: 32px;
    background-color: var(--color);
    height: 2px;
    content: " ";
    z-index: 2;
    right: 0;
    left: 0;
    transition: .3s cubic-bezier(.4,0,.2,1);
    transition-property:  opacity, transform;
    opacity: 0;
    transform: scaleX(0);
  }
  &.active {
    &:before {
      transform: scaleX(1);
      opacity: 1;
      transition: .3s cubic-bezier(.4,0,.2,1);
      transition-property: opacity, transform;
    }
  }

}
.optional {
  position: absolute;
  font-size: 12px;
  color: gray;
  top: 0px;
  right: 0;
}
.input-label {
  position: absolute;
  font-size: 16px;
  display: flex;
  align-items: center;
  transition: .3s cubic-bezier(.25,.8,.25,1);
  top: 12px;
  transition-property: font-size,top,color;
  color: #2c3e50;
  z-index: -1;
  &.active {
    color: var(--color);
    font-size: 12px;
    top: 0px;
  }
  &.has-error:not(.focused) {
    color: #ff1744;
  }
}
.input-field {
  height: 22px;
  padding: 0;
  margin: 0;
  display: block;
  font-family: inherit;
  border: none;
  background: none;
  transition-property: font-size,padding-top,color;
  // background-color: white;
  font-size: 16px;
  outline: none;
  border-bottom: 1px solid rgba(0,0,0,.54);
  border-radius: 0;
  color: #2c3e50;
  &.has-error {
    border-bottom: 1px solid #ff1744;
  }
}
.input-tooltip {
  font-size: 12px;
  color: #2c3e50;
  text-align: start;
}
.input-error {
  position: relative;
  font-size: 12px;
  color: #ff1744;
  text-align: start;
}
.slide-enter-active {
  transition: .3s cubic-bezier(.4,0,.2,1);
}
.slide-enter-from {
  opacity: 0;
  transform: translateY(-100%);
}
.slide-leave-to {
  opacity: 1;
  transform: translateY(0%);
}
</style>
