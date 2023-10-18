<template>
  <div class="reply" v-if="reply">
    <div class="reply-footer">
      <div class="reply-id" v-if="index">Reply No. {{index}}</div>
      <div class="reply-name">{{reply.user.firstname}} {{reply.user.lastname}}</div>
      <div class="reply-email">{{reply.user.email}}</div>
      <div class="reply-date">{{reply.created_at}} Uhr</div>
    </div>

    <h4 class="reply-subject">{{reply.subject}}</h4>
    <div class="reply-content">{{reply.content}}</div>
    <div class="reply-attachments">
      <a v-for="file in reply.files" :key="file" target="_blank" :href="file.url" class="reply-attachment">
        <img class="preview" :src="require('@/assets/file.svg')">
        <div class="file-path">{{trimString(file.name)}}</div>
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Reply',
  props: {
    reply: Object,
    index: Number,
  },
  methods: {
    trimString(str) {
      if(str.length>20) {
        return `...${str.slice(-17)}`
      }
      return str
    }
  }

}
</script>


<style scoped lang="scss">
.reply {
  width: 100%;
  max-width: 210mm;
  border: 1px solid rgba(0,0,0,0.6);
  border-radius: 4px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 20px;
}
.reply-subject {
  margin: 0;
  width: 100%;
  text-align: start;
  padding: 4px 0;
  border-bottom: 1px solid black;
}
.reply-content {
  margin: 8px 0;
}
.reply-attachments {
  width: 100%;
  display: flex;
  flex-direction: row;
  gap: 10px 20px;
  margin: 20px 0 0 0;
  >.reply-attachment {
    max-width: 38mm;
    font-size: .9em;
    overflow-wrap: anywhere;
  }
}
.reply-footer {
  right: 0;
  display: flex;
  flex-direction: column;
  align-self: flex-end;
  align-items: flex-start;
  margin: 0px 0 10px 0;
  font-size: .7em;
}
</style>
