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
		$table_titles=array(0 => '�[���ɶ�(hour)',
							1 => '��������(hPa)',
							2 => '����������(hPa)',
							3 => '���(�J)',
							4 => '�S�I�ū�(�J)',
							5 => '�۹�ë�(%)',
							6 => '���t(m/s)',
							7 => '���V(360degree)',
							8 => '�̤j�}��(m/s)',
							9 => '�̤j�}�����V(360degree)',
							10 => '�����q(mm)',
							11 => '�����ɼ�(hr)',
							12 => '��Ӯɼ�(hr)',
							13 => '���ѪŤ�g�q(MJ/�T)',
							14 => '�ਣ��(km)',	
							15 => '���~�u����',
							16 => '�`���q(0~10)');  //Add the table title
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
		echo '������~�A�Э��s��J';
		
	}
}
?>
<?php header("Content-Type:text/html; charset=big5"); ?>
<!DOCTYPE html>
<html>
  <head>
    <meta equiv="Content-Type" charset="big5">
	<title>������H����H�[����ƤU����</title>
  </head>
  <body style="      font-family:Microsoft JhengHei;">
    <div style="text-align: center; border: 1px dashed; width: 60%; margin-left:20%;">������H����H�[����ƤU����
      <form name="form" action="" method="GET"> ���� (�p�G 467490)�G<input name="station_id" type="text"> <br>
        �_�l��� (�p�G 20190816)�G<input name="startdate" type="text"> <br>
        ������� (�p�G 20190913)�G<input name="enddate" type="text"> <br>
        <input value="submit" type="submit"> </form>
    </div>
    <br>
    <ul>
	  <li>��X�ɮ׮榡��CSV</li>
      <li>����Ƭ��F��K�@��ϥΪ̨ϥ�Excel�}�ҡA�ϥ�big5�s�X</li>
      <li>����ƥi�H�����ϥ�GET�覡���o�G&nbsp;
        ?station_id=����&amp;startdate=�_�l���&amp;enddate=�������</li>
	  <li>�ѩ��H�[����Ƭd�ߨt��(CODiS)���C���s(���Ѫ���Ʃ��Ѥ~�|��)�A�]������������i�H�W�L�Q�ѡA�_�h�|�Q�۰��Y��</li>
      <li>����ƨӦۮ�H�[����Ƭd�ߨt��(CODiS) <a href="https://e-service.cwb.gov.tw/HistoryDataQuery/index.jsp">https://e-service.cwb.gov.tw/HistoryDataQuery/index.jsp</a></li>
      <li>�����H�����θԲӸ�T�A�аѾ\�G <a href="https://e-service.cwb.gov.tw/wdps/obs/state.htm">https://e-service.cwb.gov.tw/wdps/obs/state.htm</a></li>
      <li>���{����l�X�аѦҡGhttps://github.com/Raingel/cwb_CODiS_downloader</li>
	</ul>
  </body>
</html>
