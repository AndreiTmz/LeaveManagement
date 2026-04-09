<?php

	class dbClass
	{
			private $host;
			private $user;
			private $password;
			private $db;
			private $link;
			
			function __construct()
			{
				 $this->host = "mysql";
				 $this->user = "root";
				 $this->password = "root";
				 $this->db = "timeoff";
			}

			function connect()
			{
				$this->link=mysqli_connect($this->host,$this->user,$this->password,$this->db);
				if(!$this->link)
				{	
					die("Nu am putut stabili conexiunea ".mysqli_connect_error());					
				}				
			}

			function getLink()
			{
				return $this->link;
			}			

			function getDataFromDb($query)
			{
				$result = mysqli_query($this->link, $query);
				return $result;
				
				echo mysqli_num_rows($result);				
			}			

			function countRows($res)
			{
				return mysqli_num_rows($res);
			}
			
			function fetch($res)
			{
				return mysqli_fetch_assoc($res);
			}

			function insertData($query)
			{
				return mysqli_query($this->link,$query);
			}

			function updateData($query)
			{
				mysqli_query($this->link,$query);
			}

			function getUserInformation($userId)
			{
				$getUserInfoQuery = "SELECT * FROM angajati WHERE id_angajat='".$userId."'";
				$getUserInfoRes = $this->getDataFromDb($getUserInfoQuery);
				$userInfo = $this->fetch($getUserInfoRes);
				
				return $userInfo;				
			}

			function getUserId($username)
			{
				$findUserIdQuery = "SELECT id_angajat FROM angajati WHERE username='".$username."'";
				$findUserIdRes = $this->getDataFromDb($findUserIdQuery);
				$userId = $this->fetch($findUserIdRes);
				$userId = $userId['id_angajat'];
				
				return $userId;
			}
			
			function getDepInformation($depId)
			{
				$getDepInfoQuery = "SELECT * FROM departamente WHERE id_departament='".$depId."'";
				$getDepInfoRes = $this->getDataFromDb($getDepInfoQuery);
				$depInfo = $this->fetch($getDepInfoRes);
				
				return $depInfo;
			}

			//calcul zile ramase neplanificate
			function remainingDays($userId,$an)
			{
				$zileRamaseQuery = "SELECT nr_zile FROM concedii WHERE id_angajat='".$userId."' AND YEAR(data_inceput) ='".$an."'";
				$zileRamaseResult = $this->getDataFromDb($zileRamaseQuery);				
				$countZileRamase = 0;
				
				while($zileRamase = $this->fetch($zileRamaseResult))
					$countZileRamase += $zileRamase['nr_zile'];
				
				$countZileRamase = 36 - $countZileRamase;
				return $countZileRamase;
			}			

			// numar perioade concediu
			function countPeriods($userId,$an)
			{
					$verifyPeriodsCountQuery = "SELECT COUNT(id_angajat) AS nr_perioade FROM concedii WHERE id_angajat='".$userId."' AND YEAR(data_inceput) = '".$an . "'";
					$verifyPeriodsCountResult = $this->getDataFromDb($verifyPeriodsCountQuery);
					$countPeriods = $this->fetch($verifyPeriodsCountResult);
					$countPeriods = $countPeriods['nr_perioade'];
					
					return $countPeriods;
			}
			
			function checkPeriodsOverlap($query,$data_start,$data_sfarsit)
			{
				
				$verifyPeriodsResult = $this->getDataFromDb($query);
			
				while($date = $this->fetch($verifyPeriodsResult))
				{
					
					$start = strtotime($date['data_inceput']);
					$end   = strtotime($date['data_sfarsit']);
					
					$data_inceput_compare = strtotime($data_start);
					$data_sfarsit_compare = strtotime($data_sfarsit);
					
					
					if($start <= $data_inceput_compare && $data_inceput_compare <= $end || $start <= $data_sfarsit_compare && $data_sfarsit_compare <= $end
					|| $data_inceput_compare <= $start && $data_sfarsit_compare >= $end)
					{
						
						return -1; // "Aveti deja programat concediu in aceasta perioada!";
					}
					else if($data_start != "" && $data_sfarsit != "")
					{
						
						$beginDateInfo = explode("-",$date['data_inceput']);
						$ziuaInc = $beginDateInfo[2]; //din BD
						$lunaInc = $beginDateInfo[1];
						
						$endDateInfo = explode("-",$date['data_sfarsit']);
						$ziuaSf = $endDateInfo[2];
						$lunaSf = $endDateInfo[1];
						
					
						if($lunaInc == 6 && $ziuaInc >=15 || $lunaSf==9 && $ziuaSf<=15 
						|| $lunaInc == 7 || $lunaInc == 8 || $lunaSf == 7 || $lunaSf == 8)
						{
						
							$newBeginDateInfo = explode("-",$data_start); //din input-uri
							$newEndDateInfo = explode("-",$data_sfarsit);
							
							if($newBeginDateInfo[1] == 7 || $newBeginDateInfo[1] == 8 || $newEndDateInfo[1] == 7 || $newEndDateInfo[1] == 8
							|| $newBeginDateInfo[1] == 6 && $newBeginDateInfo[2] >=15 || $newEndDateInfo[1] == 9 && $newEndDateInfo[2] <=15
							|| $newBeginDateInfo[1] == 9 && $newBeginDateInfo[2] <=15 || $newEndDateInfo[1]==6 && $newEndDateInfo[2] >=15)
							{
								return -2; // Ati planificat deja concediu pentru perioada verii!
							}
						}
					}								
				}
				return 0;
			}
			
			function checkPeriodAvailableInDepartment($userId,$idDep,$data_start,$data_sfarsit)
			{
					//numara angajatii din departament
					$nrAngajatiDepQuery = "SELECT COUNT(id_angajat) AS nr_ang FROM angajati WHERE id_departament = '" . $idDep . "'";
					$nrAngajatiDepResult = $this->getDataFromDb($nrAngajatiDepQuery);
					$nrAngajati = $this->fetch($nrAngajatiDepResult);
					$nrAngajati = $nrAngajati['nr_ang'];
					
				//verifica numarul de perioade suprapuse cu cele ale altor angajati din acelasi departament
				$concediiDinDepartamentQuery = "SELECT * FROM concedii WHERE id_angajat IN (SELECT id_angajat FROM angajati WHERE id_departament = '". $idDep."') AND id_angajat!='".$userId."'";
				$concediiDinDepartamentRes = $this->getDataFromDb($concediiDinDepartamentQuery);
				$i = 0; // perioade suprapuse cu ale altor angajati
				
				while($concediu = $this->fetch($concediiDinDepartamentRes))
				{
					$start = strtotime($concediu['data_inceput']);
					$end   = strtotime($concediu['data_sfarsit']);
					
					if($start <= $data_start && $data_start <= $end || $start <= $data_sfarsit && $data_sfarsit <= $end
						|| $data_start < $start && $data_sfarsit>$end)
					{
						$i++; //daca se suprapun, creste i 
						
					}
					
				}
				
				// daca i > 2/3 din angajati, nu se valideaza 
				if($i >= 2/3 * $nrAngajati)
				{
						return -1; // Nu sunt disponibile concedii in aceasta perioada pentru departamentul dv.
				}
				else return 0;
				
			}
			
			function deleteFromDB($query)
			{
				mysqli_query($this->link,$query);
			}
			
};
?>