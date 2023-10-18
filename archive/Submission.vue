<template>
  <div id="submissions">
    <div id="form-submissions" v-if="formName != null">

      <div id="form-submissions-header">
        <div>
          <div id="create-file"  v-if="filename == null">
            <div @click="createFile()" v-if="!creatingFileLoading">
              <div >
                Create Excel File
              </div>
              <img :src="require(`@/assets/file.svg`)">
            </div>
            <div id="file-create-loading" v-if="creatingFileLoading">Loading...</div>
          </div>
          <form id="file-download" method="get" :action="`https://www-3.mach.kit.edu/dfiles/submissions/${filename}`" v-else>
            <button type="submit">Download</button>
          </form>          
        </div>

        <div class="header-title">
          <div>
            Submissions for form: <span>{{formName}}</span>
          </div>
        </div>              
        
      </div>
      <div id="form-submissions-body" :style="gridStyle">
        <div id="col-names" v-for="col in colNames" :key="col">
          <template v-for="(item,name) in col" :key="item">
            <template v-if="name == 'data'">{{item}}</template>
          </template>
        </div>
        <div class="row" v-for="row in data" :key="row">
          <div class="row-item" v-for="(item, index) in row" :key="item">
            <template v-for="(value, key) in item" :key="value">
              <a v-if="key == 'file'" :href="fileBaseUrl.concat(value)">{{value.split("/").pop().split("_").pop()}}</a>
              <template v-if="key == 'data'">{{value}}</template>
              <template v-if="index == row.length-1">
                <div class="form-item-options">           
                  <div class="option" :class="{disabled: !value}" @click="editSubmission(key, row[0]['data'], value)">
                    <img :src="require(`@/assets/edit.svg`)">
                  </div>
                  <div class="option" :class="{disabled: !value}" @click="deleteSubmission(key,  row[0]['data'], value)">
                    <img :src="require(`@/assets/delete.svg`)">
                  </div>                
                </div>
              </template>
            </template>
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="error==404">
      no submissions found
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  name: 'Submissions',
  components: {
  },
  data() {
    return {
      formName: null,
      elements: null,
      submissions: null,
      error: null,
      creatingFileLoading: false,
      filename: null
    }
  },
  computed: {
    fileBaseUrl: function() {
      return this.$store.getters.getBaseFileUrl
    },
    data: function() {
      var data = []
      for(let i=0; i<this.submissions.length; i++) {
        var row = []
        for(let j=0; j<this.colNames.length; j++) {
          const temp = {}
          if('type' in this.colNames[j]) {
            if(this.colNames[j].type == 'file') {
              temp['file'] = this.submissions[i]['displayData']['files'][this.colNames[j].id]
            } else if(this.colNames[j].type == 'data'){
              temp['data'] = this.submissions[i]['displayData']['data'][this.colNames[j].id]
            } else if(this.colNames[j].type == 'selection') {
              var el = this.elements.filter(obj => {
                return obj.elementId == this.colNames[j].id
              })[0]
              temp['data'] = el.data.options[this.submissions[i]['displayData']['data'][this.colNames[j].id]]             
            } else if(this.colNames[j].type == 'write') {
              temp[this.submissions[i]['displayData']['userId']] = this.submissions[i]['notDisplayData']['write']
            }
          } else {
            temp['data'] = this.submissions[i]['displayData'][this.colNames[j].id]
          }
          row.push(temp)
        }
        data.push(row)
      }
      return data
    },
    gridStyle: function() {
      return {gridTemplateColumns: `repeat(${this.colNames.length}, auto)`}
    },
    colNames: function() {
      var cols = [{id: 'formSubmissionId', data: 'id'}, {id: 'firstname', data: 'First Name'}, {id: 'lastname', data: 'Last Name'}]
      for(let i=0; i<this.elements.length; i++) {
        const temp = {}
        if(this.elements[i].type == 'file') {
          temp['type'] = 'file'
        } else if(this.elements[i].type == 'input'){
          temp['type'] = 'data'
        } else if(this.elements[i].type == 'selection'){
          temp['type'] = 'selection'
        }
        temp['id'] = this.elements[i].elementId
        temp['data'] = this.elements[i].data.labelName
        cols.push(temp)
      }
      cols.push({id: 'dateOfSubmission', data: 'Date'})
      cols.push({id: 'options', data: 'Options', type: 'write'})
      return cols
    },

  },  
  beforeCreate() {
    axios({
      method: 'post',
      url: 'https://www-3.mach.kit.edu/api/getFormSubmissions.php',
      data: {formId: this.$route.params.id, mode: 'select'}
    }).then(response => {
      console.log(response.data)
      if(response.data.error == null) {
        if(response.data.formSubmissions != null) {
          this.formName = response.data.formSubmissions.formName;
          this.elements = response.data.formSubmissions.elements;
          this.submissions = response.data.formSubmissions.submissions;
        } else {
          this.error=404
        }
      } else {
        this.$router.push({name: 'Home'})
      }
      
    })
  },
  methods: {
    createFile(){
      this.creatingFileLoading = true
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/getFormSubmissions.php',
        data: {formId: this.$route.params.id, mode: 'createFile'}
      }).then(response => {
        console.log(response.data)
        
        if(response.data.error == null) {
          this.creatingFileLoading = false
          this.filename = response.data.filename
        } else {
          this.$router.push({name: 'Home'})
        }
        
      })
    },
    deleteSubmission(submissionOwnerId, formSubmissionId, write) {
      if(write) {
        this.submissions.splice(this.submissions.indexOf(this.submissions.filter(el => el.displayData.formSubmissionId == formSubmissionId)), 1)
        axios({
          method: 'post',
          url: 'https://www-3.mach.kit.edu/api/getFormSubmissions.php',
          data: {formId: this.$route.params.id, mode: 'delete', formSubmissionId: formSubmissionId, submissionOwnerId: submissionOwnerId}
        }).then(response => {  
          console.log(response.data)          
        })
      }
      
    },
    editSubmission(submissionOwnerId, formSubmissionId, write) {
      if(write) {
        this.$router.push({
          path: `/form/${this.$route.params.id}/${formSubmissionId}`
        })
      }
    }    
  }

}
</script>

<style lang="scss" scoped>
  #submissions {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  #form-submissions {
    width: 100%;
    background: #eee;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  #form-submissions-header {
    display: grid;
    width: 100%;
    margin-bottom: 5px;
    grid-template-columns: 1fr auto 1fr;
    > * {
      display: flex;
      align-items: center;
      align-content: center;
    }
    > .header-title {
      font-size: 20px;
      font-weight: 500;
      > span {
        text-decoration: underline;
      }
    }

  }
  #create-file {
    display: flex;
    flex-direction: row;
    border: 1px solid #2c3e50;
    border-radius: 2px;
    background-color: #e0e0e0;
    margin: 0.5px;
    padding: 1.5px;
    width: 160px;
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
  #file-create-loading {
    width: 100%;
    text-align: center;
  }
  #file-download {
    border: 1px solid #2c3e50;
    border-radius: 2px;
    background-color: #e0e0e0;
    margin: 0.5px;
    padding: 1.5px;
    width: 160px;
    text-align: center;
    &:hover {
      box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8);
      cursor: pointer;
    }
    &:active {
      box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8), 0 0 0 1px black;
    }
    > button {
      width: 100%;
      display: contents;
      color: #2c3e50;
      font-size: 16px;
    }   
  }
  #form-submissions-body {
    width: 100%;
    display: grid;
    grid-gap: 4px;
    row-gap: 4px;
    grid-auto-rows: auto;
  }

  .column {
    display: contents;
  }
  .row {
    display: contents;
  }

  .grid-item {
    background-color: #fff;
  }
  .form-item-options {
    display: flex;
    flex-direction: row;
    align-items: center;
    height: 100%;
    > .option {
      box-sizing: border-box;
      border: 1px solid #2c3e50;
      border-radius: 2px;
      background-color: #e0e0e0;
      width: 100%;      
      display: flex;
      margin: 0.5px;
      padding: 1.5px;
      &.disabled {
        filter: grayscale(100%);
        opacity: 0.2;
      }
      &:hover {
        &:not(.disabled) {
          box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8);
          cursor: pointer;
        }

      }
      &:active {
        &:not(.disabled) {
          box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8), 0 0 0 1px black;
        }
      }
      > img {
        height: 20px;
        margin: auto;
      }

    }
  }
</style>