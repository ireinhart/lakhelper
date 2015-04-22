<?php

require(__DIR__ . '/bootstrap.php');

require(__DIR__ . '/login.php');

// read own player data (many)
$request = "http://ios-hh.lordsandknights.com/XYRALITY/WebObjects/LKWorldServer-".$player['world'].".woa/wa/SessionAction/update?callback=Session.updateCallbackWithoutRepaint&".$player['playerID']."=".$player['playerHash'];
$httpClient->setUri($request);
$response = $httpClient->send();
$updateResponse = json_decode(str_replace(array('Session.updateCallbackWithoutRepaint(', '})'), array('', '}'), $response->getBody()), true);
//print_r($updateResponse);

require(__DIR__ . '/building_plan.php');
$buildPlan = getBuildPlan();
//print_r($buildPlan);

// look at all own habitats what building can build as next
foreach($updateResponse['Data']['Habitat'] as $habitatKey => $habitat) {
    if (!isset($habitat['player'])) {
        // free habitat (not own habitat)
        continue;
    }
    if ($habitat['player'] != $player['playerID']) {
        // not own habitat
        continue;
    }
    foreach ($buildPlan as $stepId => $stepValue) {
        if($habitat['habitatBuildingKeyArray'][$stepValue['bXLevelKey']] >= $stepValue['primaryKey']) {
            //print_r($habitat['name'].': '.$stepValue['identifier'].' already build'."\n");
        } else {
            if(count($habitat['habitatBuildingUpgradeArray'])>=2) {  
                // maximum builds in progress
                print_r($habitat['name'].': 2 builds in progress'."\n");
                continue 2;
            } elseif (count($habitat['habitatBuildingUpgradeArray'])==1) { 
                // one build in progress
                if($habitat['habitatBuildingUpgradeArray']['0'] == $stepValue['primaryKey'].'-'.$habitat['id']) {
                    // trying to build same building again, that won't work, run foreach once again
                    print_r($habitat['name'].': Work on '.$stepValue['identifier'].' already in progress '.$stepValue['primaryKey']."\n");
                    continue 1;
                }
            }
            if(($habitat['habitatResourceDictionary']['4']['storeAmount'] - $habitat['habitatResourceDictionary']['4']['amount']) < $stepValue['volumeAmount']) {
                // print_r($habitat['name'].': not enough farmers => override '.$stepValue['identifier'].", level up the farm now\n");
                foreach ($habitat['habitatBuildingKeyArray'] as $buildKey) {
                    if(substr($buildKey, 0, 1) == 8) { // 8 = Farm
                        $nextFarmKey = $buildKey + 1;
                    }
                }
                foreach($buildPlan as $buildStep) {
                    if($buildStep['primaryKey'] == $nextFarmKey) {
                        $stepValue = $buildStep;
                    }
                }
                //print_r($habitat['name'].': new step is '.$stepValue['primaryKey'].' '.$stepValue['identifier']);
            }
            // now, i know which building can be build
            print_r($habitat['name'].': '.$stepValue['identifier'].' can be build => use in call this primaryKey: '.$stepValue['primaryKey']."\n");
            if($stepValue['buildResourceDictionary']['1']<$habitat['habitatResourceDictionary']['1']['amount'] and
                $stepValue['buildResourceDictionary']['2']<$habitat['habitatResourceDictionary']['2']['amount'] and
                $stepValue['buildResourceDictionary']['3']<$habitat['habitatResourceDictionary']['3']['amount']) {
                // upgrade building
                $request = "http://ios-hh.lordsandknights.com/XYRALITY/WebObjects/LKWorldServer-".$player['world'].".woa/wa/HabitatAction/upgradeBuilding?callback=Session.updateCallback&habitatID=".$habitat['id']."&paymentGranted=false&primaryKey=".$stepValue['primaryKey']."&".$player['playerID']."=".$player['playerHash'];
                $httpClient->setUri($request);
                $response = $httpClient->send();
                //print_r($response->getBody()."\n");
                $updateResponse = json_decode(str_replace(array('Session.updateCallback(', '})'), array('', '}'), $response->getBody()), true);
                //print_r($updateResponse);
                if($updateResponse == '') {
                    print_r('Error (cant read json): '.$response->getBody()."\n");
                }
                if(array_key_exists('error', $updateResponse)) {
                    print_r('Error: '.$updateResponse['error']);
                }
            } else {
                print_r($habitat['name'].': Not enough amount of resources '."\n");
            }
            continue 2;
        }
    }
}

