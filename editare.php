<?php
	include ("inc/connect.php");
		
	$dbConnection = new dbClass();
	$dbConnection->connect();

	if(!isset($_SESSION['logat_concedii']) && $_SESSION['logat_concedii']!=true)
		header("Location:index.php");
	
	$userId = $_SESSION['user_id_concedii'];
	$userInfo = $dbConnection->getUserInformation($userId);
	$countZileRamase = $dbConnection->remainingDays($userId,date("Y"));
	
	$getPeriodInfoQuery = "SELECT * FROM concedii WHERE id_angajat=$userId AND data_sfarsit > CURDATE() ORDER BY data_inceput";
	
	$getPeriodInfoRes = $dbConnection->getDataFromDb($getPeriodInfoQuery);
	$p = 1;
	
	$error = false;
	$errorMsg = "";
	$done = "";	
	
	$data_noua_inceput = isset($_POST['change_begin'])?$_POST['change_begin']:"";
	$data_noua_sfarsit = isset($_POST['change_end'])?$_POST['change_end']:"";		
	$data_start_init = isset($_POST['data_inceput_init'])?$_POST['data_inceput_init']:"";
		
	if(isset($_POST['update']))
	{	
		$dStart = new DateTime($data_noua_inceput);
		$dEnd  = new DateTime($data_noua_sfarsit);
		$dDiff = $dStart->diff($dEnd);
		$nr_nou_zile =  $dDiff->format('%a') + 1; 	
		
		//validari
		if(empty($data_noua_inceput))
		{
			$error=true;
			$errorMsg = "Introduceti data de inceput!";
		}
		else if(empty($data_noua_sfarsit))
		{
			$error=true;
			$errorMsg="Introduceti data de sfarsit!";
		}		

		// verificare incadrare in an curent
		$start_year = date("Y",strtotime($data_noua_inceput));
		$end_year = date("Y",strtotime($data_noua_sfarsit));
		if($start_year != date("Y") || $end_year != date("Y"))
		{
			$error = true;
			$errorMsg = "Puteti selecta numai zile din anul curent!";
		}
		
		//verificare maxim 36 zile 
		if($_POST['nr_zile_edit']>$countZileRamase + $_POST['nr_zile_init'])
		{
			$error = true;
			$errorMsg = "Ati depasit maximul de 36 de zile!";
		}			
		//verificare suprapunere concedii proprii
		else 
		{
				$verifyPeriodsQuery = "SELECT data_inceput, data_sfarsit FROM concedii WHERE id_angajat='".$userId."' AND data_inceput!='".$data_start_init."'";
				
				$errVal = $dbConnection -> checkPeriodsOverlap($verifyPeriodsQuery,$data_noua_inceput,$data_noua_sfarsit);
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
		}
			
			
		//verificare minim 1/3 prezenti in departament
		$data_inceput_compare = strtotime($data_noua_inceput);	//datele preluate din inputuri
		$data_sfarsit_compare = strtotime($data_noua_sfarsit);
		
		$errVal = $dbConnection->checkPeriodAvailableInDepartment($userId,$userInfo['id_departament'],$data_inceput_compare,$data_sfarsit_compare);

		if($errVal == -1)
		{
			$error = true;
			$errorMsg = "Nu sunt disponibile concedii in aceasta perioada pentru departamentul dv.";
		}		
		
		//daca nu sunt erori, se actualizeaza datele
		if(!$error)
		{			
			$done = "Modificare efectuata cu succes " . " <a href='editare.php'> Actualizeaza tabelul </a>";
			$updatePeriodQuery = "UPDATE concedii SET data_inceput = '$data_noua_inceput',
			data_sfarsit = '$data_noua_sfarsit',nr_zile = '$nr_nou_zile'
			WHERE id_angajat='$userId' AND data_inceput='$data_start_init'";
			$dbConnection->updateData($updatePeriodQuery);			
		}									
	}
?>
	
<!DOCTYPE HTML>
<html>
	<head>
		<title>Editare perioade</title>
		 <!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
		<link rel="stylesheet" href="css/legend.css" />
		<link rel="stylesheet" href="css/edit.css" />
		<link rel="stylesheet" href="css/forms.css" />
		
		<!-- jQuery library -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>

		<!-- Latest compiled JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script> 
		<script src="js/update.js"> </script>
		
	</head>
	<body body="setDatesForUpdate()">
		<div class="container-fluid">	
			<h2>Editare perioade <?php echo $userInfo['nume'] . ' '  . $userInfo['prenume']; ?> </h2>
			
			<div class="info_concedii">
				<em> Info/optiuni </em>
				Nr. zile neplanificate in anul curent: <?php echo $countZileRamase; ?>  /
				<a href = "concedii.php"> Adauga inca o perioada </a>  /
				<a href = "vizualizare.php">  Inapoi la vizualizare concedii </a>	/	
			<a href="deconectare.php"> <button> Deconectare </button> </a>
			</div>	
		</div>
		<br><br>
		<div class="container-fluid">
			<table class="table">
				<tr>
					<th>Nr.perioada </th>
					<th>Data inceput</th>
					<th>Data sfarsit</th>
					<th>Nr zile </th>
					<th>Editare</th>
					<th>Anulare</th>
				</tr>

<?php
	while($periodInfo = $dbConnection->fetch($getPeriodInfoRes))
	{
?>			
		<tr>
			<td> <?php echo $p; ?> </td>
			<td> <?php echo $periodInfo['data_inceput']; ?> </td>
			<td> <?php echo $periodInfo['data_sfarsit']; ?> </td>
			<td> <?php echo $periodInfo['nr_zile']; ?> </td>
			<td> 	
				<button onclick=
				"update( <?php echo $p; ?>, 
				<?php echo strtotime($periodInfo['data_inceput']);?>, 
				<?php echo strtotime($periodInfo['data_sfarsit']); ?> ,
				<?php echo $periodInfo['nr_zile']; ?>)"> Modifica perioada</button>	
			</td>
			<td>
				<a href="sterge_perioada.php?d=<?php echo $periodInfo['data_inceput']; ?>"><button> Anuleaza perioada </button></a>
			</td>
		</tr>
			
<?php
		$p++;
	}
	if($p == 1) 
		echo "<div class='no_periods'> Nu aveti planificat niciun concediu pentru perioada urmatoare! ( <a href='concedii.php'> Adauga perioada </a>) </div>";
?>
		</table>
	</div>
	<br> <br>
	
	<div id="editForm" class="container-fluid forms">
		<form method="POST" action="editare.php"> 
			<h2 id="title" align="center"> </h2>
				<div class="form-group">
				<label class="control-label col-sm-5" for="change_begin"> Modifica data de inceput: </label> 
					<div class="col-sm-6">
						<input class="form-control" id="change_begin" name="change_begin" type="date" onchange="calculNrZileEdit()" /> (ll/zz/aaaa)
					</div>
				</div>
				
				<div class="form-group">
				<label class="control-label col-sm-5" for="change_end"> Modifica data de sfarsit: </label> 
					<div class="col-sm-6">
						<input class="form-control" id="change_end" name="change_end" type="date" onchange="calculNrZileEdit()" /> (ll/zz/aaaa) <br> 
					</div>
				</div>
				
				<div class="form-group">
					<div class="col-sm-12">
						 
						<button type="submit" class="submit_btn" name="update"> Actualizare </button>
					</div>
				</div>							
				
				<input id="change_hidden" name="data_inceput_init" type="date" hidden readonly />
				<input id="nr_zile_edit" type = "text" name="nr_zile_edit" hidden readonly />
				<input id="nr_zile_init" type = "text" name="nr_zile_init" hidden />				
		</form>
	</div> <br> <br> <br>
	<div id="editInfo">
		<p>  						
			Click pe butonul "Modifica perioada" in dreptul perioadei pe care doriti sa o modificati						
		</p>
	</div>
	<br />
	<span id="err" class="help-inline text-danger" > <b> <?php if($error==true) echo $errorMsg; else echo $done; ?> </b>  </span>  <br>
</body>
</html>
