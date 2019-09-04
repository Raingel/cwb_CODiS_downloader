<?php
//For available CWB weather station code and time span, please refer to https://e-service.cwb.gov.tw/wdps/obs/state.htm
include('./day_weather_grab_class.php');
$cache_weather_data='./cache_weather_data';  //path to store cache data
$grab_weather= new grab_weather();
$data=array();
if (isset($_GET['station_id'])){
	if(strtotime(@$_GET['startdate']) and strtotime(@$_GET['enddate'])){ //check if date and station id is valid
		$start_timestamp=strtotime(@$_GET['startdate']);
		$end_timestamp=strtotime(@$_GET['enddate']);
		$station_id=$_GET['station_id'];
		if($end_timestamp>strtotime('yesterday')){
			$end_timestamp=strtotime('yesterday');  //Since CWB weather data is only available after 1 day. Set the end_timestamp not to exceed yesterday.
		}
		while ($start_timestamp<=$end_timestamp){  //loop each day
			$cached_file_path=$cache_weather_data.'/'.$station_id.'/'.date('Y-m-d',$start_timestamp).'.csv';
			if (file_exists($cached_file_path) and  filesize($cached_file_path)>2000){ //if the data was present in cache, if the cache data is too small, the data might be corrupted and need to be update
				$data[date('Y-m-d',$start_timestamp)]=$grab_weather->load_data($cached_file_path); //load from the cache
			}else{  //if the data was not in cache
				$newly_grab_data=$grab_weather -> fetch($station_id,date('Y-m-d',$start_timestamp),date('Y-m-d',$start_timestamp));  //get the data of that day
				$data=$data+$newly_grab_data;  //merge to data pool
				$grab_weather -> save_as_csv($cached_file_path,array_shift($newly_grab_data));  //save to cache
			}
			//echo date('Ymd',$start_timestamp).'<br>';
			$start_timestamp=$start_timestamp+86400;
		}
		//Output data as CSV format
		//
		$temp_file_path='./'.time().rand(0,999999).'.csv';
		$fp=fopen($temp_file_path,'w');
		$table_titles=array(0 => '觀測時間(hour)',
							1 => '測站氣壓(hPa)',
							2 => '海平面氣壓(hPa)',
							3 => '氣溫(℃)',
							4 => '露點溫度(℃)',
							5 => '相對溼度(%)',
							6 => '風速(m/s)',
							7 => '風向(360degree)',
							8 => '最大陣風(m/s)',
							9 => '最大陣風風向(360degree)',
							10 => '降水量(mm)',
							11 => '降水時數(hr)',
							12 => '日照時數(hr)',
							13 => '全天空日射量(MJ/㎡)',
							14 => '能見度(km)',	
							15 => '紫外線指數',
							16 => '總雲量(0~10)');  //Add the table title
		fputcsv($fp,$table_titles);
		foreach ($data as $date_key => $data_value){  //go through all day
			//$data_value[0] and $data_value[1] are table titles in Chinese and English respectively, therfore skipped
			$i=2;  //data start from the third row
			while ($i<=25){  //go through every hour in a day
				$data_value[$i][0]=$date_key.'-'.$data_value[$i][0].'00';
				fputcsv($fp,$data_value[$i]);
				$i++;
			}
		}
		fclose($fp);
		header('Content-type: text/csv');
		header('Content-disposition: attachment;filename='.$_GET['station_id'].'-'.$_GET['startdate'].'to'.$_GET['enddate'].'.csv');
		echo file_get_contents($temp_file_path);
		unlink($temp_file_path);
		exit();
	}else{
		echo '日期有誤，請重新輸入';
		
	}
}
?>
<?php header("Content-Type:text/html; charset=big5"); ?>
<!DOCTYPE html>
<html>
  <head>
    <meta equiv="Content-Type" charset="big5">
	<title>中央氣象局氣象觀測資料下載器</title>
  </head>
  <body style="      font-family:Microsoft JhengHei;">
    <div style="text-align: center; border: 1px dashed; width: 60%; margin-left:20%;">中央氣象局氣象觀測資料下載器
      <form name="form" action="" method="GET"> 站號 (如： 467490)：<input name="station_id" type="text"> <br>
        起始日期 (如： 20190816)：<input name="startdate" type="text"> <br>
        結束日期 (如： 20190913)：<input name="enddate" type="text"> <br>
        <input value="submit" type="submit"> </form>
    </div>
    <br>
    <ul>
	  <li>輸出檔案格式為CSV</li>
      <li>本資料為了方便一般使用者使用Excel開啟，使用big5編碼</li>
      <li>本資料可以直接使用GET方式取得：&nbsp;
        ?station_id=站號&amp;startdate=起始日期&amp;enddate=結束日期</li>
	  <li>由於氣象觀測資料查詢系統(CODiS)為每日更新(今天的資料明天才會有)，因此結束日期不可以超過昨天，否則會被自動縮減</li>
      <li>本資料來自氣象觀測資料查詢系統(CODiS) <a href="https://e-service.cwb.gov.tw/HistoryDataQuery/index.jsp">https://e-service.cwb.gov.tw/HistoryDataQuery/index.jsp</a></li>
      <li>關於氣象站號及詳細資訊，請參閱： <a href="https://e-service.cwb.gov.tw/wdps/obs/state.htm">https://e-service.cwb.gov.tw/wdps/obs/state.htm</a></li>
      <li>本程式原始碼請參考：https://github.com/Raingel/cwb_CODiS_downloader</li>
	</ul>
  </body>
</html>
