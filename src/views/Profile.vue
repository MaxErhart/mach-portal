<template>
  <div id="profile">
    <h1 @click="test()">Profile Settings</h1>
    <form id="profile-content" @submit.prevent="updateProfile()" ref="form">
      <div class="content-container">
        <h4>Shibboleth User Information</h4>
        <div class="content-grid">
          <div class="profile-attribute" v-for="attribute in attributes" :key="attribute" :class="{readonly: attribute.readonly}">
            <div class="attribute-name" @click="focusAttribute(attribute)"><span>{{attribute.label}}</span></div>
            <div class="attribute-value">
              <ProfileInput :name="attribute.name" :presetValue="attribute.value" :readonly="attribute.readonly" :ref="attribute.name"/>
            </div>
          </div>
        </div>
      </div>

      <div class="content-container">
        <h4>User Information</h4>
        <div id="fill-out-missing-information" v-if="missingUserInfo">Please fill out missing user information.</div>
        <div class="content-grid">
          <div class="profile-attribute" v-for="attribute in attributesAddress" :key="attribute" :class="{readonly: attribute.readonly}">
            <div class="attribute-name" @click="focusAttribute(attribute)">
              <span :style="labelStyle(attribute)">{{attribute.label}}</span>
            </div>
            <div class="attribute-value">
              <ProfileSelect v-if="attribute.select" :options="attribute.options" :name="attribute.name" :presetValue="attribute.value" :readonly="attribute.readonly"  :autocomplete="attribute.autocomplete" :ref="attribute.name"/>
              <ProfileInput v-else :name="attribute.name" :presetValue="attribute.value" :readonly="attribute.readonly" :autocomplete="attribute.autocomplete" :ref="attribute.name"/>
            </div>
          </div>
        </div>
      </div>
      <Checkbox name="consent" label="I consent to the storage of my data." :required="true" ref="consent"/>
      <Button ref="submitButton" text="Submit" :disabled="submitDisabled" :loading="submitLoading"/>

    </form>
  </div>
</template>

<script>
import json from '@/components/inputs/countries.json'
import ProfileInput from '@/components/user/profileInputs/ProfileInput.vue'
import ProfileSelect from '@/components/user/profileInputs/ProfileSelect.vue'
import Button from '@/components/Button.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
import axios from "axios";
export default {
  name: 'Profile',
  components: {
    ProfileInput,
    ProfileSelect,
    Button,
    Checkbox,
  },
  props: {
    user: Object,
  },
  data() {
    return {
      submitDisabled: false,
      submitLoading: false,
      attributeSettingsShib: {
        'firstname': {name: 'firstname', label: 'First Name', readonly: true, autocomplete: null},
        'lastname': {name: 'lastname', label: 'Last Name', readonly: true, autocomplete: null},
        'email': {name: 'email', label: 'Email', readonly: true, autocomplete: null},
        'shib_id': {name: 'shib_id', label: 'ID', readonly: true, autocomplete: null},
        'matriculationNumber': {name: 'matriculationNumber', label: 'Matriculation Number', readonly: true, autocomplete: null},
        'fieldOfStudyText': {name: 'fieldOfStudyText', label: 'Field of Study', readonly: true, autocomplete: null},
      },
      attributeSettingsAddress: {
        'address_street': {name: 'address_street', label: 'Street, Number', readonly: false, autocomplete: 'street-address', select: false},
        'address_postalcode': {name: 'address_postalcode', label: 'Postal Code', readonly: false, autocomplete: 'postal-code', select: false},
        'address_city': {name: 'address_city', label: 'City', readonly: false, autocomplete: 'address-level2', select: false},
        'address_country': {name: 'address_country', label: 'Country', readonly: false, autocomplete: 'country-name', select: true, options: json},
        'private_email': {name: 'private_email', label: 'Private Email', readonly: false, autocomplete: 'email', select: false},
      },
    }
  },
  computed: {
    missingUserInfo() {
      if(!this.user) {
        return false
      }
      if(this.user.address_street && this.user.address_postalcode && this.user.address_city && this.user.address_country && this.user.private_email) {
        return false
      }
      return true
    },
    attributesAddress() {
      var attributeList = [];
      if(!this.user) {
        return attributeList
      }
      for (const [key, value] of Object.entries(this.attributeSettingsAddress)) {
        if(!(key in this.user)) {
          continue
        }
        attributeList.push({
          name: value.name,
          autocomplete: value.autocomplete,
          label: value.label,
          value: this.user[key],
          readonly: value.readonly,
          select: value.select,
          options: value.options,
          
        })
      }
      return attributeList
    },
    attributes() {
      var attributeList = [];
      if(!this.user) {
        return attributeList
      }
      for (const [key, value] of Object.entries(this.user)) {
        if(!(key in this.attributeSettingsShib)) {
          continue
        }
        attributeList.push({
          name: this.attributeSettingsShib[key].name,
          autocomplete: this.attributeSettingsShib[key].autocomplete,
          label: this.attributeSettingsShib[key].label,
          value: value,
          readonly: this.attributeSettingsShib[key].readonly
        })
      }
      return attributeList
    }
  },
  methods: {
    labelStyle(attribute) {
      if(!this.user) {
        return null
      }
      if(this.user[attribute.name]) {
        return null
      }
      return {
        'color': 'rgb(155 28 28)'
      }
    },
    focusAttribute(attribute) {
      if(attribute.readonly) {
        return
      }
      this.$refs[attribute.name].focus()
    },
    updateProfile() {
      const url = this.$store.getters.getApiUrl+'/users/'+this.user.id
      var formData = new FormData(this.$refs.form)
      for(const pair of formData.entries()) {
        console.log(`${pair[0]}, ${pair[1]}`);
      }
      this.submitLoading = true
      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(response=>{
        console.log(response.data)
        this.$refs.submitButton.success = true
        this.submitLoading = false
        this.$emit('userInfoChange', response.data)
      }).catch(error=>{
        console.log(error.response)
        this.$refs.submitButton.error = true
        this.submitLoading = false
        if(error.response.status==500) {
          this.$refs[error.response.data.element_id].deFocusedOnce=true
        }
      }) 
    },
    test() {
      console.log(this.user)
    }
  }
}
</script>

<style lang="scss" scoped>
#fill-out-missing-information {
  color: rgb(155 28 28);
}
#profile {
  color: #2c3e50;
  display: flex;
  flex-direction: column;
  align-items: center;
}
#profile-content {
  max-width: 210mm;
  padding: 24px 0;
  width: 100%;
  > * {
    margin: 12px 0;
  }
  >:first-child {
    margin: 0 0 12px 0;
  }
  >:last-child {
    margin: 12px 0 0 0;
  }
}
.content-container {
  border: 1px solid #2c3e50;
  border-radius: 4px;
  padding: 12px;
}
.content-grid {
  text-align: center;
  display: grid;
  grid-template-columns: 33.3% 66.6%;
  .profile-attribute {
    display: contents;
    .attribute-name {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      padding: 4px 8px;
      border-top-left-radius: 4px;
      border-bottom-left-radius: 4px;
    }
    > * {
      padding: 14px 10px;
      margin: 4px 0;     
    }
    &:hover {
      > * {
        background-color: #f5f5f5;
        cursor: pointer;
      }
    }
  }
  .profile-attribute.readonly {
    &:hover {
    > * {
      background-color: #f5f5f5;
      cursor: not-allowed;
    }
    }
  }
}
</style>