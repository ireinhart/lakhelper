<?php

require(__DIR__ . '/bootstrap.php');

require(__DIR__ . '/login.php');

// read own player data (many)
$request = "http://ios-hh.lordsandknights.com/XYRALITY/WebObjects/LKWorldServer-".$player['world'].".woa/wa/SessionAction/update?callback=Session.updateCallbackWithoutRepaint&PropertyListVersion=3&".$player['playerID']."=".$player['playerHash'];
$httpClient->setUri($request);
$response = $httpClient->send();
$updateResponse = json_decode(str_replace(array('Session.updateCallbackWithoutRepaint(', '})'), array('', '}'), $response->getBody()), true);
//print_r($updateResponse);

// build habitat array for logged in player
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

// send csv file
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
