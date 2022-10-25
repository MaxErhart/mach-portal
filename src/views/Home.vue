<template>
  <div class="home">
    <div id="content">
      <h1>Herzlich Willkommen im MACH Portal!</h1>
      <div>Das Portal steht Studierenden und Mitarbeitenden der KIT-Fakultät für Maschinenbau zur Verfügung um Anmeldungen, Anträge, Abfragen und Informationsaustausch rund um Studium und Lehre zu erleichtern.</div>
      <div style="margin-top: 18px;">Stand 12.10.2022</div>
    </div>
  </div>
</template>

<script>
// import axios from "axios";
export default {
  name: 'Home',
  components: {
  },
}
</script>

<style lang="scss" scoped>
  .home {
    color: #2c3e50;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  #content {
    text-align: center;
    max-width: 210mm;
  }
</style>