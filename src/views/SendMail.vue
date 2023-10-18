<template>
  <div class="send-mail">
    <div class="mail-form-container">
      <MailForm :users="selected_users"/>
    </div>
    <div class="select-grid">
      <div v-if="groups">
        <JSONToTable :col_filter="true" @select="groupSelect($event)" name="groups" :data="groups" :columns="groups_table_header" :select="true" ref="groups_table"/>
      </div>
      <div v-else>
        <DataPlaceholder animation="table"/>
      </div>
      <div v-if="users">
        <JSONToTable :col_filter="true" @select="userSelect($event)" name="users" :data="users" :columns="users_table_header" :select="true" ref="users_table"/>
      </div>
      <div v-else>
        <DataPlaceholder animation="table"/>
      </div>
    </div>

  </div>
</template>

<script>
import JSONToTable from '@/components/JSONToTable2.vue'
import DataPlaceholder from '@/components/DataPlaceholder.vue'
import MailForm from '@/components/MailForm.vue'
export default {
  name: 'SendMail',
  components: {
    JSONToTable,
    DataPlaceholder,
    MailForm,
  },
  data() {
    return {
      users: null,
      groups: null,
      selected_users_id: {},
      selected_groups_id: {},
    }
  },
  mounted() {
    this.getUsers()
    this.getGroups()
  },
  computed: {
    selected_users() {
      return this.users?.filter(u=>{
        return u.id in this.selected_users_id
      })
    },
    groups_table_header() {
      return [
        {id:'name',name:'name',show:true},
      ]
    },
    users_table_header() {
      return [
        {id:'firstname',name:'firstname',show:true},
        {id:'lastname',name:'lastname',show:true},
        {id:'email',name:'email',show:true},
        {id:'private_email',name:'private_email',show:true},
      ]
    },
  },
  methods: {
    groupSelect(event) {
      const event_groups = event.rows
      const deselect = []
      const select = []
      const user_ids = this.users.map(u=>u.id)
      Object.keys(this.selected_groups_id).forEach(group_id => {
        if(event_groups?.map(g=>`${g.id}`).indexOf(group_id)<0) {
          const group = this.groups.find(g=>g.id==group_id)
          deselect.push(group)
        }
      })
      event_groups?.forEach(group=>{
        if(!(group.id in this.selected_groups_id)) {
          select.push(group)
        }
      })
      if(select.length>0) {
        select.forEach(group=>{
          this.selected_groups_id[group.id] = true
          group.users.forEach(member=>{
            // this.selected_users_id[member.id] = true
            const user_idx = user_ids.indexOf(member.id)
            const selected_idx = this.$refs.users_table.selected.indexOf(user_idx)
            if(selected_idx<0) {
              this.$refs.users_table.selected.push(user_idx)
            }
          })
        })
        this.userSelect({rows:this.$refs.users_table.selected_rows})
      }
      if(deselect.length>0){
        deselect.forEach(group=>{
          delete this.selected_groups_id[group.id]
          group.users.forEach(member=>{
            const user_idx = user_ids.indexOf(member.id)
            const selected_idx = this.$refs.users_table.selected.indexOf(user_idx)
            if(selected_idx>-1) {
              this.$refs.users_table.selected.splice(selected_idx,1)
            }
          })
        })
        this.userSelect({rows:this.$refs.users_table.selected_rows})
      }
    },
    userSelect(event) {
      const deselect = []
      const select = []
      Object.keys(this.selected_users_id).forEach(user_id=>{
        if(event.rows?.map(u=>`${u.id}`).indexOf(user_id)<0) {
          const user = this.users.find(u=>u.id==user_id)
          deselect.push(user)
        }
      })
      event.rows?.forEach(user=>{
        if(!(user.id in this.selected_users_id)) {
          select.push(user)
        }
      })
      if(select.length>0) {
        select.forEach(user=>{
          this.selected_users_id[user.id] = true
        })
        this.$refs.groups_table.filter(this.groups).forEach((group,index)=>{
          var select_group = group.users.length>0 && !(group.id in this.selected_groups_id)
          if(!select_group) {
            return
          }
          group.users.forEach(member=>{
            if(!(member.id in this.selected_users_id)) {
              select_group = false
            }
          })
          if(select_group) {
            this.$refs.groups_table.selected.push(index)
            this.selected_groups_id[group.id] = true
          }
        })
      } 
      if(deselect.length>0) {
        deselect.forEach(user=>{
          delete this.selected_users_id[user.id]
        })
        this.groups.forEach((group,index)=>{
          var deselect_group = !(group.users.length>0 && group.id in this.selected_groups_id)
          if(deselect_group) {
            return
          }
          group.users.forEach(member=>{
            if(!(member.id in this.selected_users_id)) {
              deselect_group = true
            }
          })
          if(deselect_group) {
            delete this.selected_groups_id[group.id]

            var sel_index_index = this.$refs.groups_table.selected.indexOf(index)
            this.$refs.groups_table.selected.splice(sel_index_index, 1)
          }
        })
      }
    },
    async getUsers() {
      const {users} = await this.$store.dispatch('users')
      this.users = users
    },
    async getGroups() {
      const {groups} = await this.$store.dispatch('groups')
      this.groups = groups
    }
  }
}
</script>

<style lang="scss" scoped>
.mail-form-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  margin: 32px 0;
}
.select-grid {
  display: grid;
  gap: 32px;
  grid-template-columns: auto auto;
}
</style>