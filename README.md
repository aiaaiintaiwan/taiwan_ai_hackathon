# taiwan_ai_hackathon
## 使用深度學習進行X光影像上的結節與腫塊偵測與標註
#### 臺灣的醫療品質是世界奇蹟，但高品質的醫療服務的代價是醫護人員過多的工作量與過長工時。因此，本計畫希望透過AI的輔助，來減輕醫護人員過勞的問題，分攤醫護人員的辛勞。在放射影像中，胸部X光是使用量最多、數據量最大，也是最重要的初步診斷工具。在診斷胸部X光的結節時，面臨的困難是小的結節肉眼辨識不易，因而延誤早期治療之時機。
#### 本團隊使用開放資料集NIH Chest X-ray的影像進行模型訓練，並且與臺中童綜合醫院的放射科醫師團隊合作，進行資料的清理與確認。原本的資料分類標籤有部分可能有誤，透過放射科醫師的協助本團隊使用的影像類別標籤具有一致性且高度可靠(圖3)。模型架構以EfficientNet B5為主體，並在測試過多種影像前處理方式之後選定效果最佳的方式進行最終的訓練。訓練完成的模型以線上平台與AI Hub上的API兩種方式呈現，針對使用者上傳的X光影像預測包含結節與腫塊的機率，並使用視覺化的方式(Grad CAM)來呈現模型判斷的依據。

## AI Hub API: 
上傳AIHUB過程中發生不明錯誤, 故改提供 (API + image base64 url file)
#### Demo image: 
http://api.aisland.tw/hackathon/source_file/image02.png
#### Demo image base64: 
http://api.aisland.tw/hackathon/source_file/image02_base64.txt
#### API example: (約一分鐘計算時間)
http://api.aisland.tw/hackathon/?image_base64_url=http://api.aisland.tw/hackathon/source_file/image02_base64.txt
#### Result: 
{"result":"Pulmonary Nodules","accuracy":"99.991%","image_url":"http://api.aisland.tw/hackathon/viewer/?prediction_image=20201014223006_239443149"}
說明:
* result: None or Pulmonary Nodules
* accuracy: 百分比數值
* image_url: 為預測的熱圖連結 

## Web AI Portal: 
網址: http://hackathon.aisland.tw/
