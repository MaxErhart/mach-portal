<template>
  <div id="submissions">
    <h1>Form Submissions</h1>
    <div id="form-submissions" v-if="form != null">
      <div id="form-submissions-header">
        Submissions for form: <span>{{form.name}}</span>
      </div>
      <div id="form-submissions-body" :style="gridStyle">
        <div class="column" v-for="col in grid" :key="col">
          <div class="row" v-for="(item, key) in col" :key="key">
            <div class="grid-item" v-for="val in item" :key="val">
              {{val}}
            </div>
          </div>
        </div>
      </div>
    </div>
    
  </div>
</template>

<script>
import axios from "axios";
export default {
  name: 'Submissions',
  components: {
  },
  data() {
    return {
      form: null,
      elements: null,
      submissions: null,
    }
  },
  computed: {
    grid: function() {
      var grid = [{id: ['id']}];
      for(let i=0; i<this.elements.length; i++) {
        let temp = {}
        temp[this.elements[i].elementId] = [`${this.elements[i].data.labelName}`]
        grid.push(temp);
      }

      for(let i=0; i<grid.length; i++) {
        let key = Object.keys(grid[i])[0];
        for(let j=0; j<this.submissions.length; j++) {
          if(key == 'id') {
            grid[i][key].push(this.submissions[j][key])
          } else {
            grid[i][key].push(this.submissions[j]['data'][key])
          }          
          
        }
      }
      console.log(this.elements)
      console.log(grid)
      return grid
    },
    gridStyle: function() {
      console.log(this.grid[0]['id'].length)
      return {gridTemplateRows: `repeat(${this.grid[0]['id'].length}, auto)`, gridTemplateColumns: `repeat(${this.grid.length}, auto)`}
    }
  },  
  beforeCreate() {
    axios({
      method: 'post',
      url: 'https://www-3.mach.kit.edu/api/getFormSubmissions.php',
      data: {id: this.$route.params.id}
    }).then(response => {
      console.log(response.data)
      if(response.data.success) {
        this.form = response.data.metadata;
        this.elements = response.data.elements;
        this.submissions = response.data.submissions;
      } else {
        this.$router.push({name: 'Home'})
      }
      
    })
  },

}
</script>

<style lang="scss" scoped>
  #submissions {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  #form-submissions {
    width: 100%;
    background: #eee;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  #form-submissions-header {
    font-size: 20px;
    font-weight: 500;
    > span {
      text-decoration: underline;
    }
  }
  #form-submissions-body {
    width: 100%;
    display: grid;
    grid-gap: 4px;
    row-gap: 4px;
    grid-auto-flow: column;
  }

  .column {
    display: contents;
  }
  .row {
    display: contents;
  }

  .grid-item {
    background-color: #fff;
  }
</style>