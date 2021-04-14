<template>
  <div class="section-element">
    <div class="item">
      <component :is="tag" class="item-content">
        {{content}}
      </component>      
    </div>


    <div class="edit-element-window" v-if="editable">
      <section>
        <label for="content">Text:</label>
        <textarea name="content" v-model="content" @change="updateElData()"></textarea>
      </section>
    </div>

  </div>
</template>

<script>
export default {
  name: 'SecitonElement',
  props: {
    editable: Boolean,
    id: String,
  },
  data() {
    return {
      type: 'section',
      content: 'Seciton',
      tag: 'div'
    }
  },
  mounted() {
    this.$store.commit('addSelection', {id: this.id, type: this.type, data: {tag: this.tag, content: this.content}});
  },
  beforeUnmount() {
    this.$store.commit('deleteSelection', {id: this.id});
  },  
  methods: {
    generateHtml() {
      return `<${this.tag} class="item-content">${this.content}</${this.tag}>`;
    },
    updateElData() {
      this.$store.commit('updateSelectionsData', {id: this.id, type: this.type, data: {tag: this.tag, content: this.content}});
      console.log(this.$store.getters.getSelectionsData)
    }    
  }
}
</script>


<style scoped lang="scss">
  textarea {
    user-select: auto !important;
    display: block;
    width: 100%;
    height: 180px;
    font-size: 16px;
    border: 1px solid #ccc;
    padding: 15px;
  }
  label {
    display: block;
    float: left;
    margin: 2px 0;
  }
  .item-content {
    width: 100%;
    display: block;
    min-height: 1em;
    text-align: start;
    white-space: pre-line;
    word-break: break-all;
    // pointer-events: none;
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