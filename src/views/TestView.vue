<template>
  <div class="about">
    <InputElement :placeholder="null" labelName="Test" inputType="email" :required="true" :validationType="INPUT_TYPES.NUMBER" />
  </div>
</template>

<script>
import {INPUT_TYPES} from '../validationSettings.js'
import InputElement from '../components/InputElementTEMP.vue'

export default {
  name: 'TestView',
  components: {
    InputElement
  },
  data() {
    return {
      INPUT_TYPES,
    }
  },  
  mounted() {
    // this.$store.commit('setCurrentRoute', this.$store.getters.getRoutes[3])
  },  
}
</script>

<style lang="scss" scoped>
  .about {
    width: 680px;
    // height: 60px;
    // border: 1px solid black;
    background-color: #fafafa;
  }
</style>