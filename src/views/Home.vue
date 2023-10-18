<template>
  <div class="home">
    <div id="content">
      <h2>Herzlich Willkommen im MACH-Portal!</h2>
      <div>Das Portal steht Studierenden und Mitarbeitenden der KIT-Fakultät für Maschinenbau zur Verfügung um Anmeldungen, Anträge, Abfragen und Informationsaustausch rund um Studium und Lehre zu erleichtern.</div>
      <div style="margin-top: 18px;">Stand 20.06.2023</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Home',
  components: {
  },
}
</script>

<style lang="scss" scoped>
@import 'D:\\inetpub\\MPortal\\src\\_variables';

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