<template>
  <div>
    <button class="folder" @click.prevent="open({names:[],content:{name,fragments,contents,permission,is_dir}})" :class="{open:is_open}">
      <ion-icon v-if="is_open" name="folder-open-outline" class="icon"></ion-icon>
      <ion-icon v-else name="folder-outline" class="icon"></ion-icon>
      <span class="name">{{name}}</span>
    </button>
    <div class="contents" v-if="is_open" :class="{open:is_open}">
      <template v-if="!contents || !(contents.files?.length>0 || contents.folders?.length>0)">Empty</template>
      <template v-if="n_pages>1">
        <div class="paginate-wrapper">
          <div class="paginate">
            <button :class="{'col-1': curr_page>3, 'col-2':curr_page<=3}" @click="curr_page=1" :disabled="curr_page<3"><span>1</span></button>
            <div class="col-2" v-if="curr_page>3"><span>...</span></div>
            <button class="col-3" @click="curr_page--" :disabled="curr_page===1"><span>{{curr_page-1}}</span></button>
            <div class="active col-4"><span>{{curr_page}}</span></div>
            <button @click="curr_page++" :disabled="curr_page===n_pages"><span>{{curr_page+1}}</span></button>
            <div v-if="curr_page<n_pages-2"><span>...</span></div>
            <button @click="curr_page=n_pages" :disabled="curr_page>n_pages-2"><span>{{n_pages}}</span></button>
          </div>
        </div>
      </template>
      <Folder v-for="content in page_content.folders" :key="content" v-bind="content" @open="open($event)"/>
      <File v-for="content in page_content.files" :key="content" v-bind="content" @open="open($event)"/>
    </div>
  </div>
</template>

<script>
import Folder from '@/components/Folder.vue'
import File from '@/components/File.vue'
export default {
  name: 'Folder',
  components: {
    Folder,
    File,
  },
  props: {
    name: String,
    contents: Object,
    fragments: Array,
    is_dir: Boolean,
    permission: Number,
  },
  data() {
    return {
      is_open: false,
      curr_page: 1,
      entries_per_page: 50,
    }
  },
  computed: {
    n_pages() {
      if(!this.contents?.files || !this.contents?.folders) {
        return 0
      }
      return parseInt((this.contents.files.length+this.contents.folders.length)/this.entries_per_page)+1
    },
    n_folder_pages() {
      if(!this.contents?.folders) {
        return 1
      }
      return parseInt((this.contents.folders.length)/this.entries_per_page)+1
    },
    n_file_pages() {
      if(!this.contents?.files) {
        return 1
      }
      return parseInt((this.contents.files.length)/this.entries_per_page)+1
    },
    page_content() {
      var files = []
      var folders = []
      if(!this.contents.files || !this.contents.folders) {
        return {folders,files}
      }

      if(this.curr_page<=this.n_folder_pages) {
        folders = this.contents.folders.slice((this.curr_page-1)*this.entries_per_page,this.curr_page*this.entries_per_page)
      } else {
        var offset_folders = this.contents.folders.slice((this.n_folder_pages-1)*this.entries_per_page,this.n_folder_pages*this.entries_per_page)
      }
      if(this.curr_page===this.n_folder_pages) {
        files = this.contents.files.slice(0,this.entries_per_page-folders.length)
      } else if(this.curr_page>=this.n_folder_pages) {
        files = this.contents.files.slice(this.entries_per_page-offset_folders.length + (this.curr_page - this.n_folder_pages - 1)*this.entries_per_page, this.entries_per_page-offset_folders.length + (this.curr_page - this.n_folder_pages)*this.entries_per_page)
      }
      console.log({folders,files})
      return {folders,files}
    }
  },
  methods: {
    getPageContent(content, page, type) {
      console.log(content,page,type)
      if(page<this.n_folder_pages) {
        return 
      }
      return []
    },
    open({names,content}) {
      if(names.length<=0) {
        this.is_open = !this.is_open
      }
      if(this.is_open) {
        this.$emit('open', {names:[this.name,...names],content})
      }
    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.paginate-wrapper {
  display: flex;
  justify-content: flex-start;
  .paginate {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr 1fr;
    gap: 4px;
    .col-1 {
      grid-column: 1/2;
    }
    .col-2 {
      grid-column: 2/3;
    }
    .col-3 {
      grid-column: 3/4;
    }
    .col-4 {
      grid-column: 4/5;
    }
    .col-5 {
      grid-column: 5/6;
    }
    .col-6 {
      grid-column: 6/7;
    }
    .col-7 {
      grid-column: 7/8;
    }
    button,div {
      font-size: 18px;
      border: 1px solid #aaa;
      border-radius: 2px;
      width: 30px;
      aspect-ratio: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      &.active {
        border: 1px solid $kit_green !important;
        color: $kit_green;
      }
    }
    button {
      cursor: pointer;
      &:hover {
        background-color: #ccc;
      }
      &:disabled {
        visibility: hidden;
      }
    }
  }

  // div {
  //   text-align: center;
  //   border: 1px solid black;
  //   aspect-ratio: 1;
  //   font-size: 18px;
  //   width: 24px;
  // }
}
.folder {
  cursor: pointer;
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 8px;
  white-space: nowrap;
  &:hover {
    background-color: rgba(0,0,0,0.2);
  }
  &.open {
    color: $kit_green;
  }
  .icon {
    transform: scale(1.2);
  }
}
.contents {
  padding: 4px 0px 4px 8px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  border-left: 2px solid $kit_green;
}
</style>
