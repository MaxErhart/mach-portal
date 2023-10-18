<template>
  <div class="forms" ref="forms">
    <div class="tags" :style="tagsStyle">
      <TagsBar :tags="tags" @toggle="updateFilter($event)"/>
    </div>
    <div class="forms-content">
      <NavBar :tabs="navTabs" @change="navActiveTab=$event.id"/>
      <template v-if="navActiveTab==0">
        <JSONToTable :loading="awaitData" :blacklist="false" :list="tableColumns" :data="filter(activeForms)" @rowClick="selectForm($event)"/>
      </template>
      <template v-if="navActiveTab==1">
        <JSONToTable :loading="awaitData" :blacklist="false" :list="tableColumns" :data="filter(inactiveForms)" @rowClick="selectForm($event)"/>
      </template>
      <template v-if="navActiveTab==2">
        <JSONToTable :loading="awaitData" :blacklist="false" :list="tableColumns" :data="filter(ownForms)" @rowClick="selectForm($event)"/>
      </template>
    </div>
  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue'
import JSONToTable from '@/components/JSONToTable.vue'
import TagsBar from '@/components/TagsBar.vue'
import moment from 'moment'
import axios from 'axios';
export default {
  name: 'Forms',
  components: {
    JSONToTable,
    NavBar,
    TagsBar,
  },
  data() {
    return {
      // rowMenuOptions: [
      //   {icon: 'trash', text: 'test', emit: 'test', permission: 3},
      // ],
      tableColumns: ['name', 'deadline'],
      forms: [],
      fetchedAllData: false,
      awaitData: false,
      unauthorized: false,
      navActiveTab: 0,
      tags: [],
      selected_tags: {},
    }
  },
  mounted() {
    this.getForms();
    this.getTags();

  },

  computed: {
    navTabs() {
      const tabs = [
        {id: 0,text: 'Current', route: null},
        {id: 1,text: 'Expired', route: null},
      ]
      if(this.ownForms?.length>0) {
        tabs.push({id: 2,text: 'Personal Forms', route: null},)
      }
      return tabs
    },
    tagsStyle() {
      
      var width = this.$store.getters.getScreenWidth
      if(this.$store.getters.getSideNavigationOn) {
        width -= this.$store.getters.getSideNavigationWidth + 28
      }
      return {
        'width': width+'px'
      }
    },
    ownForms() {
      const user = this.$store.getters.getProfile
      return this.forms?.filter(form=>{
        return form.creator_id===user.id
      })
    },
    activeForms() {
      return this.forms?.filter(form=>{
        return form.deadline==null || moment(form.deadline).format("yyyy-MM-DD")>=moment().format("yyyy-MM-DD")
      })
    },
    inactiveForms() {
      return this.forms.filter(form=>{
        return form.deadline!=null && moment(form.deadline).format("yyyy-MM-DD")<moment().format("yyyy-MM-DD")
      })    
    },
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },
  },
  methods: {
    filter(forms) {
      const selected_tag_ids = Object.keys(this.selected_tags)
      if(selected_tag_ids.length<=0) {
        return forms
      }
      var select = false
      selected_tag_ids.forEach(tag_id=>{
        if(this.selected_tags[tag_id] && !select) {
          select = true
        }
      })
      return forms.filter(form=>{
        var veto = false
        var select_form = !select
        form.tags.forEach(tag=>{
          if(select && tag.id in this.selected_tags) {
            select_form = true
          }
          if(tag.id in this.selected_tags && !this.selected_tags[tag.id]){
            veto = true
          }
        })
        return !veto && select_form
      })
    },
    updateFilter(selected_tags) {
      this.selected_tags = selected_tags
    },
    getTags() {
      this.tags = []
      const url = `${this.apiUrl}/tags`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        console.log(response.data)
        this.tags = this.tags.concat(response.data);
      }).catch(error=>{
        console.log(error?.response)
      })
    },
    selectForm(form) {
      this.$router.push({name: 'submissions', params: {id: form.id}})
    },
    async getForms() {
      this.awaitData = true
      const {forms, error} = await this.$store.dispatch('_forms', {method:'get'})
      this.forms = forms
      this.awaitData = false
      if(error?.response.status===403) {
        this.emitter.emit('handle403')
      }
    },
  }
}
</script>

<style lang="scss" scoped>
.forms {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
}
.tags {
  align-self: flex-start;
  position: sticky;
  // width: 100%;
  top: 0;
  left: 0;
}
.forms-content {
  width: 100%;
  background-color: #fff;
  box-shadow: rgba(0, 0, 0, 0.12) 2px 1px 3px, rgba(0, 0, 0, 0.24) 2px 1px 2px;
}
</style>