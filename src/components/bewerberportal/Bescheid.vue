<template>
  <div class="bescheid">
    <h4 class="bescheid-title">
      {{name}}
    </h4>    
    <div class="bescheid-body">
      Download Bescheid
      <a :href="path" download>
        <img class="download_pdf_img" src="@/assets/download_pdf.png">
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Bescheid',
  props: {
    name: String,
    file_pdf: String,
  },
  computed: {
    path() {
      console.log(this.file_pdf.split('\\')[this.file_pdf.split('\\').length-1])
      return `https://www-3.mach.kit.edu/dfiles/bescheide/${this.file_pdf.split('\\')[this.file_pdf.split('\\').length-1]}`
    }
  }
}
</script>


<style scoped lang="scss">
.bescheid {
  border-radius: 6px;
  margin: 5px;  
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
