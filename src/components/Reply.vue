<template>
  <div class="reply">

    <div class="select-submissions">
      <div v-for="selectedSubmissionId in selectedSubmissionIds" :key="selectedSubmissionId">
        {{selectedSubmissionId}}
      </div>
    </div>
    <div class="reply-wrapper"></div>

    <section>
      <label for="color-input">Reply Message</label>
      <textarea id="color-input" v-model="replyMessage" rows="4" cols="50"/>
    </section>

    <button class="kit-button" @click="submitReply()">Submit</button>

  </div>
</template>

<script>
import axios from "axios";
export default {
  name: 'Reply',
  props: {
    formId: String,
    selectedSubmissionIds: Object,
  },
  data() {
    return {
      replyMessage: '',
    }
  },
  methods: {
    submitReply() {

      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/saveSubmissionReply.php',
        data: {mode: 'insert', formId: this.formId, submissionIds: this.selectedSubmissionIds, message: this.replyMessage}
      }).then(response => {
        console.log(response.data)
      })
    }
  }

}
</script>


<style scoped lang="scss">

</style>
