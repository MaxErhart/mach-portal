<template>
  Filter forms by tags
  <div class="tags-bar">
    <button class="tag" @click.prevent="toggle(tag)" :class="{include: selected[tag.id]===true, veto: selected[tag.id]===false}" v-for="tag in tags" :key="tag">{{tag.name}}</button>
  </div>
</template>

<script>
export default {
  name: 'TagsBar',
  emits: ['toggle'],
  props: {
    tags: Array,
  },
  data() {
    return {
      selected: {}
    }
  },
  methods: {
    toggle(tag) {
      if(tag.id in this.selected && this.selected[tag.id]) {
        this.selected[tag.id] = false
      } else if(tag.id in this.selected && !this.selected[tag.id]) {
        delete this.selected[tag.id]
      } else {
        this.selected[tag.id] = true
      }
      this.$emit('toggle', this.selected)
    }
  }

}
</script>


<style scoped lang="scss">
.tags-bar {
  display: flex;
  flex-direction: row;
  gap: 8px;
  width: 100%;
  padding: 8px 0;
  overflow-x: auto;
}
.tag {
  font-size: 16px;
  padding: 8px 16px;
  border: 1px solid black;
  border-radius: 20px;
  cursor: pointer;
  &:hover {
    background-color: gray;
  }
  &.include {
    background-color: green;
  }
  &.veto {
    background-color: red;
  }
}
</style>
