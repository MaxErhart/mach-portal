<template>
  <div class="reply">
    <div class="reply-header" v-if="from">
      <span>Reply from {{from}}</span>
      <span class="reply-date-span">{{reply.replyDate}}</span>
    </div>    
    <div class="reply-header" v-else>
      <span>Reply from {{reply.firstname}} {{reply.lastname}}</span>
      <span class="reply-date-span">{{reply.replyDate}}</span>
    </div>
    <div class="reply-message">
      {{reply.replyMessage}}
    </div>
    <div class="reply-file" v-if="reply.attachedFilePath">
      <label>Attached File</label>
      <a :href="reply.attachedFilePath">{{reply.attachedFilePath.split("/").pop().split("_").pop()}}</a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DisplaySubmissionReply',
  props: {
    reply: Object,
    from: String,
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
.reply {
  width: 100%;
  // border: 1px solid #2c3e50;
  border-radius: 2px;
  padding: 10px;
  margin: 10px 0 10px 0;
  background-color: #eaeaea;
}
.reply-header {
  width: 100%;
  font-weight: 600;
  border-bottom: 1px solid black;
  margin: 6px 0;
  display: flex;
  flex-direction: row;
}
.reply-message {
  width: 100%;
  margin: 15px 0 15px 0;
}
.reply-date-span {
  margin-left: auto;
}
.reply-file {
  display: flex;
  flex-direction: column;
  > label{
    text-decoration: underline;
  }
}
</style>
