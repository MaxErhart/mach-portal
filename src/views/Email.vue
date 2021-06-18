<template>
  <div class="email">
    <div class="email-body">

      <section class="email-send-date-section">
        <label for="email-send-date" class="email-send-date-lable">Scheduled Email</label>
        <input type="datetime-local" id="email-send-date" class="email-send-date" v-model="emailSendDate">
      </section>

      <section class="email-subject-section">
        <label for="email-subject" class="email-subject-lable">Email Subject</label>
        <input type="text" id="email-subject" class="email-subject" v-model="emailSubject">
      </section>

      <section class="email-content-seciont">
        <div class="email-content-title-lable">Upload Email Content</div>
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

      <section class="email-to-section" v-if="groups != null && users != null">

        <label for="email-to" class="email-to-lable">Email Recipients</label>
        <div class="select-once">
          <div class="selected-items" v-for="selectedUser in selectedUsers" :key="selectedUser">
            <div class="selected-item">
              <div class="selected-item-name">{{getUserName(selectedUser)}}</div>
              <div class="remove-selected-item" @click="removeUser(selectedUser)">
                <img :src="require(`@/assets/delete.svg`)">  
              </div>
            </div>      
          </div>        
          <div class="select-new-item">
            <select name="object-select" class="select-object" v-model="currentSelectedUserId">
              <option :value="user.userId" v-for="user in remainingUsers" :key="user.userId"> {{user.lastname}} {{user.firstname}} </option>
            </select>
            <input type="button" value="add" class="kit-button add-button" @click="selectedUsers.push(currentSelectedUserId)">
          </div>          
        </div>

        <div class="select-once">
          <div class="selected-items" v-for="selectedGroup in selectedGroups" :key="selectedGroup">
            <div class="selected-item">
              <div class="selected-item-name">{{getGroupName(selectedGroup)}}</div>
              <div class="remove-selected-item" @click="removeGroup(selectedGroup)">
                <img :src="require(`@/assets/delete.svg`)">  
              </div>
            </div>      
          </div>        
          <div class="select-new-item">
            <select name="object-select" class="select-object" v-model="currentSelectedGroupId">
              <option :value="group.groupId" v-for="group in remainingGroups" :key="group.groupId">{{getGroupName(group.groupId)}}</option>
            </select>
            <input disable type="button" value="add" class="kit-button add-button" @click="selectedGroups.push(currentSelectedGroupId)">
          </div>          
        </div>

      </section>

      <section>
        <div class="kit-button submit" @click="submit()">
          <div>Submit</div>
        </div>
      </section>

    </div>
    
   
  </div>
</template>

<script>
import axios from "axios";
export default {
  name: 'Email',
  data() {
    return {
      users: null,
      groups: null,
      selectedUsers: [],
      selectedGroups: [],
      fileUploaded: false,
      currentSelectedGroupId: null,
      currentSelectedUserId: null,
      file: '',
      uploadPercentage: 0,
      emailSendDate: null,
      emailSubject: null,
    }
  },
  mounted() {
    if(this.$route.params.id) {
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/getForm.php',
        data: {id: this.$route.params.id}
      }).then(response => {
        console.log(response.data)
        if(response.data.error == null) {
          this.selectedUsers=response.data.metadata.targetUsers.users    
          this.selectedGroups=response.data.metadata.targetUsers.groups
          console.log(this.selectedGroups)
        }
      })      
    }
    axios({
      method: 'get',
      url: 'https://www-3.mach.kit.edu/api/getUsersAndGroups.php',
    }).then(response => {
      console.log(response.data)
      if(response.data.error == null) {
        this.users=response.data.users
        this.groups=response.data.groups
      } else {
        this.users = []
        this.groups = []
      }
    })
  },
  computed: {
    remainingUsers: function() {
      if(this.selectedUsers) {
        return this.users.filter(el => !this.selectedUsers.includes(el.userId))
      } else {
        return this.users
      }      
    },
    remainingGroups: function() {
      if(this.selectedGroups) {
        return this.groups.filter(el => !this.selectedGroups.includes(el.groupId))
      } else {
        return this.groups
      }      
    }    
  },
  methods: {
    submit() {
      var formData = new FormData()  
      formData.append('file', this.file)
      formData.append('mode', 'insert')
      formData.append('subject', this.emailSubject)
      formData.append('sendDate', this.emailSendDate)
      formData.append('users', JSON.stringify(this.selectedUsers))
      formData.append('groups', JSON.stringify(this.selectedGroups))
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
    removeUser(userId) {
      const index = this.selectedUsers.indexOf(userId)
      this.selectedUsers.splice(index, 1)
    },
    removeGroup(groupId){
      const index = this.selectedGroups.indexOf(groupId)
      this.selectedGroups.splice(index, 1)
    }
  }
}
</script>


<style scoped lang="scss">
section{
  display: flex;
  flex-direction: column;  

}

.email-send-date-lable,.email-subject-lable,.email-content-title-lable,.email-to-lable{
  text-decoration: underline;
  margin: 12px 0 4px 0;
}
input {
  user-select: auto !important;
  display: block;
  height: 40px;
  width: 250px;
  font-size: 16px;
  border: 1px solid #ccc;
  height: 28px;
}
.submit {
  height: 28px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 250px;
}
.email {
  padding: 15px;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 10px;
  background-color: #f2f2f2;
}
.email-body {
  padding: 10px;
  > * {
    margin: 5px 0;
  }
}



#email-content {
  display: none;
}

.email-content-lable {
  position: relative;
  height: 28px;
  width: 250px;
  display: flex;
  align-items: center;
  margin: 2px 0 18px 0;
  text-align: start;
  border: 1px solid rgba(0, 0, 0, 0.2);
  justify-content: center;

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
  padding: 5px 0;
}

.selected-item {
  display: flex;
  flex-direction: row;
  align-items: center;
  // border: 1px solid black;
  width: 293px;
  margin: 2px 0;
  &:hover{
    background-color: #ddd;
  }
  > .selected-item-name {
    width: 250px;
    // border: 1px solid red;
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

</style>
