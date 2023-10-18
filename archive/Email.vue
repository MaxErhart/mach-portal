<template>
  <div class="email" v-if="users != null && groups != null">
    <section class="email-send-date-section">
      <label for="email-send-date" class="email-send-date-lable">Scheduled Email</label>
      <input type="datetime-local" id="email-send-date" class="email-send-date" v-model="emailSendDate">
    </section>

    <section class="email-subject-section">
      <label for="email-subject" class="email-subject-lable">Email Subject</label>
      <input type="text" id="email-subject" class="email-subject" v-model="emailSubject">
    </section>

    <section class="email-content-seciont">
      <label class="email-content-title-lable">Upload Email Content</label>
      <input id="email-content" type="file" ref="file" name="email-content" @change="fileU()">
      <label for="email-content" class="email-content-lable" :class="{active: fileUploaded}">
        <template v-if="!fileUploaded">
          <span><img :src="require(`@/assets/upload.svg`)"></span>
          <span>Upload File</span>
        </template>
        <template v-else >{{filename}}</template>
        <div class="progress-bar" v-if="uploadPercentage>0">
          <div class="progress-percent">{{uploadPercentage+'%'}}</div>
          <div class="progress-bar-backdrop" :style="{width: uploadPercentage+'%'}"></div>
        </div>
      </label>
    </section>

    <section class="email-to-section">
      <label for="email-to" class="email-to-lable">Email Recipients</label>
      <div class="select-once">
        <div class="selected-items" v-for="selectedUser in userSelection" :key="selectedUser">
          <div class="selected-item">
            <div class="selected-item-name">{{getUserName(selectedUser)}}</div>
            <!-- <div class="remove-selected-item" @click="removeUser(selectedUser)">
              <img :src="require(`@/assets/delete.svg`)">  
            </div> -->
          </div>      
        </div>        
        <!-- <div class="select-new-item">
          <select name="object-select" class="select-object" v-model="currentSelectedUserId">
            <option :value="user.userId" v-for="user in remainingUsers" :key="user.userId"> {{user.lastname}} {{user.firstname}} </option>
          </select>
          <input type="button" value="add" class="kit-button add-button" @click="selectedUsers.push(currentSelectedUserId)">
        </div>           -->
      </div>

      <div class="select-once">
        <div class="selected-items" v-for="selectedGroup in groupSelection" :key="selectedGroup">
          <div class="selected-item">
            <div class="selected-item-name">{{getGroupName(selectedGroup)}}</div>
            <!-- <div class="remove-selected-item" @click="removeGroup(selectedGroup)">
              <img :src="require(`@/assets/delete.svg`)">  
            </div> -->
          </div>      
        </div>        
        <!-- <div class="select-new-item">
          <select name="object-select" class="select-object" v-model="currentSelectedGroupId">
            <option :value="group.groupId" v-for="group in remainingGroups" :key="group.groupId">{{getGroupName(group.groupId)}}</option>
          </select>
          <input disable type="button" value="add" class="kit-button add-button" @click="selectedGroups.push(currentSelectedGroupId)">
        </div>           -->
      </div>
    </section>
    <section class="scheduled-emails-overview">
      <button class="kit-button" @click="scheduleEmail()">Add Scheduled Email</button>
      <div class="scheduled-emails-content">
        <div class="emails-type">Scheduled Emails</div>
        <div class="item">Date</div>
        <div class="item">Subject</div>
        <div class="item">Content</div>
        <div class="scheduled-email" v-for="email in emails.scheduledEmails" :key="email.scheduledEmailId">
          <div class="item">{{email.sendDate}}</div>
          <div class="item">{{email.subject}}</div>
          <a :href="'https://www-3.mach.kit.edu/dfiles/emailContent/' + formatContentFileUrl(email.contentFile)" class="item">{{formatContentFileUrl(email.contentFile).split('_')[2]}}</a>
        </div>

        <div class="emails-type">Already Sent Emails</div>
        <div class="item">Date</div>
        <div class="item">Subject</div>
        <div class="item">Content</div>        
        <div class="scheduled-email" v-for="email in emails.sendEmails" :key="email.scheduledEmailId">
          <div class="item">{{email.sendDate}}</div>
          <div class="item">{{email.subject}}</div>
          <a :href="'https://www-3.mach.kit.edu/dfiles/emailContent/' + formatContentFileUrl(email.contentFile)" class="item">{{formatContentFileUrl(email.contentFile).split('_')[2]}}</a>
        </div>        
      </div>
    </section>
  </div>
</template>

<script>

import axios from "axios";
export default {
  name: 'Email',
  props: {
    formId: Number,
    userSelection: Object,
    groupSelection: Object,
    users: Object,
    groups: Object,
    emails: Object,
  },
  data() {
    return {
      fileUploaded: false,
      currentSelectedGroupId: null,
      currentSelectedUserId: null,
      file: '',
      uploadPercentage: 0,
      emailSendDate: null,
      emailSubject: null,
    }
  },
  computed: {
    remainingUsers: function() {
      if(this.userSelection) {
        return this.users.filter(el => !this.userSelection.includes(el.userId))
      } else {
        return this.users
      }      
    },
    remainingGroups: function() {
      if(this.groupSelection) {
        return this.groups.filter(el => !this.groupSelection.includes(el.groupId))
      } else {
        return this.groups
      }      
    }    
  },
  methods: {
    formatContentFileUrl(path) {
      var tempPath = path.substring(0, path.length - 1);
      tempPath = tempPath.split('\\')
      tempPath = tempPath[tempPath.length-1]
      return tempPath
    },
    fileU() {
      this.file = this.$refs['file'].files[0]
      const filename = this.$refs['file'].value.split('\\')
      if(filename[filename.length-1] == "") {
        this.fileUploaded = false
      } else {
        this.fileUploaded = true
        this.filename = filename[filename.length-1]
      }
    },  
    getUserName(userId) {
      var user = this.users.filter(el => el.userId == userId)[0]
      return user.lastname+' '+user.firstname
    },
    getGroupName(groupId) {
      var name = this.groups.filter(el => el.groupId == groupId)[0].groupName
      if(name.includes("MACH-Portal", 0)) {
        return name.substring(12)
      } else {
        return name
      }
    },
    scheduleEmail() {
      var formData = new FormData()  
      formData.append('file', this.file)
      formData.append('mode', 'insert')
      formData.append('subject', this.emailSubject)
      formData.append('sendDate', this.emailSendDate)
      formData.append('users', JSON.stringify(this.userSelection))
      formData.append('groups', JSON.stringify(this.groupSelection))
      formData.append('formId', this.formId)
      axios.post('https://www-3.mach.kit.edu/api/email.php',
        formData,
        {
          headers: {
              'Content-Type': 'multipart/form-data'
          },
          onUploadProgress: function( progressEvent ) {
            this.uploadPercentage = parseInt( Math.round( ( progressEvent.loaded / progressEvent.total ) * 100 ))
          }.bind(this)
        }
      ).then((response) => {
          console.log(response.data)
        }
      ) 
    },
    // removeUser(userId) {
    //   const index = this.selectedUsers.indexOf(userId)
    //   this.selectedUsers.splice(index, 1)
    // },
    // removeGroup(groupId){
    //   const index = this.selectedGroups.indexOf(groupId)
    //   this.selectedGroups.splice(index, 1)
    // }
  }
}
</script>


<style scoped lang="scss">
label {
  display: block;
  font-size: 16px;
  font-weight: 500;
  margin: 5px 0;
  text-align: left;
}
input {
  user-select: auto !important;
  display: block;
  height: 40px;
  width: 100%;
  font-size: 16px;
  border: 1px solid #ccc;
}
section {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  width: 100%;
}
.email {
  width: 544px;
  padding: 5px 10px;
  display: flex;
  flex-direction: column;
  align-items: center; 
}

#email-content {
  display: none;
}

.email-content-lable {
  position: relative;
  height: 40px;
  width: 100%;
  display: flex;
  align-items: center;
  margin: 2px 0 18px 0;
  text-align: start;
  border: 1px solid rgba(0, 0, 0, 0.2);
  justify-content: center;
  background-color: #fff;
  > .progress-bar {
    > .progress-percent {
      width: 100%;
      position: absolute;
      text-align: center;
      transform: translateY(-2px);
      z-index: 2;
    }
    > .progress-bar-backdrop {
      position: absolute;
      background-color: #00876c;
      height: 100%;
      border-radius: 10px;
    }
    position: absolute;
    height: 12px;
    left: 0;
    border-radius: 10px;
    bottom: -18px;
    width: 100%;
    box-shadow: 0 0 2px 1px rgba(0,0,0,0.2)
  }
  > span:first-child {
    margin-right: 5px;
  }
  &:hover {
    box-shadow: inset 0 0 2px 1px rgba(0,0,0,0.2);
  }  
}

.select-once {
  width: 100%;
}

.selected-item {
  display: flex;
  flex-direction: row;
  align-items: center;
  // border: 1px solid black;
  width: 100%;
  height: 40px;
  margin: 2px 0;
  background-color: #fff;
  border: 1px solid rgba(0,0,0,0.2);
  &:hover{
    background-color: #ddd;
  }
  > .remove-selected-item {
    box-sizing: border-box;

    &:hover {
      box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8);
      cursor: pointer;
    }
    &:active {
      box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8), 0 0 0 1px black;
    }      
    width: 38px;
    height: 26px;
    display: flex;
    cursor: pointer;
    border: 1px solid #2c3e50;
    border-radius: 2px;
    > img {
      height: 24px;
      margin: auto;
    }      
  }    
}

.remove-selected-item {
  margin-left: auto;
}
.select-new-item {
  // border: 1px solid black;
  display: flex;
  flex-direction: row;
  > select {
    display: block;
    width: 250px;
    height: 24px;
    font-size: 14px;
    border: 1px solid #ccc;
  }    
}
.add-button {
  width: 40px;
  height: 24px;
  padding: 1px;
  margin: 0 4px;
}
.scheduled-emails-overview {
  width: 100%;
}
.scheduled-emails-content {
  width: 100%;
  display: grid;
  grid-template-columns: repeat(3, auto);
  > .scheduled-email {
    display: contents;
  }
}
.emails-type {
  grid-column: 1/-1;
  text-align: left;
  margin: 5px 0;
}
</style>