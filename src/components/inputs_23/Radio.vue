<template>
  <div class="radio" :style="{'--bg':bg}" :class="{disabled}">
    <div class="label" :class="{error:show_error}">{{label}}</div>
    <div class="options" :style="flex_style" :class="{error:show_error}">
      <div class="option" v-for="option in options" :key="option.id">
        <Checkbox :label="option.label" @change="change($event,option.id)" :ref="`option_${option.id}`" :readonly="disabled"/>
      </div>
    </div>
    <div class="error-message" v-if="show_error">{{error?.message}}</div>
  </div>
</template>

<script>
import Checkbox from '@/components/inputs_23/Checkbox.vue'

export default {
  name: 'Radio',
  components: {
    Checkbox,
  },
  props: {
    label: String,
    options: Array,
    direction: {
      default: "column",
      type: String,
    },
    bg: {
      type: String,
      default: "#fff",
    },
    required: Boolean,
    disabled: Boolean,
    external_error: Object,
    force_show_error:Boolean,
  },
  data() {
    return {
      value: null,
      user_interaction_ended_once: false,
    }
  },
  computed: {
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
      if(this.required && this.value===null) {
        return {message: "Input Required"}
      }
      return null
    },
    flex_style() {
      const style = {
        'flex-direction': 'column',
        'gap': '8px'
      }
      if(this.direction==="row") {
        style['flex-direction'] = 'row'
        style['gap'] = '16px'
      }
      return style
    },
  },
  methods: {
    setValue(id) {
      this.options.forEach(option=>{
        if(option.id===id) {
          this.$refs[`option_${option.id}`].checked = true
          this.value = option.id
          return
        } 
        this.$refs[`option_${option.id}`].clear()
      })
    },
    change(active,clicked_id) {
      this.user_interaction_ended_once = true
      if(active) {
        this.value = clicked_id
        this.options.filter(option=>option.id!==clicked_id).forEach(option=>{
          this.$refs[`option_${option.id}`].clear()
        })
      } else if(!active && clicked_id===this.value) {
        this.$refs[`option_${this.value}`].clear()
        this.value = null
      }
    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.label {
  position: absolute;
  top: 0;
  left: 0.6rem;
  padding: 0 0.4rem;
  font-size: 0.875rem;
  background-color: var(--bg);
  &.error {
    color: $error;
  }
}
.error-message {
  position: absolute;
  bottom: 0;
  left: 0.6rem;
  padding: 0 0.4rem;
  font-size: 0.875rem;
  background-color: var(--bg);
  color: $error;
}
.options {
  display: flex;
  // border: 1px solid red;
  border-radius: 4px;
  padding: 15.5px 1.5rem;
  box-shadow: 1px 0 0 0 black, -1px 0 0 0 black, 0 1px 0 0 black, 0 -1px 0 0 black;
  &.error {
    box-shadow: 1px 0 0 0 $error, -1px 0 0 0 $error, 0 1px 0 0 $error, 0 -1px 0 0 $error;
  }
}
.radio {
  position: relative;
  font-family: 'Quicksand', sans-serif;
  font-weight: 600;

  padding: 0.5rem 0;
  &.disabled {
    &::before {
      position: absolute;
      content: " ";
      width: calc(100% + 2px);
      height: calc(100% + 2px);
      top:-1px;
      left:-1px;
      z-index: 1;
      background-color: rgba(249,249,249,0.5);
    }
  }
}
</style>
