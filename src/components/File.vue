<template>
  <a class="file" download="filename" @click.prevent="open({name,content:{name,fragments,permission,is_dir,href}})" :href="href" :class="{open:is_open}">
    <ion-icon name="document-outline" class="icon"></ion-icon>
    <span class="name">{{name}}</span>
  </a>
</template>

<script>
export default {
  name: 'File',
  props: {
    name: String,
    is_dir: Boolean,
    permission: Number,
    href: String,
    fragments: Array,
  },
  data() {
    return {
      is_open: false,
    }
  },
  methods: {
    open({name,content}) {
      content['object'] = this
      this.$emit('open', {names:[name],content})
    }
  },
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.file {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  white-space: nowrap;

  .icon {
    transform: scale(1.2);
  }
  &:hover {
    background-color: rgba(0,0,0,0.2);
  }
  &.open {
    color: $kit_green !important;
  }
}
</style>
