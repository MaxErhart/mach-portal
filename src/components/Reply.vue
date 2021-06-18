<template>
  <div class="reply">

    <div class="select-submissions">
      <div v-for="selectedSubmissionId in selectedSubmissionIds" :key="selectedSubmissionId">
        {{selectedSubmissionId}}
      </div>
    </div>
    <div class="reply-wrapper"></div>

    reply
  </div>
</template>

<script>
export default {
  name: 'Reply',
  props: {
    selectedSubmissionIds: Object,
  },
  data() {
    return {

    }
  },
  methods: {

  }

}
</script>


<style scoped lang="scss">

</style>
