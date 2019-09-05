<?php
date_default_timezone_set('Asia/Taipei');
class grab_weather{
	public function fetch($station_number,$start_from,$end_to){
		$start_time=strtotime($start_from);
		$end_time=strtotime($end_to);
		while($start_time<=$end_time){
			$buffer=file_get_contents('https://e-service.cwb.gov.tw/HistoryDataQuery/DayDataController.do?command=viewMain&station='.$station_number.'&stname=%25E9%259E%258D%25E9%2583%25A8&datepicker='.date('Y-m-d',$start_time));
			$buffer=mb_convert_encoding($buffer,'big5');  //Excel read csv in big5 by default
			preg_match_all('/<tr class="second_tr">.*<\/tbody>/s',$buffer,$matches);  //parsing the table
			preg_match_all('/(?:<tr class="second_tr">|<tr>).*?<\/tr>/s',$matches[0][0],$matches1);
			//print_r($matches1);
			//echo $buffer;
			foreach($matches1[0] as $key =>$value){
				preg_match_all('/(?:<th>|<td>|<td nowrap>).*?(?:<\/th>|<\/td>)/',$value,$matches2);
				$result[date('Y-m-d',$start_time)][$key]=str_replace(array('<td nowrap>','<td>','</td>','<th>','</th>','<br>','&nbsp',';'),'',$matches2[0]); //remove html table tag from data array
			}
			$start_time=$start_time+86400;  //going to the next day
		}
		return $result;
	}
	public function save_as_csv($path,$data){  //given file path and 2D array, saving as csv
		$fp=fopen($path,'w');
		foreach($data as $key => $value){
			fputcsv($fp,$value);
		}
		fclose($fp);
	}
	public function load_data($path){  //given csv file path, returning a 2D array
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
	public function load_specific_hour($path,$hour){ //given csv file path and hour, returning the specific hour data in 1D array 
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


?>