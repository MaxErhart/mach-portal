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

      <input id="file-input" type="file">
      <label id="file-upload-text">
          <span><img :src="require(`@/assets/upload.svg`)"></span>
          <span>Upload File</span>
      </label>


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
        <label for="rel-path-input">Path:</label>
        <input id="rel-path-input" type="text" name="path" v-model="path" @change="updateElData()">
      </section>      

      <section>
        <label for="help">Help Text:</label>
        <input type="text" name="help" v-model="tooltip" @change="updateElData()">
      </section>

    </div>

  </div>
</template>

<script>
export default {
  name: 'FileUploadElement',
  props: {
    editable: Boolean,
    id: String,
    preset: Boolean
  },
  data() {
    return {
      type: 'file',
      labelName: 'My Label',
      tag: 'input',
      tooltip: "",
      required: false,
      path: "",
    }
  },
  mounted() {
    if(this.preset) {
      const element = this.$store.getters.getSelectionsData.filter(el => el.elementId == this.id)[0]
      this.labelName = element.data.labelName
      this.path = element.data.path
      this.tooltip = element.data.tooltip
      this.required = element.data.required
    } else {
      this.$store.commit('addSelection', {component: 'FileUploadElement', data: {path: this.path, tag: this.tag, labelName: this.labelName, tooltip: this.tooltip, required: this.required}, elementId: this.id, props: {editable: false, id: this.id, preset: true}});
    }
  },  
  methods: {
    generateHtml() {
      return `<div><label>${this.content}<span class="span-content" v-if="required">*</span>:</label><${this.tag} class="item-content"/></div>`;
    },
    updateElData() {
      this.$store.commit('updateSelectionsData', {elementId: this.id, data: {path: this.path, tag: this.tag, labelName: this.labelName, tooltip: this.tooltip, required: this.required}})
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
  #file-upload-text {
    border:1px solid #2c3e50;
    height: 40px;
    justify-content: center;
    box-shadow: 0 0 2px 1px rgba(0,0,0,0.2);

    background: linear-gradient(to right, rgba(0, 255, 0, 0.781) 50%, white 50%);
    background-size: 200% 100%;
    background-position: right bottom;rgba

    > span:first-child {
      margin-right: 5px;
    }
    &:hover {
      box-shadow: inset 0 0 2px 1px rgba(0,0,0,0.2);
    }
    &.active {
    background-position: left bottom;
    transition:all 600ms ease;
    }
  } 
  #file-input {
    position: absolute;
    z-index: -1;
    top: 6px;
    left: 0;
    font-size: 15px;
    color: rgb(153,153,153);
    display: none;

  }   
</style>