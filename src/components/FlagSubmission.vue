<template>
  <div class="flag-submission" v-if="selectedSubmissionIds.length > 0">
    Flag Selected Submissions
    <div class="select-submissions">
      <div v-for="selectedSubmissionId in selectedSubmissionIds" :key="selectedSubmissionId">
        {{selectedSubmissionId}}
      </div>
    </div>
    <section>
      <label for="remove-flag-input">Remove Flag</label>
      <input id="remove-flag-input" :checked="removeFlag" @change="removeFlag=$event.target.checked" type="checkbox">
    </section>

    <section>
      <label for="color-input">Pick Flag Color</label>
      <input id="color-input" v-model="submissionFlag" type="color" :disabled="removeFlag">
    </section>

    <section>
      <label for="hover-text-input">Pick Hover Text</label>
      <input id="hover-text-input" v-model="flagHoverText" type="text" :disabled="removeFlag">
    </section>
 
    <button class="kit-button flag-submit" @click="submitFlags()">Submit</button>
    
  </div> 

  <div v-else>Es wurde keine Submission zur Bearbeitung ausgewählt</div>

</template>

<script>
import axios from "axios";
export default {
  name: 'FlagSubmission',
  props: {
    selectedSubmissionIds: Object,
  },
  data() {
    return {
      submissionFlag: '#FF0000',
      flagHoverText: null,
      removeFlag: false,
    }
  },
  methods: {
    submitFlags() {
      console.log(this.removeFlag)
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/saveSubmissionFlags.php',
        data: {mode: 'insert', removeFlag: this.removeFlag, submissionFlag: this.submissionFlag, flagHoverText: this.flagHoverText, submissionIds: this.selectedSubmissionIds}
      }).then(response => {
        console.log(response.data)
      })
    }
  }

}
</script>


<style scoped lang="scss">
.flag-submission {
  width: 100%;
  max-width: 740px;
  padding: 20px 10px;  
}
.select-submissions{
  margin:0 0 5px 0;
}
section{
  display:flex; 
  flex-direction: column;
  margin:2.5px 0 2.5px 0;
  justify-content: flex-start;
  align-items:flex-start;
}
.flag-submit{
  margin:5px 0 0 0;
}
input {
  user-select: auto;
  display: block;
  width: 100%;
  height: 40px;
  font-size: 16px;
  border: 1px solid #ccc;
  padding: 15px;
}
label {
  display: flex;
  align-items: center;
  margin: 2px 0;
  text-align: start;
  width: 100%;
}
#remove-flag-input {
  width: 40px;
  height: 40px;
  margin: 0;
}
#color-input {
  padding: 6px 15px 6px 15px ;
}
</style>