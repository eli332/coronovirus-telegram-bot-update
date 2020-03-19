<?php
$dbname="db_name";
$dbuser="dbuser";
$dbpass="dbpass";
$dbnhost="dbnhost";
$conn=new PDO( "mysql:host=$dbnhost;dbname=$dbname", $dbuser, $dbpass);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ERRMODE_EXCEPTION, PDO::ATTR_ERRMODE);
  
  //stats table has 4 cols, id(AI), death(int) ], infected(int), timestamp(timestamp) 
$stmt = $conn->query("SELECT * FROM stats ORDER BY id DESC LIMIT 1");
$user = $stmt->fetch();
$sql = "INSERT INTO stats (death, infected, timestamp) VALUES (?,?,?)";
$stmt2 = $conn->prepare($sql);

$curl = curl_init('https://www.worldometers.info/coronavirus/');
 curl_setopt($curl, CURLOPT_FAILONERROR, true);
 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  
 $result = curl_exec($curl);
 $dom = new DOMDocument();
 $res=$dom->loadHTML($result);
 $xpath = new DomXPath($dom);
$class = 'maincounter-number';
$divs = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]");
if(count($divs)> 0) {
    $death = (int) implode("",explode(",", $divs[1]->nodeValue));
    $infected = (int) implode("",explode(",", $divs[0]->nodeValue));
    $death_db = (int)$user['death'];
    $infected_db = (int)$user['infected'];
	//time zone of country
    $date = new DateTime("now", new DateTimeZone('Asia/Baku') );
    if( $infected>$infected_db ||  $death > $death_db)
    {
        $formatted =  $date->format('Y-m-d H:i:s');
        $stmt2->execute([$death, $infected, $formatted]);
		
          $text = urlencode("\xE2\x9D\x97\xE2\x9D\x97" . " <b>Corovirus Update " . "\xE2\x9D\x97\xE2\x9D\x97 \n &#09;&#09;&#09;&#09;&#09;&#09;&#09;&#09;&#09;&#09;&#09;&#09;" . 
         $formatted.
      "\n <pre>| Cols     |  Before  |   After  |
|----------|:--------:|:--------:|
| Infected |  " .$infected_db ."  |  " .$infected ."  |
| Deatch   |   " .$death_db ."   |   " .$death ."   |  </pre>" . "</b>");
      
          $url_telegram = "https://api.telegram.org/bot{BOT_TOKEN}/sendMessage?chat_id={CHAT_ID}&text="
      . $text . "&parse_mode=html";
  
      //telegram-send
      $curl = curl_init($url_telegram);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($curl);
      curl_close($curl);
    }
}
?>