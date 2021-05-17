
<template>
  <div id="forms" v-if="forms != null">
    <h1>View Forms</h1>
    <div id="all-forms-body">
      <section id="tabs">
        <button class="tab active" @click="sitchTab('AllForms')" ref="AllForms">All Forms</button>
        <button class="tab" @click="sitchTab('MyForms')" ref="MyForms">My Forms</button>
      </section>
      <section id="tab-contents">
        <div id="all-forms" class="content" v-if="activeTab =='AllForms'">
          <div class="form-item" v-for="form in forms" :key="form" @click="redirect(form.formId)">{{form.formName}}</div>
        </div>
        <div id="my-forms" class="content" v-if="activeTab =='MyForms'">
          My Forms
        </div>
      </section>
    </div> 
  </div>
</template>

<script>
import axios from "axios";
export default {
  name: 'Forms',
  components: {
  },
  data() {
    return {
      forms: null,
      activeTab: 'AllForms'
    }
  },
  beforeCreate() {
    axios({
      method: 'get',
      url: 'https://www-3.mach.kit.edu/api/getAllForms.php'
    }).then(response => {
      if(response.data.success) {
        this.forms = response.data.forms;
      } else {
        this.$router.push({name: 'Home'})
      } 
    })  
  },
  methods: {
    redirect(id) {
      this.$router.push({
        path: `/form/${id}`
      })
    },
    sitchTab(tabClicked) {
      this.fetchTabData(tab)
      for(var tab in this.$refs) {
        this.$refs[tab].classList.remove('active')
      }
      this.$refs[tabClicked].classList.add('active')
      this.activeTab = tabClicked
    },
    fetchTabData(tab) {
      if(tab == 'AllTabs') {
        axios({
          method: 'get',
          url: 'https://www-3.mach.kit.edu/api/getAllForms.php'
        }).then(response => {
          if(response.data.success) {
            this.forms = response.data.forms;
          } else {
            this.$router.push({name: 'Home'})
          } 
        })
      } else if(tab == 'MyTabs') {
        axios({
          method: 'get',
          url: 'https://www-3.mach.kit.edu/api/getMyForms.php',
        }).then(response => {
          if(response.data.success) {
            this.forms = response.data.forms;
          } else {
            this.$router.push({name: 'Home'})
          } 
        })        
      }
    }
  }
}
</script>

<style lang="scss" scoped>
  #forms {
    text-align: center;
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 800px;   
  }
  #all-forms-body {
    width: 100%;
    height: 100%;
    background-color: white;
    padding: 8px;
  }

  #tabs {
    overflow: hidden;
    border: 1px solid #ccc;
    background-color: #f1f1f1;
    display: grid;
    grid-template-columns: 1fr 1fr;

  }

  .tab {    
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 8px 0;
    transition: 0.3s;
    font-size: 17px;    
    &:hover {
      background-color: #ddd;
    }
    &.active {
      background-color: #ccc;
    }
  }
  #tab-contents {
    border: 1px solid #ccc;
    border-top: 0px;
    padding: 0px 0px;
  }
  .content {
    min-height: 240px;
  }
  .form-item {
    font-size: 16px;
    display: block;
    text-align: left;
    padding: 4px 8px;
    border-radius: 2px;
    cursor: pointer;
    border: 1px solid rgba(233,233,233);
    &:hover {
      background-color: rgba(223,223,223);
    }
    &:active {
      box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.3);
    }
  }  
</style>