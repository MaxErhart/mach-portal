<template>
  <form id="reply">

    <div class="select-submissions">
      <div v-for="selectedSubmissionId in selectedSubmissionIds" :key="selectedSubmissionId">
        {{selectedSubmissionId}}
      </div>
    </div>
    <div class="reply-wrapper"></div>

    <section>
      <label for="message-input">Reply Message</label>
      <textarea id="message-input" name="replyMessage" v-model="replyMessage" rows="4" cols="50"/>
    </section>

    <section>
      <label for="file-input">Attach File</label>
      <input id="file-input" name="replyFile" type="file">
    </section>

    <button class="kit-button" @click.prevent="submitReply()">Submit</button>

  </form>
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
      var formData = new FormData(document.getElementById("reply"))
      formData.append('mode', 'insert')
      formData.append('formId', this.formId)
      formData.append('submissionIds', JSON.stringify(this.selectedSubmissionIds))
      axios.post('https://www-3.mach.kit.edu/api/saveSubmissionReply.php',
        formData,
        {
          headers: {
              'Content-Type': 'multipart/form-data'
          }           
        }
      ).then(response => {
        console.log(response.data)
      })
    }
  }

}
</script>


<style scoped lang="scss">

</style>
