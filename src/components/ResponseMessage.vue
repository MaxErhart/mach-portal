<template>
  <div id="response-message" class="danger" v-if="response.status!=200">
    <div id="response-wrapper">
      <div id="response-title" class="danger">Error {{response.status}}: {{response.data.message}}</div>
      <div id="response-body">
      </div>
    </div>
  </div>
  <div id="response-message" class="success" v-else>
    <div id="response-wrapper">
      <div id="response-title" class="success">Success!</div>
      <div id="response-body">
      </div>
    </div>
  </div>  
</template>

<script>
export default {
  name: 'ResponseMessage',
  props: {
    response: Object,
    message: String,
    code: Number,
    
  },
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';

#response-message {
  position: fixed;
  // border: 2px solid $kit_green;
  top: 30%;
  border-radius: 16px;
  background-color: #fff;
  width: calc(100% - 40px);
  padding: 10px 20px 10px 20px;
  z-index: 100;
  display: flex;
  justify-content: center;
  &.danger{
    border: 2px solid #dc3545;
  }
  &.success{
    border: 2px solid #4F8A10;
  }
}

#response-title {
  font-weight: bold;
  padding: 5px 0;
  &.danger{
    color: #dc3545;
  }
  &.success{
    color: #4F8A10;
  }  
}
.error {
  margin: 0.5em 0;
}
.error-value {
  margin-left: 1em;
}
</style>
