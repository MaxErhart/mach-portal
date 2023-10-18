<template>
  <div class="input-element" :class="{focus,active,disabled,error:show_error}" :style="{'--color':color,'--bg':bg,'--bg-invert':bg_invert}">
    <label :class="{focus,active,disabled,error:show_error}" :for="name">{{label}}</label>
    <input :class="{focus,active,disabled,error:show_error}" :required="required" :disabled="disabled" :placeholder="active ? placeholder : null"  :type="type" ref="input" :id="name" :name="name" v-model="value" @focus="handleFocus()" @blur="handleBlur()">
    <div class="active-label-placeholder" :class="{active}">{{label}}</div>
    <div class="error-message-background" :class="{focus,active,disabled}" v-if="show_error">{{error?.message}}</div>
    <div class="error-message" v-if="show_error">
      {{error?.message}}
    </div>
  </div>
</template>

<script>
export default {
  name: 'InputElement',
  props: {
    type: {
      default: 'text',
      type: String,
    },
    label: String,
    name: String,
    placeholder: String,
    bg: {
      type: String,
      default: "#fff",
    },
    color: {
      type: String,
      default: "#00876c",
    },
    required: Boolean,
    disabled: Boolean,
    external_error: Object,
    force_show_error:Boolean,
  },
  data() {
    return {
      value: null,
      focus: false,
      user_interaction_ended_once: false,
    }
  },
  computed: {
    bg_invert() {
      if(!this.bg) {
        return this.bg
      }
      var rgb_values = []
      if(this.bg.split("(").length>1) {
        rgb_values = this.bg.split("(")[1].split(")")[0].split(",").map(string_val=>string_val.trim())
      } else {
        if(this.bg.length===4) {
          rgb_values = [parseInt(this.bg[1]+this.bg[1], 16),parseInt(this.bg[2]+this.bg[2], 16),parseInt(this.bg[3]+this.bg[3], 16)]
        } else if(this.bg.length===7) {
          rgb_values = [parseInt(this.bg[1]+this.bg[2], 16),parseInt(this.bg[3]+this.bg[4], 16),parseInt(this.bg[5]+this.bg[6], 16)]
        }
      }
      const r = 255-rgb_values[0]*0.1
      const g = 255-rgb_values[1]*0.1
      const b = 255-rgb_values[2]*0.1
      return `rgb(${r},${g},${b})`
    },
    active() {
      return this.focus || this.value || this.type==='date'
    },
    show_error() {
      if(!this.error || (!this.user_interaction_ended_once && !this.force_show_error)) {
        return false
      }
      return true
    },
    error() {
      if(this.external_error) {
        return this.external_error
      }
      if(this.required && !this.value) {
        return {message: "Input Required"}
      }
      return null
    },
  },
  methods: {
    setValue(value) {
      this.value = value
    },
    handleFocus() {
      if(this.disabled) {
        return
      }
      if(this.type==="date") {
        this.$refs.input.showPicker()
      }
      this.focus = true
    },
    handleBlur() {
      this.focus = false
      this.user_interaction_ended_once = true
    },
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.input-element {
  font-size: 1.125rem;
  position: relative;
  background-color: var(--bg);
  padding: 0.5em 0;
  font-family: 'Quicksand', sans-serif;
  font-weight: 600;
  &.disabled {
    &::before {
      position: absolute;
      z-index: 3;
      content: " ";
      top:-1px;
      left:-1px;
      width: calc(100% + 2px);
      height: calc(100% + 2px);
      background-color: rgba(249,249,249,0.5);
    }
  }
}
input {
  font-family: 'Quicksand', sans-serif;
  font-weight: 500;
  font-size: 1.125rem;
  width: 100%;
  padding: 0 1.5em;
  background-color: var(--bg);
  height: 54px;
  outline: none;
  border-radius: 4px;
  border: none;
  box-shadow: 1px 0 0 0 black, -1px 0 0 0 black, 0 1px 0 0 black, 0 -1px 0 0 black;
  &.focus {
    box-shadow: 2px 0 0 0 var(--color), -2px 0 0 0 var(--color), 0 2px 0 0 var(--color), 0 -2px 0 0 var(--color);
  }
  &.error {
    box-shadow: 1px 0 0 0 $error, -1px 0 0 0 $error, 0 1px 0 0 $error, 0 -1px 0 0 $error;
    &.focus {
      box-shadow: 2px 0 0 0 $error, -2px 0 0 0 $error, 0 2px 0 0 $error, 0 -2px 0 0 $error;
    }
  }
}
.active-label-placeholder {
  font-size: 0.875rem;
  position: absolute;
  top: 0;
  left: 0.60rem;
  background-color: var(--bg);
  padding: 0 0.40rem;
  color: transparent;
  visibility: hidden;
  pointer-events: none;
  &.active {
    visibility: visible;
  }
}
.error-message-background {
  font-size: 0.875rem;
  position: absolute;
  bottom: 0px;
  left: 0.60rem;
  // background-color: #5cf;
  background-color: var(--bg);
  padding: 0 0.40rem;
  color: transparent;
  // &.disabled {
  //   background: linear-gradient(0deg, var(--bg) 46%, var(--bg-invert) 46%);
  // }

  // filter
}
.error-message {
  font-size: 0.875rem;
  position: absolute;
  bottom: 0;
  left: 0.60rem;
  background:none;
  color: $error;
  padding: 0 0.40rem;
  color: $error;
  pointer-events: none;
}
label {
  z-index: 1;
  font-size: 1.125rem;
  position: absolute;
  top: 50%;
  left: 1.5rem;
  transform: translateY(-50%);
  pointer-events: none;
  transition: top 100ms linear, left 100ms linear, font-size 100ms linear;
  &.error {
    color: $error !important;
  }
  &.focus {
    color: var(--color);
  }
  &.active {
    font-size: 0.875rem;
    top:0.5rem;
    left: 1rem;
  }
}
</style>
