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
      <input :type="inputType" :placeholder="placeholder">
    </div>

    <div class="edit-element-window" v-if="editable">
      <section>
        <label for="labelName">Label Name:</label>
        <input type="text" name="labelName" v-model="labelName">
      </section>

      <section>
        <label for="required">Required:</label>
        <input id="checkbox-input" type="checkbox" name="required" v-model="required">
      </section>

      <section>
        <label for="help">Help Text:</label>
        <input type="text" name="help" v-model="tooltip">
      </section>

      <section>
        <label for="placeholder">Placeholder Text:</label>
        <input type="text" name="placeholder" v-model="placeholder">
      </section>    

      <section>
        <label for="inputType">Placeholder Text:</label>
        <select name="inputType" v-model="inputType">
          <option value="text">text</option>
          <option value="email">email</option>
          <option value="number">number</option>
          <option value="date">date</option>
        </select>
      </section>

    </div>

  </div>
</template>

<script>
export default {
  name: 'InputeElement',
  props: {
    editable: Boolean,
  },
  data() {
    return {
      type: 'input',
      labelName: 'My Label',
      inputType: 'text',
      tag: 'input',
      tooltip: "cfadadsasd",
      placeholder: "",
      required: false,
    }
  },
  methods: {
    generateHtml() {
      return `<div><label>${this.content}<span class="span-content" v-if="required">*</span>:</label><${this.tag} class="item-content"/></div>`;
    }
  }
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