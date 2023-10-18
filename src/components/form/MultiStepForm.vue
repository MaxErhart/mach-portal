<template>
  <div class="multi-step-form" :style="style_root" >

    <div class="multi-step-form-step-indication">
      <div class="progress-indicator" :style="style_progress_indicator"></div>
      <div class="step-indicator" :class="{'active': isStepActive(index-1)}" v-for="index in n_steps" :key="index">
        <div class="step-number">{{index}}</div>
        <div class="step-text">{{step_titles[index-1]}}</div>
      </div>

    </div>
    <form class="multi-step-form-body" ref="form" @submit.prevent>

      <div class="step" :style="style_multi_step_form" ref="step">
        <slot name="step"/>
      </div>

      <div class="step-navigation">
        <div class="multi-step-form-button" v-if="step>0">
          <Button @click.prevent="decrement(step)" class="button-inner" :text="getPrevText(step)" :loading="button_previous_loading" ref="button_previous"/>
        </div>
        <div class="multi-step-form-button next" v-if="step<n_steps-1">
          <Button @click.prevent="increment(step)" class="button-inner" :text="getNextText(step)" :loading="button_next_loading" ref="button_next"/>
        </div>
        <div class="multi-step-form-button next" v-if="step==n_steps-1">
          <Button @click.prevent="submit()" class="button-inner" :text="submit_text" :loading="button_submit_loading" ref="button_submit"/>
        </div>
      </div>


      
    </form>


  </div>
</template>

<script>
import Button from '@/components/Button.vue'
export default {
  name: 'MultiStepForm',
  components: {
    Button,
  },
  props: {
    step: Number,
    n_steps: Number,
    max_height: Number,
    step_titles: Array,
    refs: Object,
    next_text: Object,
    prev_text: Object,
    submit_text: {
      default: "Submit",
      type: String,
    },
    aspect_ratio: {
      default: '0.75',
      type: String,
    },
  },
  data() {
    return {
      button_previous_loading: false,
      button_next_loading: false,
      button_submit_loading: false,
    }
  },
  watch: {
    refs(to) {
      console.log(to)
    }
  },
  computed: {
    style_root() {
      return {
        'height': this.max_height+'px',
        'aspect-ratio': this.aspect_ratio,
      }
    },
    style_multi_step_form() {
      const gap = 41
      return {
        'grid-template-columns': `repeat(${this.n_steps},calc(100% / ${this.n_steps} - ${(this.n_steps-1)/this.n_steps}*${gap}px)`,
        'left': `calc(-${this.step}*100%)`,
        'width': `calc(${this.n_steps}*100%)`,
        'gap': gap+'px',
        'transition': 'left ease 0.2s',
        'align-items': 'center',
        'padding': '0 20px',
      }
    },
    style_progress_indicator() {
      return {
        'width': this.step/(this.n_steps-1)*100+'%' 
      }
    },
    current_next_button() {
      if(this.step<this.n_steps-1) {
        return this.$refs.button_next
      }
      return this.$refs.button_submit
    }
  },
  methods: {
    getNextText(step) {
      if(this.next_text && step in this.next_text) {
        return this.next_text[step]
      }
      return 'Next'
    },
    getPrevText(step) {
      if(this.prev_text && step in this.prev_text) {
        return this.next_text[step]
      }
      return 'Previous'
    },
    prev_steps_valid(button) {
      var step_component = null
      for(var i=0;i<=this.step;i++) {
        step_component = this.refs?.[`step_${i}`]
        if(!step_component?.isValid()) {
          button.error = true
          button.errorMessage = step_component?.error_message
          return false
        }
      }
      return true
    },
    isStepActive(step) {
      if(this.step<step) {
        return false
      }
      return true
    },
    errorHandler(error, button) {
      if(error?.response.status!=400 || !error?.response?.data?.form) {
        this.emitter.emit('showErrorMessage', {error: error, action: 'Increment step', redirect: null})
        return
      }
      if(error.response.data.form.submit) {
        button.errorMessage = error.response.data.form.submit
      }
      if(error.response.data.form.inputs) {
        error.response.data.form.inputs.forEach(input=>{
          this.refs[input.key]?.setError({message:input.message})
        })
      }
    },

    submit() {
      this.button_submit_loading = true
      if(!this.valid()) {
        this.button_submit_loading = false
        return 
      }
      this.$refs.button_submit.errorMessage = null
      this.$emit("submit", this)
    },
    valid() {
      if(this.prev_steps_valid(this.current_next_button)) {
        return true
      }
      return false
    },
    async increment(step) {
      console.log(this.refs)
      this.button_next_loading = true
      if(!this.valid() || this.step>=this.n_steps-1) {
        this.button_next_loading = false
        return
      }

      const step_component = this.refs?.[`step_${this.step}`]
      if(step_component.incrementFunction) {
        const {error} = await step_component.incrementFunction()
        if(error) {
          this.errorHandler(error, this.current_next_button)
          this.button_next_loading = false
          return
        }
      }
      this.refs[`step_${step}`].next(this)
      this.$emit('newStep', this.step+1)
      this.$refs.button_next.errorMessage = null
      this.button_next_loading = false
    },
    decrement() {
      this.button_previous_loading = true
      if(this.$refs.button_next) {
        this.$refs.button_next.errorMessage = null
      }
      if(this.step>0) {
        this.$emit('newStep', this.step-1)
      }
      this.button_previous_loading = false
    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.step {
  position: absolute;
  display: grid;
  top: calc(50% - 38px);
  transform: translateY(-50%);
  height: calc(100% - 116px);
}
.multi-step-form {
  display: flex;
  flex-direction: column;
  padding: 0 60px 20px 60px;
}
.multi-step-form-step-indication {
  position: relative;
  height: 90px;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  .progress-indicator {
    position: absolute;
    left: 0;
    z-index: 0;
    height: 2px;
    background-color: $kit_green;
  }
  .step-indicator {
    position: relative;
    font-size: 13px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 1;
    gap: 4px;
    &.active {
      .step-number {
        background-color: $kit_green;
        color: white;
      }
    }
    .step-number {
      display: flex;
      flex-direction: column;
      justify-content: center;
      text-align: center;
      aspect-ratio: 1;
      width: 22px;
      border-radius: 3px;
      background-color: white;

    }
    .step-text {
      position: absolute;
      transform: translate(0, 125%);
      white-space: nowrap;
    }
  }
}
.multi-step-form-body {
  min-width: 377px;
  position: relative;
  border: 1px solid black;
  border-radius: 4px;
  padding: 26px 20px;
  overflow: hidden;
  width: 100%;
  height: calc(100% - 90px);
  display: flex;
  flex-direction: column;
  .step {
    margin: auto 0;
  }
}

.step-navigation {
  position: relative;
  width: 100%;
  bottom: -100%;
  transform: translateY(-100%);
  display: flex;
  flex-direction: row;
  justify-content: center;
  gap: 15px;
}
.multi-step-form-button {
    width: 160px;
    height: 50px;

  .button-inner {
    background-color: $kit_green;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    width: 100%;
    height: 100%;
  }

  padding: 2px;
  border-radius: 2px;
  border: 1px solid rgba(0,0,0,0);
  &:hover {
    border: 1px solid $kit_green;
  }
  &:active {
    padding: 1px;
  }
}
</style>
