<template>
  <div class="reply">
    Flag Selected Submissions
    <div class="select-submissions">
      <div v-for="selectedSubmissionId in selectedSubmissionIds" :key="selectedSubmissionId">
        {{selectedSubmissionId}}
      </div>
    </div>
    <div class="reply-wrapper"></div>
    <section>
      <label for="color-input">Pick Flag Color</label>
      <input id="color-input" v-model="submissionFlag" type="color">
    </section>

    <section>
      <label for="hover-text-input">Pick Hover Text</label>
      <input id="hover-text-input" v-model="flagHoverText" type="text">
    </section>

    <section>
      <label for="remove-flag-input">Remove Flag</label>
      <input id="remove-flag-input" :checked="removeFlag" @change="removeFlag=$event.target.checked" type="checkbox">
    </section>

    <button class="kit-button" @click="submitFlags()">Submit</button>
    
  </div>
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
      submissionFlag: null,
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

</style>