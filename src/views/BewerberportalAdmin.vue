<template>
  <div class="admin-bewerberportal">
    <h1>Admin Bewerberportal</h1>
    <NavBar :tabs="navTabs"/>

    <template v-if="!$route.meta.new">
      <SelectElement :data="selectElementData_entranceExam" @selectedEntry="getBewerber($event)"/>
      <div class="grid" :style="gridStyle" v-if="bewerber.length>0">
  
        <div class="grid-item">
          <Checkbox :presetValue="allSelected" :data="checkboxElementData_allSelect" :clean="true" @click="selectAll()"/>
        </div>

        <div v-for="column in columns" :key="column" class="grid-item">
          <span class="column-name">{{column}}</span>
          <span class="filter" @click="openFilter(column, $event)" :class="{active: columnFiltered(column)}">Filter</span>
        </div>
        <div v-for="val in values" :key="val" class="grid-row">
          <div class="grid-item">
            <Checkbox :presetValue="selected.includes(val.id)" :data="checkboxElementData_rowSelect" :clean="true" @click="selectOne(val)"/>
          </div>
          <div v-for="column in columns" :key="column" class="grid-item">
            {{val[column]}}
          </div>      
        </div>

      </div>

      <div class="make-bescheide">
        <h4>Make Bescheide</h4>
        <form ref="form_make_bescheid" @submit.prevent="makeBescheid()">
          <SelectElement :data="selectElementData_bescheid" name="name"/>
          <SelectElement :data="selectElementData_bescheid_lang" name="lang"/>
          <Checkbox :presetValue="sendMailWithBescheid" :data="checkboxElementData_sendMail" @inputChange="sendMailWithBescheid=$event" name="mail"/>
          <template v-if="sendMailWithBescheid">
            <InputElement :data="inputElementData_mailSubject" name="mail_subject"/>
            <FileUploadElement :data="fileUploadElementData_emailAttachments" name="attachment_files" @fileChange="fileUploadElementData_emailAttachments.files = $event"/>
            <textarea name="mail_content"/>
            <div>
              Use placeholders: %FIRSTNAME_NO_SPACES%, %EMAIL_NO_DOMAIN%, %NAME%, %VORNAME%, %EMAIL%, %BEWERBUNGSNUMMER%, %DEGREE_DE%, %DEGREE%, %DEGREE_EN%, %ILIAS%, %EXAM_DATE%, %EXAM_TIME%              
            </div>
          </template>
          <Button :loading="buttonElementData_makeBescheid.loading" :disabled="buttonElementData_makeBescheid.disabled" :text="buttonElementData_makeBescheid.text" />
        </form>
      </div>
    </template>

    <template v-else>
      <form ref="form_entrance_exam" @submit.prevent="postEntranceExam()">
        <SelectElement :data="selectElementData_term" name="term"/>
        <InputElement :data="inputElementData_year" name="year"/>
        <InputElement :data="inputElementData_exam_date" name="exam_date"/>
        <InputElement :data="inputElementData_exam_time" name="exam_time"/>
        <InputElement :data="inputElementData_vorsitz" name="vorsitz"/>
        <InputElement :data="inputElementData_zeichen" name="zeichen"/>
        <Checkbox :data="checkboxElementData_current" name="current"/>
        <Button :loading="buttonElementData_save.loading" :disabled="buttonElementData_save.disabled" :text="buttonElementData_save.text" />
      </form>
    </template>






  </div>
  <div class="overlay" v-if="filterWindow.active" @click="closeFilter()"></div>
  <div class="filter-window" v-if="filterWindow.active" :style="{'left': `${filterWindow.x}px`, 'top': `${filterWindow.y}px`}">
    <Checkbox :presetValue="filterAllChecked(filterWindow.column)" :data="checkboxElementData_allSelect" @inputChange="updateAllFilter($event, filterWindow.column)"/>
    <div class="value-item" v-for="value in filterWindowValues(filterWindow.column)" :key="value">
      <Checkbox :presetValue="filterSingleChecked(filterWindow.column, value)" :data="{label: value, disabled: false}" @inputChange="updateSingleFilter($event, filterWindow.column, value)"/>
    </div>
  </div>

</template>

<script>
import axios from "axios";
import NavBar from '@/components/NavBar.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import FileUploadElement from '@/components/inputs/FileUploadElement.vue'
import InputElement from '@/components/inputs/InputElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
import Button from '@/components/Button.vue'
export default {
  name: 'BewerberportalAdmin',
  components: {
    SelectElement,
    InputElement,
    Button,
    FileUploadElement,
    Checkbox,
    NavBar
  },
  data() {
    return {
      fileUploadElementData_emailAttachments: {label: "Upload Attachments", required: false, files: null},
      sendMailWithBescheid: false,
      selected: [],
      bewerber: [],
      entranceExams: [],
      selectElementData_term: {label: "term", required: true, tooltip: null, 0: "summer", 1: "winter"},
      selectElementData_bescheid: {label: "Bescheid", required: true, tooltip: null, 0: "Email Only", 1: "Admission", 2: "Rejection", 3: "Bestanden", 4: "Nicht Bestanden", 5: "Nicht Bestanden Nicht Teilgenommen"},
      selectElementData_bescheid_lang: {label: "Sprache", required: true, tooltip: null, 0: "de", 1: "en"},
      inputElementData_year: {label: "year", required: true, tooltip: null, type: "text"},
      inputElementData_exam_date: {label: "Exam Date", required: true, tooltip: null, type: "date"},
      inputElementData_exam_time: {label: "Exam Time", required: true, tooltip: null, type: "time"},
      inputElementData_vorsitz: {label: "Vorsitz", required: true, tooltip: null, type: "text"},
      inputElementData_zeichen: {label: "Zeichen", required: true, tooltip: null, type: "text"},
      inputElementData_mailSubject: {label: "Email Subject", required: true, tooltip: null, type: "text"},
      checkboxElementData_current: {label: "Current", disabled: false},
      checkboxElementData_sendMail: {label: "Send Email", disabled: false},
      buttonElementData_save: {loading: false, disabled: false, text: "Save"},
      buttonElementData_makeBescheid: {loading: false, disabled: false, text: "Make Bescheid"},

      checkboxElementData_allSelect: {label: "Select all", disabled: false, text: "Save"},
      checkboxElementData_rowSelect: {label: "", disabled: false, text: "Save"},

      // checkboxElementData_allSelect_filter: {label: "Select all", disabled: false, text: "Save"},
      // checkboxElementData_rowSelect_filter: {label: "", disabled: false, text: "Save"},

      filterWindow: {active: false, x: 0, y: 0, column: null},
      filters: {},


      bescheide: [
        {name: "Bescheid A"},
        {name: "Admission"},
      ],
      navTabs: [
        {text: 'List Exams', route: {name: 'Exams'}},
        {text: 'New Exam', route: {name: 'NewExam'}},
      ],      
    }
  },
  computed: {

    gridStyle() {
      const ncols = this.columns.length
      return {
        'display': 'grid',
        'grid-template-columns': `max-content repeat(${ncols}, minmax(max-content, auto))`
      }
    },
    columns() {
      if(this.bewerber===null || this.bewerber.length<=0) {
        return []
      }
      var columns = []
      for(var [key] of Object.entries(this.bewerber[0])) {
        console.log(key)
        columns.push(key)
      }
      return columns
    },

    values() {
      if(this.bewerber_filtered===null || this.bewerber_filtered.length<=0) {
        return []
      }
      var values = []
      this.bewerber_filtered.forEach(bewerber=>{
        var row_vals = {}
        this.columns.forEach(column=>{
          row_vals[column]=bewerber[column]
        })
        values.push(row_vals)
      })
      return values
    },

    selectElementData_entranceExam() {
      var data = {label: "Entrance Exam"}
      for(var i=0; i<this.entranceExams.length; i++) {
        data[`${this.entranceExams[i].id}`] = `${this.entranceExams[i].term} ${this.entranceExams[i].year} ${this.entranceExams[i].degree_de}`
      }
      return data
    },
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },
    bewerber_filtered() {
      if(this.bewerber===null || this.bewerber.length<=0) {
        return []
      }      
      var filtered = this.bewerber.filter(bewerber=>{
        var veto = false
        this.columns.forEach(column=>{
          if(column in this.filters && this.filters[column].includes(bewerber[column])) {
            veto = true
          }        
        })
        return !veto
      })
      if(filtered===null || filtered.length<=0) {
        return []
      }
      return filtered
    },
    allSelected() {
      var allIds = this.bewerber_filtered.map(bewerber=>{
        return bewerber.id
      })
      if(JSON.stringify(this.selected.slice().sort())===JSON.stringify(allIds.sort())) {
        return true
      }
      return false
    },
  },
  mounted() {
    this.getEntranceExams()
  },

  methods: {
    columnFiltered(column) {
      if(column in this.filters) {
        return true
      }
      return false
    },    
    makeBescheid() {
      const url = `${this.apiUrl}/bescheid`
      var formData = new FormData(this.$refs.form_make_bescheid);
      formData.append('bewerber_ids', JSON.stringify(this.selected))

      for(const pair of formData.entries()) {
        console.log(`${pair[0]}, ${pair[1]}`);
      }
      if(this.fileUploadElementData_emailAttachments.files!=null) {
        for(var i=0; i<this.fileUploadElementData_emailAttachments.files.length; i++) {
          formData.append('attachment_files[]', this.fileUploadElementData_emailAttachments.files[i])
        }
      }

      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(response=>{
        console.log(response.data)
      }).catch(error=>{
        console.log(error.response)
      })      
    },
    filterWindowValues(column) {
      return this.bewerber.map(bewerber=>{
        return bewerber[column]
      }).filter((value,index,self) => self.indexOf(value)==index)
    },    
    filterAllChecked(column) {
      if(column in this.filters) {
        return false
      }
      return true
    },   
    filterSingleChecked(column, value) {
      if(!(column in this.filters)) {
        return true
      }
      if(this.filters[column].includes(value)) {
        return false
      }
      return true
    },
    updateSingleFilter(checked, column, value) {
      if(checked && column in this.filters && this.filters[column].includes(value)) {
        var index = this.filters[column].indexOf(value)
        this.filters[column].splice(index, 1)      
      } else if(!checked && column in this.filters && !this.filters[column].includes(value)) {
        this.filters[column].push(value)
      } else if(!checked && !(column in this.filters)) {
        this.filters[column] = [value]
      }
      if(column in this.filters && this.filters[column].length==0) {
        delete this.filters[column]
      }
      this.updateSelectionByFilteredColumns()
    },
    updateAllFilter(checked, column) {
      if(checked && column in this.filters) {
        delete this.filters[column]
      } else if(!checked) {
        this.filters[column] = this.filterWindowValues(this.filterWindow.column)
      }
      this.updateSelectionByFilteredColumns()
    },
    openFilter(column, event) {
      this.filterWindow.column = column
      this.filterWindow.active = true
      this.filterWindow.x = event.layerX
      this.filterWindow.y = event.layerY
    },
    closeFilter() {
      this.filterWindow.active = false
      this.filterWindow.x = 0
      this.filterWindow.y = 0
    },
    clearSelection() {
      this.selected=[]
    },
    updateSelectionByFilteredColumns() {
      const visibleIds = this.bewerber_filtered.map(bewerber=>bewerber.id)
      this.selected = this.selected.filter(value => visibleIds.includes(value))
    },
    selectAll() {
      if(this.allSelected) {
        this.selected = []
        return
      }
      this.selected = this.bewerber_filtered.map(bewerber=>{
        return bewerber.id
      })
      console.log(this.selected.length)
    },
    selectOne(bewerber) {
      if(this.selected.includes(bewerber.id)) {
        var index = this.selected.indexOf(bewerber.id)
        this.selected.splice(index, 1)
        return
      }
      this.selected.push(bewerber.id)
    },
    getEntranceExams() {
      const url = `${this.apiUrl}/entrance_exam`
      axios({
        method: 'get',
        url: url,
      }).then(response=>{
        this.entranceExams = response.data
        console.log(response.data)
      }).catch(error=>{
        console.log(error.response)
      })
    },
    getBewerber(entranceExam) {
      this.clearSelection()
      this.bewerber = this.entranceExams.filter(exam => {
        return exam.id==entranceExam.id
      })[0].bewerber
      var bescheids = []
      this.bewerber.forEach(bewerber=>{
        bewerber.bescheid.forEach(bescheid=>{
          if(!(bescheids.includes(bescheid.name))) {
            bescheids.push(bescheid.name)
          }
        })
      })
      this.bewerber.forEach(bewerber=> {
        bescheids.forEach(bescheidName => {
          bewerber[bescheidName] = bewerber.bescheid.filter(bescheid=>{
            return bescheid.name==bescheidName
          }).length
        })  
        delete bewerber["bescheid"]      
      })
      // console.log(bescheids)
      console.log(this.bewerber)
    },
    postEntranceExam() {
      const url = `${this.apiUrl}/entrance_exam`
      var formData = new FormData(this.$refs.form_entrance_exam);
      for(const pair of formData.entries()) {
        console.log(`${pair[0]}, ${pair[1]}`);
      }      
      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(response=>{
        console.log(response.data)
      }).catch(error=>{
        console.log(error.response)
      })
    }
  }
}
</script>

<style lang="scss" scoped>
  .overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
  }
  .filter-window {
    z-index: 2;
    padding: 15px;
    border-radius: 2px;
    background-color: white;
    box-shadow: 0px 0px 6px 2px rgba(0,0,0,0.35);
    position: absolute;
    border: 1px solid black;
    .value-item {
      padding: 0 0 0 16px;
    }
  }
  .column-name {
    margin-right: 10px;
  }
  .filter {
    border: 1px solid black;
    cursor: pointer;
    margin-left: auto;
    &:hover {
      background-color: #e1e1e1;
    }
    &.active {
      background-color: #d1d1d1;
    }
  }
  .admin-bewerberportal {
    padding: 2px;
    width: 100%;
    position: relative;
    // border: 1px solid black;
  }
  .grid {
    padding: 15px 2px;
    overflow: hidden;
    .grid-row {
      display: contents;
    }

    .grid-item {
      padding: 5px;
      outline: 1px solid black;
      display: flex;
      justify-content: center;
      align-items: center;
    }
  }
  .make-bescheide {
    width: 720px;
  }
</style>