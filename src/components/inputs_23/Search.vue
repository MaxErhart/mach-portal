<template>
  <div class="search" :style="{'--color':color,'--bg':bg}">
    <label :for="name" :class="{focus,active}">{{label}}</label>
    <input :disabled="disabled" ref="input" autocomplete="off" :id="name" :name="name" type="text" @focus="handleFocus()" @blur="handleBlur()" v-model="value" :class="{focus,active}" @keyup="change($event)" @keyup.enter="enter()">
    <div class="active-label-placeholder" :class="{active}">{{label}}</div>

    <div class="suggestions" :class="{show_suggestions:show_suggestions && value && (suggestions.length>0 || suggestions_loading)}">
      <div class="awaiting-suggestions" v-if="suggestions_loading">
        <DataPlaceholder :side_length="32"/>
      </div>
      <div class="suggestion" v-for="suggestion in suggestions.slice(0, 100)" :key="suggestion" @mousedown="select(suggestion)" >
        <template v-if="search_dot_prefix">...</template>{{suggestionCast(suggestion)}}
      </div>
    </div>

  </div>
</template>

<script>
import DataPlaceholder from '@/components/DataPlaceholder.vue'
export default {
  name: 'Search',
  components: {
    DataPlaceholder,
  },
  props: {
    name: String,
    label: String,
    bg: {
      type: String,
      default: "#fff",
    },
    color: {
      type: String,
      default: "#00876c",
    },
    suggestions: Array,
    suggestions_loading: Boolean,
    search_dot_prefix: Boolean,
    suggestionCast: {
      default: (option)=>{return option.word},
      type:Function,
    },
    complete_word: {
      default: false,
      type:Boolean,
    },
    disabled: Boolean,
  },
  data() {
    return {
      focus:false,
      value: null,
      show_suggestions:false,
    }
  },
  computed: {
    active() {
      return this.focus || this.value
    },
  },
  methods: {
    clear() {
      this.value = ""
    },
    handleFocus() {
      this.focus = true
      this.show_suggestions = true
    },
    handleBlur() {
      this.focus=false
      this.show_suggestions = false
    },
    select(suggestion) {
      if(this.complete_word) {
        const fragments = this.value.split(" ")
        for(var i=fragments.length-1;i>=0;i--) {
          if(fragments[i]==="") {
            continue;
          }
          break;
        }
        fragments[i] = this.suggestionCast(suggestion)
        this.value = fragments.filter(word=>word!=="").join(" ")
        this.submit(this.value)
      } else {
        this.value = this.suggestionCast(suggestion)
        this.submit(suggestion)
      }

    },
    enter() {
      this.submit(this.value)
    },
    submit(value) {
      this.show_suggestions = false
      this.$refs.input.blur()
      this.$emit('enter', value)
    },
    change(event) {
      if(["ArrowUp","ArrowDown","ArrowRight","ArrowLeft","Home","PageUp","PageDown","End","Insert","Pause","ScrollLock","F1","F2","F3","F4","F5","F6","F7","F8","F9","F10","F11","F12","Enter","Control","Alt","Meta","Shift","CapsLock","Tab","AltGraph","ContextMenu"].includes(event.key)) {
        return
      }
      if(event.key==="Escape") {
        this.show_suggestions = false
      } else {
        this.show_suggestions = true
      }
      this.$emit('typing', this.value)

    }
  }

}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.awaiting-suggestions {
  width: 100%;
  display: flex;
  justify-content: flex-start;
  align-items: flex-start;
}
.search {
  font-size: 1.125rem;
  position: relative;
  background-color: var(--bg);
  padding: 0.5em 0;
  font-family: 'Quicksand', sans-serif;
  font-weight: 600;
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
  &.focus {
    color: var(--color);
  }
  &.active {
    font-size: 0.875rem;
    top:0.5rem;
    left: 1rem;
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
.suggestions {
  overflow: auto;
  width: 100%;
  top: 100%;
  border-radius: 4px;
  border: 1px solid black;
  background-color: var(--bg);
  position: absolute;
  max-height: 324px;
  // overflow: hidden;
  padding: 0.5rem 1.5rem;
  display: none;
  z-index: 10001;
  &.show_suggestions {
    display: block;
  }
  .suggestion {
    padding: 0.15rem 0;
    cursor: pointer;
    &:hover {
      background-color: rgba(0,0,0,0.1);
      // color: white;
    }
  }
}
</style>
