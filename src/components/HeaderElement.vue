<template>
  <div class="header-element" :style="!editable ? 'border: none': 'border: 1px solid black'">
    <div class="item" v-html="generateHtml()"></div>
  
    <div class="edit-element-window" v-if="editable">
      <section>
        <label for="content">Titel:</label>
        <input name="content" type="text" v-model="content">
      </section>
      <section>
        <label for="tag">Size:</label>
        <select name="tag" v-model="tag">
          <option value="h1">h1</option>
          <option value="h2">h2</option>
          <option value="h3">h3</option>
          <option value="h4">h4</option>
        </select> 
      </section>

   
    </div>
  </div>

</template>

<script>
export default {
  name: 'HeaderElement',
  props: {
    editable: Boolean,
  },
  data() {
    return {
      type: "header",
      content: "Header",
      tag: "h1"
    }
  },
  methods: {
    generateHtml() {
      return `<${this.tag} class="item-content">${this.content}</${this.tag}>`;
    }
  }

}
</script>


<style scoped lang="scss">
  input {
    user-select: auto !important;
    display: block;
    width: 100%;
    height: 40px;
    font-size: 16px;
    border: 1px solid #ccc;
    padding: 15px;
  }
  label {
    display: block;
    float: left;
    margin: 2px 0;
  }
  select {
    display: block;
    width: 100%;
    height: 40px;
    font-size: 16px;
    border: 1px solid #ccc;
  }
  .header-element {
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