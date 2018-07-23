<?php
$DBHOST = "THAY HOST CỦA DB VÔ"; 
$DBUSER = "THAY USER CỦA DB";
$DBNAME = "TÊN DATABASE";
$DBPW =  "PASSWORD CỦA DB";
$conn = mysqli_connect($DBHOST,$DBUSER,$DBPW,$DBNAME) or die("không thể kết nối tới database");
mysqli_query($conn,"SET NAMES 'UTF8'");

function curl_get_contents($url)
{
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}
$accessToken = "ACCESS TOKEN CỦA PAGE";
$cfs="LINK CFS NẾU CÓ...CÒN KHÔNG THAY BẰNG 0";

// check token at setup
if ($_REQUEST['hub_verify_token'] === $hubVerifyToken) {
  echo $_REQUEST['hub_challenge'];
  exit;
}


$input = json_decode(file_get_contents('php://input'), true);
$page_id = $input['entry'][0]['id'];
$senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
$messageText = isset($input['entry'][0]['messaging'][0]['message']['text']) ? $input['entry'][0]['messaging'][0]['message']['text']: '' ;
$payload = $input['entry'][0]['messaging'][0]['postback']['payload'];

$ref = $input['entry'][0]['messaging'][0]['postback']['referral']['ref'];

$postback = isset($input['entry'][0]['messaging'][0]['message']['quick_reply']['payload']) ? $input['entry'][0]['messaging'][0]['message']['quick_reply']['payload']: '' ;

$msg_type = isset($input['entry'][0]['messaging'][0]['message']['attachments'][0]['type']) ? $input['entry'][0]['messaging'][0]['message']['attachments'][0]['type']: '' ;
$msg_url = isset($input['entry'][0]['messaging'][0]['message']['attachments'][0]['payload']['url']) ? $input['entry'][0]['messaging'][0]['message']['attachments'][0]['payload']['url']: '' ;
$response = null;

//FUNCTION










function response($ID,$response){
global $conn;
global $input;
$user=mysqli_fetch_array(mysqli_query($conn,"SELECT IDT FROM chat WHERE ID='$ID' "));
$IDT=$user['IDT'];
$data=mysqli_fetch_array(mysqli_query($conn,"SELECT token FROM token WHERE id='$IDT' "));
$accessToken=$data['token'];
$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
if(!empty($input)){
$result = curl_exec($ch);
}
curl_close($ch);
}

//END FUNCTION

$get=json_decode(curl_get_contents('https://graph.facebook.com/v2.6/1744900548932102?fields=first_name,last_name,profile_pic,locale,gender,timezone&access_token='.$accessToken),true);
$first_name=$get['first_name'];
$last_name=$get['last_name'];
$gender=$get['gender'];
$profile_pic_url=$get['profile_pic'];
$locale=$get['locale'];
$timezone=$get['timezone'];

function sendText($text){
global $senderId;
$response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => [ 'text' => $text ]
];
response($senderId,$response);
}




$check=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM chat WHERE ID='$senderId' "));
if($check==0){
mysqli_query($conn,"INSERT INTO chat(IDT,ID,hangcho,trangthai,ketnoi)VALUES('$id','$senderId','0','0',NULL)");
}
$u=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM chat WHERE ID='$senderId' "));

if($u['banned']==1){
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"⚠️Bạn đang bị khoá tính năng này do vi phạm điều khoản sử dụng",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Nếu có thắc mắc hoặc cần hỗ trợ, vui lòng liên hệ dulieu.vblc@gmail.com ",
          ]
        ]
      ]
    ]];

     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
   response($senderId,$response);



exit();
}

 if($u['trangthai']==1){
$ketnoi=$u['ketnoi'];
if(isset($msg_type)){
$response=[
 'recipient' => [ 'id' => $ketnoi ],
 'message' => [
 'attachment' => [
 'type' => $msg_type,
 'payload' => [
 'url' => $msg_url,
 'is_reusable' => 'true'
         ]
      ]
   ]
];
response($ketnoi,$response);


    }
  }

 if($u['trangthai']==1){
$ketnoi=$u['ketnoi'];
$response = [
    'recipient' => [ 'id' => $ketnoi ],
    'message' => [ 'text' => $messageText ]
];
response($ketnoi,$response);
     }

if(($payload=="CHAT_VOI_NGUOI_LA")||($messageText=="cvnl")){
if($u['trangthai']!=0){
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Thính vẫn còn định chạy đi đâu?",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Bạn cần ngưng thả thính cũ, trước khi tìm thính mới",
            "buttons"=>[
              [
                "type"=>"postback",
                "title"=>"Ngưng thả thính",
                "payload"=>"NGAT_KET_NOI_NGAY"
              ]        
            ]
          ]
        ]
      ]
    ]];

     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
   response($senderId,$response);
exit();
}else{

 if($data['hangcho']==1){
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Đang thả thính...",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Hãy chờ ai đó đớp thính đi nào"
          ]
        ]
      ]
    ]];


     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
   response($senderId,$response);

}else{
  $q=mysqli_query($conn,"SELECT * FROM chat WHERE ID !='$senderId' AND hangcho='1' LIMIT 1");
if(mysqli_num_rows($q)==0){
mysqli_query($conn,"UPDATE chat SET hangcho='1' WHERE ID='$senderId' ");
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Đang thả thính...",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Hãy chờ ai đó đớp thính đi nào"
          ]
        ]
      ]
    ]];


     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
   response($senderId,$response);

}else{
$pdata=mysqli_fetch_array($q);
$pid=$pdata['ID'];
mysqli_query($conn,"UPDATE chat SET ketnoi='$pid',trangthai='1',hangcho='0' WHERE ID='$senderId' ");
mysqli_query($conn,"UPDATE chat SET ketnoi='$senderId',trangthai='1',hangcho='0' WHERE ID='$pid' ");


$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Đang thả thính...",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Hãy chờ ai đó đớp thính đi nào"
          ]
        ]
      ]
    ]];


     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
   response($senderId,$response);


$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Đớp thính!",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Chúc bạn trò chuyện vui vẻ, nhập pp để ngưng thả thính",
              "buttons"=>[
              [
                "type"=>"postback",
                "title"=>"Xem đánh giá",
                "payload"=>"DANH_GIA_CUA_NGUOI_LA"
              ]        
            ]
          ]
        ]
      ]
    ]];


     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
   response($senderId,$response);
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Đớp thính!",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Chúc bạn trò chuyện vui vẻ, nhập pp để ngưng thả thính",
                "buttons"=>[
              [
                "type"=>"postback",
                "title"=>"Xem đánh giá",
                "payload"=>"DANH_GIA_CUA_NGUOI_LA"
              ]        
            ]
          ]
        ]
      ]
    ]];

     $response = [
    'recipient' => [ 'id' => $pid ],
    'message' => $answer 
];
   response($pid,$response);


    }
   }
  }
}

function dis(){
global $u;
global $conn;
global $senderId;
if(($u['trangthai']!=1)&&($u['hangcho']!=1)){
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Bạn chưa thả thính",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Hãy thả thính ngay đi nào",
               "buttons"=>[
              [
                "type"=>"postback",
                "title"=>"Bắt đầu thả thính",
                "payload"=>"CHAT_VOI_NGUOI_LA"
              ]        
            ]
          ]
        ]
      ]
    ]];


     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
   response($senderId,$response);

   }else{
if($u['hangcho']!=0){
mysqli_query($conn,"UPDATE chat SET hangcho='0',trangthai='0' WHERE ID='$senderId' ");
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Bạn đã ngừng thả thính",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Thính của bạn đã thiu...Bạn có muốn thả thính lại không?",
               "buttons"=>[
              [
                "type"=>"postback",
                "title"=>"Thả thính lại",
                "payload"=>"CHAT_VOI_NGUOI_LA"
              ]        
            ]
          ]
        ]
      ]
    ]];


     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
   response($senderId,$response);
}else{
$pid=$u['ketnoi'];
mysqli_query($conn,"UPDATE chat SET trangthai='0',ketnoi=NULL,rated='0' WHERE ID='$senderId' ");
mysqli_query($conn,"UPDATE chat SET trangthai='0',ketnoi=NULL,rated='0' WHERE ID='$pid' ");
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Thính đã chạy mất",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Thính thiu mất rồi...Bạn muốn tìm thính mới chứ?",
               "buttons"=>[
              [
                "type"=>"postback",
                "title"=>"Tìm thính mới",
                "payload"=>"CHAT_VOI_NGUOI_LA"
              ]        
            ]
          ]
        ]
      ]
    ]];


     $response = [
    'recipient' => [ 'id' => $pid ],
    'message' => $answer 
];
   response($pid,$response);
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Bạn đã ngưng thả thính",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Thính của bạn đã thiu...Bạn có muốn thả thính lại không?",
               "buttons"=>[
              [
                "type"=>"postback",
                "title"=>"Tìm thính mới",
                "payload"=>"CHAT_VOI_NGUOI_LA"
              ]        
            ]
          ]
        ]
      ]
    ]];


     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
   response($senderId,$response);
    }

  }
}


 if($payload=="NGAT_KET_NOI_NGAY"||$postback=="NGAT_KET_NOI_NGAY"||$messageText=="pp"||$messageText=="PP"||$messageText=="pP"||$messageText=="Pp"){
dis();
}
 if($payload=="WELCOME_MESSAGES"){
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Bắt đầu trò chuyện, thả thính với người lạ nào ",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Mau mau ấn ngay nút dưới để tìm thính thơm ngay thôi...",
            "buttons"=>[
              [
                "type"=>"postback",
                "title"=>"Bắt đầu thả thính",
                "payload"=>"CHAT_VOI_NGUOI_LA"
              ]              
            ]
          ]
        ]
      ]
    ]];

     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
   response($senderId,$response);
}


//BLOCK




if($payload=="REPORT_CHATXXX"){

report($u['ketnoi'],"Gạ chịch, xoạc, chatxxx");
}


if($payload=="REPORT_ADS"){

report($u['ketnoi'],"Quảng cáo, tuyển ref");
}

if($payload=="REPORT_TUC"){
report($u['ketnoi'],"Nói tục, phản động");
}



function report($ID,$lido){
global $conn;
$sql="SELECT * FROM chat WHERE ID='$ID' ";
$query=mysqli_query($conn,$sql);
$data=mysqli_fetch_array($query);
$report_times=$data['report_times']+1;
if($time>10){
mysqli_query($conn,"UPDATE chat SET report_times='0',banned='1' WHERE ID='$ID' ");
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"⚠️Bạn đã bị khoá các tính năng do vi phạm điều khoản sử dụng",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Có thắc mắc hay cần hỗ trợ, vui lòng liên hệ dulieu.vblc@gmail.com để được giúp đỡ"
            ]
          ]
        ]
    ]
];

     $response = [
    'recipient' => [ 'id' => $ID ],
    'message' => $answer 
];
response($ID,$response);
}else{
mysqli_query($conn,"UPDATE chat SET report_times='$report_times' WHERE ID='$ID' ");
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Người lạ vừa báo cáo bạn vi phạm điều khoản sử dụng",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Lý do: $lido .Nếu bạn bị báo cáo quá 10 lần/ ngày sẽ bị khoá 7 ngày"
            ]
          ]
        ]
    ]
];

     $response = [
    'recipient' => [ 'id' => $ID ],
    'message' => $answer 
];
response($ID,$response);
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Đã tố cáo thành công",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Hãy cùng nhau xây dựng một cộng đồng chat với người lạ lành mạnh nhé."
            ]
          ]
        ]
    ]
];

     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
response($senderId,$response);
dis();
  }
}



if($payload=="DANH_GIA_NGUOI_LA"){
if($u['trangthai']!=1){
none();
exit();
}

$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Hãy lựa chọn một đánh giá tương ứng với số sao",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Bạn thấy thinh thế nào? Hãy cho mọi người biết."
            ]
          ]
        ]
    ],
     "quick_replies"=>array(
    array(
    "content_type"=>"text",
   "title"=>"⭐️",
   "payload"=>"RATE_1",
   "image_url"=>""
     ),
   array(
    "content_type"=>"text",
   "title"=>"⭐️⭐️",
   "payload"=>"RATE_2",
   "image_url"=>""
     ),
   array(
    "content_type"=>"text",
   "title"=>"🌟🌟🌟",
   "payload"=>"RATE_3",
   "image_url"=>""
     ),
   array(
    "content_type"=>"text",
   "title"=>"🌟🌟🌟🌟",
   "payload"=>"RATE_4",
   "image_url"=>""
     ),
   array(
    "content_type"=>"text",
   "title"=>"🌟🌟🌟🌟🌟",
   "payload"=>"RATE_5",
   "image_url"=>""
     )
   )
];

     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
response($senderId,$response);
}

if(strpos($postback,"RATE")===0){
if($u['rated']==0){
$star=str_replace("RATE_","",$postback);
$pid=$u['ketnoi'];
$get=mysqli_fetch_array(mysqli_query($conn,"SELECT rate FROM chat WHERE ID='$pid' "));
$a=$get['rate'];
$data=json_decode($a,true);
$p=$data[$star]+1;
unset($data[$star]);
$data[$star]=$p;
$r=json_encode($data);
mysqli_query($conn,"UPDATE chat SET rate='$r' WHERE ID='$pid' ");
mysqli_query($conn,"UPDATE chat SET rated='1' WHERE ID='$senderId' ");
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Bạn đã đánh giá người lạ thành công",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Đánh giá của bạn: $star 🌟"
            ]
          ]
        ]
    ]
];

     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
response($senderId,$response);
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Người lạ đã đánh giá bạn $star 🌟",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Bạn có thể đánh giá người lạ bằng cách ấn trên menu: Tố cáo & Đánh giá > Đánh giá người lạ"
            ]
          ]
        ]
    ]
];

     $response = [
    'recipient' => [ 'id' => $pid ],
    'message' => $answer 
];
response($pid,$response);
 }else{
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Bạn đã đánh giá người lạ trước đó",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Bạn không thể đánh giá ngay lúc này, thử lại sau."
            ]
          ]
        ]
    ]
];

     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
response($senderId,$response);
 }
}


if($payload=="DANH_GIA_VE_TOI"){
$rdata=$u['rate'];
$rate=json_decode($rdata,true);
$tota=$rate['1']*1+$rate['2']*2+$rate['3']*3+$rate['4']*4+$rate['5']*5;
$c=$rate['1']+$rate['2']+$rate['3']+$rate['4']+$rate['5'];
if($tota==0){
$total=0;
}else{
$total=$tota/$c;
$total= round($total, 2);
}
if(($total<2)&&($total!=0)){
$ok="Tồi tệ";
}else if(($total>=2)&&($total<3)){
$ok="Khá thân thiện";
}else if(($total>=3)&&($total<4)){
$ok="Tốt";
}else if(($total>=4)&&($total<5)){
$ok="Tuyệt vời";
}else if($total==5){
$ok="Xuất sắc";
}else if($total==0){
$ok="Chưa có dữ liệu";
$total="Chưa có dữ liệu";
}
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Đánh giá bạn: $ok",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Số sao trung bình: $total ⭐️"
            ]
          ]
        ]
    ]
];

     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
response($senderId,$response);
}


if($payload=="DANH_GIA_CUA_NGUOI_LA"){
 if($u['trangthai']!=1){
none();
exit();
  }
$pid=$u['ketnoi'];
$get=mysqli_fetch_array(mysqli_query($conn,"SELECT rate FROM chat WHERE ID='$pid' "));
$data=$get['rate'];
$rate=json_decode($data,true);
$tota=$rate['1']*1+$rate['2']*2+$rate['3']*3+$rate['4']*4+$rate['5']*5;
$c=$rate['1']+$rate['2']+$rate['3']+$rate['4']+$rate['5'];
if($tota==0){
$total=0;
}else{
$total=$tota/$c;
$total= round($total, 2);
}
if(($total<2)&&($total!=0)){
$ok="Tồi tệ";
}else if(($total>=2)&&($total<3)){
$ok="Khá thân thiện";
}else if(($total>=3)&&($total<4)){
$ok="Tốt";
}else if(($total>=4)&&($total<5)){
$ok="Tuyệt vời";
}else if($total==5){
$ok="Xuất sắc";
}else if($total==0){
$ok="Chưa có dữ liệu";
$total="Chưa có dữ liệu";
}
$answer = ["attachment"=>[
      "type"=>"template",
      "payload"=>[
        "template_type"=>"generic",
        "elements"=>[
          [
            "title"=>"Đánh giá của người lạ: $ok",
            "item_url"=>"",
            "image_url"=>"",
            "subtitle"=>"Số sao trung bình: $total ⭐️"
            ]
          ]
        ]
    ]
];

     $response = [
    'recipient' => [ 'id' => $senderId ],
    'message' => $answer 
];
response($senderId,$response);
}



if(rand(1,5)==2){
function send($url,$data){
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_exec($ch);
curl_close($ch);
}
send("https://graph.facebook.com/v2.6/me/messenger_profile?access_token=$accessToken",'{ 
  "get_started":{
    "payload":"WELCOME_MESSAGES"
  }
}');

if($cfs=="0"){

$data='
{
  "persistent_menu":[
    {
      "locale":"default",
      "composer_input_disabled": false,
      "call_to_actions":[
  {
              "title":"💓Bắt đầu thả thính",
              "type":"postback",
              "payload":"START_BOT"
            },
        {
          "type":"web_url",
          "title":"😡Ngưng thả thính",
          "type":"nested",
          "call_to_actions":[
    {
              "title":"💔Ngưng thả thính ngay",
              "type":"postback",
              "payload":"NGAT_KET_NOI_NGAY"
            }
          ]
        },
        {
          "type":"web_url",
          "title":"🍀Tố cáo & Đánh giá",
          "type":"nested",
          "call_to_actions":[
          {
          "type":"web_url",
          "title":"⚠️Tố cáo vi phạm",
          "type":"nested",
          "call_to_actions":[
    {
              "title":"🔞Khiêu dâm, gạ chatxxx",
              "type":"postback",
              "payload":"REPORT_CHATXXX"
            },
    {
              "title":"📣Quảng cáo",
              "type":"postback",
              "payload":"REPORT_ADS"
            },
    {
              "title":"😑Lời lẽ thô tục",
              "type":"postback",
              "payload":"REPORT_TUC"
            },
    {
              "title":"📖Tố cáo sẽ ngừng thả thính",
              "type":"postback",
              "payload":"OK"
            }
          ]
        },
             {
              "title":"🌟Đánh giá người lạ",
              "type":"postback",
              "payload":"DANH_GIA_NGUOI_LA"
            },
             {
              "title":"📖Các đánh giá của người lạ",
              "type":"postback",
              "payload":"DANH_GIA_CUA_NGUOI_LA"
            },
             {
              "title":"🍀Các đánh giá của tôi",
              "type":"postback",
              "payload":"DANH_GIA_VE_TOI"
            }
          ]
        }
      ]
    },
    {
      "locale":"zh_CN",
      "composer_input_disabled":false,
      "call_to_actions":[
        {
          "title":"Cút",
          "type":"postback",
          "payload":"PAYBILL_PAYLOAD"
        }
      ]    
    }
  ]
}
';
}else{


$data='
{
  "persistent_menu":[
    {
      "locale":"default",
      "composer_input_disabled": false,
      "call_to_actions":[
        {
          "type":"web_url",
          "title":"😡Ngưng thả thính",
          "type":"nested",
          "call_to_actions":[
    {
              "title":"💔Ngưng thả thính ngay",
              "type":"postback",
              "payload":"NGAT_KET_NOI_NGAY"
            }
          ]
        },
        {
          "type":"web_url",
          "title":"🍀Tố cáo & Đánh giá",
          "type":"nested",
          "call_to_actions":[
          {
          "type":"web_url",
          "title":"⚠️Tố cáo vi phạm",
          "type":"nested",
          "call_to_actions":[
    {
              "title":"🔞Khiêu dâm, gạ chatxxx",
              "type":"postback",
              "payload":"REPORT_CHATXXX"
            },
    {
              "title":"📣Quảng cáo",
              "type":"postback",
              "payload":"REPORT_ADS"
            },
    {
              "title":"😑Lời lẽ thô tục",
              "type":"postback",
              "payload":"REPORT_TUC"
            },
    {
              "title":"📖Tố cáo sẽ ngừng thả thính",
              "type":"postback",
              "payload":"OK"
            }
          ]
        },
             {
              "title":"🌟Đánh giá người lạ",
              "type":"postback",
              "payload":"DANH_GIA_NGUOI_LA"
            },
             {
              "title":"📖Các đánh giá của người lạ",
              "type":"postback",
              "payload":"DANH_GIA_CUA_NGUOI_LA"
            },
             {
              "title":"🍀Các đánh giá của tôi",
              "type":"postback",
              "payload":"DANH_GIA_VE_TOI"
            }
          ]
        },
           {
              "type":"web_url",
              "title":"💋Gửi Confession",
              "url":"'.$cfs.'",
              "webview_height_ratio":"full"
            }
      ]
    },
    {
      "locale":"zh_CN",
      "composer_input_disabled":false,
      "call_to_actions":[
        {
          "title":"Cút",
          "type":"postback",
          "payload":"PAYBILL_PAYLOAD"
        }
      ]    
    }
  ]
}
';
}
send("https://graph.facebook.com/v2.6/me/messenger_profile?access_token=$accessToken",$data);
}
?>