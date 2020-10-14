<?php
//http://api.aisland.tw/hackathon/result.php?prediction_image=result/20201014141930_1710156324/prediction.png
if (!isset($_GET["prediction_image"])){
echo "error\n"; exit();
}else{
 $prediction_image=$_GET["prediction_image"];
}
$prediction_image="../result/$prediction_image/prediction.png";
$p1="";
$prediction=substr($prediction_image,0,-4).".txt";
if (is_file($prediction)){
 $pArr=file($prediction);
 $pValue=trim($pArr[0]);
 $pValue=(substr($pValue,0,-1)/100);  
 if ($pValue>0.5) { 
  $pResult="無, ".($pValue*100)."%"; 
 } else {
  $pResult="中度懷疑/高度懷疑, ".((1-$pValue)*100)."%";
 } 
 $p1="Nodules: ".$pResult;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Pulmonary Nodules Prediction</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
  <h2 style="text-align:center;">Pulmonary Nodules Prediction</h2>
  <!--p>The .table-striped class adds zebra-stripes to a table:</p-->            
  <table class="table table-striped">
    <thead>
      <tr>
        <th><h3><p class="text-center">Result</p></h3></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><p class="text-center"><?=$p1;?></p></td>
      </tr>
      <tr>
        <td><p class="text-center"><img src="viewer.php?file=<?=$prediction_image;?>"></p></td>
      </tr>
    </tbody>
  </table>
</div>

</body>
</html>
