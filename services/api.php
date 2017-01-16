<?php
	header("Access-Control-Allow-Origin: *");
	session_start();

	const DB_SERVER = "localhost";
    const DB_USER = "sensoruser";
    const DB_PASSWD = "sensoruser";
    const DATABASE = "sensordatabase";
	
	
	$con = mysqli_connect(DB_SERVER,DB_USER,DB_PASSWD,DATABASE);
	if (!$con) {
		die('Could not connect: ' . mysqli_error($con));
	}
	
	
	$requestMethod=$_SERVER['REQUEST_METHOD'];
	if($requestMethod==='GET'){
		$endPoint=strtolower($_GET["method"]);
	}
	
	$endPoint;
	if ($requestMethod == 'POST' && empty($_POST)){
		$_POST = json_decode(file_get_contents('php://input'), true);
		$endPoint=strtolower($_POST["method"]);
	}/*
	if ($requestMethod == 'GET' && empty($_GET)){
		$_GET = json_decode(file_get_contents('php://input'), true);
		$endPoint=strtolower($_GET["method"]);
	}
	*/
	$json =array();
	
	switch($endPoint){
		case "createsensor": //POST,READY
		
			$name=$_POST['type'];
			$changeable=$_POST['changeable'];
			$sqlQuery="INSERT INTO sensors (sen_type,changeable) VALUES ('{$name}','{$changeable}')";
			
			$result=mysqli_query($con,$sqlQuery);
			if($result === FALSE) { 
				echo("FAILED RESULT"); // TODO: better error handling
				die();
			}
			
			$affectedRows = mysqli_affected_rows($con);
			
			if($affectedRows>0){
				header('Content-Type: application/json');
				array_push($json,"inserted");
				echo(json_encode($json));
			}else{
				header('Content-Type: application/json');
				array_push($json,null);
				echo(json_encode($json));
			}
			break;
			
		case "deletesensor":  //POST, READY
			
			$id=$_POST['id'];
			$sqlQuery="DELETE FROM sensors WHERE sen_id='{$id}'";
			$dropFKQuery="SET foreign_key_checks = 0";
				mysqli_query($con,$dropFKQuery);
			$result=mysqli_query($con,$sqlQuery);
			$deleted;
			if($result === FALSE) { 
				echo("Something went wrong");
				die();
			}
			
			$affectedRows = mysqli_affected_rows($con);			
			if($affectedRows>0){
				$setForeginKeyBackQ="SET foreign_key_checks = 1";
				mysqli_query($con,$setForeginKeyBackQ);
				$deleted=true;
				array_push($json,$deleted);
				header('Content-Type: application/json');
				echo(json_encode($json));
			}else if($affectedRows==0){
				header('Content-Type: application/json');
				$deleted="Id does not exists!";
				array_push($json,$deleted);
				echo(json_encode($json));
			}
			break;
			
		case "listsensors":  //GET, READY
			$sqlQuery="select * from sensors;";
			$result=mysqli_query($con,$sqlQuery);
			if($result === FALSE) { 
				echo("FAILED RESULT"); // TODO: better error handling
				die();
			}
			
			while ($row=mysqli_fetch_array($result)){
                $bus=array(
                    "id"=>"{$row['sen_id']}",
                    "type"=>"{$row['sen_type']}",
					"changeable"=>"{$row['changeable']}"
                );
                array_push($json,$bus);
            }
			 if(!empty($json)){
				header("Content-Type: application/json");
                echo json_encode(array_values($json));
			 }
            else{			
				header('Content-Type: application/json');
                echo(json_encode(null));
			}
			
			break;
			
		case "getlatestsensordata": //POST, READY
			$id=$_POST['id'];
			$sqlQuery="select * from sensor_data where data_sen={$id} ORDER BY data_id DESC LIMIT 1;";
			$result = mysqli_query($con,$sqlQuery);
			if($result === FALSE) { 
				echo("FAILED RESULT"); // TODO: better error handling
				die();
			}	
			
            while ($row=mysqli_fetch_array($result)){
                $bus=array(
                    "time"=>"{$row['data_time']}",
                    "value"=>"{$row['data_value']}",
					"unit"=>"{$row['si_unit']}",
					"sensorid"=>"{$id}"
                );
                array_push($json,$bus);
            }
            
            if(!empty($json)){
				header("Content-Type: application/json");
                echo(json_encode($json));
			}
            else{
			   header("Content-Type: application/json");
               echo(json_encode(null));
			}
			break;
			
		case "changesensorvalue": //POST
			$sensorId=$_POST['sensorid'];
			$sensorValue=$_POST['value'];
			$siUnit=$_POST['unit'];
			$sqlQuery="INSERT INTO sensor_data (data_sen,data_value,si_unit) VALUES ('{$sensorId}','{$sensorValue}','{$siUnit}')";
			$result=mysqli_query($con,$sqlQuery);
			if($result === FALSE) { 
				echo("FAILED RESULT"); 
				die();
			}
			
			$affectedRows = mysqli_affected_rows($con);
			
			if($affectedRows>0){
				header('Content-Type: application/json');
				array_push($json,"changed");
				echo(json_encode($json));
			}else{
				header('Content-Type: application/json');
				array_push($json,null);
				echo(json_encode($json));
			}
			
			break;
			
		default:
			return http_response_code(404);
			mysqli_close($con);
			die();
	}
?>