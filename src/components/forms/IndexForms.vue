<template>
  <div id="index-forms">
    <JSONToTable :optionLoading="deleteLoading?3:null" permissionKey="permission" :loading="loading" :data="data" :blacklist="false" :list="columnList" :rowMenuOptions="rowMenuOptions" @menuClick="handleMenuOption($event)"/>
  </div>
</template>

<script>
import JSONToTable from '@/components/JSONToTable.vue'
export default {
  name: 'IndexForms',
  props: {
    data: Object,
    loading: Boolean,
    deleteLoading: Boolean,
    copyLoading: Boolean,
  },
  components: {
    JSONToTable,
  },
  data() {
    return {
      columnList: ["name"],
      rowMenuOptions: [
        {id: 0, icon: 'cloud-upload-outline', text: 'Upload Submissions', name: 'uploadSubmissions', permission: 2, twoStep: false},
        {id: 1, icon: 'create-outline', text: 'Edit Form', name: 'editForm', permission: 2, twoStep: false},
        {id: 2, icon: 'copy-outline', text: 'Copy Form', name: 'copyForm', permission: 2, twoStep: false},
        {id: 3, icon: 'trash-outline', text: 'Delete Form', name: 'deleteForm', permission: 3, twoStep: true},
      ],
    }
  },
  computed: {
    optionLoading() {
      if(this.deleteLoading) {
        return 3
      }
      if(this.copyLoading) {
        return 2
      }
      return null
    }
  },
  methods: {
    handleMenuOption(event) {
      switch(event.option.name) {
        case 'editForm':
          this.$emit('editForm', event.row.id)
          break
        case 'uploadSubmissions':
          this.$emit('uploadSubmissions', event.row.id)
          break
        case 'deleteForm':
          this.$emit('deleteForm', event.row.id)
          break
        case 'copyForm':
          this.$emit('copyForm', event.row.id)
      }
    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
#index-forms-body-data-grid {
  position: relative;
  display: grid;
  justify-content: center;
  margin: 0 auto;
  list-style: none;
}
.column-name {
  position: relative;
  width: 100%;
  text-align: center;
  background-color: $kit_green;
  color: $text_light;
  padding: 8px 0;
}
.row {
  display: contents;
  outline: black;
  
  &:nth-child(2n){
    > * {
      background-color: #eee;
    }
  }
  &:nth-child(2n+1){
    > * {
      background-color: #fff;
    }
  }
  &:hover {
    > * {
      cursor: pointer;
      background-color: $text_dark;
      color: $text_light;
    }
    #delete-pseudo-element {
      visibility: visible;
      fill: $text_light;
    }
  }
  
}
.data-field {
  position: relative;
  text-align: center;
  padding: 8px 0;
}
#row-pseudo-element {
  position: absolute;
  pointer-events: none;
  background: none;
  margin: none;
  padding: none;
  border: none;
  border-radius: none;
  transition: box-shadow 0.2s ease;
  box-shadow: 0 0 2px 1px rgba(0, 0, 0, 0.0);
  &.active {
    transition: box-shadow 0.4s ease;
    box-shadow: 0 0 2px 1px rgba(0, 0, 0, 0.65);
    z-index: 11;
    outline: 2px solid black;
  }
}
#delete-pseudo-element {
  position: absolute;
  visibility: visible;
  background: none;
  border: none;
  right: 0px;
  height: 100%;
  z-index: 10;
  top: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  cursor: pointer;
  &:hover {
    > * {
      fill: red;

    }
  }
}
</style>
