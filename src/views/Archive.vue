<template>
  <div class="archive">

    <div class="search">
      <Search ref="search" :complete_word="true" bg="rgb(249, 249, 249)" :search_dot_prefix="search_dot_prefix" @typing="handleSearchTyping($event)" @enter="handleSearchEnter($event)" :suggestions_loading="false" :suggestions="search_suggestions" name="search" label="Search..."/>
    </div>
    <div class="actions-file">
      <button class="action" :class="{active:active_action===0}" @click="active_action=0" >
        <span class="icon">
          <ion-icon name="document-outline"></ion-icon>
        </span>
        <span class="text">
          View File
        </span>
      </button>
    </div>

    <div class="active-directory-path">
      Selected file:
      <div v-if="active_file" class="active-file">
        <ion-icon name="document-outline" class="icon"></ion-icon>
        <span v-for="fragment in active_file.fragments" :key="fragment">/{{fragment}}</span>
      </div>
      <div v-else>none</div>
    </div>


    <div class="directory-tree-content" >
      <div class="directory-toggle">
        <button @click="$router.push({name:$route.name,query:$route.query,params:{sub_route:[]}})" :class="{active:!route_is_search_results}">Root directory</button>
        <button @click="$router.push({name:$route.name,query:$route.query,params:{sub_route:['search_results']}})" :class="{active:route_is_search_results}">Search Results</button>
      </div>
      <div class="scroll-top-wrapper" @scroll="syncScroll($event)" ref="scroll_top" v-if="!route_is_search_results">
        <div class="scroll-top" :style="directory_tree_scroll_top_style" >asdf</div>
      </div>
      <div class="directory-tree-wrapper"  @scroll="syncScroll($event)" ref="directory_tree_wrapper">
        <div class="search-results" v-if="route_is_search_results">
          <h3>List of search results:</h3>
          <div class="search-results-content">
            <template v-if="search_loading">
              <DataPlaceholder/>
            </template>
            <template v-else>
              <template v-if="search_string && search_string in search_results && (search_results[search_string].files.length>0 || search_results[search_string].folders.length>0)">
                <Folder v-for="content in search_results[search_string].folders" :key="content" v-bind="content" @open="handleOpenFolderContent($event)"/>
                <File v-for="content in search_results[search_string].files" :key="content" v-bind="content" @open="handleOpenFolderContent($event)"/>   
              </template>
              <template v-else>No results</template>
            </template>
          </div>
        </div>
        <div class="directory-tree" ref="directory_tree" v-else>
          <template v-if="directory_tree_loading">
            <DataPlaceholder/>
          </template>
          <template v-else>
            <Folder v-for="content in directory_tree.folders" :key="content" v-bind="content" @open="handleOpenFolderContent($event)"/>
            <File v-for="content in directory_tree.files" :key="content" v-bind="content" @open="handleOpenFolderContent($event)"/>
          </template>
        </div>
      </div>

    </div>


    <div class="action-content">
      <div class="contents-file-window body-contents" v-if="active_action===0">
        <iframe :src="active_file.href" height="1200" width="1400" v-if="active_file">{{active_file.name}}</iframe>
        <span v-else>No file selected</span>
      </div>
    </div>

  </div>
</template>

<script>
import Search from '@/components/inputs_23/Search.vue'
import Folder from '@/components/Folder.vue'
import File from '@/components/File.vue'
import axios from 'axios';
import DataPlaceholder from '@/components/DataPlaceholder.vue'

export default {
  name: 'Archive',
  components: {
    Search,
    Folder,
    File,
    DataPlaceholder,
  },
  data() {
    return {
      search_dot_prefix: false,
      search_string: null,
      search_suggestions: [],
      search_results: {},

      name: null,

      directory_tree: null,

      directory_tree_scroll_width: 0,

      active_action: 0,

      active_file: null,
      active_file_object:null,
      directory_toggle: 0,
      directory_tree_loading:true,
      search_loading:false,
    }
  },
  computed: {
    directory_tree_scroll_top_style() {
      const width = this.directory_tree_scroll_width + 16
      return {
        'width': width+'px'
      }
    },
    route_is_search_results() {
      if(this.$route.params.sub_route?.[0]==="search_results") {
        return true
      }
      return false
    }
  },
  async mounted() {
    console.log('mounting...', this.$route)
    this.name = this.$route.name
    this.$refs.search.value = this.$route.query.value
    this.search_string = this.$route.query.value

    if(this.route_is_search_results) {
      if(!this.search_string || this.search_string.length<2 || this.search_string in this.search_results) {
        return
      } 
      const search_result = await this.searchArchive(this.$route.query.value,this.$route.meta.root)
      this.search_results[this.$route.query.value] = search_result
    } else {
      const directory_contents = await this.getDirectory(this.$route.params.sub_route?this.$route.params.sub_route:[],this.$route.meta.root)
      this.directory_tree = directory_contents
      this.directory_tree_loading=false
      this.updateDirectoryTreeScrollWidth()
    }

  },
  watch: {
    '$route': {
      handler: async function(to) {
        this.$refs.search.value = to.query.value
        this.search_string = this.$route.query.value
        if(this.route_is_search_results) {
          if(!this.search_string || this.search_string.length<2 || this.search_string in this.search_results) {
            return
          } 
          const search_result = await this.searchArchive(to.query.value,this.$route.meta.root)
          this.search_results[to.query.value] = search_result
          this.directory_contents = search_result
        } else if(!this.directory_tree){
          const directory_contents = await this.getDirectory(this.$route.params.sub_route?this.$route.params.sub_route:[],this.$route.meta.root)
          this.directory_tree = directory_contents
          this.directory_tree_loading=false
          this.updateDirectoryTreeScrollWidth()
        }
      }
    }
  },
  methods: {
    async updateDirectoryTreeScrollWidth() {
      await this.$nextTick()
      this.directory_tree_scroll_width = this.$refs.directory_tree.getBoundingClientRect().width
    },
    syncScroll(event) {
      if(event.srcElement===this.$refs.directory_tree_wrapper) {
        this.$refs.scroll_top.scrollLeft = this.$refs.directory_tree_wrapper.scrollLeft
      } else if(event.srcElement===this.$refs.scroll_top) {
        this.$refs.directory_tree_wrapper.scrollLeft = this.$refs.scroll_top.scrollLeft
      }
    },
    async handleOpenFolderContent({names,content}) {
      if(content.is_dir) {
        console.log(names,content,this.$route.meta.root)
        const directory_contents = await this.getDirectory(content.fragments.filter(fragment=>fragment!==this.$route.meta.root),this.$route.meta.root)
        var layer = null
        if(this.route_is_search_results) {
          layer = this.search_results[this.search_string].folders
        } else {
          layer = this.directory_tree.folders
        }
        names.forEach((name,index)=>{
          layer = layer.find(dir=>dir.name===name)
          if(index!==names.length-1) {
            layer = layer.contents.folders
          }
        })
        layer.contents = directory_contents
        this.updateDirectoryTreeScrollWidth()
      } else {
        if(this.active_file) {
          this.active_file.object.is_open = false
        }
        this.active_file = content
        content.object.is_open = true
        console.log(names,content)
      }

    },
    async getDirectory(dir,root) {
      const url = this.$store.getters.getApiUrl+'/archive/directory'
      const form_data = new FormData()
      form_data.append('dir', JSON.stringify(dir))
      form_data.append('root', root)
      const {data,error} = await axios({
          method: 'post',
          url: url,
          data: form_data,
          headers: {
            'Content-Type': 'multipart/form-data'
          }
      }).catch(error=>{
          return {data:null,error}
      })
      console.log(data,error?.response)
      if(error) {
        this.emitter.emit('showErrorMessage', {error: error, action: `Accessing Folder ${dir}`, redirect: null})
        return
      }
      return data
    },
    async searchArchive(value,root) {
      this.search_loading = true
      const url = this.$store.getters.getApiUrl+'/archive/search'
      const form_data = new FormData()
      form_data.append('search_string',value)
      form_data.append('search_offset',0)
      form_data.append('root',root)

      const {data, error} = await axios({
          method: 'post',
          url: url,
          data: form_data,
          headers: {
            'Content-Type': 'multipart/form-data'
          }
      }).catch(error=>{
          return {data:null,error}
      }) 
      console.log(data,error?.response)
      this.search_loading=false
      return data
    },
    handleSearchEnter(value) {
      this.$router.push({name:this.name,params:{sub_route:['search_results']},query: {value}})
    },
    isValidSearchString(search_string) {
      if(!search_string || search_string===this.search_string) {
        return false
      }
      return true
    },
    async handleSearchTyping(search_string) {
      search_string = search_string.trim()
      if(!this.isValidSearchString(search_string)) {
        return
      }
      this.search_string = search_string
      const url = this.$store.getters.getApiUrl+'/archive/searchsuggestions'
      const form_data = new FormData()
      form_data.append('search_string',this.search_string)
      const {data, error} = await axios({
          method: 'post',
          url: url,
          data: form_data,
          headers: {
            'Content-Type': 'multipart/form-data'
          }
      }).catch(error=>{
          return {data:null,error}
      })
      if(error) {
        console.log(error?.response)
        return
      }

      if(data.search_string!==this.search_string) {
        return
      }
      this.search_suggestions = data.search_suggestions

      if(data.search_string===data.search_word) {
        this.search_dot_prefix = false
      } else {
        this.search_dot_prefix = true
      }
    },
  }
}
</script>

<style lang="scss" scoped>
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.directory-toggle {
  display: grid;
  grid-template-columns: 1fr 1fr;
  button {
    cursor: pointer;
    height: 42px;
    box-shadow: rgba(0, 0, 0, 0.05) 0px 0px 0px 1px, rgb(209, 213, 219) 0px 0px 0px 1px inset;
    &:hover {
      background-color: rgba(0, 0, 0, 0.2);
    }
    &:active {
      outline: 2px solid $text_dark;
    }
    &.active {
      color: white;
      background-color: $text_dark;
    }
  }
}
.search-results {
  padding: 8px;
  overflow-x: auto;
  .search-results-content {
    width: fit-content;
    min-height: 120px;
  }
}
.action-content {
  border-left: 2px solid gray;;
  height: 100%;
}
.archive {
  border: 2px solid gray;
  display: grid;
  grid-template-columns: 420px auto;
}
.search {
  padding: 4px;
}
.active-directory-path {
  grid-column: 1/3;
  height: 52px;
  display: flex;
  align-items: center;
  flex-direction: row;
  padding: 8px;
  gap: 8px;
  font-size: 20px;
  border-top: 2px solid gray;
  border-bottom: 2px solid gray;
  .active-file {
    color: $kit_green;
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 4px;
    .icon {
      transform: scale(1.2);
    }
  }

}
.directory-tree-content {
  .scroll-top-wrapper {
    overflow: auto;
    .scroll-top {
      height: 1px;
    }
  }

  .directory-tree-wrapper {
    min-height: 1204px;
    overflow: auto;
    .directory-tree {
      width: fit-content;
      // display: flex;
      // flex-direction: column;
      // justify-content: stretch;
      gap: 2px;
      padding: 4px 0px 4px 8px;
    }
  }

}
.home {
  font-size: 24px;
  display: flex;
  justify-content: center;
  align-items: center;

  button {
    transition: 225ms color ease;
    display: flex;
    flex-direction: row;
    &:disabled {
      color: #ccc !important;
    }
    &:hover {
      color: $kit_green;
    }
    &:active {
      filter: grayscale(50%);
    }
    cursor: pointer;
    // width: 32px;
    // height: 32px;
  }

}
.actions-file {
  border-left: 2px solid gray;
  display: flex;
  flex-direction: row;
  gap:16px;
  align-items: center;
  padding: 0 8px;
  .action {
    cursor: pointer;
    height: 56px;
    border: 1px solid black;
    border-radius: 4px;
    aspect-ratio: 1.25;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    &:active {
      filter: grayscale(50%);
    }
    &:hover {
      color: $kit_green;
      border: 1px solid $kit_green;
    }
    &:disabled {
      color: #ccc !important;
      border: 1px solid #ccc;
    }
    &.active {
      background-color: rgb(44, 62, 80);
      border: 1px solid rgb(44, 62, 80);
      color: white;
    }
    .icon {
      font-size: 32px;
    }
    .text {

    }
  }
}
</style>