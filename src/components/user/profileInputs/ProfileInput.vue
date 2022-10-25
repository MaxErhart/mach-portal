<template>
  <div class="profile-select">
    <input :name="name" :autocomplete="autocomplete" :type="type" v-model="value" :readonly="readonly" :class="{readonly: readonly}" ref="input"/>
  </div>
</template>

<script>
export default {
  name: 'ProfileSelect',
  props: {
    name: String,
    autocomplete: {
      default: null,
      type: String,
    },
    readonly: {
      default: false,
      type: Boolean,
    },
    type: {
      default: 'text',
      type: String,
    },
    presetValue: String,

  },
  data() {
    return {
      value: null,
    }
  },
  mounted() {
    this.value = this.presetValue
  },
  watch: {
    presetValue(newVal) {
      this.value = newVal
    },
  },
  methods: {
    focus() {
      this.$refs.input.focus()
    }
  }

}
</script>


<style scoped lang="scss">
input {
  padding: 4px 8px;
  width: 100%;
  border-radius: 0;
  border: 1px solid #2c3e50;
  font-size: 1em;
  font-family: "Roboto","Arial",sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
  &:focus {
    outline: 1px solid #00876c;
    border: 1px solid #00876c;
  }
  &.readonly {
    color: gray;
    &:hover {
      cursor: not-allowed;
    }
  }
}
</style>
