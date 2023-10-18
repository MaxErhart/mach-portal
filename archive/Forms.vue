<template>
  <div class="forms">
    <h1>Forms</h1>
    <div class="forms-content">
      <template v-if="awaitData">
        Awaiting Data
      </template>
      <template v-else>
        <IndexFormsSubmission v-if="method=='index'" :method="method" :forms="forms" @deleteEntry="deleteEntry($event)"/>
      </template>      
    </div>
  </div>
</template>

<script>
import IndexFormsSubmission from '@/components/forms/IndexFormsSubmission.vue'
import axios from "axios";
export default {
  name: 'Forms',
  components: {
    IndexFormsSubmission,
  },
  data() {
    return {
      forms: [],
      fetchedAllData: false,
      awaitData: false,
    }
  },
  mounted() {
    this.getForms();
  },
  computed: {
    apiUrl() {
      return this.$store.getters.getApiUrl;
    },
    method() {
      if(this.$route.meta.new && this.$route.params.id) {
        return 'update';
      }
      if(this.$route.meta.new) {
        return 'store';
      }
      return 'index';
    },    
  },
  methods: {
    getForms() {
      this.forms=[];
      this.fetchedAllData = true;
      this.awaitData = true;
      const url = `${this.apiUrl}/forms`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.forms = this.forms.concat(response.data);
        this.awaitData = false;
      })
    },       
  }
}
</script>

<style lang="scss" scoped>
.forms {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
}
.forms-content {
  width: 100%;
  background-color: #fff;
  box-shadow: rgba(0, 0, 0, 0.12) 2px 1px 3px, rgba(0, 0, 0, 0.24) 2px 1px 2px;
}
</style>