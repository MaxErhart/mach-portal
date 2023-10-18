<template>
  <div class="section-element">
    <div class="item">
      <component :is="tag" class="item-content" :style="{'text-decoration': underline ? 'underline' : 'none', 'font-weight': bold ? 'bold' : 'normal'}">
        {{content}}
      </component>      
    </div>


    <div class="edit-element-window" v-if="editable">
      <section>
        <label for="content">Text:</label>
        <textarea name="content" v-model="content" @change="updateElData()"></textarea>
      </section>

      <section class="checkbox-section">
        <label for="content">Underline:</label>
        <input type="checkbox" v-model="underline" @change="updateElData()">
      </section>

      <section class="checkbox-section">
        <label for="content">Bold:</label>
        <input type="checkbox" v-model="bold" @change="updateElData()">
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
    preset: Boolean
  },
  data() {
    return {
      type: 'section',
      content: 'Seciton',
      tag: 'div',
      underline: false,
      bold: false,
    }
  },
  mounted() {
    if(this.preset) {
      const element = this.$store.getters.getSelectionsData.filter(el => el.elementId == this.id)[0]
      this.content = element.data.content
      this.underline = element.data.underline
      this.bold = element.data.bold
    } else {
      this.$store.commit('addSelection', {component: 'SectionElement',data: {tag: this.tag, content: this.content, underline: this.underline, bold: this.bold}, elementId: this.id, props: {editable: false, id: this.id, preset: true}});
    }
    
  }, 
  methods: {
    updateElData() {
      this.$store.commit('updateSelectionsData', {elementId: this.id, data: {tag: this.tag, content: this.content, underline: this.underline, bold: this.bold}});
    },  
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
}
.section-element {
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
.checkbox-section {
  display: flex;
  flex-direction: row;
}
</style>