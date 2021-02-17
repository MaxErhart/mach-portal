<template>
  <div class="create-table">
    <h1>Create Form</h1>
    <div class="create-table-body">
      <FormCreator/>
    </div>
  </div>
</template>

<script>
import FormCreator from '@/components/FormCreator.vue'
export default {
  name: 'CreateForm',
  components: {
    FormCreator
  }
}
</script>

<style lang="scss" scoped>
  .create-table {
    text-align: center;
    display: flex;
    flex-direction: column;
    height: 100%;
    width: 100%;
    max-width: 800px;
  }
  .create-table-body {
    box-shadow: 0 2px 3px 0 rgba(0,0,0,.2);
    background-color: rgb(243, 243, 243);
    min-height: 500px;
    width: 100%;
  }
</style>