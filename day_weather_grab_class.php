<?php
date_default_timezone_set('Asia/Taipei');
class grab_weather{
	public function fetch($station_number,$start_from,$end_to){
		$start_time=strtotime($start_from);
		$end_time=strtotime($end_to);
		while($start_time<=$end_time){
			$buffer=file_get_contents('https://e-service.cwb.gov.tw/HistoryDataQuery/DayDataController.do?command=viewMain&station='.$station_number.'&stname=%25E9%259E%258D%25E9%2583%25A8&datepicker='.date('Y-m-d',$start_time));
			$buffer=mb_convert_encoding($buffer,'big5');
			preg_match_all('/<tr class="second_tr">.*<\/tbody>/s',$buffer,$matches);
			preg_match_all('/(?:<tr class="second_tr">|<tr>).*?<\/tr>/s',$matches[0][0],$matches1);
			//print_r($matches1);
			//echo $buffer;
			foreach($matches1[0] as $key =>$value){
				preg_match_all('/(?:<th>|<td>|<td nowrap>).*?(?:<\/th>|<\/td>)/',$value,$matches2);
				$result[date('Y-m-d',$start_time)][$key]=str_replace(array('<td nowrap>','<td>','</td>','<th>','</th>','<br>','&nbsp',';'),'',$matches2[0]); //remove html table tag from data array
			}
			$start_time=$start_time+86400;
		}
		return $result;
	}
	public function save_as_csv($path,$data){
		$fp=fopen($path,'w');
		foreach($data as $key => $value){
			fputcsv($fp,$value);
		}
		fclose($fp);
	}
	public function load_data($path){
		if(!file_exists($path)){
			//echo 'FILE_NOT_FOUND';
			return FALSE;
		}else{
			$data_buffer=array();
			$fp=fopen($path,'r');
			while(($data=fgetcsv($fp))!==FALSE){
				array_push($data_buffer,$data);
			}
		return $data_buffer;
		}
		
	}
	public function load_specific_hour($path,$hour){
		if(!file_exists($path)){
			//echo 'FILE_NOT_FOUND';
			return FALSE;
		}else{
			$data_buffer=array();
			$fp=fopen($path,'r');
			while(($data=fgetcsv($fp))!==FALSE){
				array_push($data_buffer,$data);
			}
		array_shift($data_buffer);
		return($data_buffer[$hour]);
		}
	}
}

//$weather_downloder= new grab_weather();
//$weather_downloder->station_number='C0X130';
//$data=$weather_downloder->fetch('20190310','20190311');
//$weather_downloder->save_as_csv('./data_day/',$data);
/*
if(FALSE){
	$fp=fopen('station.csv','r');
	$station_list=array();
	while(($data=fgetcsv($fp))!== FALSE){  //load station list
		array_push($station_list,$data[0]);
	}
	unset($station_list[0]);  //unset 站號


	$weather_downloder= new grab_weather();
	foreach ($station_list as $value){
		$data=$weather_downloder->fetch($value,'20190320','20190321');
		if(!file_exists('./data_day/'.$value.'/'))
		{
			mkdir('./data_day/'.$value.'/');
		}
		//print_r($data);
		foreach($data as $key1 => $value1){ //loop every day $value=station_number $key1=date $value1=data
			$weather_downloder->save_as_csv('./data_day/'.$value.'/'.$key1.'.csv',$value1);
		}
	}
}
*/
if(FALSE){
	$weather_class= new grab_weather();
	print_r($weather_class->load_data('./data_day/466880/2019-03-17.csv'));
	$weather_class->load_specific_hour('./data_day/C0G790/2019-03-21.csv',10); 
}


?>

