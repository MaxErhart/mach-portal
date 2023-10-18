<template>

  <div class="button" :class="{error: error, 'error-message': errorMessage, stretch,disabled: error || disabled || loading}" :style="{'--bg-color': bgColor, '--color': color}">
    <button class="submit-button" :class="{loading, stretch}" :disabled="error || disabled || loading">
      <template v-if="loading">
        <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
      </template>
      <span class="button-text" :class="{loading}">{{text}}
        <div class="circle" :class="{animate: success, stretch}">
          <ion-icon class="check-icon" name="checkmark-outline"></ion-icon>
          <span>Success!</span>
        </div>
      </span>
    </button>
    <Transition name="slide">
      <div class="input-error" v-if="errorMessage">
        {{errorMessage}}
      </div>
    </Transition>
  </div>
</template>

<script>
export default {
  name: 'Button',
  props: {
    bgColor: {
      default: '#00876c',
      type: String,
    },
    color: {
      default: '#ffffff',
      type: String,
    },
    stretch: {
      default: false,
      type: Boolean,
    },
    loading: Boolean,
    disabled: Boolean,
    text: String,
  },
  emits: ['buttonClick'],
  data() {
    return {
      error: false,
      errorMessage: null,
      success: false,
    }
  },
  watch: {
    error(newVal) {
      if(newVal) {
        setTimeout(()=>{
          this.error=false
        },
        500)
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

  },
  methods: {
  }
}
</script>


<style scoped lang="scss">
.button-text {
  user-select: none;
}
.input-error {
  width: 100%;
  position: absolute;
  font-size: 12px;
  color: #ff1744;
  text-align: start;
  height: 12px;
  bottom: 0;
  z-index: 0;
  transform: translateY(100%);
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
.circle {
  position: absolute;
  > * {
    margin: 0 2px;
  }
  .check-icon {
    font-size: 2rem;
    color: #2C3E50;
  }
  &.stretch {
    color: var(--color);
    .check-icon {
      color: var(--color);
    }
  }
  color: #2C3E50;
  right: 0;
  top: 50%;
  transform: translate(calc(100% + 16px), -50%);
  border: none;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  border-radius:50%;
  opacity: 0;
  display: none;
  &:not(.animate) {
    transition: opacity 400ms;
  }
  &.animate {
    display: flex;
    opacity: 1;
  }
}
.button {
  position: relative;
  &.disabled {
    cursor: not-allowed !important;
    filter: grayscale(75%);
  }
  &.stretch {
    width: 100%;
  }
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: flex-start;
  min-width: 96px;
  &.error {
    animation: error 0.4s linear;
  }
  // &.error-message {
  //   margin: 2px 0 0 0;
  // }
}
.submit-button {
  position: relative;
  z-index: 1;
  span {
    position: relative;
    &.loading {
      opacity: 0;
    }
  }
  padding: 0 8px;
  background-color: var(--bg-color);
  color: var(--color);
  min-height: 38px;
  height: 100%;
  position: relative;
  font-weight: 500;
  cursor: pointer;
  &.stretch {
    width: 100%;
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
@keyframes error {
  0% {
    transform: translateX(0px)
  }
  17% {
    transform: translateX(15px)
  }
  50% {
    transform: translateX(-9px)

  }
  67% {
    transform: translateX(6px)

  }
  100% {
    transform: translateX(0px)
  }
}
</style>
