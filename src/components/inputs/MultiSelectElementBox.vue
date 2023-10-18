<template>
  <div class="multi-select-element-box">
    <SelectElement :emptyEmptySearchOption="emptyEmptySearchOption" @emptySearchOption="emptySearchOption($event)" :optional="false" ref="select" @selectedEntry="addItem($event)" :inputTypeable="inputTypeable" :label="label" :search="search" :required="required" :tooltip="tooltip" :placeholder="placeholder" :data="parse(filter(data))" :cast="cast"/>
    <div class="selected-list">
      <div class="selected-item" v-for="(item,index) in selected" :key="index">
        <span class="item-name">{{cast(item).name}}</span>
        <button @click.prevent="removeItem(index)">
          <svg width="16" height="16" viewBox="0 0 16 16"><path d="M5.83 5.146a.5.5 0 0 0 0 .708L7.975 8l-2.147 2.146a.5.5 0 0 0 .707.708l2.147-2.147 2.146 2.147a.5.5 0 0 0 .707-.708L9.39 8l2.146-2.146a.5.5 0 0 0-.707-.708L8.683 7.293 6.536 5.146a.5.5 0 0 0-.707 0z" data-v-4d7acd4a=""></path><path d="M13.683 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7.08a2 2 0 0 1-1.519-.698L.241 8.65a1 1 0 0 1 0-1.302L5.084 1.7A2 2 0 0 1 6.603 1h7.08zm-7.08 1a1 1 0 0 0-.76.35L1 8l4.844 5.65a1 1 0 0 0 .759.35h7.08a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1h-7.08z" data-v-4d7acd4a=""></path></svg>
        </button>
      </div>
    </div>
    <input type="hidden" :name="name" :value="JSON.stringify(selected)">
  </div>
</template>

<script>
import SelectElement from '@/components/inputs/SelectElement.vue'
export default {
  name: 'MultiSelectElementBox',
  components: {
    SelectElement,
  },
  emits: ['emptySearchOption','change'],
  props: {
    name: String,
    options: Array,
    label: String,
    search: Boolean,
    required: Boolean,
    tooltip: String,
    placeholder: String,
    data: Array,
    presetValue: Array,
    unique: Boolean,
    emptyEmptySearchOption: Object,
    inputTypeable: {
      default: false,
      type: Boolean,
    },
    cast: {
      default: (option)=>{return {id: option.id, name: option.name}},
      type: Function,
    },
  },
  data() {
    return {
      selected: [],
    }
  },
  mounted() {
    if(this.presetValue) {
      this.selected = this.parsePreset(this.presetValue)
    }
  },
  watch: {
    presetValue(to) {
      this.selected = this.parsePreset(to)
    }
  },
  computed: {

  },
  methods: {
    parsePreset(values) {
      const pre_selected = {}
      values.forEach(value=>{
        pre_selected[value.id] = value
      })
      return Object.values(pre_selected)
    },
    emptySearchOption(option) {
      this.$emit('emptySearchOption', option)
    },
    parse(data) {
      const object = {}
      data?.forEach(row=>{
        object[row.id] = row
      })
      return object
    },
    filter(data) {
      if(!data || data.length<=0) {
        return
      }
      return data?.filter(item=>!this.selected?.map(sel=>sel.id).includes(item.id))
    },
    addItem(item) {
      item = this.data.find(row=>row.id==item.id)
      console.log(this.selected, item)
      this.selected.push(item)
      this.$refs.select.clear()
      console.log(this.selected)
      this.$emit('change', this.selected)
    },
    removeItem(index) {
      this.selected.splice(index, 1)
      this.$emit('change', this.selected)
    }
  },
}
</script>


<style scoped lang="scss">
.selected-list {
  box-shadow: inset 0 0 4px 0px rgba(0,0,0,0.35);
  // border: 2px solid black;
  border-radius: 4px;
  height: 180px;
  overflow-y: auto;
  .selected-item {
    width: 100%;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    min-height: 28px;
    padding: 0 12px;
    button {
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
