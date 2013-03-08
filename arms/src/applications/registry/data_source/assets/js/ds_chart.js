/**
 */
$(document).ready(function() {
	google.setOnLoadCallback(drawChart);
	google.setOnLoadCallback(drawStatusCharts);

	$('#quality_report_status_dropdown').on('change',function(e){
		drawChart(null, this.value);
	});

	function drawChart(e, status) {

		if (typeof(status) == "undefined") { status = ''; }
	
		$.ajax({
			url: base_url + 'data_source/charts/getDataSourceQualityChart/' + $('#data_source_id').val() + "/" + status, 	//?XDEBUG_TRACE=1', //XDEBUG_PROFILE=1&
			type: 'GET',
			success: function(data)
			{
				var chart_data = new google.visualization.DataTable();
				var columns = {};


				// Calculate the sums of quality levels per status for % calculation
				$.each(data, function(i, item){
  					var miniSum=0;
  					$.each(item, function(j, qa_i){
  						if(!isNaN(qa_i))
  						{
  							miniSum += qa_i;

  							if (qa_i != 0)
  							{
  								columns[j] = true;
  							}
 
  						}
  					});
  					data[i].sum = miniSum;

  				});


				// Setup the graph columns, only displaying columns which actually have data...
  				chart_data.addColumn("string", "Class");
  				$.each(columns, function(i, item){
  					if (i != 'sum' && columns[i])
  					{
  						chart_data.addColumn({
  	  				        	label:i,
  	  				        	type:'number'
  	  				    });
  					}
  				});
  				chart_data.addColumn("number", "Sum");

  				// Handle colour corrections
				var chosenColourChart = [];
				var colorChart = {
					"Quality Level 1": '#F06533',
					"Quality Level 2": '#F2CE3B',
					"Quality Level 3": '#6DA539',
					"Gold Standard Record": '#4491AB',
				}

				for (_class in data)
				{
					var row = [_class];
					for (_quality in data[_class])
					{
						if (_quality != 'sum')
						{
							if (columns[_quality])
							{
								// If it hasn't already, add it to the color chart
								if (columns[_quality] > 0)
								{
									if (colorChart[_quality])
									{
										chosenColourChart.push(colorChart[_quality]);
										colorChart[_quality] = false;

									}
								}


								// Calculate value as a percentage!
								var numRecords = parseInt(data[_class][_quality]);
								var sumRecords = parseInt(data[_class].sum)
								row.push({v:numRecords/sumRecords, f:numRecords + " record(s)"});
							}
						}
						else
						{
							var sum = parseInt(data[_class][_quality]);
						}
					}
					row.push(sum);

					if (data[_class].sum > 0)
					{
						chart_data.addRow(row);
					}
				}


				// Setup the chart...
				var options = {
				  title: '',
				  sliceVisibilityThreshold:0,
				  isStacked:true,
				  colors: chosenColourChart,
				  hAxis: {title: "",format:'##%'},
				  vAxis: {title: "Class"},
				  chartArea:{left:100, width:"68%"},
				  height: 250,
				  legend: {position: 'right'},
				  backgroundColor: '#f9f9f9',
				};
				var dataView = new google.visualization.DataView(chart_data);

				// Set the display for all columns (Except the first!)
				columns = [0];
				for (var i=1; i<=chart_data.getNumberOfColumns()-1; i++)
				{
					if (chart_data.getColumnLabel(i) != 'Sum')
					{
						columns.push(i);
					}
				}

				if (columns.length > 1)
				{

					dataView.setColumns(columns);

					var chart = new google.visualization.BarChart(document.getElementById('overall_chart_div'));
					chart.draw(dataView, options);
				}
				else
				{
					$('#overall_chart_div').html("<i>No record data to display</i>");	
				}
			},
			dataType: 'json'
		});
	
	}


	function drawStatusCharts()
	{
		$.ajax({
			url: base_url + 'data_source/charts/getDataSourceStatusChart/' + $('#data_source_id').val(), 
			type: 'GET',
			dataType: 'json',
			success: function(data)
			{
				$.each(data, function(i, val)
				{
					var table_data = google.visualization.arrayToDataTable(val);
					var options = {
			          title: i,
			          chartArea: { width: 300, height:250 },
					  backgroundColor: '#f9f9f9',
			          pieSliceText: 'label',
			          legend: { position: 'none'},
			          pieSliceTextStyle: { fontSize: 13},
			          titleTextStyle: { fontSize: 12 } ,
			        };

			        $('#status_charts').append('<div id="status_chart_'+i+'" class="status_report_chart"></div>');

			        var chart = new google.visualization.PieChart(document.getElementById('status_chart_'+i));
		       		chart.draw(table_data, options);
				});
     		}
     	});
	}

});