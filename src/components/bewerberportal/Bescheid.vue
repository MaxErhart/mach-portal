<template>
  <div class="bescheid" v-if="bescheid">
    <div class="bescheid-head">
      <h4 class="bescheid-date">
        {{bescheid.updated_at}}
      </h4> 
      <h4 class="bescheid-title">
        {{bescheid.name}}
      </h4> 
    </div>
   
    <div class="bescheid-body">
      Download File
      <a :href="bescheid.file?.url" download>
        <img class="download_pdf_img" src="@/assets/download_pdf.png">
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Bescheid',
  props: {
    bescheid: Object,
  },

}
</script>


<style scoped lang="scss">
.bescheid-head {
  width: 100%;
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  .bescheid-date {
    justify-self: flex-start;
  }
}
.bescheid {
  border-radius: 6px;
  margin: 5px;
  padding: 0 10px;
  border: 2px solid gray;
  display: flex;
  flex-direction: column;
  align-items: center;  
}
.download_pdf_img {
  height: 36px;
}
.bescheid-body {
  display: flex;
  flex-direction: column;
  align-items: center;  
  padding: 5px;  
  // border: 1px solid black;
  button {
    padding: 5px 10px 0 10px;
    border: 1px solid black;
    background-color: white;
    border-radius: 4px;
    &:hover {
      background-color: #f1f1f1;
    }
  }
}
</style>
