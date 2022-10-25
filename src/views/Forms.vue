<template>
  <div class="forms">
    <!-- <h1>Forms</h1> -->
    <div class="forms-content">
      <template v-if="awaitData">
        Awaiting Data
      </template>
      <template v-if="unauthorized">
        Unauthorized please login.
      </template>
      <template v-if="!awaitData && !unauthorized">
        <NavBar :tabs="navTabs" :singleRoute="true" @change="navActiveTab=$event"/>
        <template v-if="navActiveTab==0">
          <JSONToTable :data="activeForms" :columnSettings="columnSettings" :itemClickable="true" @itemClicked="selectForm($event)"/>
        </template>
        <template v-if="navActiveTab==1">
          <JSONToTable :data="inactiveForms" :columnSettings="columnSettings" :itemClickable="true" @itemClicked="selectForm($event)"/>
        </template>
      </template>      
    </div>
  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue'
import JSONToTable from '@/components/JSONToTable.vue'
import axios from "axios";
import moment from 'moment'
export default {
  name: 'Forms',
  components: {
    JSONToTable,
    NavBar,
  },
  data() {
    return {
      columnSettings: {type: 'whitelist', items: ['name', 'deadline']},
      forms: [],
      fetchedAllData: false,
      awaitData: false,
      unauthorized: false,
      navTabs: [
        {text: 'Current', route: {name: 'Forms'}},
        {text: 'Expiered', route: {name: 'Forms'}},
      ],
      navActiveTab: 0,  
    }
  },
  mounted() {
    this.getForms();
  },

  computed: {
    activeForms() {
      return this.forms.filter(form=>{
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
    method() {
      if(this.$route.meta.new && this.$route.params.id) {
        return 'update';
      }
      if(this.$route.meta.new) {
        return 'store';
      }
      return 'index';
    },    
  },
  methods: {
    selectForm(form) {
      this.$router.push({name: 'SubmitForm', params: {id: form.id}})
    },
    getForms() {
      this.forms=[];
      this.fetchedAllData = true;
      this.awaitData = true;
      const url = `${this.apiUrl}/forms`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.forms = this.forms.concat(response.data);
        console.log(this.forms)
        this.awaitData = false;
      }).catch(error=>{
        this.awaitData = false;
        this.unauthorized = true;
        console.log(error.response)
      })
    },       
  }
}
</script>

<style lang="scss" scoped>
.forms {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
}
.forms-content {
  width: 100%;
  background-color: #fff;
  box-shadow: rgba(0, 0, 0, 0.12) 2px 1px 3px, rgba(0, 0, 0, 0.24) 2px 1px 2px;
}
</style>