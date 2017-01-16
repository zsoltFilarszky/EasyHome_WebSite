var myApp = angular.module('myApp', []);

myApp.factory('helloWorldService', function($http){	
	return{
		getSensorList:function(){
			
			return $http.get('services/api.php?method=listsensors');
		},
		
		deleteSelectedSensor: function (id){
			data = {
				'method' : 'deleteSensor',
				'id' : id
			};
			
			return $http.post('services/api.php',data);
		},
		
		createSensor: function (name,changeable){
			data = {
				'method' : 'createSensor',
				'changeable' : changeable,
				'type' : name
			};
			
			return $http.post('services/api.php',data);
		},
		
		getLatestSensorData: function (id){
			data = {
				'method' : 'getlatestsensordata',
				'id' : id
			};
			
			return $http.post('services/api.php',data);
		},
		
		changeSensorValue: function(id,value,unit){
			data={
				'method': 'changesensorvalue',
				'sensorid' : id,
				'value': value,
				'unit' : unit
			};
			
			return $http.post('services/api.php',data);
		}
	};
	
	
	
});

myApp.controller('Controller', function($scope, helloWorldService){
	
	var sensorItem=helloWorldService.getSensorList().then(function(response){
		console.log("Sensorlist:");	
		console.log(response.data);	
		$scope.sensorList=response.data;
		
	});
	

				
	$scope.deleteSensor=function(id){
		var returnedVaue=helloWorldService.deleteSelectedSensor(id).then(function(response){
				if(response.data[0]==true)
					location.reload();
				else{
					alert(response.data[0]);
					location.reload();
				}
		});
		
	};
	

	$scope.sensorData=function(id){
		var returnedValue=helloWorldService.getLatestSensorData(id).then(function(response){			
			var tdElementCollection = document.getElementById(id).cells;
			
			if(response.data!=null){
				var responseData = response.data[0];
				
				tdElementCollection[0].innerHTML=responseData.time;
				if(responseData.value==0){
					tdElementCollection[1].innerHTML="Off";
				}else if(responseData.value==1){
					tdElementCollection[1].innerHTML="On";
				}
				else{
					tdElementCollection[1].innerHTML=responseData.value;
				}
					
				tdElementCollection[2].innerHTML=responseData.unit;
			}else{
				tdElementCollection[0].innerHTML="-";
				tdElementCollection[1].innerHTML="-";
				tdElementCollection[2].innerHTML="-";
			}
			
		});
	}
	
	$scope.changeSensorValue=function(id,name){
		var addvalue=prompt("Change value of "+name+" sensor! Ex: 1,SWITCH");
		var splittedArray= addvalue.split(",");
		var value=splittedArray[0];
		var siUnit=splittedArray[1];
		
		var response = helloWorldService.changeSensorValue(id,value,siUnit).then(function(response){
			if(response.data=="changed"){
					alert("Success!");
					location.reload();
				}
				else{
					alert(response.data);
				}
		});
	};
	
	
	$scope.createSensor=function(){
		var name=prompt("Give the name of the sensor! Ex:NewSensor,1 (Changeable=1, NOT Changeable=0");
		if(name!=null){
			var splittedArray= name.split(",");
			var sensorName=splittedArray[0];
			var changeable=parseInt(splittedArray[1]);
			if(changeable>2 && changeable<0){
				changeable=0;
			}
			var response = helloWorldService.createSensor(sensorName,changeable).then(function(response){
				if(response.data=="inserted"){
					alert("Success!");
					location.reload();
				}
				else{
					alert(response.data);
				}
			});
		}
	};
});
