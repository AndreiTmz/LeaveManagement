<?php
if(!isset($_SESSION))
{
	session_start();		
}
include("inc/connect.php");
	$dbConnection = new dbClass();
	$dbConnection->connect();
	
	$error=false;
	$errMsg = "";

	if(isset($_POST['conectare']))
	{
		$loginOk = false;
		
		$user = $_POST['user_login'];
		$parola = md5($_POST['pass_login']);	
		
		$checkUserExistsQuery = "SELECT username FROM angajati WHERE username='".$user."'";		
		$res = $dbConnection->getDataFromDb($checkUserExistsQuery);
		$rows = $dbConnection->countRows($res);
		
		if(!$res || $rows < 1)
		{
			$error = true;
			$errMsg = "Utilizatorul nu exista!";
		}
		else 
		{
			$checkPassQuery = "SELECT parola FROM angajati WHERE username='".$user."'";
			$checkPassRes = $dbConnection->getDataFromDb($checkPassQuery);
			$pass = $dbConnection->fetch($checkPassRes);
			$pass = $pass['parola'];
			
			if($pass != $parola)
			{
				$error = true;
				$errMsg = "Parola introdusa nu este corecta!";
			}
			else
			{	$_SESSION['user_id_concedii'] = $dbConnection->getUserId($user);
				$_SESSION['user_concedii'] = $user;
				$_SESSION['logat_concedii'] = true;
				header("Location:concedii.php");
			}
		}		
	}
?>

<!DOCTYPE HTML>
<html lang="en">
	<head>
		<title>Conectare</title>
		<link rel="stylesheet" href="/css/forms.css" />

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
		<div id="loginForm" class="container-fluid forms">
			<form class="form-horizontal" method="POST" action="index.php">
			<div class="form-group">
				<label class="control-label col-sm-2" for="user_login">Username</label>
				<div class="col-sm-8 input-group">
					<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
					<input type="text" class="form-control" name="user_login" maxlength="30" autofocus value="<?php echo isset($_POST['user_login'])?$_POST['user_login']:""; ?>"  required /> <br>
				</div>
			</div>
			
			<div class="form-group">
				<label class="control-label col-sm-2" for="pass_login">Parola</label>
				<div class="col-sm-8 input-group">
					<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
					<input type="password" class="form-control" name="pass_login" maxlength="30"  required /> <br>
				</div>
			</div>
			<span class="help-inline text-danger"> <b> <?php if($error==true) echo $errMsg . "<br>"; ?> </b> </span> 	
			
			<div class="form-group">
				<div class="col-sm-12">
					
					<button type="submit" class="submit_btn" name="conectare"> Conectare </button>
				</div>
			</div>
			
			<a href="inregistrare.php"> Creeaza cont </a>	
			</form>
		</div>
	</body>
</html>