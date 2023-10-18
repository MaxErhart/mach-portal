<template>
  <div class="button-wrapper" >
    
    <div class="button-success-wrapper">

      <button class="button-container" :style="button_container_style" :disabled="disabled || loading" :class="{disabled:disabled||loading}" @click="handleButtonClick()">
        <div class="button" :class="{disabled:disabled||loading,animate_error}" >
          <div v-if="loading" class="lds-ring"><div></div><div></div><div></div><div></div></div>
          <div v-else>{{label}}</div>
        </div>
      </button>
      <div class="success" v-if="success">
        <ion-icon class="check-icon" name="checkmark-outline"></ion-icon>
        <span>
          Success!
        </span>
      </div>

    </div>

    <div class="error" :style="error_style" :class="{active}">
      <div class="error-message" ref="error_message">
        {{error_message}}
      </div>
    </div>

  </div>



</template>

<script>
export default {
  name: 'Button',
  props: {
    width: {
      default: "160px",
      type: String,
    },
    height: {
      default: "50px",
      type: String,
    },
    loading: {
      default: false,
      type: Boolean,
    },
    label: String,
    disabled: Boolean,
  },
  components: {
  },
  data() {
    return {
      error_message: null,
      active: false,
      success: false,
      error_style_vars: {height: 0},
      animate_error: false,
    }
  },
  watch: {
    animate_error(newVal) {
      if(newVal) {
        setTimeout(()=>{
          this.animate_error=false
        },
        500)
      }
    },
    async error_message(to) {
      if(to) {
        var error_message_rect = {height: 0}
        var count = 1000
        while (error_message_rect.height<=0 && count>0) {
          await this.$nextTick()
          error_message_rect = this.$refs.error_message.getBoundingClientRect()
          count--
        }
        this.error_style_vars.height = error_message_rect.height
        this.active = true

      }
    },
    success(newVal) {
      if(newVal) {
        setTimeout(()=>{
          this.success=false
        },
        3500)
      }
    }
  },
  computed: {
    button_container_style() {
      return {
        width:this.width,
        height:this.height,
      }
    },
    error_style() {
      return {
        '--height': this.error_style_vars.height + 'px',
      }
    }
  },
  methods: {
    handleButtonClick() {
      if(this.loading || this.disabled) {
        return
      }
      this.$emit('button')
      // this.error_message = "error"
      // const error_message_rect = this.$refs.error_message.getBoundingClientRect()
      // console.log(error_message_rect)
    },
  }
}
</script>

<style lang="scss" scoped>
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.button-wrapper {
  display: inline-block;
}
.button-success-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
}
.success {
  display: flex;
  align-items: center;
}
.check-icon {
  font-size: 2rem;
  color: #2C3E50;
}
.button-container {
  position: relative;
  border: 1px solid black;
  padding: 2px;
  transform: translateX(-2px);
  &:not(.disabled) {
    cursor: pointer;
  }

  border-radius: 2px;
  border: 1px solid rgba(0,0,0,0);
  &:hover {
    &:not(.disabled) {
      border: 1px solid $kit_green;
    }
  }
  &:focus {
    border: 1px solid $kit_green;
  }
  &:active {
    &:not(.disabled) {
      padding: 1px;
    }
  }

}

.button {
  width: 100%;
  height: 100%;
  background-color: $kit_green;
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  &.disabled {
    cursor: not-allowed;
    filter: grayscale(80%);
  }
  &.animate_error {
    animation: error-shake 500ms linear infinite;
  }
}

@keyframes error-shake {
  0% {
    transform: translateX(0px);
  }
  14% {
    transform: translateX(10px);
  }
  29% {
    transform: translateX(0px);
  }
  43% {
    transform: translateX(-6.1px);
  }
  57% {
    transform: translateX(0px);
  }
  71% {
    transform: translateX(3.7px);
  }
  86% {
    transform: translateX(0px);
  }
  100% {
    transform: translateX(-2.2px);
  }  
}

.error {
  position: relative;
  height: 0px;
  text-align: start;
  transform: translateY(100%);
  opacity: 0;
  transition: opacity .3s ease, height .3s ease;
  color: #ff1744;
  font-size: 12px;
  &.active {
    opacity: 1;
    height: var(--height);
    top: calc(-1 * var(--height))
  }
}



.lds-ring {
  left: 50%;
  top: 50%;
  position: absolute;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  transform: translate(0, -14px);
}
.lds-ring div {
  box-sizing: border-box;
  display: block;
  position: absolute;
  width: 28px;
  height: 28px;
  border: 3px solid #fff;
  border-radius: 50%;
  animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  border-color: #fff transparent transparent transparent;
}
.lds-ring div:nth-child(1) {
  animation-delay: -0.45s;
}
.lds-ring div:nth-child(2) {
  animation-delay: -0.3s;
}
.lds-ring div:nth-child(3) {
  animation-delay: -0.15s;
}


@keyframes lds-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>