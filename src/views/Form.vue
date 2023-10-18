<template>
  <div class="form">
    <div class="wrapper-form-proxy-user">
      <SelectElement label="Select Proxy User"/>
    </div>
  </div>
</template>

<script>
import SelectElement from '@/components/inputs/SelectElement.vue'

export default {
  name: 'Form',
  components: {
    SelectElement,
  },
}
</script>

<style lang="scss" scoped>
@import 'D:\\inetpub\\MPortal\\src\\_variables';

</style>