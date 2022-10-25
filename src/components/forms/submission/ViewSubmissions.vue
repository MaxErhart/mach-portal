<template>
  <template v-if="submissions!=null && form!=null">
    
    <div class="view-submissions">
     
      <div class="view-submissions-body" :style="gridStyle" ref="body">
        <JSONToTable :rowMenu="submissionOptions" :data="submissionsTransformed" :columnSettings="columnSettings" :itemClickable="false" @menuItemClicked="submissionMenuItemClicked($event)"/>
      
      </div>
    </div>
  </template>
  <form id="file-download" v-if="hasSubmissions" method="get" :action="`https://www-3.mach.kit.edu/dfiles/submissions/${submissions[0]['export']}`">
    <button type="submit">Download</button>
  </form>    
</template>

<script>
// import IconButton from '@/components/IconButton.vue'
import JSONToTable from '@/components/JSONToTable.vue'
export default {
  name: 'ViewSubmissions',
  components:  {
    // IconButton,
    JSONToTable,
  },
  emits: ['editSubmission', 'deleteSubmission'],
  props: {
    submissions: Object,
    form: Object,
    showUser: {
      default: true,
      type: Boolean,
    },
  },
  data() {
    return {
      submissionOptions: [
        {icon: 'cogwheel', text: 'Edit Submission', width: 24, height: 24, name: 'editSubmission'},
        {icon: 'trash', text: 'Delete Submission', width: 24, height: 24, name: 'deleteSubmission'},
      ],
      activeRow: {index: null, top: null, optionsHeight: 80},
      bodyRect: {top: null},

    }
  },

  mounted() {
    // console.log(this.$myGlobalVariable(this.submissions[0]))
    
  },
  computed: {
    columnSettings() {
      return {
        type: 'blacklist',
        order: this.inputElements ? this.inputElements.map(e=>e.data.label):null,
        items: [
          'permission','form.submissions','form.created_at',
          'form.updated_at','form.no_login','form.display',
          'export','user.id','id',
          'user_id','form_id','read',
          'edit','form_elements','form.form_elements',
          'form.name','form.deadline','form.multiple_submissions',
          'form.public','form.creator_id','form.id',
          'user.affiliation','created_at', 'updated_at',
          'user.created_at', 'user.updated_at', 'user.groups',
          'form.email_element_id','user.email_verified_at'
        ],
      }
    },
    submissionsTransformed() {
      return this.submissions.map(submission=>{
        var subContainer = this.$myGlobalVariable(submission)
        var submissionData = submission.form_elements.filter(formElement=>{
          return this.inputElements.map(el=>el.id).includes(formElement.id)
        })
        submissionData.forEach(data=>{
          if(!data.data.show) {
            return
          }
          if(data.data.columnName) {
            subContainer[data.data.columnName] = data.pivot.data
          } else {
            subContainer[data.data.label] = data.pivot.data
          }
        })
        return subContainer
      })
    },
    hasSubmissions() {
      if(this.submissions!==null && this.submissions.length>0) {
        return true
      }
      return false
    },
    inputElements() {
      return this.form.form_elements.filter(e=>{
        return (['InputElement', 'FileUploadElement', 'SelectElement', 'Checkbox', 'SelectReferenceElement'].includes(e.component) && e.data.show)
      })
    },
    gridStyle() {
      const ncols = this.headers.length-1
      return {
        'display': 'grid',
        // 'grid-template-columns': `minmax(20px, 120px) repeat(${ncols}, minmax(200px, auto))`
        'grid-template-columns': `max-content repeat(${ncols}, minmax(max-content, auto))`
      }
    },
    rowStyle() {
      return {
        'top': `${this.activeRow.optionsHeight}px`,
      }
    },
    headers() {
      var headers = []
      if(this.showUser && this.form.no_login==0) {
        headers.push({name: 'Username', id: 'username'})
        headers.push({name: 'Email', id: 'useremail'})
      }      
    
      this.inputElements.forEach(e=>{
        headers.push({name: e.data.label, id: e.id})
      })

      return headers
    },
    optionsStyle() {
      return {
        'top': `${this.activeRow.top}px`,
        'height': `${this.activeRow.optionsHeight}px`,
        'width': `${this.$refs.body.scrollWidth}px`,
        'border-bottom': '1px solid black',
        'border-left': '1px solid black',
        'border-right': '1px solid black',
        'z-index': 0,
      }
    }
  },
  methods: {
    submissionMenuItemClicked(event) {
      if(event.name=='editSubmission') {
        this.editSubmission(event.index)
      } else if(event.name=='deleteSubmission') {
        this.deleteSubmission(event.index)
      }
    },
    getData(submission) {
      var data = []
      this.headers.forEach(h=>{
        if(h.id=='username') {
          data.push(submission.user.lastname)
        } else if(h.id=='useremail') {
          data.push(submission.user.email)
        } else {
          data.push(submission.form_elements.find(e=>e.id==h.id).pivot.data)
        }
      })
      return data
    },
    open(event, index) {
      var correction = 0
      if(this.bodyRect.top==null) {
        this.bodyRect.top = this.$refs.body.getBoundingClientRect().top
      }
      if(index>this.activeRow.index && this.activeRow.index!=null) {
        correction = -this.activeRow.optionsHeight
      }
      this.indexOpen = index
      const top = event.target.getBoundingClientRect().top + event.target.getBoundingClientRect().height - this.bodyRect.top + correction
      this.activeRow.index = index
      this.activeRow.top = top

    },
    close() {
      this.activeRow = {index: null, top: null, optionsHeight: 80}
    },
    deleteSubmission(index) {
      this.close()
      const id = this.submissions[index].id
      this.$emit("deleteSubmission", {id: id, index: index})
    },
    editSubmission(index) {
      const id = this.submissions[index].id
      this.$emit("editSubmission", {id: id, index: index})
    },
  },

}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';

#file-download {
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
}
.view-submissions {
  position: relative;
  width: 100%;
  height: 100%;
}
.view-submissions-body {
  position: relative;
}
.submissions-columnames {
  position: relative;
  width: 100%;
  text-align: center;
  background-color: $kit_green;
  color: $text_light;
  padding: 8px 18px;
}
.submission-row {
  position: relative;
  display: contents;
  :first-child {
    &.active {
      border-top-left-radius: 4px;
      border-left: 1px solid black;
    }
  }
  :last-child {
    &.active {
      border-top-right-radius: 4px;
      border-right: 1px solid black;
    }
  }
  &:nth-child(2n) {
    > * {
      background-color: #eee;
    }
  }
  &:nth-child(2n+1) {
    > * {
      background-color: #f9f9f9;
    }
  }  
  &:hover {
    > * {
      cursor: pointer;
    }
  }
}
.submission-item {
  position: relative;
  padding: 8px;
  top: 0;
  transition: top .4s ease;
  z-index: 1;
  &.active {
    border-top: 1px solid black;
  }

}
.row-options {
  padding: 5px;
  position: absolute;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  // width: 100%;
  z-index: -1;
  height: 0;
  transition: height .4s ease;
  box-shadow: 0px 0px 4px 0px inset rgba(0, 0, 0, 0.2);
  overflow: hidden;
  border-bottom-right-radius: 4px;
  border-bottom-left-radius: 4px;
  padding: 4px 0;
  > * {
    padding: 4px;
    width: 100%;
  }
}
</style>
