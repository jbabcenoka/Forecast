<?php

/* 
 * This is the main file which receives and analyzes data, 
 * generates response data and finally calls the template.
 */

// show all warnings and errors on the screen
error_reporting(E_ALL);
ini_set('display_errors', 1);

$cities = array ("Cēsis" => "Latvia/Cesu/Cēsis", 
"Daugavpils" => "Latvia/Daugavpils/Daugavpils", 
"Jēkabpils" => "Latvia/Jekabpils/Jēkabpils", 
"Jelgava" => "Latvia/Jelgava/Jelgava", 
"Jūrmala" => "Latvia/Jurmala~/Jūrmala", 
"Liep�?ja" => "Latvia/Liepaja/Liep�?ja", 
"Ogre" => "Latvia/Ogres/Ogre", 
"Rēzekne" => "Latvia/Rezekne/Rēzekne", 
"Riga" => "Latvia/Riga/Riga", 
"Salaspils" => "Latvia/Salaspil/Salaspils", 
"Tukums" => "Latvia/Tukuma/Tukums", 
"Valmiera" => "Latvia/Valmieras/Valmiera", 
"Ventspils" => "Latvia/Ventspils/Ventspils");

// DO NOT EDIT BEFORE THIS LINE

/* Functions and classes You might want to use (you have to study function descriptions and examples)
 * Note: You can easily solve this task without using any regular expressions
file_get_contents() http://lv1.php.net/file_get_contents
file_put_contents() http://lv1.php.net/file_put_contents
file_exists() http://lv1.php.net/file_exists
SimpleXMLElement http://php.net/manual/en/simplexml.examples-basic.php http://php.net/manual/en/class.simplexmlelement.php 
date() http://lv1.php.net/manual/en/function.date.php or Date http://lv1.php.net/manual/en/class.datetime.php
Multiple string functions (choose by studying descriptions) http://lv1.php.net/manual/en/ref.strings.php
Multiple variable handling functions (choose by studying descriptions) http://lv1.php.net/manual/en/ref.var.php
Optionally you can use some array functions (with $_GET, $cities) http://lv1.php.net/manual/en/ref.array.php
*/

// Your code goes here
$result = ""; //valid values: empty string, "OK", "ERROR"
$error_message = "";
$date = "";
$city = "";
$forecast = [];



function validateDate($date, $format = 'd/m/Y H:i'){
    $a = DateTime::createFromFormat($format, $date);
    return $a && $a->format($format) == $date;
}
function creat_user_date($user_date){
    if( validateDate($user_date,"Y-m-d\TH:i")||validateDate($user_date)){
        $format1 = "d/m/Y H:i";
        $format2 = "Y-m-d\TH:i";
        $dateobj = DateTime::createFromFormat($format1, $user_date);//Mozila data input version
        $dateobj2 = DateTime::createFromFormat($format2, $user_date);//Chrome data input version
        if ($dateobj===false && $dateobj2===false) {return 'invalid date';}
        if ($dateobj!==false) { 
            $iso_datetime = $dateobj->format('d-m-Y\TH:i');
             return $iso_datetime;
        }  else if ($dateobj2!==false) {
            $iso_datetime = $dateobj2->format('d-m-Y\TH:i');
            return $iso_datetime;
       }
    }
    return 'invalid date';
}   

function CreateDom($filename,$rss_doc,&$dom){
   $dom_sxe = dom_import_simplexml($rss_doc); //from SimpleXML to DOM object
   $dom_sxe = $dom->importNode($dom_sxe, true);
   $dom_sxe = $dom->appendChild($dom_sxe);
   $dom->save($filename); //saving DOM document to the path
}

function find_forecast($xml,$created_user_date){  //create forecast for the user`s date and time
   foreach ($xml->forecast->tabular->time as $item){
        $date1=$item->attributes()['from'];
        $date2=$item->attributes()['to'];
        $date1_1=new DateTime($date1);
        $date2_1=new DateTime($date2);
        if ($created_user_date>=$date1_1->format('d-m-Y\TH:i') && $created_user_date<=$date2_1->format('d-m-Y\TH:i')) {
            $t_result=$item->temperature->attributes()['value'];
            $temp1=$item->precipitation;
            if( $temp1['value'] != '0') {
               $p_result=$temp1->attributes()['minvalue'].'-'.$temp1->attributes()['maxvalue'];
             } else { $p_result='0'; }
             $w_result=$item->windSpeed->attributes()['mps'].' m/s from '.$item->windDirection->attributes()['name'];
             $forecast=["Temperature"=>$t_result,  "Precipitation"=>$p_result, "Wind"=>$w_result];
             return $forecast;   //the forecast was found
         }
    }
    return [];
}


date_default_timezone_set("Europe/Riga");
if (isset($_GET["city"]) && isset($_GET["date"])){
    if (!empty($_GET["city"])){
        if (!empty($_GET["date"])){
                                 //date validation
        $date1=$_GET["date"];
        $user_date=creat_user_date($date1); 
        if ($user_date==='invalid date'){
            $result = "ERROR";
            $error_message = "The date is invalid!";
        } else {
            $city=$_GET["city"];
            $now_date=date('d-m-Y\TH:i');
                       if (strtotime($now_date)>strtotime($user_date)) { 
                                    $result='ERROR';
                                    $error_message="The date must be not in the past!";
                        } else { 
                                      $content = "$city";
                                      $chars = explode("/", $content);
                                      $city_result="$chars[2]";
                                      $city_name="$city_result";
                                      $file_path = "xml/$city_name.xml";   //path to the xml file 
                                      if(!is_writable('xml')){ chmod('xml', 0777);}
                                      if (file_exists($file_path)) {   
                                                $dom = new DOMDocument('1.0');
                                                $dom->load($file_path);
                                                $xml = simplexml_import_dom($dom);
                                                $lastupdate=$xml->meta->lastupdate;
                                                $update_date=new DateTime($lastupdate);   //The date of forecast`s lastupdate
                                                $update_date_2=$update_date->format('d.m.Y');
                                                if ($update_date_2<date('d.m.Y')) {  //if file is expired
                                                     $rss_url=file_get_contents("https://www.yr.no/place/$city/forecast.xml");   //dowloading XML
                                                     $rss_doc=simplexml_load_string($rss_url);   //create XML object
                                                      if ($rss_doc === false) {   // if service is not available
                                                             $error_message= "Sorry, could not load the document";
                                                             $result="ERROR";
                                                       } else {   // if service is available
                                                                     //remove old information
                                                            $dom_sxe = $dom->documentElement;
                                                            $dom_old= $dom->removeChild($dom_sxe);
                                                                          //writing new information
                                                            CreateDom($file_path,$rss_doc,$dom);
                                                            $xml = simplexml_import_dom($dom); //from DOM to SimpleXML for reading
                                                            $lastupdate=$xml->meta->lastupdate;
                                                            $update_date=new DateTime($lastupdate);   //Change the date of forecast`s lastupdate
                                                      }
                                                } else  {  //if file is not expired
                                                       $xml = simplexml_load_file($file_path); // Interprets an XML file into an object
                                                }
                                                if (find_forecast($xml, $user_date)===[]){ //in case the forecast wan`t found 
                                                    $result='ERROR';
                                                    $error_message="The forecast for this date and time is not available";
                                                } else { //in case the forecast was found
                                                    $result='OK';
                                                    $city=$city_name;
                                                    $date=$update_date->format('d.m.Y H:i');
                                                    $error_message='';
                                                    $forecast=find_forecast($xml, $user_date);
                                                }
                                       } else { // if file doesn`t exist
                                           $rss_url=file_get_contents("https://www.yr.no/place/$city/forecast.xml");   //dowloading XML
                                           $rss_doc=simplexml_load_string($rss_url);   //create XML object
                                            if ($rss_doc === false) { 
                                                   $error_message= "Sorry, could not load the document";
                                                   $result="ERROR";
                                            } else {   //in case can load the document
                                                                //creating DOM and writing
                                                   $dom = new DOMDocument('1.0');//creat DOM document   
                                                   CreateDom($file_path,$rss_doc,$dom);
                                                                //reading from XML file
                                                   $xml = simplexml_import_dom($dom); //from DOM to SimpleXML
                                                   $lastupdate=$xml->meta->lastupdate;
                                                   $update_date=new DateTime($lastupdate);   //The date of forecast`s lastupdate
                                                   if (find_forecast($xml, $user_date)===[]){ //in case the forecast wasn`t found 
                                                        $result='ERROR';
                                                        $error_message="The forecast for this date and time is not available";
                                                    } else {   //in case the forecast was found 
                                                        $result='OK';
                                                        $city=$city_name;
                                                        $date=$update_date->format('d.m.Y H:i');
                                                        $error_message='';
                                                        $forecast=find_forecast($xml, $user_date);
                                                    }
                                            }
                                        }
                            }     
            }  
        }  else {
            $error_message= "";
             $result='';
        }
    }  else {
        $error_message= "";
        $result='';
   }
} else {
      $error_message= "";
      $result='';
}
       
// DO NOT EDIT AFTER THIS LINE

require("view.php");