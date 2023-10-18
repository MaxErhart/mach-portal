<template>
  <div class="public-form">
    <Submitting :form="form"/>
  </div>
</template>

<script>
import Submitting from '@/components/forms/submission/Submitting.vue'
export default {
  name: 'PublicForm',
  components: {
    Submitting,
  },
  data() {
    return {
      form: null,
    }
  },
  mounted() {
    if(this.form==null) {
      this.getForm(this.$route.params.id)
    }
  },
  methods: {
    async getForm(id) {
      this.awaitFormData = true
      const {form,error} = await this.$store.dispatch('_forms', {form_id:id})
      this.form = form
      console.log(error?.response)
      console.log(form)
      this.awaitFormData = false
    },
  }
}
</script>

<style lang="scss" scoped>
  .home {
    color: #2c3e50;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  #content {
    text-align: center;
    max-width: 210mm;
  }
</style>