
var app = new Vue({
    el: '#app',
    data() {
        return {
          dataset: [],
          intervalid1:'',
        };
    },
    mounted: function() {
        this.getData();
        this.todo();
    },
    methods:{
        todo: function(){           
            this.intervalid1 = setInterval(function(){
                this.getData();
                console.log("I getting data");
            }.bind(this), 5000);
        },
        getData: function(){
            axios
              .get('/api/getData.php')
              .then(response => (this.dataset = response.data));
        },
        onChange: function(event){
            console.log(event.target.value);
        },
    }
  });