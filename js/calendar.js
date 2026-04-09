var periodDetailsArray = [];
var periodsArray = [];
  // culoare diferita pt fiecare angajat atunci cand se afiseaza pt un singur departament
var colorArr = ["#f4a864","#7303b8","#24c6f4","#a48733","#00fd48","#103243","#a7de8e"];

$(function() {
	showTodaysDate();
	initializeCalendar();
	getCalendars();  
	disableEnter();
	
	$("#everybody").click(function(){
		$("#calendar1").fullCalendar("removeEvents");
		$("#calendar1").fullCalendar("addEventSource",periodsArray);
	});
  
});

//reload events per department
function seePeriodsPerDep(dep)
{
	$("#calendar1").fullCalendar('removeEvents');
		var k = 0; //pt initializare vector nou cu perioadele pe departament
		periodsPerDepArray = [];
		for(var i=0;i<periodDetailsArray.length;i++)
		{
			var index = periodDetailsArray[i].id_ang  % colorArr.length;
			color = colorArr[index];
			
			if(periodDetailsArray[i].id_dep == dep)
			{	 periodsPerDepArray[k++] = {
					  id: i,
					  title: periodDetailsArray[i].nume + " - " + periodDetailsArray[i].id_dep,
					  start: periodDetailsArray[i].data_inceput,
					  end:  periodDetailsArray[i].data_sfarsit,
					  backgroundColor: color
						}	
						
			}					
		}
		$("#calendar1").fullCalendar('addEventSource',periodsPerDepArray);	
}

/* --------------------------load date in navbar-------------------------- */

var showTodaysDate = function() {
  n =  new Date();
  y = n.getFullYear();
  m = n.getMonth() + 1;
  d = n.getDate();
  $("#todaysDate").html("Today is " + m + "/" + d + "/" + y);
  
};


			
			
/* --------------------------initialize calendar-------------------------- */
var initializeCalendar = function() {
	
	var xmlhttp;
	
	xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		var loading = document.getElementById("loading");
		loading.style.display = "block";
		
		if (this.readyState == 4 && this.status == 200) {
			loading.style.display = "none";
			var jsonArray = this.responseText.split("<br>");
			
			var color;
			var txtColor;
			
			var fctAndDep = document.getElementById("func").innerHTML;
			fctAndDep = fctAndDep.split(",");
			fctAndDep[0] = fctAndDep[0].trim();
			fctAndDep[1] = fctAndDep[1].trim();

			for(var i=0;i<jsonArray.length-1;i++)
			{
				periodDetailsArray [i] = JSON.parse(jsonArray[i]);
			}
			
			for(var i=0;i<periodDetailsArray.length;i++)
			{
				
			if(fctAndDep[0] == "sef_dep" && fctAndDep[1]=="HR")
			{
				if(periodDetailsArray[i].id_dep == "IT")
				{
					color = "#264E36";
					txtColor = "white";
				}
				else if(periodDetailsArray[i].id_dep == "HR")
				{	
					color = "#FFD662";
					txtColor = "black";
				}
				else if(periodDetailsArray[i].id_dep == "Aprovizionare")
				{
					color = "#223A5E";			
					txtColor = "white";
				}
			}
			else if(fctAndDep[0] == "sef_dep" && fctAndDep[1] != "HR")
			{	
				var index = periodDetailsArray[i].id_ang  % colorArr.length;
				color = colorArr[index];
			}
			else
			{
				color = "#4169E1";
			}
			
			periodsArray[i] = {
				id: i,
				title: periodDetailsArray[i].nume + " - " + periodDetailsArray[i].id_dep,
				start: periodDetailsArray[i].data_inceput,
				end:  periodDetailsArray[i].data_sfarsit,
				backgroundColor: color,
				textColor: txtColor
			}
		}
			
			
		var x = $('.calendar').fullCalendar({
			editable: true,
			eventLimit: true, // allow "more" link when too many events
			// create events
			events: periodsArray,
			defaultTimedEventDuration: '00:30:00',
			forceEventDuration: true,
			//eventBackgroundColor: 'red', //'#337ab7'
			editable: true,
			height: screen.height - 160,
			timezone: 'Romania/Bucharest'
		});	
		
		initializeLeftCalendar();
		}
	};
	
	xmlhttp.open("GET", "getEvents.php", true);
	xmlhttp.send();
}

/* -------------------manage cal1 (left pane)------------------- */

var initializeLeftCalendar = function() {
  $cal1.fullCalendar('option', {
      header: {
          left: 'prev,next today',
          center: 'title',
          right: 'month,agendaWeek'
      },
      navLinks: false,
      dayClick: function(date) {
          cal2GoTo(date);
      },
      eventClick: function(calEvent) {
          cal2GoTo(calEvent.start);
      }
  });
} 

/*--------------------------calendar variables--------------------------*/
var getCalendars = function() {
  $cal = $('.calendar');
  $cal1 = $('#calendar1');
}

var disableEnter = function() {
  $('body').bind("keypress", function(e) {
      if (e.keyCode == 13) {
          e.preventDefault();
          return false;
      }
  });
}