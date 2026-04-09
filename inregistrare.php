<?php
include("inc/connect.php");
$dbConnection = new dbClasS();
$dbConnection->connect();
	$error = false;
	$numeErr="";
	$prenumeErr="";
	$userErr="";
	$passErr="";
	$depErr="";
	$done ="";
	
	$nume = isset($_POST['nume'])?trim($_POST['nume']):"";
	$prenume = isset($_POST['prenume'])?trim($_POST['prenume']):"";;
	$user = isset($_POST['user_reg'])?trim($_POST['user_reg']):"";;
	$parola = isset($_POST['pass_reg'])?trim($_POST['pass_reg']):"";;
	$departament = isset($_POST['departament'])?trim($_POST['departament']):"";;

	if (isset($_POST['inregistrare']))
	{		
		//validare nume
		if(empty($_POST['nume']))
		{
		$error = true;
		$numeErr="Introduceti nume";
		}
		else if(!preg_match("/^[A-Za-z]+$/",$nume))
		{
			$error=true;
			$numeErr = "Numele contine numai litere!";
		}
		//validare prenume
		if(empty($_POST['prenume']))
		{
			$error = true;
			$prenumeErr = "Introduceti prenume";
		}
		else if(!preg_match("/^[A-Za-z]+$/",$prenume))
		{
			$error=true;
			$prenumeErr = "Prenumele contine numai litere!";
		}
		
		//validare username
		if(empty($_POST['user_reg']))
		{
			$error=true;
			$userErr = "Introduceti username";
		}
		else if(strlen($user)<3)
		{
			$error = true;
			$userErr = "Introduceti username mai lung!";
		}
		else
		{
			$query = "SELECT username FROM angajati WHERE username='".$_POST['user_reg']."'";
			$result = $dbConnection->getDataFromDb($query);
			
			if(mysqli_num_rows($result)!=0)
			{
				$error=true;
				$userErr="Acest username a fost deja folosit";
			}
		}
		//validare parola
		if(empty($_POST['pass_reg']))
		{
					$error=true;
					$passErr="Introduceti parola!";								
		}
		else if(strlen($parola)<6)
		{
				$error = true;
				$passErr="Cel putin 6 caractere!";									
		}
		else
		{
			$parola = md5($parola);
		}
		//validare departament
		if($departament == "0")
		{
			$error=true;
			$depErr = "Selectati departament";
		}
		else
		{
			if($departament=="HR")
			{
				$func = "spec_recrut";
			}
			else if($departament=="IT")
			{
				$func = "prog";
			}
			else if($departament=="Aprovizionare")
			{
				$func = "aprov";
			}
		
				
			$idDepQuery = "SELECT id_departament FROM departamente WHERE nume_departament='".$departament ."'";
			$idDepResult = $dbConnection->getDataFromDb($idDepQuery);
			$idDep = $dbConnection->fetch($idDepResult);
			$idDep = $idDep['id_departament'];
			
		}
			
		if(!$error)
		{
			$insertUserQuery = "INSERT INTO angajati (`nume`,`prenume`,`username`,`parola`,`id_departament`,`functie`) VALUES ('$nume','$prenume','$user','$parola','$idDep','$func')";
			$insertUserResult = $dbConnection->insertData($insertUserQuery) or die ( "Error : ". mysqli_error($dbConnection->getLink()));;
			
			if($insertUserResult)
						{
							$done = "Inregistrare reusita!";
						} 						
		}
		else
		{
			$errorMsg = "A aparut o eroare la inregistrare! Incercati din nou!";
		}			
	}

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Inregistrare</title>
		<link rel="stylesheet" href="css/forms.css" />

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">

		<!-- jQuery library -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

		<!-- Latest compiled JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script> 
	</head>
	<body>
		<div id="registerForm" class="container-fluid forms">
			<form class="form-horizontal" method="POST" action="inregistrare.php" class="forms">
				
				<div class="form-group">	
					<label for="nume" class="control-label col-sm-2">Nume</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="nume" maxlength="30" value="<?php echo $nume; ?>" autofocus required />
						<span class="help-inline text-danger"> <b> <?php if($error==true) echo $numeErr; ?> </b> </span> 	
					</div>
					<br>
					
					<br>
				</div>
				
				<div class="form-group">
					<label for="prennume" class="control-label col-sm-2">Prenume</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="prenume" maxlength="30" value= "<?php echo $prenume; ?>" required /> 
						<span class="help-inline text-danger"> <b> <?php if($error==true) echo $prenumeErr; ?> </b> </span> 
					</div>
				</div>
				<div class="form-group">
					<label for="departament" class="control-label col-sm-2">Departament </label>	
					<div class="col-sm-8">
						<select name="departament" class="form-control" required>
						<option value="0"> Alege departament </option>
						<?php 
							$selectDepQuery = "SELECT nume_departament FROM departamente";
							$selectDepResult = $dbConnection->getDataFromDb($selectDepQuery);
							while ($nume_dep = $dbConnection->fetch($selectDepResult)) 
							{ 
						?>
							<option value= "<?php echo $nume_dep['nume_departament'];?>"
							<?php if ($nume_dep['nume_departament'] == $departament) { ?> selected <?php }?>> 
							<?php echo $nume_dep['nume_departament']; ?>  
							</option>
							
							<?php } ?>
							
						</select>
						<span class="help-inline text-danger"> <b> <?php if($error==true) echo $depErr; ?> </b> </span> 
					</div>
				</div>
				
				<br>
				
				<div class="form-group">
					<label for="user_log" class="control-label col-sm-2">Username</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="user_reg" maxlength="30" value= "<?php echo $user; ?>" required />
						<span class="help-inline text-danger"> <b> <?php if($error==true) echo $userErr; ?> </b> </span> 
					</div>
					<br>
					
					<br>
				</div>
				
				<div class="form-group">
					<label for="pass_reg" class="control-label col-sm-2">Parola</label>
					<div class="col-sm-8">
						<input class="form-control" type="password" name="pass_reg" maxlength="30" required /> 
						<span class="help-inline text-danger" > <b> <?php if($error==true) echo $passErr; ?> </b></span> 
					</div>
					
					<br>
				</div>
				
				<button class="submit_btn" type="submit" name="inregistrare" >Inregistrare cont</button>
				<span style = "font-size:medium; font-weight:bold; color:blue;"> <?php if($done!="") echo $done ?> </span>
					<a href="index.php">Inapoi la pagina de conectare</a>	
				
			</form>
		</div>
	</body>
</html>