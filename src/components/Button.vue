<template>

  <div class="button" :class="{error: error}">
    <button class="submit-button" :class="{loading: loading, disabled: disabled}" :disabled="error || disabled || loading">
      <template v-if="loading">
        <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
      </template>
      <template v-else>{{text}}</template>
    </button>
    <div class="circle" :class="{animate: success}">
      <IconButton class="checkmark" icon="checkmark" text="" :width="24" :height="24" :noHover="true"/>
      <span>Success!</span>

    </div>
  </div>
</template>

<script>
import IconButton from '@/components/IconButton.vue'
export default {
  name: 'Button',
  components: {
    IconButton,
  },
  props: {
    loading: Boolean,
    disabled: Boolean,
    text: String,
  },
  data() {
    return {
      error: false,
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
        2500)
      }
    }
  },
  computed: {

  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.circle {
  > * {
    margin: 0 2px;
  }
  border: none;
  display: flex;
  justify-content: center;
  align-items: center;
  // width: 38px;
  border-radius:50%;
  opacity: 0;
  &:not(.animate) {
    transition: opacity 400ms;
  }
  &.animate {
    opacity: 1;
  }
}
.button {
  display: flex;
  flex-direction: row;
  &.error {
    animation: error 0.4s linear;
  }
}
.submit-button {
  margin-right: 12px;
}
.lds-ring {
  position: relative;
  transform: translate(-14px, -14px);
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
