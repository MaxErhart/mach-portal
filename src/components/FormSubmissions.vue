<template>
  <div id="submissions">
    <div id="form-submissions" v-if="submissions != null">

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
        <div class="row" v-for="(row, rowIndex) in data" :key="rowIndex">
          <div class="row-item red-flag" v-for="(item, itemIndex) in row" :key="itemIndex">
            <div class="flag" v-if="itemIndex==0">
              <svg width="32px" height="32px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                <path d="M8,11.4939236 L8,29.9939236 L9,29.9939236 L9,15.4917069 C10.2662537,15.0112371 12.688621,14.5518079 16,15.9939238 C21.022644,18.1813011 24,15.9939236 24,15.9939236 L24,4.99392359 C24,4.99392359 21.0237426,7.23025181 16,4.9939236 C10.9762573,2.75759551 8,4.99392359 8,4.99392359 L8,11.4939236 L8,11.4939236 Z" id="flag" sketch:type="MSShapeGroup">
                </path>
              </svg>
            </div>
            <a :href="item.data" v-if="item.type=='file'">{{item.data.split("/").pop().split("_").pop()}}</a>
            
            <template v-else-if="item.type=='data'">{{item.data}}</template>
            
            <div class="form-item-options" v-else-if="item.type=='options'">  
              <div class="option" :class="{disabled: !item.data}" @click="editSubmission(submissions[rowIndex])">
                <img :src="require(`@/assets/edit.svg`)">
              </div>
              <div class="option" :class="{disabled: !item.data}" @click="deleteSubmission(submissions[rowIndex])">
                <img :src="require(`@/assets/delete.svg`)">
              </div>                
            </div>

            <input type="checkbox" :checked="selectedSubmissionIds.includes(item.data)" v-else-if="item.type=='checkbox'" @change="updateSelectedSubmissions($event, item.data)">

          </div>

        </div>
      </div>
    </div>
    <div v-else>
      no submissions found
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  name: 'FormSubmissions',
  props: {
    formName: String,
    elements: Object,
    submissions: Object,
    selectedSubmissionIds: Object,
  },
  components: {
  },
  data() {
    return {
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
              temp['type'] = 'file'
              temp['data'] = this.submissions[i]['displayData']['files'][this.colNames[j].id]
              // temp['file'] = this.submissions[i]['displayData']['files'][this.colNames[j].id]
            } else if(this.colNames[j].type == 'input'){
              temp['type'] = 'data'
              temp['data'] = this.submissions[i]['displayData']['data'][this.colNames[j].id]
              // temp['data'] = this.submissions[i]['displayData']['data'][this.colNames[j].id]
            } else if(this.colNames[j].type == 'selection') {
              var el = this.elements.filter(obj => {
                return obj.elementId == this.colNames[j].id
              })[0]
              temp['type'] = 'data'
              temp['data'] = el.data.options[this.submissions[i]['displayData']['data'][this.colNames[j].id]]             
            } else if(this.colNames[j].type == 'writePermissions') {
              temp['type'] = 'options'
              temp['data'] = this.submissions[i]['notDisplayData']['write']
            } else if(this.colNames[j].type == 'checkbox') {
              temp['type'] = 'checkbox'
              temp['data'] = this.submissions[i]['displayData']['formSubmissionId']
            }
          } else {
            temp['type'] = 'data'
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
        if(this.elements[i].component == 'FileUploadElement') {
          temp['type'] = 'file'
        } else if(this.elements[i].component == 'InputElement'){
          temp['type'] = 'input'
        } else if(this.elements[i].component == 'SelectionElement'){
          temp['type'] = 'selection'
        }
        temp['id'] = this.elements[i].elementId
        temp['data'] = this.elements[i].data.labelName
        cols.push(temp)
      }
      cols.push({id: 'dateOfSubmission', data: 'Date'})
      cols.push({id: 'options', data: 'Options', type: 'writePermissions'})
      cols.unshift({id: 'checkbox', data: 'Select', type: 'checkbox'})
      return cols
    },

  },  
  methods: {
    updateSelectedSubmissions(event, id) {
      if(event.target.checked) {
        this.$emit('selected-submission-change', {type: 'add', id: id})
      } else {
        this.$emit('selected-submission-change', {type: 'remove', id: id})
      }
    },
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
    deleteSubmission(submission) {
      this.$emit('delete-submission', {data: submission})     
    },
    editSubmission(submission) {
      this.$emit('edit-submission', {data: submission})
      console.log(submission.displayData)
      this.$store.commit('setFormSubmissionData', submission.displayData)
    }    
  }

}
</script>

<style lang="scss" scoped>
.row-item {
  position: relative;
  display: flex;
  align-items: center;
}
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
    width: 170px;
    > * {
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: row;
    }
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
    // grid-gap: 4px;
    row-gap: 4px;
    grid-auto-rows: auto;
  }

  .column {
    display: contents;
  }
  .row {
    display: contents;
    > * {
      border-top: 1px solid #2c3e50;
      border-bottom: 1px solid #2c3e50;
    }
    
    > :first-child {
      border-left: 1px solid #2c3e50;
      border-top: none;
    }
    > :first-child::before {
        position: absolute;
        content: "";
        padding-left: 10px;
        border-top: 1px solid #2c3e50;
        top: 0;
    }
    > :first-child::after {
        position: absolute;
        content: "";
        padding-left: calc(100% - 26px);
        border-top: 1px solid #2c3e50;
        top: 0;
        right: 0;
    }    
    > :last-child {
      border-right: 1px solid #2c3e50;
    }
  }
  .flag {
    position: absolute;
    top: 0;
    fill: #28a745;
    transform: scale(0.5) translateY(-100%);
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