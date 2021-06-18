
<template>
  <div id="forms">
    <h1>View Forms</h1>
    <div id="all-forms-body">
      <section id="tabs">
        <button class="tab tab-left" :class="{active: activeTab=='AllForms'}" @click="sitchTab('AllForms')">All Forms</button>
        <button class="tab tab-right" :class="{active: activeTab=='MyForms'}" @click="sitchTab('MyForms')">My Forms</button>
      </section>
      <section id="tab-contents">
        <div id="all-forms" class="content" v-if="activeTab =='AllForms'">
          <div class="form-item-columns">
            <div class="form-name">Form Name</div>
            <div class="form-create-date">Deadline</div>            
          </div>
          <template v-if="forms != null">
            <div class="form-item-wrapper" v-for="form in forms" :key="form">
              <div class="form-item" @click="redirect(form.displayData.formId)">
                <div class="form-name">{{form.displayData.formName}}</div>
                <template v-if="form.displayData.deadline != null">
                  <div class="form-create-date" >{{form.displayData.deadline}}</div>
                </template>
                <template v-else>
                  <div class="form-create-date" ></div>
                </template>
              </div>
              <!-- <div class="form-item-options" v-if="form.notDisplayData.write">
                <div class="option" @click="viewSubmissions(form.displayData.formId)">
                  <img :src="require(`@/assets/view.svg`)">
                </div>
                <div class="option" @click="editForm(form.displayData.formId)">
                  <img :src="require(`@/assets/edit.svg`)">
                </div>
                <div class="option" @click="deleteForm(form.displayData.formId)">
                  <img :src="require(`@/assets/delete.svg`)">
                </div>                
              </div> -->
            </div>
          </template>
        </div>
        <div id="all-forms" class="content" v-if="activeTab =='MyForms'">
          <div class="form-item-columns">
            <div class="form-name">Form Name</div>
            <div class="form-create-date">Deadline</div>            
          </div>
          <template v-if="forms != null">
            <div class="form-item-wrapper" v-for="form in myForms" :key="form" >
              <div class="form-item" @click="redirect(form.displayData.formId)">
                <div class="form-name">{{form.displayData.formName}}</div>
                <template v-if="form.displayData.deadline != null">
                  <div class="form-create-date" >{{form.displayData.deadline}}</div>
                </template>
                <template v-else>
                  <div class="form-create-date" ></div>
                </template>
              </div>
              <div class="form-item-options" v-if="form.notDisplayData.write">
                <div class="option" @click="viewSubmissions(form.displayData.formId)">
                  <img :src="require(`@/assets/view.svg`)">
                </div>                
                <div class="option" @click="editForm(form.displayData.formId)">
                  <img :src="require(`@/assets/edit.svg`)">
                </div>
                <div class="option" @click="deleteForm(form.displayData.formId)">
                  <img :src="require(`@/assets/delete.svg`)">
                </div>                
              </div>
            </div>
          </template>
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
      console.log(response.data)
      if(response.data.error == null) {
        this.forms = response.data.forms;
      } else {
        this.$router.push({name: 'Home'})
      } 
    })  
  },
  mounted() {
    this.$store.commit('setCurrentRoute', this.$store.getters.getRoutes[5])
  },
  computed: {
    myForms() {
      if(this.forms != null) {
        return this.forms.filter(el => el.displayData.userId == JSON.parse(localStorage.user).userId)
      } else {
        return null
      }
      
    }
  },
  methods: {
    redirect(id) {
      this.$router.push({
        path: `/form/${id}`
      })
    },
    sitchTab(tabClicked) {
      this.activeTab = tabClicked
    },
    deleteForm(formId) {
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/getAllForms.php',
        data: {formId: formId, mode: 'delete'}
      })       
    },
    editForm(formId) {
      this.$router.push({
        path: `/createform/${formId}`
      })      
    },
    viewSubmissions(formId) {
      this.$router.push({
        path: `/submissions/${formId}`
      })       
    }
  }
}
</script>

<style lang="scss" scoped>
  #forms {
    text-align: center;
    display: flex;
    flex-direction: column;
    width: 92%; 
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
    border-bottom: none;
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
      background-color: #fff;
    }
  }
  .tab-left {
    &:not(.active) {
      box-shadow: inset -2px -2px 3px 0px rgba(0,0,0,0.1);
    }    
  }

  .tab-right {
    &:not(.active) {
      box-shadow: inset 2px -2px 3px 0px rgba(0,0,0,0.1);
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
  .form-item-columns {
    font-size: 16px;
    display: grid;
    grid-template-columns: auto auto;
    padding: 4px 8px;
    border-radius: 2px;
    border-bottom: 1px solid rgba(233,233,233);
    >.form-name {
      text-align: left;
    }
    >.form-create-date {
      text-align: right;
    }
  } 

  .form-item {
    font-size: 16px;
    display: grid;
    grid-template-columns: auto auto;
    padding: 4px 8px;
    border-radius: 2px;
    border: 1px solid rgba(233,233,233);
    cursor: pointer;
    
    &:hover {
      background-color: rgba(223,223,223);
    }
    &:active {
      box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.3);
    }
    >.form-name {
      text-align: left;
    }
    >.form-create-date {
      text-align: right;
    }    
  }
  .form-item-wrapper {
    position: relative;
  }
  .form-item-options {


    display: flex;
    flex-direction: row;
    align-items: center;    
    position: absolute;
    right: 0;
    height: 100%;
    // border: 1px solid black;
    // background-color: #fff;
    transform: translate(100%, -100%);
    > .option {
      box-sizing: border-box;
      border: 1px solid #2c3e50;
      border-radius: 2px;
      background-color: #e0e0e0;
      width: 100%;      
      display: flex;
      margin: 0.5px;
      padding: 1.5px;
      &:hover {
        box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8);
        cursor: pointer;
      }
      &:active {
        box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8), 0 0 0 1px black;
      }
      > img {
        height: 20px;
        margin: auto;
      }

    }    
  }
</style>