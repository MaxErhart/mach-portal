<template>
  <div id="submissions">
    <h1>Form Submissions</h1>
    <div id="form-submissions" v-if="formName != null">
      <div id="form-submissions-header">
        Submissions for form: <span>{{formName}}</span>
      </div>
      <div id="form-submissions-body" :style="gridStyle">
        <div id="col-names" v-for="col in colNames" :key="col">
          <template v-for="(item,name) in col" :key="item">
            <template v-if="name == 'data'">{{item}}</template>
          </template>
        </div>

        <div class="row" v-for="row in data" :key="row">
          <div class="row-item" v-for="item in row" :key="item">
            <template v-for="(value, key) in item" :key="value">
              <a v-if="key == 'file'" :href="fileBaseUrl.concat(value)">{{value.split("/").pop().split("_").pop()}}</a>
              <template v-if="key == 'data'">{{value}}</template>
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
              temp['file'] = this.submissions[i]['files'][this.colNames[j].id]
            } else if(this.colNames[j].type == 'data'){
              temp['data'] = this.submissions[i]['data'][this.colNames[j].id]
            } else if(this.colNames[j].type == 'selection') {
              var el = this.elements.filter(obj => {
                return obj.elementId == this.colNames[j].id
              })[0]
              temp['data'] = el.data.options[this.submissions[i]['data'][this.colNames[j].id]]             
            }
          } else {
            temp['data'] = this.submissions[i][this.colNames[j].id]
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
      return cols
    }
  },  
  beforeCreate() {
    axios({
      method: 'post',
      url: 'https://www-3.mach.kit.edu/api/getFormSubmissions.php',
      data: {id: this.$route.params.id}
    }).then(response => {   
      console.log(response.data) 
      if(response.data.success) {

        this.formName = response.data.formName;
        this.elements = response.data.elements;
        this.submissions = response.data.submissions;
        console.log(this.data)
      } else if(response.data.errorCode == 404) {
        this.error=404
      } else {
        this.$router.push({name: 'Home'})
      }
      
    })
  },

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
    font-size: 20px;
    font-weight: 500;
    > span {
      text-decoration: underline;
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
</style>