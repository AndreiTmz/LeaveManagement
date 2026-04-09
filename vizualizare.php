<?php 
		include("inc/connect.php");
		$dbConnection = new dbClass();
		$dbConnection->connect();
		
		class event{
			public $id_ang, $data_inceput, $data_sfarsit, $nume, $id_dep;

			function event($id,$start,$end,$n,$dep)
			{
				$this->id_ang = $id;
				$this->data_inceput = $start;
				$this->data_sfarsit = $end;
				$this->nume = $n;
				$this->id_dep = $dep;
			}
		}
		
		if(!isset($_SESSION['logat_concedii']) && $_SESSION['logat_concedii']!=true)
		header("Location:index.php");
		
	
	$userId = $_SESSION['user_id_concedii'];
	$userInfo = $dbConnection->getUserInformation($userId);	
	$numeAngajat = $userInfo['nume'] . " " . $userInfo['prenume'];
	$userFct = $userInfo['functie'];
	
	//calcul numar de zile libere deja programate
	$countZileRamase = $dbConnection->remainingDays($userId,date("Y"));
	
	//nume departament
	$depInfo = $dbConnection->getDepInformation($userInfo['id_departament']);
	$numeDep = $depInfo['nume_departament'];
		
	//id dep
	$depId = $depInfo['id_departament'];
	
	//verificare functie pentru afisare sau nu a legendei
	if($userInfo['functie'] == "sef_dep" && $depId!= 2)   // HR id = 2
	{
		$x = "sef_dep";
		$veziPerioadeQuery = "SELECT * FROM concedii WHERE id_angajat IN (SELECT id_angajat FROM angajati WHERE id_departament = '" . $depId."')";		
	}
	else if($userInfo['functie'] == "sef_dep" && $depId == 2)  
	{
		$x = "sef_HR";
		$veziPerioadeQuery = "SELECT * FROM concedii";
	}
	else
	{
		$x = "ang";
		$veziPerioadeQuery = "SELECT * FROM concedii WHERE id_angajat='".$userId."'";
	}
		
	$veziPerioadeResult = $dbConnection->getDataFromDb($veziPerioadeQuery);	
?>
	
<span id="func" hidden> <?php echo $userFct . "," . $depInfo['nume_departament']; ?> </span>
	
<!DOCTYPE HTML>
<html  lang="en">
	<head>
		<title>Vizualizare concedii</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">

		<!-- jQuery library -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

		<!-- Latest compiled JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script> 
		<link href="css/legend.css" rel="stylesheet" />
		<link href='css/vendor/bootstrap.min.css' rel='stylesheet' />
		<link href='css/vendor/fullcalendar.css' rel='stylesheet' />
		<link href='css/style.css' rel='stylesheet' />

		<script src='js/vendor/jquery.min.js'></script>
		<script src='js/vendor/moment.min.js'></script>
		<script src='js/vendor/bootstrap.min.js'></script>
		<script src='js/vendor/fullcalendar.js'></script>
		<script src='js/calendar.js'></script>
	</head>
	<body>
		<div class="container-fluid">	
			<h3> <?php echo $numeAngajat; ?> , perioadele de concedii sunt: </h3>
			
			<div class="info_concedii">
				<em> Info/optiuni </em>
				Nr. zile neplanificate in anul curent: <?php echo $countZileRamase; ?>  /
				<a href = "concedii.php"> Adauga inca o perioada </a>  /
				<a href = "editare.php">  Modifica propriile concedii </a>	/	
			<a href="deconectare.php"> <button> Deconectare </button> </a>
			</div>	
		</div>

		<br> <br>

		<nav class="navbar navbar-default">
			<div class="container-fluid">
				<p class="navbar-brand" id="todaysDate"></p>
			</div>
		</nav>

		<div class="container-fluid row">
			<div>
			<?php if ($x == "sef_HR")  { ?>
				<div id="legenda" class="col-md-3" align="center"> 
					<h4>Legenda</h4>
					<hr>
					IT    <div id="it_dep" onclick="seePeriodsPerDep('IT')" title = "Vezi concedii pe departament" class = "eventsLegend" style="background-color:#264E36"> </div> <br>
					HR  <div id="hr_dep" onclick="seePeriodsPerDep('HR')" title = "Vezi concedii pe departament" class = "eventsLegend" style="background-color:#FFD662"> </div> <br>
					Aprovizionare  <div id="aprov_dep" onclick="seePeriodsPerDep('Aprovizionare')" title = "Vezi concedii pe departament" class = "eventsLegend" style="background-color:#223A5E"> </div> <br>
					<hr>
					<button id="everybody"> Vezi tot </button>
				</div>
				<div id='calendar1' class='calendar col-md-9'></div> 
			<?php  }else { ?>
				<h2>Departamentul <?php echo $numeDep; ?></h2>
				<div id='calendar1' class='calendar col-md-12'></div> 
			<?php } ?>
				<div id="loading" class="loadingInProgress"> </div>
					
			</div>
		</div>
	</body>
</html>

