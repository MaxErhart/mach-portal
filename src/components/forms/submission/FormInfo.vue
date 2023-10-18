<template>
  <div class="form-info">
    <h4 v-if="!form?.info_subject">Informationen zum Formular</h4>
    <h4 v-else v-html="form.info_subject"></h4>
    <div class="body" v-if="!form?.info_body">
      <p>Das Formular/die Anmeldung/die Abfrage die Sie bearbeiten wollen, befindet sich unter dem Reiter „Submit Form“.
      Sie sehen nur Formulare für die Sie persönlich oder als Teil einer Gruppe berechtigt wurden.</p>
      <p>Unter dem Reiter „Submssion“ finden Sie Ihre Eingaben, die Sie dort bearbeiten oder löschen können und die Daten, die über Ihre Anmeldung via Shibboleth erhoben wurden. Die Summe der Daten dient der KIT-Fakultät für Maschinenbau für Sie tätig zu werden.</p>
    </div>
    <div class="body" v-else v-html="form.info_body"></div>
    <div v-if="!form?.info_footer">Bei Fragen wenden Sie sich gerne an <a href="mailto:portal@mach.kit.edu">portal@mach.kit.edu</a>.</div>
    <div v-else v-html="form.info_footer"></div>
  </div>
</template>

<script>
export default {
  name: 'FormInfo',
  props: {
    form: Object,
  }
}
</script>


<style scoped lang="scss">
.form-info {
  max-width: 210mm;
  margin: 0 auto;
}
.body {
  margin-bottom: 1rem;
}
</style>
