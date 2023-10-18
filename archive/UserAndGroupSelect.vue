<template>
  <div class="user-and-group-select" v-if="users!=null && groups != null">
    <label for="target-users">Visibility for Users:</label>
    <div class="select-once">
      <div class="selected-items" v-for="selectedUser in userSelection" :key="selectedUser">
        <div class="selected-item">
          <div class="selected-item-name">
            <div>
              {{getUserName(selectedUser)}}
            </div>            
          </div>
          <div class="remove-selected-item" @click="removeUser(selectedUser)">
            <img :src="require(`@/assets/delete.svg`)">  
          </div>
        </div>      
      </div>        
      <div class="select-new-item">
        <select name="object-select" class="select-object" v-model="currentSelectedUserId">
          <option :value="user.userId" v-for="user in remainingUsers" :key="user.userId"> {{user.lastname}} {{user.firstname}} </option>
        </select>
        <input type="button" value="add" class="kit-button add-button" @click="addUser(currentSelectedUserId)">
      </div>          
    </div>

    <label for="target-groups">Visibility for Groups:</label>
    <div class="select-once">
      <div class="selected-items" v-for="selectedGroup in groupSelection" :key="selectedGroup">
        <div class="selected-item">
          <div class="selected-item-name">
            <div>
              {{getGroupName(selectedGroup)}}
            </div>
          </div>
          <div class="remove-selected-item" @click="removeGroup(selectedGroup)">
            <img :src="require(`@/assets/delete.svg`)">  
          </div>
        </div>      
      </div>        
      <div class="select-new-item">
        <select name="object-select" class="select-object" v-model="currentSelectedGroupId">
          <option :value="group.groupId" v-for="group in remainingGroups" :key="group.groupId"> {{getGroupName(group.groupId)}} </option>
        </select>
        <input type="button" value="add" class="kit-button add-button" @click="addGroup(currentSelectedGroupId)">
      </div>          
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserAndGroupSelect',
  props: {
    users: Object,
    groups: Object,
    userSelection: Object,
    groupSelection: Object,
  },
  components: {
  },
  data() {
    return {
      currentSelectedUserId: null,
      currentSelectedGroupId: null,
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
    getUserName(userId) {
      var user = this.users.filter(el => el.userId == userId)[0]
      return user.lastname+' '+user.firstname
    },
    getGroupName: function(groupId) {
      var name = this.groups.filter(el => el.groupId == groupId)[0].groupName
      if(name.includes("MACH-Portal", 0)) {
        return name.substring(12)
      } else {
        return name
      }
    },    
    removeUser(user) {
      this.$emit('remove-user', {user: user})
    },
    removeGroup(group) {
      this.$emit('remove-group', {group: group})
    },
    addUser(user) {
      this.$emit('add-user', {user: user})
    },
    addGroup(group) {
      this.$emit('add-group', {group: group})
    }
  }

}
</script>

<style scoped lang="scss" >
label {
  display: block;
  font-size: 16px;
  width: 100%;
  font-weight: 500;
  margin: 5px 0;
  text-align: left;
}
input {
  user-select: auto !important;
  display: block;
  height: 40px;
  font-size: 16px;
  border: 1px solid #ccc;
  padding: 15px;
  margin: 5px 10px;
}
.user-and-group-select {
  width: 544px;
  padding: 5px 0;
  // border: 1px solid black;
}
.select-once {
  width: 100%;
  // border: 1px solid blue;
}
.target-users {
  display: grid;
  min-height: 60px;
  grid-template-columns: auto auto;
  grid-template-rows: auto auto;
  grid-auto-flow: column;
}
.selected-item {
  display: flex;
  flex-direction: row;
  align-items: center;
  height: 36px;
  margin: 2px 0;
  > .selected-item-name {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    height: 100%;
    width: 543px;
    border: 1px solid rgba(0,0,0,0.2);
    background-color: #fff;
    &:hover{
      background-color: #ddd;
    }    
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
    margin: 0 0 0 2px;
    width: 35px;
    height: 28px;
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
  width: 100%;
  display: flex;
  flex-direction: row;
  align-items: center;
  > select {
    display: block;
    width: 100%;
    height: 40px;
    font-size: 14px;
    border: 1px solid #ccc;
  }    
}
.add-button {
  width: 35px;
  height: 24px;
  padding: 0;
  margin: 0 0 0 2px;
  border: none;
  box-shadow: none;
}

</style>