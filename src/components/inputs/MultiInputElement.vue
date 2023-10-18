<template>
  <div class="multi-input-element" :class="{is_focused}">
    <InputElement :label="label" tooltip="Press Enter to add input" type="email" @enter="addValue($event)" ref="input" @focus="focus()" @blur="blur()"/>
    <div class="input-preview-window" v-if="value_list.length>0">
      <div class="input" v-for="(value,index) in value_list" :key="value">
        <span class="input-value">{{value}}</span>
        <div class="icon-wrapper" @click="removeValue(index)">
          <ion-icon class="icon" name="close-outline"></ion-icon>
        </div>
      </div>
    </div>
    <div class="input-preview-window empty" v-else>
      <div>
        "{{label}}" values
      </div>
    </div>
  </div>
  <input type="hidden" :value="JSON.stringify(value_list)">
</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'
export default {
  name: 'MultiInputElement',
  components: {
    InputElement,
  },
  props: {
    label: String,
    name: String,
  },
  data() {
    return {
      value_list: [],
      is_focused: false,
    }
  },
  computed: {
  },
  methods: {
    blur() {
      this.is_focused=false
    },
    focus() {
      this.$refs.input.deFocusedOnce = true
      this.is_focused=true
    },
    addValue(value) {
      if(this.$refs.input.error===null) {
        this.value_list.push(value)
        this.$refs.input.clear()
      }
    },
    removeValue(index) {
      this.value_list.splice(index,1)
    }
  },
}
</script>


<style scoped lang="scss">
.multi-input-element {

  &.is_focused {
    .input-preview-window {
      border: 1px solid #00876c;
    }
  }

}
.input-preview-window {
  border-radius: 2px;
  background-color: white;
  height: calc(28 * 5px);
  overflow-y: auto;
  border: 1px solid rgb(133,133,133);
  &.empty {
    display: flex;
    justify-content: center;
    align-items: center;
  
  }
}
.input {
  display: flex;
  flex-direction: row;
  align-items: center;
  padding: 0 0 0 16px;
  gap: 4px;
  &:hover {
    background-color: rgb(222,222,222);
  }
  .icon-wrapper {
    padding: 2px 6px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-left: auto;
    cursor: pointer;
    &:hover {
      background-color: #dc3545;
      color: #FAF9F6;
    }
    .icon {
      font-size: 22px;
    }
  }
}

</style>
