<template>
  <form @submit.prevent="test()" ref="form">
    <DoubleInputElement label_url="Testlabel URL" label_alias="Testlabel ALIAS"/>
    <Button text="Submit"/>
  </form>
  <CreatorInputLink/>
</template>

<script>
import DoubleInputElement from '@/components/inputs/DoubleInputElement.vue'
import CreatorInputLink from '@/components/forms/creatorInputs/CreatorInputLink.vue'
import Button from '@/components/Button.vue'
export default {
  name: 'TestView',
  components: {
    DoubleInputElement,
    CreatorInputLink,
    Button,
  },
  data() {
    return {
      refresh: null
    }
  },
  computed: {

  },
  mounted() {

  },
  methods: {
    test() {
      const formData = new FormData(this.$refs.form)
      for (const pair of formData.entries()) {
        console.log(`${pair[0]}, ${pair[1]}`);
      }
    }
  }
}
</script>

<style scoped lang="scss">
.background {
  width: 100%;
  height: 500px;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #00876c;
}
button {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  color: white;
  aspect-ratio: 3.2;
  border-radius: 8px;
  padding: 0.5rem 1rem;
  gap: 0.5rem;
  border: 1px solid rgba(255,255,255,0.2);
  &:hover {
    background: rgba(255,255,255,0.2);
    cursor: pointer;
  }
  .icon {
    font-size: 2rem;
  }
  .text {
    font-size: 1rem;
  }
}

  
</style>