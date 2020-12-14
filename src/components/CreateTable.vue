<template>
  <div class="create-table">
    <h1>Create Table</h1>
    <div class="create-table-body"></div>
  </div>
</template>

<script>

export default {
  name: 'CreateTable',

}
</script>

<style lang="scss" scoped>
  .create-table {
    text-align: center;
    display: flex;
    flex-direction: column;
    height: 100%;
  }
  .create-table-body {
    border-radius: 10px;
    box-shadow: 0 2px 3px 0 rgba(0,0,0,.2);
    background-color: rgb(243, 243, 243);
    height: 100%;
  }
</style>