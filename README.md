# 中央氣象局觀測資料下載 cwb_CODiS_downloader
php-based weather data downloader from CODiS system of CWB https://e-service.cwb.gov.tw/HistoryDataQuery/index.jsp

中央氣象局提供之氣象觀測資料查詢系統( https://e-service.cwb.gov.tw/HistoryDataQuery/index.jsp )以網頁呈現，不方便下載成容易分析的格式。
因此寫了一個程式把小時資料批次下載成csv檔案。

##### index.php
是一個網頁介面，可以透過GET傳送需要下載的站號、開始日期、結束日期，回傳資料成csv檔案。
並將下載過的資料存在cache資料夾內，加快後續存取速度，避免增加氣象局伺服器負擔。
如：  ?station_id=站號&startdate=起始日期&enddate=結束日期
實際執行可參考 http://fungi.mycoveryhd.com/CODiS_downloader/


##### day_weather_grab_class.php
包含幾個method
###### 一、fetch($station_number,$start_from,$end_to)
提供站號(可至氣象局查詢 https://e-service.cwb.gov.tw/wdps/obs/state.htm )，開始日期及結束日期(strtotime可辨識即可，如20190816)
會回傳三層的array資料：
第一層為日期
第二層為表格的行(前兩行為表格標題，後面接續該日的小時資料，從一點開始。氣象局的資料中，換日的20190816 00:00是記在前一日，如：20190815 24:00)
第三層包含一串觀測資料


    Array
    (
        [2019-05-16] => Array
            (
                [0] => Array
                    (
                        [0] => 觀測時間(hour)
                        [1] => 測站氣壓(hPa)
                        [2] => 海平面氣壓(hPa)
                        [3] => 氣溫(℃)
                        [4] => 露點溫度(℃)
                        [5] => 相對溼度(%)
                        [6] => 風速(m/s)
                        [7] => 風向(360degree)
                        [8] => 最大陣風(m/s)
                        [9] => 最大陣風風向(360degree)
                        [10] => 降水量(mm)	
                        [11] => 降水時數(hr)
                        [12] => 日照時數(hr)
                        [13] => 全天空日射量(MJ/㎡)
                        [14] => 能見度(km)	
                        [15] => 紫外線指數
                        [16] => 總雲量(0~10)
                    )
    
                [1] => Array
                    (
                        [0] => ObsTime
                        [1] => StnPres
                        [2] => SeaPres
                        [3] => Temperature
                        [4] => Td dew point
                        [5] => RH
                        [6] => WS
                        [7] => WD
                        [8] => WSGust
                        [9] => WDGust
                        [10] => Precp
                        [11] => PrecpHour
                        [12] => SunShine
                        [13] => GloblRad
                        [14] => Visb
                        [15] => UVI
                        [16] => Cloud Amount
                    )
    
                [2] => Array
                    (
                        [0] => 01
                        [1] => 642.5
                        [2] => 3146.1
                        [3] => 4.4
                        [4] => 4.4
                        [5] => 100
                        [6] => 5.6
                        [7] => 260
                        [8] => 11.7
                        [9] => 260
                        [10] => 0.0
                        [11] => 0.0
                        [12] => ...
                        [13] => 0.00
                        [14] => ...
                        [15] => 0
                        [16] => ...
                    )
    
                [3] => Array
                    (
                        [0] => 02
                        [1] => 642.1
                        [2] => 3139.5
                        [3] => 5.0
                        [4] => 5.0
                        [5] => 100
                        [6] => 5.3
                        [7] => 250
                        [8] => 13.8
                        [9] => 260
                        [10] => 0.0
                        [11] => 0.0
                        [12] => ...
                        [13] => 0.00
                        [14] => ...
                        [15] => 0
                        [16] => ...
                    )
    
                [4] => Array
                    (
                        [0] => 03
                        [1] => 641.9
                        [2] => 3135.6
                        [3] => 5.7
                        [4] => 5.7
                        [5] => 100
                        [6] => 4.3
                        [7] => 250
                        [8] => 11.2
                        [9] => 280
                        [10] => 0.0
                        [11] => 0.0
                        [12] => ...
                        [13] => 0.00
                        [14] => ...
                        [15] => 0
                        [16] => ...
                    )
          ......
          )
    	  )****

###### 二、save_as_csv($path,$data)
將2D array存成csv檔

###### 三、load_data($path)
將csv檔讀入成2D array

###### 四、load_specific_hour($path,$hour)
載入csv檔中特定小時的觀測資料
