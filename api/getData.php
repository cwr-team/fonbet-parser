<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

function GetEvents()
{
    $url = 'http://line15.bkfon-resources.com/line/mobile/showEvents?&lang=ru&lineType=live_line&skId=1';
    
    $curl = curl_init();
    
    curl_setopt($curl, CURLOPT_HTTPHEADER, []);
    curl_setopt($curl, CURLOPT_ENCODING ,"");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_VERBOSE, 1); 
    curl_setopt($curl, CURLOPT_POST, false);
    curl_setopt($curl, CURLOPT_URL, $url);
    
    return json_decode(curl_exec($curl));
}

function GetEventInfoById($id)
{
    $url = "https://line31.bkfon-resources.com/line/eventView?sysId=2&lang=ru&scopeMarket=1600&eventId=".$id;
    
    $curl = curl_init();
    
    curl_setopt($curl, CURLOPT_HTTPHEADER, []);
    curl_setopt($curl, CURLOPT_ENCODING ,"");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_VERBOSE, 1); 
    curl_setopt($curl, CURLOPT_POST, false);
    curl_setopt($curl, CURLOPT_URL, $url);
    
    return json_decode(curl_exec($curl));
}

$result = GetEvents();
$data = [];
$total = [];
$odds = [];
$totalArr = [];
$oddsArr = [];

foreach($result->events as $event)
{
    if(strpos($event->sportName, "Футбол. FIFA 21.") !== false && $event->name != "1-й тайм" &&  $event->name != "2-й тайм")
    {
        if(isset($event->timer)){$timer = $event->timer;}else{$timer = "Событие не началось";}
        $item = GetEventInfoById($event->id);
        foreach($item->events[0]->subcategories as $subcategory)
        {
            if($subcategory->name == "Тоталы")
            {
                array_push($total, $subcategory);
            }
            if($subcategory->name == "Тотал")
            {
                array_push($total, $subcategory);
            }
            if($subcategory->name == "Форы")
            {
                array_push($odds, $subcategory);
            }
            if($subcategory->name == "Фора")
            {
                array_push($odds, $subcategory);
            }
        }
        
        if(isset($total[0]->quotes)){
          for($i=0; $i<count($total[0]->quotes); $i+=2)
          {
            $tmp = array("Value"=>$total[0]->quotes[$i]->p,"Low"=>$total[0]->quotes[$i]->value,"High"=>$total[0]->quotes[$i+1]->value);
            array_push($totalArr, $tmp);
          }
        }

        if(isset($total[1]->quotes)){
          for($i=0; $i<count($total[1]->quotes); $i+=3)
          {
            $tmp = ["Value"=>$total[1]->quotes[$i]->nameParamText,"Low"=>$total[1]->quotes[$i+1]->value,"High"=>$total[1]->quotes[$i+2]->value];
            array_push($totalArr, $tmp);
          }
        }

        if(isset($odds[0]->quotes)){
          for($i=0; $i<count($odds[0]->quotes); $i++)
          {
            if($odds[0]->quotes[$i]->name[0] == "1")
            {
              $tmp = array("Value"=>$odds[0]->quotes[$i]->p,"Team"=>$item->events[0]->team1,"Quote"=>$odds[0]->quotes[$i]->value);
            }
            else if($odds[0]->quotes[$i]->name[0] == "2")
            {
              $tmp = array("Value"=>$odds[0]->quotes[$i]->p,"Team"=>$item->events[0]->team2,"Quote"=>$odds[0]->quotes[$i]->value);
            }
            array_push($oddsArr, $tmp);
          }
        }

        if(isset($odds[1]->quotes)){
          for($i=0; $i<count($odds[1]->quotes); $i++)
          {
            if($odds[1]->quotes[$i]->nameParamText == "0")
            {
              $tmp = array("Value"=>"0","Team"=>$item->events[0]->team1,"Quote"=>$odds[1]->quotes[$i+1]->value);
              array_push($oddsArr, $tmp);
              $tmp = array("Value"=>"0","Team"=>$item->events[0]->team2,"Quote"=>$odds[1]->quotes[$i+2]->value);
              array_push($oddsArr, $tmp);
              $i+=2;
            }
            else
            {
              if($odds[1]->quotes[$i+1]->name == "1")
              {
                $tmp = array("Value"=>$odds[1]->quotes[$i+1]->p,"Team"=>$item->events[0]->team1,"Quote"=>$odds[1]->quotes[$i+1]->value);
              }
              else if(($odds[1]->quotes[$i+1]->name == "2"))
              {
                $tmp = array("Value"=>$odds[1]->quotes[$i+1]->p,"Team"=>$item->events[0]->team2,"Quote"=>$odds[1]->quotes[$i+1]->value);
              }
              array_push($oddsArr, $tmp);  
              $i++;
            }
          }
          //echo "<pre>";
          //var_dump($odds[1]->quotes);
        }
        $itemsArr = array("Event_id"=>$item->events[0]->id,
                          "Event_link"=>"https://www.fonbet.ru/live/football/".$item->events[0]->sportId."/".$item->events[0]->id,
                          "Event_name"=>$item->events[0]->sportName,
                          "Event_team1"=>$item->events[0]->team1,
                          "Event_team2"=>$item->events[0]->team2, 
                          "Event_timer"=>$timer, 
                          "Event_scores"=>$item->liveEventInfo->scores, 
                          "Event_total"=>$totalArr, 
                          "Event_odds"=>$oddsArr);
        array_push($data, $itemsArr);
        $total = [];
        $odds = [];
        $totalArr = [];
        $oddsArr = [];
    }
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
