
<?php
include("inc/connect.php");
$dbConnection = new dbClass();
	$dbConnection->connect();
	
	if(!isset($_SESSION['logat_concedii']) && $_SESSION['logat_concedii']!=true) 
	{
		header("Location:index.php");
	}
	
	$error = false;
	$data_start = isset($_POST['data_inceput'])?$_POST['data_inceput']:"";
	$data_sfarsit = isset($_POST['data_sfarsit'])?$_POST['data_sfarsit']:"";
	$nr_zile = isset($_POST['nr_zile'])?$_POST['nr_zile']:0;
	
	$errorMsg = "";
	$startDateErrorMsg="";
	$endDateErrorMsg="";
	
	//cautarea id-ului angajatului care a trimis cererea
	$userId = $_SESSION['user_id_concedii'];
	
	
	//calculare numar de zile libere deja programate
	$countZileRamase = $dbConnection->remainingDays($userId,date("Y"));
	
	// verifcare nr perioade concediu			
	$countPeriods = $dbConnection->countPeriods($userId,date("Y"));
	if($countPeriods >= 3)
	{
		$errorMsg = "Ati programat deja numarul maxim de perioade de concediu pentru anul curent";
	}
	
	if(isset($_POST['adauga_concediu']))
	{
		//validari
		if(empty($data_start))
		{
			$error=true;
			$startDateErrorMsg = "Introduceti data de inceput!";
		}
		if(empty($data_sfarsit))
		{
			$error=true;
			$endDateErrorMsg="Introduceti data de sfarsit!";
		}
		else if($nr_zile == 0)
		{
			$error=true;
			$errorMsg = "A aparut o eroare! Reincercati!";	
		}
		
		//permitem incarcarea perioadelor de concedii doar pentru anul curent
		$start_year = date("Y",strtotime($data_start));
		$end_year = date("Y",strtotime($data_sfarsit));
		if($start_year != date("Y") || $end_year != date("Y"))
		{
			$error = true;
			$errorMsg = "Planificarea unui concediu se poate face numai pentru anul curent!";
		}
			
		//verificare suprapunere date concediu			
		$verifyPeriodsQuery = "SELECT data_inceput, data_sfarsit FROM concedii WHERE id_angajat='".$userId."'";
		$errVal = $dbConnection -> checkPeriodsOverlap($verifyPeriodsQuery,$data_start,$data_sfarsit);
		if($errVal == -1)
		{
			$error = true;
			$errorMsg = "Aveti deja programat concediu in aceasta perioada!";
		}
		else if($errVal == -2)
		{
			$error = true;
			$errorMsg = "Ati planificat deja concediu pentru perioada verii!";
		}
		//echo $errVal;
		//verificare maxim 36 zile 
		if($_POST['nr_zile']>$countZileRamase)
		{
			$error = true;
			$errorMsg = "Ati depasit maximul de 36 de zile!";
		}
		//verificare maxim 3 perioade pentru anul selectat
		
		$countPeriods = $dbConnection->countPeriods($userId,$start_year);
		
		if($countPeriods >= 3)
		{
			$error = true;
			$errorMsg = "Ati programat deja numarul maxim de perioade de concediu pentru anul selectat!";
		}
		
		//verificare minim 1/3 prezenti in departament
		$userInfo = $dbConnection->getUserInformation($userId);
		$data_inceput_compare = strtotime($data_start);	//datele preluate din inputuri
		$data_sfarsit_compare = strtotime($data_sfarsit);
		
		$errVal = $dbConnection->checkPeriodAvailableInDepartment($userId,$userInfo['id_departament'],$data_inceput_compare,$data_sfarsit_compare);

		if($errVal == -1)
		{
			$error = true;
			$errorMsg = "Nu sunt disponibile concedii in aceasta perioada pentru departamentul dv.";
		}		
		
		//daca nu sunt erori, se introduc datele in baza de date
		if(!$error)
		{				
			$insertDatesQuery = "INSERT INTO concedii (`id_angajat`,`data_inceput`,`data_sfarsit`,`nr_zile`) VALUES ('$userId','$data_start','$data_sfarsit',$nr_zile)";
			
			$insertDatesRes = $dbConnection->insertData($insertDatesQuery) or die ( "Error : ". mysqli_error($dbConnection->getLink()));
			
			header("Location:vizualizare.php");			
		}		
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title> Programare cocedii </title>
		<link rel="stylesheet" href="css/forms.css" />
		<script src="js/formValidation.js"></script> 

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">

		<!-- jQuery library -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

		<!-- Latest compiled JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script> 
	</head>

	<body style="background-color:red;" onload="setDates();zileRamase()">
		<div id="holidayForm" class="container-fluid forms">
			<form class="forms-horizontal" method="POST" action="concedii.php">
				<center> Alege perioada concediu </center>
				<div class="form-group">
					<label for="data_inceput" class="control-label">Data inceput: </label>				
						<input class="form-control"  type="date" name="data_inceput" id="begin" onchange="calculNrZile();zileRamase()" 
						value="<?php if(isset($_POST['data_inceput'])) echo $_POST['data_inceput'] ?>" autofocus />				
					<span class="help-inline text-danger" > <b> <?php if($error==true) echo $startDateErrorMsg; ?> </b> </span> <br>
				</div>

				<div class="form-group">
					<label for="data_sfarsit" class="control-label">Data sfarsit: </label>
						<input class="form-control" type="date" name="data_sfarsit" id="end"  onchange="calculNrZile();zileRamase()" 
						value="<?php if(isset($_POST['data_sfarsit'])) echo $_POST['data_sfarsit'] ?>"/> 		
					<span class="help-inline text-danger" > <b> <?php if($error==true) echo $endDateErrorMsg; ?> </b> </span>  <br>
				</div>
					<label for="nr_zile">Numar zile selectate: </label>
					<input type="text" name="nr_zile" id="nrZileHiddenInput" value="<?php echo isset($_POST['nr_zile'])?$_POST['nr_zile']:"0" ?>"  hidden />  
					<span id="nrZile">  <?php echo isset($_POST['nr_zile'])?$_POST['nr_zile']:"-"; ?>  </span> <br>
				
					<label>Perioade de concediu disponibile: </label>
					<span id="nrPerioade"> <?php  echo 3-$countPeriods; ?> </span> <br>
				
					<label> Numar zile disponibile: </label>
					<input type="text" name="nr_zile_disp" id="nr_zile_disp" value="<?php   if($countPeriods == 3) echo 0; else echo $countZileRamase; ?>"  hidden />  
					<span id="nrZileRamase"> <?php if($countPeriods == 3) echo 0; else echo $countZileRamase; ?>  </span> <br>
					<span id="overflow"> </span><br>
					
					<span class="help-inline text-danger" > <b> <?php if($error==true) echo $errorMsg; ?> </b>  </span>  <br>
					<div class="form-group">
						<button class="submit_btn" type= "submit" name="adauga_concediu"  value="Trimite"> Adauga perioada </button> <br>
					</div>
					
				<a href="vizualizare.php"> Vezi concedii programate </a> <br>
				<a href="deconectare.php"> Deconectare </a>
			</form>
		</div>
	</body>
</html>