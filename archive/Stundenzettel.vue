import axios from 'axios';
<template>
<link rel="stylesheet" href="Css/bootstrap.min.css"/>
<link src="http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
<link 
  href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.css" 
  rel="stylesheet"  type='text/css'>
<div id="formAllData">
 <form  class="control-form" >
  <div class="about">
  <div class="form-overview"> 
         <div class="container">
    <div class="title" ><h1 >Stundenzettel</h1></div>
    
    <div class="content">

        <div class="user-details">
          <div class="input-box">
            <span class="details">Name</span>
            <input type="text"  readonly id="name" placeholder=" &nbsp;&nbsp; Name des Mitarbeiters" required :value="user ? `${user.firstname} ${user.lastname}` : ''">
             <i class="fa fa-user"></i>
          </div>
          <div class="input-box" style="align:right;">
            <span class="details">Personalnummer</span>
            <input type="text"  id="personalNumber" placeholder="&nbsp;&nbsp; personalNumber" required v-model="personalNumber">

            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-bounding-box" viewBox="0 0 16 16">
  <path d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1h-3zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5zM.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5z"/>
  <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
</svg>

          </div>
          <div class="input-box" style="align:right;">
            <span class="details" >Email</span>
            <input type="text" id="email"  pattern="/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/" readonly placeholder="&nbsp;&nbsp; email" required :value="user ? `${user.email}` : ''">
            <i class="fa fa-envelope"></i>
          </div>
          <div class="input-box" style="align:right;">
            <span class="details">Stundensatz</span>
            <input type="text" id="workingHours" placeholder="&nbsp;&nbsp;Stundensatz" required v-model= "workingHours">

            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hourglass-split" viewBox="0 0 16 16">
  <path d="M2.5 15a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11zm2-13v1c0 .537.12 1.045.337 1.5h6.326c.216-.455.337-.963.337-1.5V2h-7zm3 6.35c0 .701-.478 1.236-1.011 1.492A3.5 3.5 0 0 0 4.5 13s.866-1.299 3-1.48V8.35zm1 0v3.17c2.134.181 3 1.48 3 1.48a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351z"/>
</svg>

          </div>
          <div class="input-box" style="align:right;">
            <span class="details">Vertraglich vereinbarte Arbeitszeit</span>
            <input type="text" id="NumberOfHours" placeholder="&nbsp;&nbsp; vereinbarte Arbeitszeit" required v-model= "NumberOfHours">

            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
  <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.997zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.76.2 1.126.342l-.36.933zm1.37.71a7.01 7.01 0 0 0-.439-.27l.493-.87a8.025 8.025 0 0 1 .979.654l-.615.789a6.996 6.996 0 0 0-.418-.302zm1.834 1.79a6.99 6.99 0 0 0-.653-.796l.724-.69c.27.285.52.59.747.91l-.818.576zm.744 1.352a7.08 7.08 0 0 0-.214-.468l.893-.45a7.976 7.976 0 0 1 .45 1.088l-.95.313a7.023 7.023 0 0 0-.179-.483zm.53 2.507a6.991 6.991 0 0 0-.1-1.025l.985-.17c.067.386.106.778.116 1.17l-1 .025zm-.131 1.538c.033-.17.06-.339.081-.51l.993.123a7.957 7.957 0 0 1-.23 1.155l-.964-.267c.046-.165.086-.332.12-.501zm-.952 2.379c.184-.29.346-.594.486-.908l.914.405c-.16.36-.345.706-.555 1.038l-.845-.535zm-.964 1.205c.122-.122.239-.248.35-.378l.758.653a8.073 8.073 0 0 1-.401.432l-.707-.707z"/>
  <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0v1z"/>
  <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5z"/>
</svg>
          </div>
          <div class="input-box">
            <span class="details">Institut/Organisationseinheit</span>
            <input type="text"  id="institute" placeholder="&nbsp;&nbsp; Institut/Organisationseinheit" required v-model= "institute">

            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zm11-11V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"/>
  <path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"/>
</svg>
          </div>
        </div>
        
 
        <h3 class="text-success" align="center">Insert Times Below</h3><br>    

        <div class="container" id="app">
            
            <!-- Example row of columns -->
            <div class="row">
                <div class="block">
                    <form id="timeTable">
                        <table  class="table table-condensed">
                          <thead>
                          <svg id="plus" @click="addRow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-square" viewBox="0 0 16 16">
  <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
</svg>
                                
                                <tr>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Task Being Done</th>
                                    <th>Day</th>
                                    <th>Vacation</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="hoursBody">
                                
                                
                                <tr v-for='(line, index) in lines' :key="line">
                                    <td><input v-model="line.StartTime" class="form-control"  type="time"  ></td>
                                    <td><input v-model="line.EndTime" class="form-control" type="time" ></td>
                                    <td><input v-model="line.TaskBeingDone" class="taskInput" type="text" placeholder="Enter Task..."></td>
                                    <td><input v-model="line.Day"  class="form-control"  type="date" ></td>
                                    <td><input v-model="line.Vacation"  class="form-control"  type="time" ></td>
                                    <td> {{ dailyTotal(line) }}  </td>

                                    <svg  id="remove" @click="deleteRow(index)" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-backspace" viewBox="0 0 16 16">
  <path d="M5.83 5.146a.5.5 0 0 0 0 .708L7.975 8l-2.147 2.146a.5.5 0 0 0 .707.708l2.147-2.147 2.146 2.147a.5.5 0 0 0 .707-.708L9.39 8l2.146-2.146a.5.5 0 0 0-.707-.708L8.683 7.293 6.536 5.146a.5.5 0 0 0-.707 0z"/>
  <path d="M13.683 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7.08a2 2 0 0 1-1.519-.698L.241 8.65a1 1 0 0 1 0-1.302L5.084 1.7A2 2 0 0 1 6.603 1h7.08zm-7.08 1a1 1 0 0 0-.76.35L1 8l4.844 5.65a1 1 0 0 0 .759.35h7.08a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1h-7.08z"/>
</svg>
                                   
                                </tr>
                                <tr>
                                  <td>
                                    <h4>Total hours worked:</h4>
                                  </td> 
                                  <td></td>
                                  <td></td>
                                  <td></td>
                                  <td></td>
                                  <td>
                                    <h4>{{ calcWeekHours(lines) }}</h4>
                                  </td>
                                </tr>                              
                            </tbody>
                        </table>
                        <br />
                   
                    </form>
                    
                </div>
                
            </div>
            </div> <!-- /container -->
                               
         <div @click.prevent="onSubmit()"  class="kit-button submit" id="submit"  >
          <span style='text-decoration:underline' align: center>Submit</span>
         </div>
     

    </div>
  </div>   
  </div>
</div>  
</form>
</div>  
<h2> Click On the Button to Print the Web-Page ! </h2>
<button @click.prevent="printPage()"> <b>Pint Page</b></button>
</template>


<script>
import axios from "axios";
import moment from "moment"
export default {
  
  name: 'Stundenzettel',
  components: {
    
  },
  data() {
    return {
      user: null,
        form: {
              name: '',
              personalNumber: '',
              email: '',
              workingHours: '',
              NumberOfHours: '',
              institute: '',
              lines : 'null',
            },
        lines: [{StartTime:'',EndTime:'',TaskBeingDone:'',Day:'',Vacation:'',Total:''}],
            
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
  },
  watch: {
    '$store.state.user': function(to) {
      this.user = to
      console.log(to)

    }
  },
  // computed: {
  //   user() {
  //     // console.log(this.$store.getters.getLoginInformation.user)
  //     return this.$store.getters.getLoginInformation.user;
  //   }
  // },
methods: {

    printPage: function(){
      const formData = new FormData()
                  formData.append('name', this.name)
                  formData.append('personalNumber', this.personalNumber)
                  formData.append('email', this.email)
                  formData.append('workingHours', this.workingHours)
                  formData.append('NumberOfHours', this.NumberOfHours)
                  formData.append('institute', this.institute)
      var prtContent = document.getElementById("formAllData");
      var WinPrint = window.open('', '', 'left=0,top=0,width=1600,height=1800,toolbar=0,scrollbars=0,status=0');
      WinPrint.document.write(prtContent.innerHTML);
      WinPrint.document.close();
      WinPrint.focus();
      WinPrint.print();
      WinPrint.close();
      
    },

    addRow: function() {      
       this.lines.push({StartTime:'',EndTime:'',TaskBeingDone:'',Day:'',Vacation:'',Total:''})
    },
    deleteRow(index){    
        this.lines.splice(index,1);             
    } ,   
    dailyTotal: function(line) {
      
      var start = moment(line.Day + " " + line.StartTime);
      var end = moment(line.Day + " " + line.EndTime);
      var hms = line.Vacation;   
      var a = hms.split(':'); 
      var VacationMinutes = (+a[0]) * 60 + (+a[1]);

      if( line.Date == "" || line.StartTime== "" || line.EndTime == "")
      {
        return 0;
      } 
      var dailyTotal = (end.diff(start, 'minutes')) - VacationMinutes;
      var hours = Math.floor(dailyTotal / 60);          
      var minutes = dailyTotal % 60;
      line.Total = dailyTotal.toFixed(2); 
      hours = hours < 10 ? '0' + hours : hours; 
      minutes = minutes < 10 ? '0' + minutes : minutes; 
      return hours + ':' + minutes;
    },
    calcWeekHours: function(lines) {
      var totalhours = 0;

      for (var i = 0; i < lines.length; i++) {
        if( lines[i].Date == "" || lines[i].StartTime== "" || lines[i].EndTime == "")
        {
          totalhours += 0;
        } else{
          totalhours += parseFloat(lines[i].Total);
        }
      }
      var hours = Math.floor(totalhours / 60);          
      var minutes = totalhours % 60;
      hours = hours < 10 ? '0' + hours : hours; 
      minutes = minutes < 10 ? '0' + minutes : minutes; 
      return hours + ':' + minutes;
      
    },
         handleSubmit() {
            const data = {
                name: null,
                personalNumber: null,
                email: null,
                workingHours: null,
                NumberOfHours: null,
                institute: null
                };
            console.log(data)
            },  

            onSubmit() {
                  const formData = new FormData()
                  formData.append('personal_nummer', this.personalNumber)
                  formData.append('vereinbarte_arbeitszeit', this.workingHours)
                  formData.append('stundensatz', this.NumberOfHours)
                  formData.append('institut', this.institute)

                  
                  var arbeitstage = [];
                  for (var i = 0; i < this.lines.length; i++) {
                    var arbeitstag = {};
                    arbeitstag["start"] = new Date(`${this.lines[i].Day}, ${this.lines[i].StartTime}`);
                    arbeitstag["end"] = new Date(`${this.lines[i].Day}, ${this.lines[i].EndTime}`)
                    arbeitstag["task"] = this.lines[i].TaskBeingDone;
                    arbeitstag["vacation_millsec"] = this.lines[i].Vacation.split(':')[0]*60*60*1000 + this.lines[i].Vacation.split(':')[1]*60*1000;
                    arbeitstage.push(arbeitstag)
                  }
                  
                  formData.append("arbeitstage", JSON.stringify(arbeitstage));
                  

                  for(var pair of formData.entries()) {
                    console.log(pair)
                  }
                  axios.post('https://www-3.mach.kit.edu/api/shib/mach-api/public/index.php/api/stundenzettel', formData, {
                    headers: {
                     'Content-Type': 'multipart/form-data'
                     }
                  }).then((response) => {
                    console.log(response)
                  }).catch(error=>{
                    console.log(error)
                  })
                },

    getPosts(){
            axios.get('https://www-3.mach.kit.edu/api/StundenZettel.php', this.form)
                .then((response) => {
                  this.info = response
                  console.log(response.data)
                }) 

        },











               


        









    }
  
}

</script>

<style lang="scss" scoped>
  @import url(https://fonts.googleapis.com/css?family=Roboto:400,500,700);

*{
  font-family: 'Roboto', sans-serif;
}

.body{
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 10px;
  background: linear-gradient(135deg, #71b7e6, #9b59b6);
}

.container{
  max-width: 700px;
  width: 100%;
  background-color: #fff;
  padding: 25px 30px;
  border-radius: 5px;
  box-shadow: 0 5px 10px rgba(0,0,0,0.15);
}

.container .title::before{
  content: "";
  position: absolute;
  left: 0;
  bottom: 0;
  height: 3px;
  width: 30px;
  border-radius: 5px;
  background: linear-gradient(135deg, #71b7e6, #9b59b6);
}

.border-none{
  border: none;
}

.table th, .table td { 
     border-top: none !important; 
 }

 .content form .user-details{
  display: 
  flex;
  flex-wrap: wrap;
  justify-content: space-between;
  margin: 20px 0 12px 0;
  align : center;
}

form .user-details .input-box{
  margin-bottom: 15px;
  width: calc(100% / 2 - 20px);
  align : center;
}

form .input-box span.details{
  display: block;
  font-weight: 500;
  margin-bottom: 5px;
  align : center;
}


.user-details .input-box input{
  height: 45px;
  width: 100%;
  outline: none;
  font-size: 16px;
  border-radius: 5px;
  padding-left: 15px;
  border: 1px solid #ccc;
  border-bottom-width: 2px;
  transition: all 0.3s ease;
  align : center;

}

.user-details .input-box input:focus,
.user-details .input-box input:valid{
  border-color: #9b59b6;
}

form .category{
   display: flex;
   width: 80%;
   margin: 14px 0 ;
   justify-content: space-between;
 }

  form .category label{
   display: flex;
   align-items: center;
   cursor: pointer;
 }

  form .category label .dot{
  height: 18px;
  width: 18px;
  border-radius: 50%;
  margin-right: 10px;
  background: #d9d9d9;
  border: 5px solid transparent;
  transition: all 0.3s ease;
}
#dot-1:checked ~ .category label .one,
 #dot-2:checked ~ .category label .two,
 #dot-3:checked ~ .category label .three{
   background: #9b59b6;
   border-color: #d9d9d9;
 }


  form input[type="radio"]{
   display: none;
 }
 form .button{
   height: 45px;
   margin: 35px 0
 }

  form .button input:hover{
  /* transform: scale(0.99); */
  background: linear-gradient(-135deg, #71b7e6, #9b59b6);
  }
 @media(max-width: 584px){
 .container{
  max-width: 100%;
}
form .user-details .input-box{
    margin-bottom: 15px;
    width: 100%;
    align : center;
  }
  form .category{
    width: 100%;
  }
  .content form .user-details{
    max-height: 300px;
    overflow-y: scroll;
  }
  .user-details::-webkit-scrollbar{
    width: 5px;
  }
  }
  h3 {
    font-size: 24px;
    line-height: 40px;
}
 .Stundenzettel-body-wrapper {
    position:relative;
    overflow-y:auto;
  }

   .Stundenzettel-head-wrapper {
    overflow-x: hidden;
  }

  .Stundenzettel-actions {
    width: 15%;
    padding: 12px 0px;
    text-align: center;
  }
   .Stundenzettel-pagination {
    background: #f9fafb !important;
  }
  .Stundenzettel-gutter-col {
    padding: 0 !important;
    border-left: none  !important;
    border-right: none  !important;
  }
  .Stundenzettel-semantic-no-top {
    border-top:none !important;
    margin-top:0 !important;
  }
  .Stundenzettel-fixed-layout {
    table-layout: fixed;
  }
  .Stundenzettel-clip-text {
    white-space: pre-wrap;
    text-overflow: ellipsis;
    overflow: hidden;
    display: block;
  }

  .form-overview i {
    height: 0;
    float: left;
    position: relative;
    top: -33px;
    left: 2px;
  }

 

  .control-form svg{
    float: left;
    position: relative;
    top: -33px;
    left: 2px;
  }

  .kit-button{
    align: center;
    text-align: center;

  }

  .btn {
  background-color: gutter;
  padding: 14px 28px;
  font-size: 16px;
  cursor: pointer;
  display: inline-block;
}

/* On mouse-over */
.btn:hover {background: #eee;}

.success {color: green;}
.info {color: dodgerblue;}
.warning {color: orange;}
.danger {color: red;}
.default {color: black;}



#remove {
    float: left;
    position: relative;
    top: 8px;
    left: 2px;
}

#plus {
    float: left;
    position: relative;
    top: 45px;
    left: -30px;
}


</style>
