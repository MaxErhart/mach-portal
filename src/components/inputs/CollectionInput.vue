<template>
  <div class="collection-input">
    <InputElement @blur="blur()" type="text" :required="false" tooltip="Press enter to confirme" :label="label" @enter="addValue($event)" ref="input"/>
    <div class="values" :class="{error: error}">
      <div class="value" v-for="(value, index) in values" :key="value">
        <span>{{value.name}}</span>
        <div class="button" @click="removeValue(index)">
          <svg width="16" height="16" viewBox="0 0 16 16"><path d="M5.83 5.146a.5.5 0 0 0 0 .708L7.975 8l-2.147 2.146a.5.5 0 0 0 .707.708l2.147-2.147 2.146 2.147a.5.5 0 0 0 .707-.708L9.39 8l2.146-2.146a.5.5 0 0 0-.707-.708L8.683 7.293 6.536 5.146a.5.5 0 0 0-.707 0z" data-v-4d7acd4a=""></path><path d="M13.683 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7.08a2 2 0 0 1-1.519-.698L.241 8.65a1 1 0 0 1 0-1.302L5.084 1.7A2 2 0 0 1 6.603 1h7.08zm-7.08 1a1 1 0 0 0-.76.35L1 8l4.844 5.65a1 1 0 0 0 .759.35h7.08a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1h-7.08z" data-v-4d7acd4a=""></path></svg>
        </div>
      </div>
    </div>
    <input type="hidden" :value="JSON.stringify(values)" :name="name">
  </div>
</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'
import * as validationSettings from '@/validationSettings.json'
export default {
  name: 'CollectionInput',
  components: {
    InputElement,
  },
  props: {
    label: String,
    required: Boolean,
    name: String,
    presetValue: Array,
  },
  computed: {
    error() {
      if(this.deFocusedOnce & this.required && (!this.values || this.values.length<1)) {
        return this.validationSettings.default.error_types.required_error
      }
      return null
    }
  },
  data() {
    return {
      values: [],
      deFocusedOnce: false,
      validationSettings,
      counter: 0,
    }
  },
  mounted() {
    if(this.presetValue) {
      this.setValues(this.presetValue)
    }
  },
  watch: {
    presetValue(to) {
      if(to) {
        this.setValues(to)
      }
    },
  },
  methods: {
    clear() {
      this.values = []
      this.deFocusedOnce = false
      this.counter = 0
    },
    setValues(values) {
      this.values=values
      values.forEach(value=>{
        if(value.id>=this.counter) {
          this.counter = value.id+1
        }
      })
    },
    blur() {
      this.deFocusedOnce = true
    },
    addValue(value) {
      this.values.push({id: this.counter, name: value})
      this.counter +=1
      this.$refs.input.clear()
      this.$emit('valueChange', this.values)
    },
    removeValue(index) {
      this.values.splice(index, 1)
      this.$emit('valueChange', this.values)
    }
  }

}
</script>


<style scoped lang="scss">
.values {
  width: 100%;
  border: 1px solid black;
  // border-top: none;
  border-radius: 2px;
  height: 6rem;
  overflow-y: auto;
  margin: -11px 0 4px 0;
  &.error {
    border: 1px solid #ff1744;
  }
  > .value {
    background-color: #f0f0f0;
    height: 30px;
    width: 100%;
    display: flex;
    flex-direction: row;
    align-items: center;
    color: #2c3e50;
    padding: 0 12px;
    .button {
      border: none;
      background: none;
      margin-left: auto;
      &:hover {
        cursor: pointer;
        stroke: red;
        fill: red;
      }
    }
    &:hover {
      background-color: #2c3e50;
      color: white;
      fill: white;
      stroke: white;
    }
  }
}
</style>
