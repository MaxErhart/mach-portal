<template>
  <div class="input-element">
    <div class="item">
      <label for="input-field">{{labelName}}
        <span class="required-span" v-if="required">*</span>
        <span class="tooltip-element" v-if="tooltip != ''">
          ?
          <div class="tooltip-text">{{tooltip}}</div>
        </span>
      </label>
      <select id="input-main">
        <option v-for="(option, index) in options"  :key="index" :value="index">{{options[index]}}</option>
      </select>
    </div>

    <div class="edit-element-window" v-if="editable">
      <section>
        <label for="labelName">Label Name:</label>
        <input type="text" name="labelName" v-model="labelName" @change="updateElData()">
      </section>

      <section>
        <label for="required">Required:</label>
        <input id="checkbox-input" type="checkbox" name="required" v-model="required" @change="updateElData()">
      </section>

      <section>
        <label for="help">Help Text:</label>
        <input type="text" name="help" v-model="tooltip" @change="updateElData()">
      </section>

      <section>
        <label for="num-options"></label>
        <input type="number" name="num-option" v-model="numOptions" min="1" @change="updateElData()">
      </section>   

      <template v-if="numOptions > 0">
        <section v-for="index in parseInt(numOptions)" :key="index">
          <label :for="index">Option {{index}}:</label>
          <input type="text" :name="index" v-model="options[index-1]" @change="updateElData()">
        </section>          
      </template> 

    </div>

  </div>
</template>

<script>
export default {
  name: 'SelectionElement',
  props: {
    editable: Boolean,
    id: String,
    preset: Boolean
  },
  data() {
    return {
      type: 'selection',
      labelName: 'My Label',
      tag: 'select',
      tooltip: "",
      required: false,
      numOptions: 1,
      options: []
    }
  },
  mounted() {
    if(this.preset) {
      const element = this.$store.getters.getSelectionsData.filter(el => el.elementId == this.id)[0]
      this.labelName = element.data.labelName
      this.numOptions = element.data.numOptions
      this.tooltip = element.data.tooltip
      this.options = element.data.options
      this.required = element.data.required
    } else {
      this.$store.commit('addSelection', {component: 'SelectionElement', data: {tag: this.tag, labelName: this.labelName, tooltip: this.tooltip, required: this.required, numOptions: this.numOptions, options: this.options}, elementId: this.id, props: {editable: false, id: this.id, preset: true}});
    }
  },  
  methods: {
    updateElData() {
      this.$store.commit('updateSelectionsData', {elementId: this.id, type: this.type, data: {tag: this.tag, labelName: this.labelName, tooltip: this.tooltip, required: this.required, numOptions: this.numOptions, options: this.options}})
    }    
  },
}
</script>


<style scoped lang="scss">
  select {
    display: block;
    width: 100%;
    height: 40px;
    font-size: 16px;
    border: 1px solid #ccc;
  }
  .required-span {
    color: red;
  }
  .tooltip-element {
      visibility: visible;
      color: #fff;
      background: #4664aa;
      width: 16px;
      height: 16px;
      border-radius: 8px;
      display: inline-block;
      text-align: center;
      line-height: 16px;
      margin: 0 5px;
      font-size: 12px;
      cursor: default;
      pointer-events: auto;
    > .tooltip-text {
      max-width: 320px;
      white-space: pre-line;     
      border: 1px solid white;
      border-radius: 4px;
      display: inline-block;
      font-size: 16px;
      transform: translateY(-2.5em);
      padding: 3px 8px;
      position: relative;
      background: #4664aa;
      visibility: hidden;
    }      
    &:hover {
      > .tooltip-text {
        visibility: visible;
      }
    }
  }
  input {
    user-select: auto !important;
    display: block;
    width: 100%;
    height: 40px;
    font-size: 16px;
    border: 1px solid #ccc;
    padding: 15px;
  }
  #checkbox-input {
    display: block;
    width: 20px;
    height: 20px;
  }
  label {
    display: flex;
    align-items: center;
    margin: 2px 0;
    text-align: start;
    width: 100%;
  }
  .item-content {
    width: 100%;
    display: block;
    min-height: 1em;
    text-align: start;
    white-space: pre-line;
    word-break: break-all;
  }
  .section-element {
    position: relative;
    width: 100%;

    float: left;
    padding: 3px;
  }
  .item {
    > * {
      padding: 0px 0;
      margin: 0px 0;    
    } 
  }
  .edit-element-window {
    padding: 0px 0;
    pointer-events: auto;
    > section {
      margin: 5px 0;
    }
  }
</style>