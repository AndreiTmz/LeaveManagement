

function setDates()
{
	var begin = document.getElementById("begin");
	var end = document.getElementById("end");
	
	var tomorrow = new Date();
	tomorrow.setDate(tomorrow.getDate()+1);
	tomorrow=tomorrow.toISOString().split('T')[0];		
	
	begin.setAttribute('min', tomorrow);
	
	end.setAttribute('min',tomorrow);	
	begin.addEventListener('change',setEndDates);
}

function setEndDates()
{
	var end = document.getElementById("end");
	var begin = document.getElementById("begin");
	
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
	
	if(startDate > endDate || !end.value)
	{	
		end.value = startDateString;
		calculNrZile();
	}
}

function calculNrZile()
{
	var begin = document.getElementById("begin");
	var end = document.getElementById("end");
	
	var nrZileTextInput = document.getElementById("nrZile");
	var hiddenInput = document.getElementById("nrZileHiddenInput");
	
	var beginDate = new Date(begin.value);
	var endDate = new Date(end.value);
	
	 beginDate = beginDate.getTime();
	 endDate = endDate.getTime();
	
	var oneDay=1000*60*60*24;
	var nrZile = endDate - beginDate;
	nrZile = (nrZile/oneDay)+1;
	if(!isNaN(nrZile))
	{
		nrZileTextInput.innerHTML = nrZile;	
		hiddenInput.value = nrZile;
	}
	else
	{
		nrZileTextInput.innerHTML = "-";
		hiddenInput.value = 0;
	}	
}

function zileRamase()
{
	hiddenInput = document.getElementById("nrZileHiddenInput"); //zile selectate
	zileDisponibileInitial = document.getElementById("nr_zile_disp");
	nrZileRamase = document.getElementById("nrZileRamase");
	overflow = document.getElementById("overflow");
	
	if(hiddenInput.value >= 0 && zileDisponibileInitial.value - hiddenInput.value>=0)
	{
		nrZileRamase.innerHTML = zileDisponibileInitial.value - hiddenInput.value;
		overflow.innerHTML = "";
	}
	else if(zileDisponibileInitial.value - hiddenInput.value<0)
	{
		nrZileRamase.innerHTML = 0;
		overflow.innerHTML = "Ati depasit numarul maxim  cu " +  Math.abs(zileDisponibileInitial.value - hiddenInput.value) +" zile";
	}
	else
	{
		nrZileRamase.innerHTML = zileDisponibileInitial.value - 1;
		overflow.innerHTML = "";
	}		
}	

