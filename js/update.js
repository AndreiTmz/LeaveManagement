function update(p,beginDate,endDate,diff)
{
	document.getElementById("editForm").style.display = "block";
	document.getElementById("title").innerHTML = "Modificare perioada " + p;
	document.getElementById("editInfo").style.display = "none";
	
	
	beginDate = beginDate*1000;
	endDate = endDate*1000;
	
	var d = new Date(beginDate);
	d.setDate(d.getDate()+1);
	d = d.toISOString().split('T')[0];
	
	document.getElementById("change_begin").value =  d;
	document.getElementById("change_hidden").value = d;
	document.getElementById("nr_zile_init").value = diff;
	
	
	d = new Date(endDate);
	d.setDate(d.getDate() + 1);
	d = d.toISOString().split('T')[0];
	
	document.getElementById("change_end").value = d;
	document.getElementById("err").style.visibility = "hidden";
	setDatesForUpdate();
}

function setDatesForUpdate()
{
	var begin = document.getElementById("change_begin");
	var end = document.getElementById("change_end");
	
	var tomorrow = new Date();
	tomorrow.setDate(tomorrow.getDate()+1);
	tomorrow=tomorrow.toISOString().split('T')[0];	
	
		
	
	begin.setAttribute('min', tomorrow);
	
	end.setAttribute('min',tomorrow);	
	begin.addEventListener('change',setEndDatesForUpdate);
}


function setEndDatesForUpdate()
{
	var end = document.getElementById("change_end");
	var begin = document.getElementById("change_begin");
	
	var start_year = begin.value.split("-")[0];
	var start_month = begin.value.split("-")[1];
	var start_day = begin.value.split("-")[2];	
	
	
	var startDate = new Date();
	startDate.setFullYear(start_year);
	startDate.setMonth(start_month-1);
	startDate.setDate(start_day);
	startDateString = startDate.toISOString().split('T')[0];
	
	var endDate = new Date();
		endDate.setFullYear(start_year);
		endDate.setMonth(start_month-1);
		endDate.setDate(start_day);		
		endDate.setDate(endDate.getDate()+18);
		endDateString = endDate.toISOString().split('T')[0];
	
	end.setAttribute('min',startDateString);
	
	if(startDate.getDate()>=15 && startDate.getMonth()== 5 || startDate.getDate()<=28 && startDate.getMonth() == 7
		|| startDate.getMonth() == 6)
			end.setAttribute('max',endDateString);	
		else
			end.removeAttribute('max');
	
	
	//verifca daca data inceput e mai mare decat data sfarsit
	var end_year = end.value.split("-")[0];
	var end_month = end.value.split("-")[1];
	var end_day = end.value.split("-")[2];
	
	endDate = new Date();
	endDate.setFullYear(end_year);
	endDate.setMonth(end_month-1);
	endDate.setDate(end_day);
	
	if(startDate > endDate)
	{	
		end.value = startDateString;
		calculNrZileEdit();
	}
}

function calculNrZileEdit()
{
	var begin = document.getElementById("change_begin");
	var end = document.getElementById("change_end");
	
	var hiddenInput = document.getElementById("nr_zile_edit");	
	var beginDate = new Date(begin.value);
	var endDate = new Date(end.value);
	
	 beginDate = beginDate.getTime();
	 endDate = endDate.getTime();
	
	var oneDay=1000*60*60*24;
	var nrZile = endDate - beginDate;
	nrZile = (nrZile/oneDay)+1;
	if(!isNaN(nrZile))
	{
		hiddenInput.value = nrZile;
	}
	else
	{
		hiddenInput.value = 0;
	}
}