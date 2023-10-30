<template>
  <div class="user-activity">
    <button @click="getActions">TestButton</button>

    <div class="active_users">
      <JSONToTable2 :nested="true" :data="active_sessions" :columns="active_sessions_table_columns"/>
    </div>

    <div id="active_histo"></div>

    <div class="recent-user-activity">
      <JSONToTable2 :nested="true" :data="active_users" :columns="active_users_table_columns"/>
    </div>

  </div>
</template>

<script>
import JSONToTable2 from '@/components/JSONToTable2.vue'
import axios from "axios";
import Plotly from "plotly.js-dist"
export default {
  name: 'UserActivity',
  components: {
    JSONToTable2,
  },
  data() {
    return {
      limit: 50,
      offset: 0,
      active_users: [],
      active_users_table_columns: [
        {id:'created_at',name:'Time',show:true},
        {id:'request_url',name:'URL',show:true},
        {id:'request_method',name:'Method',show:true},
        {id:'request_controller',name:'Controller',show:true},
        {id:'request_controller_method',name:'Controller Method',show:true},
        {id:'request_post_data',name:'Post Data',show:true},
        {id:'request_get_data',name:'Get Data',show:true},
        {id:'user.name',name:'Username',show:true},
        {id:'user.email',name:'Email',show:true},
        {id:'user.shib_id',name:'Shib Id',show:true},
      ],


      active_sessions: [],
      active_sessions_table_columns: [
        {id:'created_at',name:'Time',show:true},
        // {id:'request_url',name:'URL',show:true},
        {id:'request_method',name:'Method',show:true},
        {id:'request_controller',name:'Controller',show:true},
        {id:'request_controller_method',name:'Controller Method',show:true},
        // {id:'request_post_data',name:'Post Data',show:true},
        // {id:'request_get_data',name:'Get Data',show:true},
        {id:'user.name',name:'Username',show:true},
        {id:'user.email',name:'Email',show:true},
        // {id:'user.shib_id',name:'Shib Id',show:true},
      ],


      get_actions_loading: false,
      get_sessions_loading: false,


      histo: {
        n_actions: 1000,
        n_bins: 30, 
      }
    }
  },
  mounted() {
    this.getSessions().then(sessions=>{
      this.active_sessions = sessions
    })
    this.makeHisto()
    this.getActions(this.limit, this.offset).then(actions=>{
      this.active_users = actions.map(action=>{
        action.request_post_data = action.request_post_data && typeof action.request_post_data==='object' && Object.keys(action.request_post_data).length>0 ? JSON.stringify(action.request_post_data) : ""
        action.request_get_data = action.request_get_data && typeof action.request_get_data==='object' && Object.keys(action.request_get_data).length>0 ? JSON.stringify(action.request_get_data) : ""
        action.request_url = action.request_url ? `<a style="text-decoration:none;color:#00876c;" href="${action.request_url}">${action.request_url.split(this.$store.getters.getApiUrl)[1]}</a>` : ""
        action.request_controller = action.request_controller.split("App\\Http\\Controllers\\API\\")[1]
        return action
      })
    })
  },
  methods: {
    async makeHisto() {
      const actions = await this.getActions(this.histo.n_actions,0)
      const last_element = actions.slice(-1)[0]
      const time_interval = (actions[0].updated_at - last_element.updated_at)/this.histo.n_bins
      var time_x = []
      var count_y = []
      for(var i=0;i<this.histo.n_bins;i++) {
        time_x.push(last_element.updated_at + (i+0.5)*time_interval)
      }
      count_y = time_x.map(()=>0)
      time_x = time_x.reverse()
      var start=0
      actions.forEach(action=>{
        for(var i=start;i<this.histo.n_bins;i++) {
          if(action.updated_at>=time_x[i]-time_interval/2) {
            count_y[i]++
            start=i
            break;
          }
        }
      })
      time_x = time_x.reverse()
      const trace = {
        x:actions.map(action=>action.updated_at),
        type: "histogram",
        nbinsx: this.histo.n_bins,
        autobinx: false,
        xbins: {
          end: time_x[0] + this.histo.n_bins * time_interval,
          size: time_interval,
          start: time_x[0],
        },

      }
      const data = [trace]
      const layout = {

        xaxis: {
          tickmode: "array",
          tickvals: time_x.map(time=>time+time_interval/2.1),
          ticktext: time_x.map(timestamp=>{
            const date = new Date(parseInt(timestamp)*1000)
            const m = `${date.getMonth()}`
            const d = `${date.getDate()}`
            const H = `${date.getHours()}`
            const i = `${date.getMinutes()}`
            return `${d.padStart(2,'0')}.${m.padStart(2,'0')}. ${H.padStart(2,'0')}:${i.padStart(2,'0')}`
            // return `${m.padStart(2,'0')}.${d.padStart(2,'0')}. ${H.padStart(2,'0')}:${i.padStart(2,'0')}`
          }),
          tickangle: 45
        }

      }
      Plotly.newPlot('active_histo', data, layout)
    },
    async getSessions() {
      const url = `${this.$store.getters.getApiUrl}sessions`
      this.get_sessions_loading = true
      const {data, error} = await axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'multipart/form-data'
        } 
      }).catch(error=>{
        return {data:null,error}
      })
      console.log(data,error?.response)
      this.get_sessions_loading = false
      return data
    },
    async getActions(limit, offset) {
      this.get_actions_loading = true
      const url = `${this.$store.getters.getApiUrl}actions`
      const {data, error} = await axios({
        method: 'get',
        params: {limit, offset},
        url: url,
        headers: {
          'Content-Type': 'multipart/form-data'
        } 
      }).catch(error=>{
        return {data:null,error}
      })
      console.log(data,error?.response)
      this.get_actions_loading = false
      return data
    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
#active_histo {
  width: 720px;
}
</style>
