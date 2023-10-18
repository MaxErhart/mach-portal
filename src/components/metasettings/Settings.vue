<template>
  <form class="settings" ref="form" @submit.prevent="submit()">
    <section class="options-group">
      <h2 class="options-title">Portal Maintanance</h2>
      <p class="options-description">
        Website maintenance settings allow for control over the duration of maintenance, the ability to turn maintenance mode on and off, and the option to provide a description of the updates being made.
        These settings ensure smooth website maintenance and improved user experience.
      </p>
      <DataPlaceholder v-if="settingsLoading"/>
      <div class="options-container" v-else>
        <Checkbox :presetValue="maintenanceOn" label="Activate maintanance mode" name="maintenance_on"/>
        <InputElement :presetValue="maintenanceEnddate" label="Expected end of maintanance mode" type="date" name="maintenance_enddate" />
        <Textarea :presetValue="maintenanceMessage" label="Enter message:" name="maintenance_message"/>
      </div> 
    </section>
    <Button :loading="settingsLoading" text="Submit Settings"/>
  </form>
</template>

<script>
import Checkbox from '@/components/inputs/Checkbox.vue'
import InputElement from '@/components/inputs/InputElement.vue'
import Textarea from '@/components/inputs/Textarea.vue'
import Button from '@/components/Button.vue'
import axios from "axios";
import DataPlaceholder from '@/components/DataPlaceholder.vue'
export default {
  name: 'Settings',
  components: {
    Checkbox,
    InputElement,
    Textarea,
    Button,
    DataPlaceholder,
  },
  data() {
    return {
      settingsLoading: false,
      maintenanceOn: null,
      maintenanceEnddate: null,
      maintenanceMessage: null,
    }
  },
  mounted() {
    this.settingsLoading = true
    axios({
      method: 'get',
      url: `${this.$store.getters.getApiUrl}/meta`,
    }).then(response=>{
      this.maintenanceOn = response.data.maintenance_on
      this.maintenanceEnddate = response.data.maintenance_enddate
      this.maintenanceMessage = response.data.maintenance_message
      this.settingsLoading = false
    }).catch(()=>{
      this.settingsLoading = false
    })
  },
  methods: {
    submit() {
    this.settingsLoading = true
      const url = `${this.$store.getters.getApiUrl}/meta`
      var formData = new FormData(this.$refs.form);   

      axios({
        method: 'post',
        url: url,
        data: formData,
      }).then(response=>{
        this.settingsLoading = false
        this.maintenanceOn = response.data.maintenance_on
        this.maintenanceEnddate = response.data.maintenance_enddate
        this.maintenanceMessage = response.data.maintenance_message
        console.log(response.data)
      }).catch(error=>{
        this.settingsLoading = false
        console.log(error.response)
      })
    }
  }
}
</script>


<style scoped lang="scss">
.settings {
  width: 100%;
  max-width: 210mm;
  display: flex;
  flex-direction: column;
  gap: 20px;
  h2 {
    margin-top: 0;
  }
  .options-group {
    padding: 20px;
    border: 1px solid #ccc;
  }
}
</style>
