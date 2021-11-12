<?php 
error_reporting(0);
 function clean_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function required($data) {
  if(empty($data['value'])){
    return $data['input_name']." field is required";
  }else{
    return '';
  }

}
function valid_email($data){
  if (!filter_var($data['value'], FILTER_VALIDATE_EMAIL)) {
      return $data['input_name']." field is invalid";
    }else{
    return '';
  }
}
  $mail_sent = false;
  $httpcode = 400;
  $httpmessage = "Bad Request";
  
  if (isset($_POST["addContact"])) {

      $send_mail = true;
      $input_items = array();
      $input_items[] = array("input_name"=>"name","rules"=>"required");
      $input_items[] = array("input_name"=>"organization","rules"=>"required");
      $input_items[] = array("input_name"=>"email","rules"=>"required|valid_email");
      $input_items[] = array("input_name"=>"phone","rules"=>"");
      $input_items[] = array("input_name"=>"message","rules"=>"");
      $input_items[] = array("input_name"=>"agreement","rules"=>"required");

      $name = isset($_POST["name"]) ? clean_input($_POST["name"]) : '';  

      foreach($input_items as $index => $input_item){
        $input_items[$index]['value'] = $input_item['value'] = isset($_POST[$input_item['input_name']]) ? clean_input($_POST[$input_item['input_name']]) : ''; 
        $rules = explode("|",$input_item['rules']);
        if(!empty($rules)){
          foreach($rules as $rule){
            if(function_exists($rule)){
                $message = call_user_func_array($rule, array($input_item));
                if(!empty($message)){
                    $send_mail = false;
                    break;   
                }
            }
          }
        }
      }
      if($send_mail){
        $from = clean_input($_POST["email"]);//client
        $to = "contact@account.in";//domain
        $subject1 = "contact form";
        //***** create a template ******
        $template1 = '<table>';
        foreach($input_items as $input_items){
          $template1 .= '<tr>
                          <th>
                          '.$input_items['input_name'].'
                          </th>
                          <td>
                          '.$input_items['value'].'
                          </td>
                        <tr>';
        }
        $template1 .= '</table>';
        //****** end of template *********  
        $headers1  = 'MIME-Version: 1.0' . "\r\n";
        $headers1 .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers1 .= 'From: <'.$from.'>';
        //$headers1 .= 'From: <'.$from.'>';
        //$headers1 .= 'Cc: '. $from . "\r\n";
        
        $subject2 = "thank you";
        $template2 = "thank you message..";
        $headers2  = 'MIME-Version: 1.0' . "\r\n";
        $headers2 .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        //$headers2 .= 'From: <'.$to.'>';
        $headers2 .= 'From: XXXXXXX <'.$to.'>' . "\r\n";
        //$headers2 .= 'Cc: '. $to . "\r\n";
  
         if(mail($to,$subject1,$template1,$headers1) && mail($from,$subject2,$template2,$headers2)){
            $httpcode = 200;
            $httpmessage = "mail successfully sent";
         }else{
             $httpcode = 500;
            $httpmessage = "internal error.Try again later";        
         }
      }

  }
  //$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
  //header($protocol.' '.$httpcode.' '.$httpmessage);
  echo json_encode(array("status"=>$httpcode,"info"=>$httpmessage));
  