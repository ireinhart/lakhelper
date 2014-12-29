<?php

$libPath = dirname(__FILE__) . '/../library/';
set_include_path($libPath . ':' . get_include_path());

include($libPath . 'Zend/Loader/StandardAutoloader.php');

// Setup autoloading
$loader = new Zend\Loader\StandardAutoloader();
$loader->setFallbackAutoloader(true);
$loader->register();

require(__DIR__ . '/config.php');

$player = array();
$player['password'] = $user['password'];
$player['login'] = $user['login'];
$player['world'] = $user['world'];
$player['worldId'] = $user['worldId'];

$player['loginId'] = ''; // from system login
$player['playerID'] = ''; // from world login cookie
$player['sessionID'] = ''; // from world login cookie
$player['playerHash'] = ''; // calculate with some data

$httpClient = new Zend\Http\Client;

$headers = new Zend\Http\Headers();
$headers->addHeaderLine('XYClient-Capabilities', 'base,fortress,partialUpdate,simplePlayerReport');
$headers->addHeaderLine('User-Agent', 'BKClient/4.3.0 (iPhone OS 8.1.2 / iPhone5,2)');

$httpClient->setHeaders($headers);

// Login at system
// Login with login / email
// get loginId
$request = "http://ios-hh.lordsandknights.com/XYRALITY/WebObjects/BKLoginServer.woa/wa/LoginAction/checkValidLogin?callback=validateUserCallback&login=".$player['login']."&password=".$player['password'];
$httpClient->setUri($request);
$response = $httpClient->send();
$loginResponse = json_decode(str_replace(array('validateUserCallback(', '})'), array('', '}'), $response->getBody()), true);
//print_r($loginResponse);
$player['loginId'] = $loginResponse['loginId'];

// Login at world
// getting sessionID + playerID from cookie
// using touchDate from connect json to create playerHash
$request = "http://backend2.lordsandknights.com/XYRALITY/WebObjects/LKWorldServer-".$player['world'].".woa/wa/LoginAction/connect?login=".$player['login']."&password=".$player['password']."&worldId=".$player['worldId']."&clientCacheVersion=LK_LK-DE-2_4-56-46";
$httpClient->setUri($request);
$response = $httpClient->send();
$connectResponse = json_decode(str_replace(array('connect(', '}]})'), array('', '}]}'), $response->getBody()), true);
//print_r($connectResponse);
foreach ($httpClient->getCookies() as $cookie) {
    if($cookie->getName() == 'playerID') {
        $player['playerID'] = $cookie->getValue();
        continue;
    }
    if($cookie->getName() == 'sessionID') {
        $player['sessionID'] = $cookie->getValue();
        continue;
    }
}
// calc playerHash
$e = $connectResponse['touchDate'];
$b = $player['playerID'];
$c = "9FF";
$a = substr($e, 0, 10)." ".substr($e, 11, 19)." Etc/GMT";
$d = $c.$b.$a;
$player['playerHash'] = sha1($d);


// set correct cookie
$httpClient->clearCookies();
$httpClient->addCookie('loginId', $player['loginId']);
$httpClient->addCookie('sessionID', $player['sessionID']);
$httpClient->addCookie('playerID', $player['playerID']);

// read own player data (many)
$request = "http://ios-hh.lordsandknights.com/XYRALITY/WebObjects/LKWorldServer-".$player['world'].".woa/wa/SessionAction/update?callback=Session.updateCallbackWithoutRepaint&PropertyListVersion=3&".$player['playerID']."=".$player['playerHash'];
$httpClient->setUri($request);
$response = $httpClient->send();
$updateResponse = json_decode(str_replace(array('Session.updateCallbackWithoutRepaint(', '})'), array('', '}'), $response->getBody()), true);
//print_r($updateResponse);


$habitats = array();

foreach($updateResponse['Data']['Habitat'] as $habitatKey => $habitat) {

    if (!isset($habitat['player'])) continue;
    if ($habitat['player'] !=  $updateResponse['selectedPlayer']) continue;

    // look for matching habitatUnitArray
    $habitatUnit = array();
    $habitatUnit['1'] = 0;
    $habitatUnit['2'] = 0;
    $habitatUnit['101'] = 0;
    $habitatUnit['102'] = 0;
    $habitatUnit['201'] = 0;
    $habitatUnit['202'] = 0;
    $habitatUnit['10001'] = 0;
    $habitatUnit['10002'] = 0;
    foreach ($updateResponse['Data']['HabitatUnit'] as $unit) {
        // only local units
        if ($unit['sourceHabitat'] == $habitat['id'] and $unit['habitat'] == $habitat['id']) {
            $habitatUnit[$unit['unitId']] = $unit['amount'];
        }
    }

    $habitatCSV['X']=$habitat['mapX'];
    $habitatCSV['Y']=$habitat['mapY'];
    $habitatCSV['Name']=$habitat['name'];
    if(array_search('19', $habitat['habitatKnowledgeIdArray'])) {
        $habitatCSV['Umgeb. Karte']='true';
    } else {
        $habitatCSV['Umgeb. Karte']='false';
    }
    
    $habitatCSV['Silb']=$habitat['habitatResourceDictionary']['6']['amount'];
    $habitatCSV['ST']=$habitatUnit['1'];
    $habitatCSV['SK']=$habitatUnit['2'];
    $habitatCSV['BS']=$habitatUnit['101'];
    $habitatCSV['AS']=$habitatUnit['102'];
    $habitatCSV['PR']=$habitatUnit['201'];
    $habitatCSV['LR']=$habitatUnit['202'];
    $habitatCSV['HK']=$habitatUnit['10001'];
    $habitatCSV['OK']=$habitatUnit['10002'];

    $habitats[] = $habitatCSV;
    
}


// csv header
$header = array();
$header['X']='X';
$header['Y']='Y';
$header['Name']='Name';
$header['Umgeb. Karte']='Umgeb. Karte';
$header['Silb']='Silb';
$header['ST']='ST';
$header['SK']='SK';
$header['BS']='BS';
$header['AS']='AS';
$header['PR']='PR';
$header['LR']='LR';
$header['HK']='HK';
$header['OK']='OK';

$application="text/csv";
header( "Content-Type: $application" );
header( "Content-Disposition: attachment; filename=habitat.csv");
header( "Content-Description: csv File" );
header( "Pragma: no-cache" );
header( "Expires: 0" );

print_r(implode(';', $header)."\n");
foreach($habitats as $habitat) {
    print_r(implode(';', $habitat)."\n");
}

