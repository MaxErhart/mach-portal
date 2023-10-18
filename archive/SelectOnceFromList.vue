<template>
  <div id="select-once-from-list" v-if="selectedItems">
    <div class="selected-items" v-for="selectedItem in selectedItems" :key="selectedItem">
      <div class="selected-item" v-if="selectedItem == 'all'">
        <div class="selected-item-name">all</div>
        <div class="remove-selected-item" @click="removeItem(selectedItem)">
          <img :src="require(`@/assets/delete.svg`)"> 
        </div>
      </div>
      <div class="selected-item" v-else>
        <div class="selected-item-name">{{objectName(listOfItems.filter(el => objectId(el) == selectedItem)[0])}}</div>
        <div class="remove-selected-item" @click="removeItem(selectedItem)">
          <img :src="require(`@/assets/delete.svg`)">  
        </div>
      </div>      

    </div>
    <div id="select-new-item">
      <select :disabled="selectedItems.includes('all') || rightObject[rightType]['users'].includes('all')" name="object-select" id="select-object" v-model="currentSelectedObject">
        <option value="all" v-if="selectedItems==null || selectedItems.length == 0 && objectType == 'users'">all</option>
        <option :value="objectId(item)" v-for="item in remainingItems" :key="objectId(item)">{{objectName(item)}}</option>
      </select>
      <input type="button" value="add" class="kit-button add-button" @click="addItem(currentSelectedObject)">
    </div>


  </div>
</template>

<script>

export default {
  name: 'SelectOnceFromList',
  props: {
    listOfItems: Array,
    rightObject: Object,
    rightType: String,
    objectType: String
  },
  data() {
    return {
      selectedItems: null,
      filterTerm: null,
      currentSelectedObject: null
    }
  },
  mounted() {
    if(this.rightObject) {
      this.selectedItems = this.rightObject[this.rightType][this.objectType]
    }
  },
  computed: {
    remainingItems() {
      if(this.selectedItems) {
        return this.listOfItems.filter(el => !this.selectedItems.includes(this.objectId(el)))
      } else {
        return this.listOfItems
      }
    },
  },
  methods: {
    addItem(item) {
      this.selectedItems.push(item)
      this.currentSelectedObject = null
      this.emitChange()
    },
    removeItem(item) {
      const index = this.selectedItems.indexOf(item)
      this.selectedItems.splice(index, 1)
      this.emitChange()
    },
    emitChange() {
      // console.log(this.rightObject)
      if(this.objectType == 'users') {
        this.$emit('selection-changed', {rightsTarget: {users: this.selectedItems, groups: this.rightObject[this.rightType].groups}, id: this.rightObject[this.rightType].id, rightType: this.rightType})
      } else [
        this.$emit('selection-changed', {rightsTarget: {users: this.rightObject[this.rightType].users, groups: this.selectedItems}, id: this.rightObject[this.rightType].id, rightType: this.rightType})
      ]
      
    },
    objectId(item) {
      if(item.userId) {
        return item.userId
      } else if(item.groupId) {
        return item.groupId
      } else {
        return null
      }
    },
    objectName(item) {
      if(item.lastname) {
        if(item.lastname != 'webuser') {
          return item.lastname+' '+item.firstname
        } else {
          return 'webuser'
        }
      } else if(item.groupName) {
        return item.groupName
      } else {
        return null
      }
    }
  }
}
</script>


<style scoped lang="scss">
select {
  display: block;
  width: 50%;
  height: 24px;
  font-size: 14px;
  border: 1px solid #ccc;
}

.add-button {
  width: 40px;
  height: 24px;
  padding: 1px;
  margin: 0 4px;
}
#select-new-item {
  display: flex;
  flex-direction: row;
  padding: 2px 4px;
}
.selected-items {
  padding: 2px 4px;
  > .selected-item {
    display: flex;
    flex-direction: row;
    align-items: center;
    background-color: #fff;
    > .selected-item-name {
      width: 100%;
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
      width: 34px;
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
}

</style>