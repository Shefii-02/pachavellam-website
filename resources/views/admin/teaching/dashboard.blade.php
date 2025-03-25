@extends('admin.teaching.section')
	@section('content2')


<!-- **************** MAIN CONTENT START **************** -->

		<div class="row">
			<div class="col-12 mb-3">
				<h1 class="h3 mb-2 mb-sm-0">Dashboard</h1>
			</div>
		</div>


			<!-- Chart and Ticket START -->
			<div class="row g-4 mb-4">
			    <div class="col-md-6 ">
			        <div class="card bg-light">
                        <div id="calicut_university" style="width:100%; max-width:100%; height:300px;"></div>
			        </div>
			    </div>
			    <div class="col-md-6">
			        <div class="card bg-light">
                        <div id="calicut_university_data_entry" style="width:100%; max-width:100%; height:300px;"></div>
                    </div>
			    </div>
			
				<div id="ChartPayout"></div>
			</div>
<!-- Page content END -->

@endsection

@section('scripts')
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script>
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
var data = google.visualization.arrayToDataTable([
  ['Course', 'Mhl'],
  ['Course',10],
  ['Subject',30],
  ['Semester',90],
]);

var options = {
  title:'Calicut University',
  backgroundColor: '#E4E4E4',
};

var chart = new google.visualization.PieChart(document.getElementById('calicut_university'));
  chart.draw(data, options);
  
    var data2 = google.visualization.arrayToDataTable([
      ['Details', 'Mhl'],
      ['Question Paper',98],
      ['Material',20],
      ['Syllabus',10],
    ]);
    
    var options2 = {
          title:'Calicut University Data Entry',
           backgroundColor: '#E4E4E4',
         
        };
  var chart = new google.visualization.PieChart(document.getElementById('calicut_university_data_entry'));
  chart.draw(data2, options2);
  
  



    var data3 = google.visualization.arrayToDataTable([
      ['Contry', ''],
      ['Mon',55],
      ['Tue',49],
      ['Wed',44],
      ['Thu',24],
      ['Fri',15],
      ['Sat',24],
      ['Sun',15]
    ]);
    
    var options3 = {
        title:'Website Views',
        backgroundColor: '#E4E4E4',
         hAxis: {title: 'Week'},
        vAxis: {title: 'No:of Users'},
    };

    var chart = new google.visualization.BarChart(document.getElementById('website_views'));
    chart.draw(data3, options3);

}
</script>
@endsection
