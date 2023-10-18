<template>
  <div class="checkbox">
    
    <button class="checkbox-button" :disabled="readonly" :class="{readonly}"  @click.prevent="check()" :style="{'--fill':fill,'--stroke':stroke}">

      <svg width="100%" height="100%" rx="15" class="checkmark-container" :class="{checked}">
        <rect rx="20%" x="5%" y="5%" width="90%" height="90%" stroke-width="10%"></rect>
      </svg>

      <svg width="100%" height="100%" rx="15" class="checkmark" :class="{checked}">
        <rect stroke-dasharray="98.8%"  x="5%" y="5%" width="32%" height="69.3%" stroke-width="10%"></rect>
      </svg>

    </button>
    <div class="checkbox-label-wrapper" v-if="label" :class="{readonly}">
      <span class="checkbox-label" @click="check()">{{label}}</span>
    </div>
    <input type="hidden" :name="name" :value="Number(checked)">


  </div>
</template>

<script>
export default {
  name: 'Checkbox',

  emits: ["change"],

  props: {
    fill: {
      default: "#00876c",
      type: String,
    },
    stroke: {
      default: "#fff",
      type: String,
    },
    name: String,
    label: String,
    readonly: Boolean,
    presetValue: [Boolean,String,Number],
  },
  


  data() {
    return {
      checked: true,
    }
  },

  mounted() {
    this.checked = this.toBoolean(this.presetValue)
  },

  watch: {
    presetValue(to) {
      this.checked = this.toBoolean(to)
    },
  },



  methods: {

    clear() {
      this.checked = false
    },

    toBoolean(value) {
      switch(value) {
        case '0': return false;
        case '1': return true;
        default: return Boolean(value);
      }

    },

    check() {
      if(this.readonly) {
        return
      }
      this.checked = !this.checked
      this.$emit('change',this.checked)
    }
  },


}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.checkbox {
  position: relative;
  display: flex;
  flex-direction: row;
  gap: 8px;
  // padding: 8px;
  align-items: center;
}
.checkbox-label-wrapper {
  display: flex;
  .checkbox-label {
    cursor: pointer;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2; /* number of lines to show */
    overflow: hidden;
    text-align: left;
  }
  &.readonly {
    color: gray;
    cursor: not-allowed;
  }
}

.checkbox-button {
  &.readonly {
    color: gray;
  }
  position: relative;
  height: 1.25em;
  aspect-ratio: 1;
  cursor: pointer;
  .checkmark-container {
    stroke: rgba(0,0,0,.54);
    fill: transparent;
    transition: fill .4s cubic-bezier(.25,.8,.25,1), stroke .4s cubic-bezier(.25,.8,.25,1);
    &.checked {
      fill: var(--fill);
      stroke: var(--fill);
    }
  }

  .checkmark {
    fill: transparent;
    position: absolute;
    left: 0;
    transform-box: fill-box;
    transform-origin: center;
    transform: scale(0%,0%) rotate(135deg) translate(22%, 21%);
    transition: transform .4s cubic-bezier(.25,.8,.25,1), opacity .4s cubic-bezier(.25,.8,.25,1);
    opacity: 0;
    stroke: var(--fill);

    &.checked {
      stroke: var(--stroke);
      transform: scale(-100%,100%) rotate(135deg) translate(22%, 21%);
      opacity: 1;
    }
  }
}
</style>
