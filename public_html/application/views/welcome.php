<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<!-- JQUERY -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<!-- DYGRAPHS PLUGIN -->
	<script src="//cdnjs.cloudflare.com/ajax/libs/dygraph/2.1.0/dygraph.min.js"></script>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/dygraph/2.1.0/dygraph.min.css" />
	<!-- JQUERY UI -->
	<script src="<?php echo base_url(); ?>js/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="<?php echo base_url(); ?>css/jquery-ui.min.css" />
	<!-- TIMEPICKER PLUGIN -->
    <script type="text/javascript" src="<?php echo base_url(); ?>js/jquery.timepicker.min.js"></script>
	<link rel="stylesheet" href="<?php echo base_url(); ?>css/jquery.timepicker.min.css" type="text/css" />
    <script type="text/javascript" src="<?php echo base_url(); ?>js/jquery-ui-timepicker-addon.js"></script>
	<link rel="stylesheet" href="<?php echo base_url(); ?>css/jquery-ui-timepicker-addon.css" type="text/css" />
	<!-- MAIN CSS -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>css/main.css" type="text/css" />

	<meta charset="utf-8">
	<title>CPU Monitor</title>
</head>
<body>

<div id="container">
	<h1>CPU Monitor</h1>

	<div id="body">
		<p>Please select a date range below.</p>
		<p>
			From: <input type="text" id="datetime_from">
            To: <input type="text" id="datetime_to">
			<input type="button" id="reset_dates" value="Reset Dates">&nbsp;
			<input type="button" id="toggle_data" value="Show Concurrency">
			<img id="loading" src="<?php echo base_url(); ?>images/loading.gif" style="display:none;margin:0 0 0 10px;" />
		</p>
		<div id="graph_area"></div>
		<div id="stats_area"></div>
	</div>

	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
</div>

<script type="text/javascript">
	$(document).ready(function() {

		//init vars
		var ajax_object, graph_object, toggle_data = 0;

		//date range input behaviour
		$('#datetime_from, #datetime_to').datetimepicker({
			dateFormat: 'dd/mm/yy',
			timeFormat: 'HH:mm',
			maxDate: '<?php echo date('d/m/Y', time()); ?>',
			onSelect: function(){
				prep_unix_timestamps();
			}
		});

		//set default dates for datetime pickers function
		function set_default_dates(){
			var temp_date = new Date();
			temp_date.setHours(temp_date.getHours() - 1);
			$('#datetime_from').datetimepicker('setDate', temp_date);
			$('#datetime_to').datetimepicker('setDate', new Date());
		}

		//set default values on page load
		set_default_dates();

		//reset date button behaviour
		$('#reset_dates').click(function(){
			set_default_dates();
			prep_unix_timestamps();
		});

		//gather timestamps from input fields function
		function prep_unix_timestamps(){
			var timestamp_from = $('#datetime_from').datetimepicker('getDate') / 1000;
			var timestamp_to = $('#datetime_to').datetimepicker('getDate') / 1000;
			generate_graph(timestamp_from, timestamp_to);
		}

		//graph generation function
		function generate_graph(timestamp_from, timestamp_to){
			//the selected dates are in the right order
			if(timestamp_from < timestamp_to){
				//show the loading gif
				$('#loading').fadeIn();
				//don't try multiple requests at once (abort any ongoing)
				if(ajax_object){
					ajax_object.abort();
				}
				//post request to the API
				ajax_object = $.post('<?php echo base_url(); ?>api/get_cpu_data',{
					'timestamp_from':timestamp_from,
					'timestamp_to':timestamp_to + 60 //add a minute as we don't show seconds (makes search inclusive)
				}, function(data){
					//hide the loading gif
					$('#loading').stop().hide();
					//if we have data, make the graph
					if(data && data.status == true){
						//process the returned results and gather stats
						var processed_data = process_api_data(data.results);

						//initialise the graph
						graph_object = new Dygraph(document.getElementById('graph_area'),
							processed_data.results,
							{
								colors: ['red', 'green'],
								connectSeparatedPoints: true,
								legend: 'always',
								highlightCircleSize: 4,
								height: 500,
								labels: ['Date/Time','CPU Load (%)','Concurrency'], 
								labelsSeparateLines: true,
								yRangePad: null,
								visibility: [(toggle_data == 0 ? true : false), (toggle_data == 1 ? true : false)] //remember which series we are plotting if dates changed
							}
						);

						//add the stats section under the graph
						$('#stats_area').stop().hide().html('<p>\
							Datapoints: <b>'+processed_data.total_count+'</b><br />\
							Min CPU Load: <b>'+processed_data.min_cpu_load.toFixed(2)+'%</b>, Avg CPU Load: <b>'+processed_data.avg_cpu_load.toFixed(2)+'%</b>, Max CPU Load: <b>'+processed_data.max_cpu_load.toFixed(2)+'%</b><br />\
							Min Concurrency: <b>'+processed_data.min_concurrency+'</b>, Avg Concurrency: <b>'+Math.round(processed_data.avg_concurrency)+'</b>, Max Concurrency: <b>'+processed_data.max_concurrency+'</b>\
						</p>').fadeIn();

						//as we have data, show the cpuLoad / concurrency plot toggle button
						$('#toggle_data').fadeIn();
					//if there is no data returned, show a message instead
					}else{
						$('#graph_area').stop().hide().html('<em>There is no data to display.</em>').fadeIn();
						$('#toggle_data, #stats_area').stop().hide();
					}
				});
			//the selected dates are not in the right order
			}else{
				$('#graph_area').stop().hide().html('<em>Invalid date selection.</em>').fadeIn();
				$('#toggle_data, #stats_area').stop().hide();
			}
		}

		//process returned api data to format timestamp and calculate stats
		function process_api_data(data){
			//init vars
			var min_cpu_load, avg_cpu_load, max_cpu_load, min_concurrency, avg_concurrency, max_concurrency, total_count = 0, total_cpu_load = 0, total_concurrency = 0;
			//process the returned results
			$.each(data, function(key, value){
				//format the timestamp into a date object for the graph
				value[0] = new Date(value[0]*1000);
				//collect stats
				total_count++;
				total_cpu_load += value[1];
				total_concurrency += value[2];
				min_cpu_load = (!min_cpu_load || value[1] < min_cpu_load ? value[1] : min_cpu_load);
				min_concurrency = (!min_concurrency || value[2] < min_concurrency ? value[2] : min_concurrency);
				max_cpu_load = (!max_cpu_load || value[1] > max_cpu_load ? value[1] : max_cpu_load);
				max_concurrency = (!max_concurrency || value[2] > max_concurrency ? value[2] : max_concurrency);
			});
			avg_cpu_load = total_cpu_load / total_count;
			avg_concurrency = total_concurrency / total_count;
			//return object
			return {
				results:data, 
				min_cpu_load:min_cpu_load, 
				avg_cpu_load:avg_cpu_load, 
				max_cpu_load:max_cpu_load, 
				min_concurrency:min_concurrency, 
				avg_concurrency:avg_concurrency, 
				max_concurrency:max_concurrency, 
				total_count:total_count, 
				total_cpu_load:total_cpu_load, 
				total_concurrency:total_concurrency
			}
		}

		//show cpu load or concurrency button behaviour
		$('#toggle_data').click(function(){
			//if we have a graph object to manipulate
			if(graph_object){
				//show concurrency
				if(toggle_data == 0){
					toggle_data = 1;
					graph_object.setVisibility(0,false);
					graph_object.setVisibility(1,true);
					$('#toggle_data').val('Show CPU Load');
				//show cpu load
				}else{
					toggle_data = 0;
					graph_object.setVisibility(0,true);
					graph_object.setVisibility(1,false);
					$('#toggle_data').val('Show Concurrency');
				}
			}
		});

		//initial graph generation on page load (after default values applied to datetimepickers)
		prep_unix_timestamps();
	});
</script>

</body>
</html>