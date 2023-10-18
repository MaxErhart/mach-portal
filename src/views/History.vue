<template>
  <div class="history">
    History
    <JSONToTable :list="whitelist" :blacklist="false" :data="actions"/>
  </div>
</template>

<script>
import JSONToTable from '@/components/JSONToTable.vue'
export default {
  name: 'History',
  components: {
    JSONToTable,
  },
  data() {
    return {
      actions: null,
      whitelist: [
        "created_at","reply_list","email_list","submission_url","user.firstname", "user.lastname",
      ],
      blacklist: [
        'permission','form.submissions','form.created_at',
        'form.updated_at','form.no_login','form.display',
        'export','owner.id','id',
        'owner_id','owner_type','form_id','read',
        'edit','form_elements','form.form_elements',
        'form.name','form.deadline','form.multiple_submissions',
        'form.public','form.creator_id','form.id',
        'owner.affiliation','updated_at',
        'owner.created_at', 'owner.updated_at', 'owner.groups',
        'form.email_element_id','owner.email_verified_at', 'owner.rightsOnApps', 'replies',
        'confirmed','confirmation_id','form.no_login_email_subject','form.no_login_email_body','user_id',
        'user.created_at','user.updated_at','submission'
      ], 
    }
  },
  mounted() {
    this.getActions()
  },
  methods: {
    async getActions() {
      var {actions, error} = await this.$store.dispatch('actions')
      console.log(actions)
      console.log(error?.response)
      actions = actions.map(action=>{
        return this.$flattenObj(action)
      })
      this.actions = actions

    }
  }
}
</script>

<style lang="scss" scoped>
.history {
  color: #2c3e50;
  display: flex;
  flex-direction: column;
  align-items: center;
}
</style>