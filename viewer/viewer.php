<?php
# example : http://api.aisland.tw/hackathon/viewer.php?file=result/20201014141930_1710156324/prediction.png
ob_start();

ini_set('max_execution_time', 0);
set_time_limit(0);

error_reporting(E_ALL^E_NOTICE^E_DEPRECATED);
date_default_timezone_set('Europe/Sofia');

header('Content-type: image/png');

    $url = $_GET['file'];
    //$url="result/20201014141930_1710156324/prediction.png";
	if (isset($_GET['height'])) {
		$height=$_GET['height'];
	}else{
		$height=300;
	}
    if(!empty($url) && file_exists($url) && is_file($url) && is_readable($url)){	
	
	
        //if (isset($_GET['width'])) { $width = $_GET['width']; $width1=$width; $width2=$width; } 
        //if (isset($_GET['height'])) { $height = $_GET['height']; $height1=$height; $height2=$height;}	
	    $height1=$height; $height2=$height;
	
		$urlImg=dirname($url)."/input.png";
   	    $urlImg_contour=dirname($url)."/".basename($url,".png").".cam";

	
		$q1Arr=file($urlImg_contour);
		$q1=substr(trim($q1Arr[0]),1,-2);

		$q2Arr=explode('),(',$q1);
		$recordArr0=array();
		$recordArr=array();

		$pNum=0;
		for($i=0;$i<count($q2Arr);$i++){
		 $q3Arr=explode(",",trim($q2Arr[$i]));
		 if (count($q3Arr)==2) {
		  $p01=round((($q3Arr[0]/456)*456)*1,1);
		  $p02=round((($q3Arr[1]/456)*456)*1,1);

		  $p1=round((($q3Arr[0]/456)*$height1)*1+$height1,1);
		  $p2=round((($q3Arr[1]/456)*$height1)*1,1);
          //$record.=$p1.", ".$p2.",";		
		  array_push($recordArr0,$p01);
		  array_push($recordArr0,$p02);
		  array_push($recordArr,$p1);
		  array_push($recordArr,$p2);
		  $pNum++;
		 }
		}
	

		
		
		

        //$width = 100; $height =100;
        list($width_orig1, $height_orig1) = getimagesize($url);

        if(empty($width1)) $width1 = $width_orig1/($height_orig1/$height1);
        if(empty($height1)) $height1 = $height_orig1/($width_orig1/$width1);

        $ratio_orig1 = $width_orig1/$height_orig1;

        if($width1/$height1 > $ratio_orig1) {
            $width1 = $height1*$ratio_orig1;
        } else {
            $height1 = $width1/$ratio_orig1;
        }



//echo $height1." ".$width1." ".$width_orig1." ".$height_orig1."<br>";
//exit();

        $image_p1 = imagecreatetruecolor($width1*2, $height1);
        $image_p2 = imagecreatetruecolor($width1, $height1);

        $image1 = imagecreatefrompng($url);
        $image2 = imagecreatefrompng($urlImg);
		
		$col_poly = imagecolorallocate($image1, 200, 10, 10);
		/*imagesetthickness($image1, 2);
		imagepolygon($image1, $recordArr0,
		$pNum,
		$col_poly);	
		*/		
		
		
		
		$cropLen=112;
		$size=800;
        imagecopyresampled($image_p1, $image1, 0, 0, 0, 0, $width1, $height1, $width_orig1, $height_orig1);
        imagecopyresampled($image_p2, $image2, 0, 0, $cropLen, $cropLen, $width1, $height1, $size, $size);


        imagecopymerge($image_p1, $image_p2, $width1, 0, 0, 0, $width1, $height1, 100); //have to play with these numbers for it to work for you, etc.
		
		$col_poly = imagecolorallocate($image_p1, 200, 10, 10);
		imagesetthickness($image_p1, 2);
		imagepolygon($image_p1, $recordArr,
		$pNum,
		$col_poly);	
	
		
		
        imagepng($image_p1);
        imagedestroy($image_p);
        imagedestroy($image);
    }

    ob_end_flush();
