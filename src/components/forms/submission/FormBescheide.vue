<template>
  <div class="form-bescheide" v-if="!loading">
    <h3>Bescheide</h3>
    <div class="list" v-for="group in groups" :key="group">
      <h4>Bescheide for group {{group}}</h4>
      <div class="bescheid" v-for="(bescheide_common,common) in bescheide[group]" :key="common" @click="openBescheid(common)" >
        <div class="common-val">
          <span>{{common}}</span>
          <ion-icon class="open-icon"  v-if="common===open_bescheid_index" name="chevron-up"></ion-icon>
          <ion-icon class="open-icon" v-else name="chevron-down"></ion-icon>
        </div>
        <div class="bescheid-options" v-if="common===open_bescheid_index" :class="{open:common===open_bescheid_index}">
          <div>Download Bescheide:</div>
          <a :href="bescheid.url" target="_blank" v-for="bescheid in bescheide_common" :key="bescheid">
            <!-- <ion-icon class="doc-icon" name="document-outline"></ion-icon> <span>{{bescheid.common_val}}</span> -->
            <ion-icon class="doc-icon" name="document-outline"></ion-icon> <span>{{bescheid.name}}</span>
          </a>
        </div>
      </div>
    </div>
  </div>
  <DataPlaceholder v-else/>
</template>

<script>
import DataPlaceholder from '@/components/DataPlaceholder.vue'
import axios from "axios";
export default {
  name: 'FormBescheide',
  components: {
    DataPlaceholder,
  },
  props: {
  },
  data() {
    return {
      open_bescheid_index: null,
      bescheide: null,
      loading: false,
      groups: [],
    }
  },
  mounted() {
    if(!this.bescheide) {
      this.getBescheide()
    }
  },
  methods: {
    getBescheide() {
      const url = this.$store.getters.getApiUrl+`/bescheid?form_id=${this.$route.params.id}&bescheid_source=form_groupedby`
      this.loading = true
      axios({
        method: 'get',
        url: url,
      }).then(response=>{
        this.bescheide = response.data
        this.groups = Object.keys(this.bescheide)
        this.loading = false
        console.log(response.data)
      }).catch(error=>{
        this.loading = false
        console.log(error, error?.response)
      })
    },
    openBescheid(index) {
      if(this.open_bescheid_index===index) {
        this.open_bescheid_index=null
        return
      }
      this.open_bescheid_index=index
    }
  }
}
</script>


<style scoped lang="scss">
.form-bescheide {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.list {
  width: 210mm;
  padding: 16px 0px;

  h4 {
    text-align: left;
    padding: 0 8px;
  }
  .bescheid {
    .bescheid-options {
      display: flex;
      flex-direction: column;
      gap: 8px;
      padding: 8px;
    }
    a {
      display: flex;
      gap: 4px;
      flex-direction: row;
      align-items: center;
      &:hover {
        outline: 1px solid #ccc;
      }
      .doc-icon {
        font-size: 32px;
      }
      text-decoration: none;
      &:visited {
        color: inherit;
      }
    }
    position: relative;
    .common-val {
      position: relative;
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
      background-color: #dddddd;
      padding: 8px;
      cursor: pointer;
      z-index: 2;
    }
    border: 1px solid #cccccc;
    text-align: left;
    .bescheid-options {
      position: relative;
      z-index: 1;
    }
  }
}
</style>
