<?php
$un_id='0';
if(isset($att['id'])){
    $un_id=$att['id'];
}
$data2 = $data;
$dataLastRow = array_pop(json_decode($data2));
// print_r($stock_statistics)  ;
// print_r($stock_statistics2) ;
?>
<div class="qwi-filter">
    <form action="" method="post" name="qwi-filter" id="qwi-filter_<?php echo $un_id;?>" > 
  <div class="row"> <label> Filter:</label></div>
 <div class="row date-picker1">   
<label>Start Date</label>
<input type="text" value=''  id="startdate" name='startdate' class="" placeholder="Select Date" require data-input>
<a class="input-button" title="toggle" data-toggle>
        <i class="fa fa-calendar" aria-hidden="true"></i>
    </a> 
</div>
<div class="row date-picker2">   
<label>End Date</label>
<input type="text" value='' id="enddate" name='enddate'  class="date-picker" placeholder="Select Date"   require data-input>
<a class="input-button" title="toggle" data-toggle>
        <i class="fa fa-calendar" aria-hidden="true"></i>
    </a>
</div>
<div class="row">
<input type='submit' value="GO" name='filter-submit'>
</div>
</form>
</div> 
<div class="chartarea">
<canvas id="myChart_<?php echo $un_id;?>"   ></canvas>
<div class="side ">
    <?php 
        foreach ($stock_statistics as $stock_statistic) {
            
            if($stock_statistic['key'] == 'Prev. Closing Price') {
                $stock_close_price = $stock_statistic['value'];
            }
            if($stock_statistic['key'] == 'Bid') {
                $stock_bid = $stock_statistic['value'];
            }
            if($stock_statistic['key'] == 'Last Traded') {
                $stock_last_trade = $stock_statistic['value'];
            }
            if($stock_statistic['key'] == 'Open') {
                $stock_open = $stock_statistic['value'];
            }
            if($stock_statistic['key'] == 'Volume Traded') {
                $stock_volume_trade = $stock_statistic['value'];
            }
        }    
    ?>
        <div class="blue-area">
            <label id="date_<?php echo $un_id;?>"><?php echo date("F d Y",strtotime( $dataLastRow->x));?></label>
            <span id="volume_<?php echo $un_id;?>"><?php echo $stock_volume_trade;?></span> | Volume Traded (units)
        </div>
         
        <div class="yellow-area">
            <div class="item">
            <label id="open_<?php echo $un_id;?>"><?php echo $stock_open;?></label>
            open
           
            </div>
            <div class="item">
            <label id="lasttraded_<?php echo $un_id;?>"><?php echo $stock_last_trade;?></label>
            Last Traded Price
            </div>
            <div class="item">
            <label id="closeprice_<?php echo $un_id;?>"><?php echo $stock_close_price;?></label>
            Close Price
            </div>
           
        </div>
</div>
</div>
<script>
var ctx = document.getElementById('myChart_<?php echo $un_id;?>').getContext('2d');
var delayed;



function addData_<?php echo $un_id;?>(chart, label, data,startdate,enddate) {
    chart.data.labels=[];
  removeData_<?php echo $un_id;?>(chart);
  var i = 0;
var data2 = data
console.log(" first data");
console.log( data2);
  //
    chart.data.datasets.forEach((dataset) => {
        //dataset.label=label;
       // console.log(dataset.parsing);
       
   dataset.data.label = label[i];
   dataset.parsing=[]
   dataset.parsing.yAxisKey=label[i]
   
        dataset.data = data; 
        console.log(" last data");
        //console.log( lastData) 
       console.log(data) 
       i++;   

    }); 
    console.log(chart.config.data.labels[0]);
    
  chart.config.options.plugins.subtitle.text=  chart.config.data.labels[0]+" - "+chart.config.data.labels[chart.config.data.labels.length]
console.log(chart);
    chart.update();
   
    
        let lastData =  data2.pop();
    //  console.log(lastData['volume']);
         jQuery("#date_<?php echo $un_id;?>").html(lastData.x);
         jQuery("#lasttraded_<?php echo $un_id;?>").html('$'+lastData.lasttraded);
         jQuery("#closeprice_<?php echo $un_id;?>").html('$'+lastData.closedprice);
         jQuery("#open_<?php echo $un_id;?>").html('$'+lastData.closingbid);
        jQuery("#volume_<?php echo $un_id;?>").html(parseFloat(lastData.volume));

   
}
function removeData_<?php echo $un_id;?>(chart) {
    //chart.data.labels.pop();
    //chart.data.datasets .pop();
    chart.data.datasets.forEach((dataset) => {
        dataset.data=[];
        //dataset.parsing=[] 
       
        
    });
    chart.update();
}


const skipped = (ctx, value) => ctx.p0.skip || ctx.p1.skip ? value : undefined;
const down = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y ? value : undefined;
var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        // labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
        datasets: [{
            label: 'Last Traded Price',
           // fill:'start',
            data: <?php echo $data;?>,
            parsing:{
                yAxisKey:'lasttraded'
            },
            backgroundColor: [
                'rgba(137, 210, 171,1)',
              
            ],
            borderColor: [
                'rgba(137, 210, 171)',
             
            ],
            borderWidth: 1,
           
        },
        {
            label: 'NAV',
           // fill:'start',
            data: <?php echo $data;?>,
            
            showLine:true,
            parsing:{
                yAxisKey:'nav'
            },
            backgroundColor: [
                'rgba(72, 161, 208,1)',
              
            ],
            borderColor: [ 
                'rgba(72, 161, 208, 1)',
             
            ],
            borderWidth: 1,
           
            
        }
    ]
    },
    options: {
    //     animation: {
    //   onComplete: () => {
    //     delayed = true;
    //   },
    //   delay: (context) => {
    //     let delay = 0;
    //     if (context.type === 'data' && context.mode === 'default' && !delayed) {
    //       delay = context.dataIndex  300 + context.datasetIndex  100;
    //     }
    //     return delay;
    //   },
    // },
    spanGaps: true,
    interaction: {
      intersect: false,
    },

        responsive:true,
        maintainAspectRatio:true,
        plugins: {
            filler: {
        propagate: false,
      },
        legend:{
            display:true
        },
        subtitle:{
            display:false,
            text:" <?php echo date("M d Y",strtotime('-60 days'));?> -  <?php echo date("M d Y");?>",
            padding:{bottom:30}
        },
        title: {
                display: false,
                text: 'QWI Investmenst Limited Stock Price History',
                padding: {
                   
                    bottom:30
                }
            }

        },
        scales: {
           
            y: {
                
                // max:1.6,
                 min:0
               
            },
            x: {
                min:-10,
                ticks:{
                    includeBounds:true,
                    sampleSize:40,
                    // maxRotation: 90,
                    // minRotation: 90
                       
                }
        }
        }
    }
});


jQuery(document).ready(function(){


    jQuery(".date-picker1").flatpickr({
        wrap: true,
        maxDate: 'today',
        theme: 'airbnb',
        dateFormat: 'Y-m-d',
        onClose: function(selectedDates, dateStr, instance){
            endpicker.set('minDate', dateStr);
        }

  
});
  let endpicker =   jQuery(".date-picker2").flatpickr({

  wrap: true,
  minDate: jQuery(".date-picker1 input").val(),
  maxDate: 'today',
  theme: 'airbnb',
  dateFormat: 'Y-m-d',
  
});


    jQuery("#qwi-filter_<?php echo $un_id;?>").on("submit",function(e){
        e.preventDefault();
        
        var data = {
	    	'action': 'chart_data',
		    'startdate': jQuery("#qwi-filter_<?php echo $un_id;?> input[name='startdate']").val(),     // We pass php values differently!
            'enddate': jQuery("#qwi-filter_<?php echo $un_id;?> input[name='enddate']").val()     // We pass php values differently!
	     };
      
        jQuery.post(myAjax.ajaxurl, data, function(response) {
          //  console.log(response);



            addData_<?php echo $un_id;?>(myChart, ['lasttraded','nav'], response,data.startdate,data.enddate)
		    //alert('Got this from the server: ' + response);
	    });
    })
})


</script>
