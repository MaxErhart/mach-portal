<template>
  <div ref="tag_select" class="tags-select" :style="{'--color':color,'--bg':bg,'--bg-invert':bg_invert}" :class="{focus,active,disabled,error:show_error}" tabindex="0" @focus="handleFocus()" @blur="handleBlur($event)">
    <label :class="{focus,active,disabled,error:show_error}">{{label}}</label>
    <div class="active-label-placeholder" :class="{active}">{{label}}</div>

    <!-- <div :class="{focus,active,disabled,error:show_error}" type="text" :id="name" >a</div> -->
    <section id="selected-tags" :class="{focus,active,disabled,error:show_error}">
      <div class="selected-tag" v-for="tag in selected_tags" :key="tag">
        <span>{{tag}}</span>
        <button class="icon" @click="deselectTag(tag)">
          <ion-icon name="close-outline"></ion-icon>
        </button>
      </div>
      <button class="clear" v-if="selected_tags.length>0" @click="clear()">
        <ion-icon name="trash-outline"></ion-icon>
      </button>
    </section>
    <section id="unselected-tags" :class="{focus,active,disabled,error:show_error}" v-if="focus">
      <button class="unselected-tag" v-for="tag in filterUnselected(tags)" :key="tag" @click="selectTag(tag)">
        <span>{{tag}}</span>
      </button>
    </section>
    <div class="error-message-background" :class="{focus,active,disabled}" v-if="show_error">{{error?.message}}</div>
    <div class="error-message" v-if="show_error">
      {{error?.message}}
    </div>
    <input type="hidden" :name="name" :value="JSON.stringify(selected_tags)">
  </div>
</template>

<script>
export default {
  name: 'TagsSelect',
  props: {
    name: String,
    label: String,
    disabled: Boolean,
    bg: {
      type: String,
      default: "#fff",
    },
    color: {
      type: String,
      default: "#00876c",
    },
    required: Boolean,
    external_error: Object,
    force_show_error:Boolean,
    tags: Array,
  },
  data() {
    return {
      focus: false,
      user_interaction_ended_once: false,
      selected_tags: [],
    }
  },
  computed: {
    value() {
      return this.selected_tags
    },
    active() {
      return this.focus || this.selected_tags.length>0
    },
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
      if(this.required && this.selected_tags.length<=0) {
        return {message: "Input Required"}
      }
      return null
    },
  },
  methods: {
    clear() {
      this.selected_tags = []
    },
    filterUnselected(tags) {
      return tags.filter(tag=>!this.selected_tags.includes(tag))
    },
    selectTag(tag) {
      this.focus = true
      this.$refs.tag_select.focus()
      this.selected_tags.push(tag)
    },
    deselectTag(tag) {
      this.focus = true
      this.$refs.tag_select.focus()
      this.selected_tags = this.selected_tags.filter(selected_tag=>selected_tag!==tag)
    },
    handleFocus() {
      if(this.disabled) {
        return
      }
      this.focus = true
    },
    handleBlur(event) {
      if(event.target.contains(event.relatedTarget)) {
        event.target.focus()
        return
      }
      console.log(event.relatedTarget)
      this.focus = false
      this.user_interaction_ended_once = true
    },
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.tags-select {
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
#selected-tags {
  position: relative;
  min-height: 54px;
  border: 1px solid black;
  // border-top-left-radius: 4px;
  // border-top-right-radius: 4px;
  border-radius: 4px;
  outline: none;
  &.focus {
    border-radius: 0;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
    border-color: var(--color);
    outline: 1px solid var(--color);
  }
  display: flex;
  flex-direction: row;
  align-items: center;
  padding: 0.5em 1.5em;
  flex-wrap: wrap;
  gap: 0.5em;
  &.error {
    outline-color: $error;
    border-color: $error;
  }
  .clear {
    position: absolute;
    z-index:11;
    right: 1.5em;
    transform: translateX(100%);
    padding: 4px;
    font-size: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    &:hover {
      color: $text-red;
    }
    border-radius: 100%;
    // border: 1px solid black;
    transition: background-color 200ms ease;
    &:active {
      background-color: rgba(0,0,0,0.2);
    }
  }
}
.selected-tag {
  padding: 4px 4px 4px 16px;
  height: fit-content;
  border: 1px solid black;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  background-color: var(--color);
  color: var(--bg);
  .icon {
    cursor: pointer;
    font-size: 24px;
    height: 24px;
    border-radius: 50%;
    &:hover {
      color: $text_red;
    }
    transition: background-color 200ms ease;
    &:active {
      background-color: rgba(0,0,0,0.2);
    }
  }
}
#unselected-tags {
  position: absolute;
  z-index: 11;
  max-height: 216px;
  padding: 0.5em 1.5em;
  width: 100%;
  border: 1px solid black;
  border-top: none;
  background-color: var(--bg);
  border-bottom-left-radius: 4px;
  border-bottom-right-radius: 4px;
  outline: none;
  &.active {
    border-color: var(--color);
    outline: 1px solid var(--color);
  }
  &.error {
    outline-color: $error;
    border-color: $error;
  }
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: flex-start;
  gap: 0.5em;
  flex-wrap: wrap;
  overflow: auto;
}
.unselected-tag {
  padding: 4px 16px;
  height: fit-content;
  border: 1px solid black;
  cursor: pointer;
  font-weight: 600;
  &:hover {
    background-color: var(--bg-invert);
  }
}
label {
  z-index: 2;
  font-size: 1.125rem;
  position: absolute;
  top: 50%;
  left: 1.5rem;
  transform: translateY(-50%);
  pointer-events: none;
  transition: top 100ms linear, left 100ms linear, font-size 100ms linear;
  &.disabled {
    filter: invert(20%);
  }
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
.active-label-placeholder {
  font-size: 0.875rem;
  position: absolute;
  z-index: 1;
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
  z-index: 30;
  font-size: 0.875rem;
  position: absolute;
  bottom: 0px;
  left: 0.60rem;
  background-color: var(--bg);
  padding: 0 0.40rem;
  color: transparent;
  &.disabled {
    background: linear-gradient(0deg, var(--bg) 46%, var(--bg-invert) 46%);
  }
}
.error-message {
  z-index: 40;
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
</style>
