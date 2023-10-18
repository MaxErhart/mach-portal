<template>
  <div>
    <div class="admin-bewerberportal">
      <h1>Admin Bewerberportal</h1>
      <NavBar :tabs="navTabs"/>

      <template v-if="!$route.meta.new">
        <SelectElement label="Entrance exam" :required="true" :data="selectElementData_entranceExam" @selectedEntry="getBewerber($event)"/>

        <JSONToTable :col_filter="true" :select="true" :loading="false" :columns="bewerber_columns" :data="values" @select="updateSelected($event)"/>
        <div class="make-bescheide">
          <h4>Make Bescheide</h4>
          <form ref="form_make_bescheid" @submit.prevent="makeBescheid()">
            <SelectElement label="Bescheid" :nameAsValue="true" :required="true" :data="selectElementData_bescheid" name="name"/>
            <!-- <SelectElement label="Sprache" :required="true" :data="selectElementData_bescheid_lang" name="lang"/> -->
            <Checkbox :presetValue="sendMailWithBescheid" label="Send mail" @inputChange="sendMailWithBescheid=$event" name="mail"/>
            <template v-if="sendMailWithBescheid">
              <InputElement label="Email subject" name="mail_subject" ref="mail_subject"/>
              <FileUploadElement :label="fileUploadElementData_emailAttachments.label" name="attachment_files" @fileChange="fileUploadElementData_emailAttachments.files = $event"/>
              <textarea class="mail_content" name="mail_content" ref="mail_content"/>

              <SelectElement :search="false" label="Select mail template" :required="true" :data="selectElementData_mail_templates" @selectedEntry="mail_template=$event"/>
              <Button v-if="mail_template" text="Use Template" @click.prevent="useTemplate(mail_template)"/>
              <div v-if="mail_template?.id===0" v-html="admission_html" ref="admission_html"></div>
              <div v-if="mail_template?.id===1" v-html="rejection_html" ref="rejection_html"></div>
              <div v-if="mail_template?.id===2" v-html="exam_info_html" ref="exam_info_html"></div>
              <div v-if="mail_template?.id===3" v-html="bestanden_html" ref="bestanden_html"></div>
              <div v-if="mail_template?.id===4" v-html="nicht_bestanden_html" ref="nicht_bestanden_html"></div>
              <div v-if="mail_template?.id===5" v-html="nicht_teilgenommen_html" ref="nicht_teilgenommen_html"></div>
              

              <h3>Use placeholders:</h3>
              <div class="placeholder-list">              
                <div class="placeholder" v-for="placeholder in placeholders" :key="placeholder">
                  <span class="placeholder-token">{{placeholder.token}}</span>
                  <span class="placeholder-description">{{placeholder.description}}</span>
                </div>


              </div>
            </template>
            <Button ref="submitBescheid" :loading="buttonElementData_makeBescheid.loading" :disabled="buttonElementData_makeBescheid.disabled" :text="buttonElementData_makeBescheid.text" />
          </form>
        </div>
      </template>
      <template v-else>
        <form ref="form_entrance_exam" @submit.prevent="postEntranceExam()">
          <SelectElement label="term" :required="true" :data="selectElementData_term" name="term"/>
          <InputElement label="year" :required="true" name="year"/>
          <InputElement  label="exam date" :required="true" name="exam_date"/>
          <InputElement  label="exam time" :required="true" name="exam_time"/>
          <InputElement  label="Vorsitz" :required="true" name="vorsitz"/>
          <InputElement  label="Zeichen" :required="true" name="zeichen"/>
          <Checkbox label="current" name="current"/>
          <Button :loading="buttonElementData_save.loading" :disabled="buttonElementData_save.disabled" :text="buttonElementData_save.text" />
        </form>
      </template>




    </div>
    <div class="overlay" v-if="filterWindow.active" @click="closeFilter()"></div>
    <div class="filter-window" v-if="filterWindow.active" :style="{'left': `${filterWindow.x}px`, 'top': `${filterWindow.y}px`}">
      <Checkbox :presetValue="filterAllChecked(filterWindow.column)" label="Select all" @inputChange="updateAllFilter($event, filterWindow.column)"/>
      <div class="value-item" v-for="value in filterWindowValues(filterWindow.column)" :key="value">
        <Checkbox :presetValue="filterSingleChecked(filterWindow.column, value)" :label="value" @inputChange="updateSingleFilter($event, filterWindow.column, value)"/>
      </div>
    </div>


    <!-- <template v-html="admission_html"/> -->
    <!-- {{admission_html}} -->
    <!-- <div v-html="admission_html" ref="template"></div> -->
    <!-- <div >Test</div> -->
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
import JSONToTable from '@/components/JSONToTable2.vue'
export default {
  name: 'BewerberportalAdmin',
  components: {
    SelectElement,
    JSONToTable,
    InputElement,
    Button,
    FileUploadElement,
    Checkbox,
    NavBar
  },
  props: {
    user: Object,
  },
  data() {
    return {
      placeholders: [
        {token:'%NAME%',description: 'Lastname'},
        {token:'%VORNAME%',description: 'Firstname'},
        {token:'%EMAIL%',description: 'Email address'},
        {token:'%ANREDE_DE%',description: 'Form of address german (e.g. Herr XY)'},
        {token:'%ANREDE_EN%',description: 'Form of address english (e.g. Mr XY)'},
        {token:'%BEWERBUNGSNUMMER%',description: 'Application number'},
        {token:'%DEGREE_DE%',description: 'Title of degree in german'},
        {token:'%DEGREE_EN%',description: 'Title of degree in english'},
        {token:'%ILIAS%',description: 'ILIAS registration code'},
        {token:'%EXAM_DATE_DE%',description: 'Date of exam german day.month.year format'},
        {token:'%EXAM_DATE_EN%',description: 'Date of exam american month.day.year format'},
        {token:'%EXAM_TIME%',description: 'Time of exam'},
        {token:'%EMAIL_NO_DOMAIN%',description: 'Email address without domain (e.g. @...)'},
        {token:'%FIRSTNAME_NO_SPACES%',description: 'Firstname without spaces'},
      ],
      bewerberColumns: ["id","Form of address","Last name", "First name","Date of birth","Number","ZIP", "Town","Country","Email","Name (en)","ILIAS","ergebnis","entrance_exam_registration_changed","entrance_exam_registered","State","Reason of rejection",
        "Admission",
        "Rejection",
        "Bestanden",
        "Nicht Bestanden",
        "Nicht Bestanden Nicht Teilgenommen",
        "data_protection",
        "last_login",
      ],
      fileUploadElementData_emailAttachments: {label: "Upload Attachments", required: false, files: null},
      sendMailWithBescheid: false,
      selected: [],
      bewerber: [],
      mail_template:null,
      entranceExams: [],
      selectElementData_term: [{id: 0, name: "summer"}, {id: 1, name: "winter"}],
      selectElementData_bescheid: [{id: 0, name: "Email Only"}, {id: 1, name: "Admission"}, {id: 2, name: "Rejection"}, {id: 3, name: "Bestanden"}, {id: 4, name: "Nicht Bestanden"}, {id: 5, name: "Nicht Bestanden Nicht Teilgenommen"}],
      // selectElementData_bescheid_lang: [{id: 0, name: "de"},{id: 1, name: "en"}],
      selectElementData_mail_templates: [{id:0,name:"Admission"},{id:1,name:"Rejection"},{id:2,name:"Exam Info"},{id:3,name:"Bestanden"},{id:4,name:"Nicht Bestanden"},{id:5,name:"Nicht Bestanden Nicht Teilgenommen"}],
      inputElementData_exam_date: {label: "Exam Date", required: true, tooltip: null, type: "date"},
      inputElementData_exam_time: {label: "Exam Time", required: true, tooltip: null, type: "time"},
      inputElementData_vorsitz: {label: "Vorsitz", required: true, tooltip: null, type: "text"},
      inputElementData_zeichen: {label: "Zeichen", required: true, tooltip: null, type: "text"},
      checkboxElementData_current: {label: "Current", disabled: false},
      buttonElementData_save: {loading: false, disabled: false, text: "Save"},
      buttonElementData_makeBescheid: {loading: false, disabled: false, text: "Make Bescheid"},

      filterWindow: {active: false, x: 0, y: 0, column: null},
      filters: {},


      bescheide: [
        {name: "Bescheid A"},
        {name: "Admission"},
      ],
      navTabs: [
        {id: 0,text: 'List Exams', route: {name: 'Exams'}},
        {id: 1,text: 'New Exam', route: {name: 'NewExam'}},
      ],
      admission_html:null,
      rejection_html:null,
      exam_info_html:null,
    }
  },
  computed: {
    bewerber_columns() {
      return this.bewerberColumns.map(col=>{
        return {id:col,name:col}
      })
    },
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
      var data = []
      for(var i=0; i<this.entranceExams.length; i++) {
        data.push({
          id: this.entranceExams[i].id,
          name: `${this.entranceExams[i].term} ${this.entranceExams[i].year} ${this.entranceExams[i].degree_de}`
        })
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
  },
  async mounted() {
    this.getEntranceExams()
    var result = await axios.get('https://www-3.mach.kit.edu/mail_templates/applicant_admission.html')
    this.admission_html = result.data
    result = await axios.get('https://www-3.mach.kit.edu/mail_templates/applicant_rejection.html')
    this.rejection_html = result.data
    result = await axios.get("https://www-3.mach.kit.edu/mail_templates/KIT Master's entrance examination - information about online examination and registration codes.html")
    this.exam_info_html = result.data
    result = await axios.get("https://www-3.mach.kit.edu/mail_templates/KIT Master's entrance examination - entrance examination passed.html")
    this.bestanden_html = result.data
    result = await axios.get("https://www-3.mach.kit.edu/mail_templates/KIT Master's entrance examination - entrance examination not passed.html")
    this.nicht_bestanden_html = result.data
    result = await axios.get("https://www-3.mach.kit.edu/mail_templates/KIT Master's entrance examination - entrance examination not attended.html")
    this.nicht_teilgenommen_html = result.data
  },

  methods: {
    useTemplate(template) {
      if(template.id===0) {
        this.$refs.mail_content.value = this.$refs.admission_html.innerHTML
        this.$refs.mail_subject.value = "Admission Subject"
      } else if(template.id===1) {
        this.$refs.mail_content.value = this.$refs.rejection_html.innerHTML
        this.$refs.mail_subject.value = "Rejection Subject"
      } else if(template.id===2) {
        this.$refs.mail_content.value = this.$refs.exam_info_html.innerHTML
        this.$refs.mail_subject.value = "KIT Master's entrance examination - information about online examination and registration codes"
      } else if(template.id===3) {
        this.$refs.mail_content.value = this.$refs.bestanden_html.innerHTML
        this.$refs.mail_subject.value = "KIT Master's entrance examination - entrance examination passed"
      } else if(template.id===4) {
        this.$refs.mail_content.value = this.$refs.nicht_bestanden_html.innerHTML
        this.$refs.mail_subject.value = "KIT Master's entrance examination - entrance examination not passed"
      } else if(template.id===5) {
        this.$refs.mail_content.value = this.$refs.nicht_teilgenommen_html.innerHTML
        this.$refs.mail_subject.value = "KIT Master's entrance examination - entrance examination not attended"
      }
    },
    columnFiltered(column) {
      if(column in this.filters) {
        return true
      }
      return false
    },    
    makeBescheid() {
      const url = `${this.apiUrl}/bescheid/bewerber`
      var formData = new FormData(this.$refs.form_make_bescheid);
      formData.append('bewerber_ids', JSON.stringify(this.selected.map(bewerber=>bewerber.id)))

      for(const pair of formData.entries()) {
        console.log(`${pair[0]}, ${pair[1]}`);
      }
      if(this.fileUploadElementData_emailAttachments.files!=null) {
        for(var i=0; i<this.fileUploadElementData_emailAttachments.files.length; i++) {
          console.log(this.fileUploadElementData_emailAttachments.files[i])
          formData.append('attachment_files[]', this.fileUploadElementData_emailAttachments.files[i].file)
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
        this.$refs.submitBescheid.success = true
        console.log(response.data)
      }).catch(error=>{
        console.log(error?.response)
        this.$refs.submitBescheid.error = true
        this.emitter.emit('showErrorMessage', {error: error.response, action: 'Make bescheid', redirect: null})
        // console.log(error.response)
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
    updateSelected(selected) {
      this.selected=selected.rows
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
      this.selected = []
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
.mail_content {
  width: 100%;
  height: 256px;
}
.placeholder-list {
  display: flex;
  flex-direction: column;
  align-items: left;
  display: grid;
  grid-template-columns: max-content auto;
  column-gap: 20px;
  .placeholder {
    display: contents;
  }
}
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
  // .table {
  //   position: relative;
  //   z-index: 1;
  // } 
  .make-bescheide {
    position: relative;
    z-index: 0;
    width: 720px;
  }
</style>