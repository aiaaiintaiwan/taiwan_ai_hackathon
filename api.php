<?php
$dirBin = dirname(__FILE__);
$today=$argv[1];
$input_image=$argv[2];
//$today  = date("YmdHis")."_".rand();
// model 位置
$model = $dirBin."/weight/model_efficientnetb5_stage-3.h5";
$modelDir=$dirBin."/tmp/";
$modelDirTmp=$modelDir.$today;
// 上傳資料模組
$resultDir = $dirBin."/result/".$today ;  if (!is_dir($resultDir)) exec("mkdir -p ".$resultDir);
$targetDir_local = $dirBin."/target/";  if (!is_dir($targetDir_local)) exec("mkdir ".$targetDir_local);
$targetDir = $dirBin."/target/".$today."/";;  if (!is_dir($targetDir)) exec("mkdir ".$targetDir);
$sourceFilePath =  $resultDir."/input.png";
$fileName = basename($sourceFilePath);
$targetFilePath = $targetDir . $fileName;
$cmd = "cp $input_image $sourceFilePath"; echo $cmd."\n"; exec($cmd);
$cmd = "cp $input_image $targetFilePath"; echo $cmd."\n"; exec($cmd);

$cmd = "python $dirBin/model_predict.py \
$today \
$targetDir \
$targetDir_local \
$model \
$modelDir ; \
sleep 1 ; rm -rf $targetDir ; \
sleep 1 ; rm -rf $modelDirTmp ;";
echo $cmd."\n";
exec($cmd);

$cmd="mv ".$targetDir_local."/".basename( $fileName,".png").".png ".$resultDir."/prediction.png"; exec($cmd);
$cmd="mv ".$targetDir_local."/".basename( $fileName,".png").".txt ".$resultDir."/prediction.txt"; exec($cmd);
$cmd="mv ".$targetDir_local."/".basename( $fileName,".png")."_cam_contour.txt ".$resultDir."/prediction.cam"; exec($cmd);


