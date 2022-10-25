<template>
  <div class="icon-button" @mousedown="border ? borderActive=true : null" @click="buttonClicked()" :style="colors" :class="{hover: !noHover, border: borderActive}">
    <div class="icon" :style="style">
      <component class="svg" :is="src" :width="width" :height="height" />
    </div>
    <div class="text" v-if="text">
      {{text}}
    </div>
  </div>
</template>

<script>
import {defineAsyncComponent} from "vue";
export default {
  name: 'IconButton',
  components: {
  },
  props: {
    icon: String,
    width: Number,
    height: Number,
    text: {
      default: null,
      type: String,
    },
    noHover: {
      default: false,
      type: Boolean,
    },
    color: {
      default: "#2c3e50",
      type: String,
    },
    hoverColor: {
      default: "#fff",
      type: String,
    },
    hoverBackgroundColor: {
      default: "#2c3e50",
      type: String,
    },
    border: {
      default: false,
      type: Boolean,
    }
  },
  mounted() {
    window.addEventListener('mouseup', () => this.borderActive=false, false);
  },
  data() {
    return {
      borderActive: false,
      availableIcons: ['cogwheel', 'trash', 'threeHorizontalBars', 'home', 'logout', 'checkmark', 'closeX'],
    }
  },
  computed: {
    style() {
      return {
        'height': `${this.height}px`,
        'width': `${this.width}px`,
        'margin-right': this.text ? '8px' : 0,
      }
    },
    colors() {
      return {
        '--color': this.color,
        '--hover-color': this.hoverColor,
        '--hover-background-color': this.hoverBackgroundColor,
      }
    },
    src() {
      if(this.availableIcons.includes(this.icon)) {
        return defineAsyncComponent(() => import(`@/assets/${this.icon}.vue`))
      }
      return null
    }
  },
  methods: {
    buttonClicked() {
      this.$emit('buttonClicked')
    }
  }

}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.icon-button {
  display: flex;
  flex-direction: row;
  align-items: center;
  &:hover {
    cursor: default;
  }
}
.icon-button.border {
  outline: 2px solid black;
  border-radius: 4px;
}
.icon-button.hover {
  color: var(--color);
  > .icon {
    padding: 0;
    > svg {
      top: 0;
      margin: 0;
      stroke: var(--color);
      fill: var(--color);
    }
  }
  &:hover {
    cursor: pointer;
    color: var(--hover-color);
    background-color: var(--hover-background-color);
    > .icon {
      > svg {
        stroke: var(--hover-color);
        fill: var(--hover-color);
      }
    }
  }

}
</style>
