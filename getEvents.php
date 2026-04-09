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

	//cautare id corespondent username-ului
	$userId = $_SESSION['user_id_concedii']	;
	$userInfo = $dbConnection->getUserInformation($userId);	
	//nume departament
	$depInfo = $dbConnection->getDepInformation($userInfo['id_departament']);
		$numeDep = $depInfo['nume_departament'];
		
	//id dep
	$depId = $depInfo['id_departament'];

	//daca functia este de conducere--> sunt vizibile concediile tuturor angajatilor din departament.
	//daca functia este de conducere HR --> sunt vizibile concediile tuturor angajatilor.	
		
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
	
	while($concediu = $dbConnection->fetch($veziPerioadeResult))
	{				
		$adjustedEndDate = date_create($concediu['data_sfarsit']);
		date_add($adjustedEndDate,date_interval_create_from_date_string("1 day"));					
		$adjustedEndDate = date_format($adjustedEndDate,"Y-m-d"); 
		
		
		// selectare nume angajat corespondent perioadei
		$userInfo = $dbConnection->getUserInformation($concediu['id_angajat']);
		$nume = $userInfo['nume'] . " " . $userInfo['prenume'];
		
		//departament
		$depInfo = $dbConnection->getDepInformation($userInfo['id_departament']);
		$dep = $depInfo['nume_departament'];					
		

		$detaliiPerioada = new event($userInfo['id_angajat'],$concediu['data_inceput'],$adjustedEndDate,$nume,$dep);
		echo json_encode($detaliiPerioada) . "<br>";	
	}
?>