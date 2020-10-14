<?php
# http://api.aisland.tw/hackathon/conda_base64.php?image_base64_url=http://api.aisland.tw/hackathon/source_file/image02_base64.txt
$dirBin = dirname(__FILE__);
$today  = date("YmdHis")."_".rand();

if (!isset($_GET["image_base64_url"])){
	//echo "請輸入 base64格式的X光影像檔案<br>";
	$object=array ('error' => "Please _GET a Base64 Image URL");
	$myJSON = str_replace("\/","/",json_encode($object));
	echo $myJSON;
}else{
	//$image_base64_url="http://api.aisland.tw/hackathon/base64.txt";
	$image_base64_url=$_GET["image_base64_url"];

	$dataArr=file($image_base64_url); 
	$data= trim($dataArr[0]);
	$data = base64_decode($data);
	$input_image = "imp/".$today.".png";
	file_put_contents("imp/".$today.".png", $data);
	//echo $input_image."<br>";
	//$input_image=$_GET["input_image"];

	// 執行預測程式
	$cmd="echo $(sshpass -p 'xxxxxxxx' ssh -o StrictHostKeyChecking=no ubuntu@api.aisland.tw 'cd /var/www/html/hackathon; /home/ubuntu/miniconda3/bin/conda run -n aigo php api.php ".$today." ".$input_image." 2>&1')";
	$message = shell_exec($cmd);
	//echo "<pre>";
	//echo $message."\n";
	//結果
	$prediction_image = "result/".$today."/prediction.png";
	$p1="";
	$prediction=substr($prediction_image,0,-4).".txt";
	if (is_file($prediction)){
	 $pArr=file($prediction);
	 $pValue=trim($pArr[0]);
	 $pValue=(substr($pValue,0,-1)/100);  
	 if ($pValue>0.5) { 
	  $result="None";
	  $pResult=($pValue*100)."%"; 
	 } else {
	  $result="Pulmonary Nodules";
	  $pResult=((1-$pValue)*100)."%";
	 } 
	}
	$image_url="http://api.aisland.tw/hackathon/viewer/?prediction_image=".$today;
	$object=array ('result' => $result, 'accuracy' => $pResult, 'image_url' => $image_url);
	$myJSON = str_replace("\/","/",json_encode($object));
	echo $myJSON;
}
