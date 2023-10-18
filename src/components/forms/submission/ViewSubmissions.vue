<template>
  <div class="view-submissions">

    <dialog ref="archive" class="archive-form">
      <h3>Archive selected submissions</h3>
      <div class="form-elements">
        <Checkbox label="Move to submissions to Archive / Remove submissions from Archive" @change="to_archive=$event" :presetValue="to_archive"/>
        <SelectElement :preset_value="to_archive_group" @change="changeArchiveGroup($event)" tooltip="Name by which archived submissions will be grouped" ref="archive_group" label="Archive group" :inputTypeable="true" :data="form?.archive_groups" :search="false"/>
        <Button text="Move Submissions" @click.prevent="archiveSelectedSubmissions()"/>
      </div>
    </dialog>

    <div class="view-options" v-if="form?.permission>=3">
      <button @click="openArchiveOptions()">Manage Archive</button>
    </div>

    <div class="archive">
      <h3>Archived submissions</h3>
      <div class="archive-submissions" v-for="key in form?.archive_groups" :key="key" :class="{open: key in open_archives}">
        <div class="archive-submissions-title" @click="openArchive(key,key in open_archives)">{{key}}</div>
        <div class="archive-submissions-body">
          <DataPlaceholder v-if="key in archive_loading && archive_loading[key]"/>
          <template v-else>
            <JSONToTable ref="table" :dataset_permission="form?.permission" :columns="columns" :data="archive_data(key)" :select="true" :col_filter="true" :rowmenu="rowmenu_archive" @option="handleOption($event)" @select="handleSelect($event)"/>
          </template>
          
        </div>
      </div>
    </div>

    <div class="submissions">
      <JSONToTable ref="table" :dataset_permission="form?.permission" :columns="columns" :data="data" :select="true" :col_filter="true" :rowmenu="rowmenu" @option="handleOption($event)" @select="handleSelect($event)"/>
    </div>
  </div>
</template>

<script>
import JSONToTable from '@/components/JSONToTable2.vue'
import DataPlaceholder from '@/components/DataPlaceholder.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import Button from '@/components/Button.vue'
import Checkbox from '@/components/inputs_23/Checkbox.vue'

export default {
  name: 'ViewSubmissions',



  components: {
    JSONToTable,
    DataPlaceholder,
    SelectElement,
    Button,
    Checkbox,
  },


props: {

    form: Object,

    // Submissions object contains all submissions related to the form
    // This includes all submissions of forms referenced by the form
    // The structure is {form_id_1: submissions_1, form_id2: submissions_2, ...}
    form_and_reference_submissions: Object,
  },


  data() {
    return {
      rowmenu: [
        {id: 0,icon: 'create-outline', text: 'View / Edit Submission', name: 'editSubmission', permission: 2, two_step: false, dataset_permission: 2},
        {id: 1,icon: 'copy-outline', text: 'Copy Submission', name: 'copySubmission', permission: 1, two_step: false, dataset_permission: 2},
        {id: 1,icon: 'lock-closed-outline', text: 'Archive', name: 'archiveSubmission', permission: 1, two_step: false, dataset_permission: 3},
        {id: 2,icon: 'trash-outline', text: 'Delete Submission', name: 'deleteSubmission', permission: 3, two_step: true, dataset_permission: 2},
      ],
      rowmenu_archive: [
        // {id: 0,icon: 'create-outline', text: 'View / Edit Submission', name: 'editSubmission', permission: 2, dataset_permission: 3},
        // {id: 1,icon: 'copy-outline', text: 'Copy Submission', name: 'copySubmission', permission: 1, twoStep: false, dataset_permission: 2},
        {id: 1,icon: 'lock-open-outline', text: 'Remove from Archive', name: 'dearchiveSubmission', permission: 1, twoStep: false, dataset_permission: 3},
        // {id: 2,icon: 'trash-outline', text: 'Delete Submission', name: 'deleteSubmission', permission: 3, twoStep: true, dataset_permission: 3},
      ],
      open_archives: {},
      archive_submissions: {},
      archive_loading: {},

      selected: [],
      to_archive: false,
      to_archive_group: null,
    }
  },


  mounted() {
    // console.log(this.submissions)
  },



  computed: {

    columns() {
      const columns = [
        {id:'owner_name',name:'owner.name',show:true,position:999},
        {id:'owner_email',name:'owner.email',show:true,position:999},
        {id:'owner_fieldOfStudyId',name:'owner.fieldOfStudyId',show:true,position:999},
        {id:'owner_fieldOfStudyText',name:'owner.fieldOfStudyText',show:true,position:999},
        {id:'owner_matriculationNumber',name:'owner.matriculationNumber',show:true,position:999},
      ]
      if(this.form?.no_login) {
        columns.push({id:'confirmed',name:'confirmed',show:true,position:998})
      }
      this.recursiveSortElements(this.elements).forEach((el,index)=>{
        columns.push({id:el.id,name:el.label,show:el.show,position:index})
      })
      return columns.sort((a,b)=>{
        if(a.position>b.position) {
          return 1
        }
        if(a.position==b.position) {
          return 0
        }
        return -1
      })
    },

    data() {
      if(!this.form_and_reference_submissions?.[this.form?.id] || !this.form?.form_elements?.[this.form.id]) {
        return []
      }
      const data = []
      this.form_and_reference_submissions?.[this.form?.id].forEach(submission=>{
        var row = {}
        if(submission.is_archived) {
          return

        }
        this.elements.forEach(el=>{
          var curr_sub = submission
          if(curr_sub.is_archived) {
            if(el.component=="DoubleInputElement") {
              row[el.id] = `<a href="${curr_sub.archive_data[el.id].url}">${curr_sub.archive_data[el.id].alias}</a>`
              return
            }
            row[el.id] = curr_sub.archive_data[el.id]
            return
          }
          for(var i=0;i<el.path.length;i++) {
            const ref_val = curr_sub._data[el.path[i].el_id]

            curr_sub = this.form_and_reference_submissions[el.path[i].form_id].find(sub=>sub.id==ref_val)
            if(ref_val===null || ref_val===undefined || curr_sub===null || curr_sub===undefined) {
              row[el.id] = null
              return
            }
            if(curr_sub.is_archived) {
              if(el.component=="DoubleInputElement") {
                row[el.id] = `<a href="${curr_sub.archive_data[el.id].url}">${curr_sub.archive_data[el.id].alias}</a>`
                return
              }
              row[el.id] = curr_sub.archive_data[el.id]
              return
            }
          }
          var value = curr_sub._data[el.id] 
          if(el.component=="SelectElement") {
            value = el.data.data.find(option=>option.id==curr_sub._data[el.id])?.name
          }
          if(el.component=="DoubleInputElement") {
            value = `<a href="${value.url}">${value.alias}</a>`
            // value = el.data.data.find(option=>option.id==curr_sub._data[el.id])?.name
          }
          row[el.id] = value
        })
        row['permission'] = submission.permission
        row['id'] = submission.id
        row['owner_name'] = submission.owner.name
        row['owner_email'] = submission.owner?.email
        row['owner_fieldOfStudyId'] = submission.owner?.fieldOfStudyId
        row['owner_fieldOfStudyText'] = submission.owner?.fieldOfStudyText
        row['owner_matriculationNumber'] = submission.owner?.matriculationNumber
        if(this.form.no_login) {
          row['confirmed'] = submission.confirmed
        }
        data.push(row)
      })
      console.log(data)
      return data
    },

    elements() {
      if(!this.form?.form_elements?.[this.form?.id]) {
        return []
      }
      var elements = []

      Object.keys(this.form.form_elements[this.form.id]).forEach(el_id=>{

        const el = this.form.form_elements[this.form.id][el_id]
        if(!el.input) {
          return
        }
        elements = elements.concat(this.getElementReferencesRecursively(this.form.form_elements,el))
      })
      return elements
    }

  },
  methods: {
    recursiveSortElements(elements) {
      const elements_sorted = elements.sort((a,b)=>{
        if(a.path.length<=0 && b.path.length<=0) {
          if(a.position>=b.position) {
            return 1
          }
          return -1
        }
        if(a.path.length<=0) {
          if(a.position>=b.path[0].position) {
            return 1
          }
          return -1
        }
        if(b.path.length<=0) {
          if(a.path[0].position>=b.position) {
            return 1
          }
          return -1
        }

        const l1 = a.path.length
        const l2 = b.path.length
        if(l1>l2) {
          for(var i=0; i<l2;i++) {
            if(a.path[i].position>b.path[i].position) {
              return 1
            }
            if(a.path[i].position<b.path[i].position) {
              return -1
            }
          }
          if(a.path[l2].position>=b.position) {
            return 1
          }
          return -1
        }
        if(l1<l2) {
          for(var j=0; j<l1;j++) {
            if(a.path[j].position>b.path[j].position) {
              return 1
            }
            if(a.path[j].position<b.path[j].position) {
              return -1
            }
          }
          if(b.path[l1].position>=a.position) {
            return -1
          }
          return 1
        }
        if(l1==l2) {
          for(var k=0; k<l1;k++) {
            if(a.path[k].position>b.path[k].position) {
              return 1
            }
            if(a.path[k].position<b.path[k].position) {
              return -1
            }
          }
        }
      })
      return elements_sorted
    },

    archive_data(key) {
      if(!(key in this.archive_submissions)) {
        return []
      }
      console.log(this.archive_submissions,key)
      return this.archive_submissions[key].map(submission=>{
        var row = JSON.parse(JSON.stringify(submission.archive_data))
        Object.keys(this.form.form_elements[this.form.id]).forEach(el_id=>{
          if(this.form.form_elements[this.form.id][el_id].component==="DoubleInputElement") {
            row[el_id] = submission.archive_data[el_id].url
          }
        })
        row['permission'] = submission.permission
        row['id'] = submission.id
        row['owner_name'] = submission.archive_owner.name
        return row
      })
    },

    openArchive(key,open) {
      if(!open) {
        this.getArchiveSubmissions(key)
      }
      if(key in this.open_archives) {
        delete this.open_archives[key]
        return
      }
      this.open_archives[key] = true
    },

    getElementReferencesRecursively(form_elements,element, path_prefix=[], prefix='') {
      if(!element) {
        return []
      }
      var label = prefix+element.data.label
      if(element.component!=="SelectReferenceElement") {
        if(element.component==="DoubleInputElement") {
          const ret_el = [{...element,label,path:path_prefix}]
          ret_el[0].label = element.data["label_url"]
          console.log(ret_el,element)
          return ret_el
        }
        if(element.data.label_short) {
          console.log(element)
          label=prefix+element.data.label_short
        }
        return [{...element,label,path:path_prefix}]
      }
      var data = []
      var path = JSON.parse(JSON.stringify(path_prefix))
      path.push({el_id:element.id,form_id:element.data.formId,position:element.position})
      element.data.formElementIds.forEach(id=>{
        data = data.concat(this.getElementReferencesRecursively(form_elements,form_elements[element.data.formId][id],path,label+'.'))
      })
      return data
    },

    highlight(submission) {
      const index = this.form_and_reference_submissions[this.form.id].map(form_submission=>form_submission.id).indexOf(submission.id)
      this.$refs.table.highlight(index)
    },

    handleSelect(selected) {
      this.selected = selected.rows.map(row=>row.id)
      console.log(this.selected)
    },

    async getArchiveSubmissions(key) {
      this.archive_loading[key] = true
      const {submissions, error} = await this.$store.dispatch('_archive', {method:'get',key,form_id:this.form.id})
      this.archive_submissions[key] = submissions
      console.log(submissions,error?.response)
      this.archive_loading[key] = false
    },

    async archiveSubmissions(object,key) {
      const formData = new FormData()
      formData.append('ids', JSON.stringify(object))
      formData.append('archive_group', key)
      formData.append('form_id', this.form.id)
      const {submissions,archive, error,archive_groups} = await this.$store.dispatch('_archive', {method:'post',form_id:this.form.id,formData})
      console.log(submissions,archive,error?.response,archive_groups)
      this.archive_submissions = archive
      this.$emit('archive', {submissions,archive_groups})
    },

    async dearchiveSubmissions(object) {
      const formData = new FormData()
      formData.append('ids', JSON.stringify(object))
      formData.append('form_id', this.form.id)
      const {submissions, archive, error,archive_groups} = await this.$store.dispatch('_archive', {method:'post',archive:false,form_id:this.form.id,formData})
      console.log(submissions,archive,error,archive_groups)
      this.archive_submissions = archive
      this.$emit('dearchive', {submissions,archive_groups})
    },

    handleOption({option,row}) {
      if(option.name=='editSubmission') {
        this.$emit("editSubmission",row.id)
      } else if(option.name=='deleteSubmission') {
        this.$emit("deleteSubmission", row.id)
      } else if(option.name=='copySubmission') {
        this.$emit("copySubmission", row.id)
      } else if(option.name=='archiveSubmission') {
        const obj = [row.id]
        this.archiveSubmissions(obj,'')
      } else if(option.name=='dearchiveSubmission') {
        const obj = [row.id]
        this.dearchiveSubmissions(obj)
      }
    },
    changeArchiveGroup(event) {
      this.to_archive_group=event
    },
    // 
    // --------------------------------
    // 
    archiveSelectedSubmissions() {
      if(this.to_archive) {
        this.archiveSubmissions(this.selected,this.to_archive_group)
      } else {
        this.dearchiveSubmissions(this.selected)
      }
      this.closeArchiveOptions()
    },
    closeArchiveOptions() {
      this.$refs.archive.close()
      this.to_archive=false
    },
    openArchiveOptions() {
      this.$refs.archive.showModal()
      this.$refs.archive_group.blur()
      this.$refs.archive.addEventListener("mousedown", e=>{
        const dialog_rect = this.$refs.archive.getBoundingClientRect()
        if(e.clientX < dialog_rect.left || e.clientX > dialog_rect.right || e.clientY < dialog_rect.top || e.clientY > dialog_rect.bottom) {
          this.closeArchiveOptions()
        }
      })
    },
  }

}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.view-submissions{
  display: flex;
  flex-direction:column;
  gap: 32px;
}
.archive {
  text-align: left;
  position: relative;
  .archive-submissions {
    position: relative;
    overflow: hidden;
    border: 1px solid #cccccc;
    z-index: 3;

    &.open {
      overflow: visible;
      outline: 2px solid black;
      border-radius: 4px;
      .archive-submissions-body {
        padding: 16px 0;
        height: 100%;
      }
    }
    .archive-submissions-title {
      position: relative;
      background-color: #dddddd;
      z-index: 1;
      padding: 8px;
      cursor: pointer;
      &:hover {
        background-color: #ccc;
      }
    }
    .archive-submissions-body {
      position: relative;
      z-index: 0;
      height: 0;
      padding: 0;
    }
  }
}

// 
// --------------------------------------------
// 
.view-options {
  text-align: left;
  button {
    padding: 16px;
    border: 1px solid black;
    cursor: pointer;
    &:hover {
      background-color: $text-dark;
      color: white; 
    }
  }
}





</style>
