<?php

function getBuildPlan()
{
    $config = array();
    $config['plan'][]['Wood Store'] = 3;
    $config['plan'][]['Stone Store'] = 3;
    $config['plan'][]['Ore Store'] = 3;
    $config['plan'][]['Lumberjack'] = 5;
    $config['plan'][]['Quarry'] = 5;
    $config['plan'][]['Ore Mine'] = 5;
    $config['plan'][]['Wood Store'] = 5;
    $config['plan'][]['Stone Store'] = 5;
    $config['plan'][]['Ore Store'] = 5;
    $config['plan'][]['Farm'] = 3;
    $config['plan'][]['Lumberjack'] = 7;
    $config['plan'][]['Quarry'] = 7;
    $config['plan'][]['Ore Mine'] = 7;
    $config['plan'][]['Wood Store'] = 7;
    $config['plan'][]['Stone Store'] = 7;
    $config['plan'][]['Ore Store'] = 7;
    $config['plan'][]['Lumberjack'] = 10;
    $config['plan'][]['Quarry'] = 10;
    $config['plan'][]['Ore Mine'] = 10;
    $config['plan'][]['Wood Store'] = 10;
    $config['plan'][]['Stone Store'] = 10;
    $config['plan'][]['Ore Store'] = 10;
    $config['plan'][]['Farm'] = 10;
    $config['plan'][]['Lumberjack'] = 15;
    $config['plan'][]['Quarry'] = 15;
    $config['plan'][]['Ore Mine'] = 15;
    $config['plan'][]['Wood Store'] = 15;
    $config['plan'][]['Stone Store'] = 15;
    $config['plan'][]['Ore Store'] = 15;
    $config['plan'][]['Farm'] = 14;
    $config['plan'][]['Keep'] = 5;
    $config['plan'][]['Market'] = 5;
    $config['plan'][]['Lumberjack'] = 18;
    $config['plan'][]['Quarry'] = 18;
    $config['plan'][]['Ore Mine'] = 18;
    $config['plan'][]['Library'] = 5;
    $config['plan'][]['Wood Store'] = 20;
    $config['plan'][]['Stone Store'] = 20;
    $config['plan'][]['Ore Store'] = 20;
    $config['plan'][]['Lumberjack'] = 20;
    $config['plan'][]['Quarry'] = 20;
    $config['plan'][]['Ore Mine'] = 20;
    $config['plan'][]['Farm'] = 15;
    $config['plan'][]['Keep'] = 8;
    $config['plan'][]['Library'] = 10;
    $config['plan'][]['Farm'] = 20;
    $config['plan'][]['Lumberjack'] = 22;
    $config['plan'][]['Quarry'] = 22;
    $config['plan'][]['Ore Mine'] = 22;
    $config['plan'][]['Wall'] = 10;
    $config['plan'][]['Arsenal'] = 15;
    $config['plan'][]['Lumberjack'] = 25;
    $config['plan'][]['Quarry'] = 25;
    $config['plan'][]['Ore Mine'] = 25;
    $config['plan'][]['Wall'] = 15;
    $config['plan'][]['Keep'] = 10;
    $config['plan'][]['Lumberjack'] = 30;
    $config['plan'][]['Quarry'] = 30;
    $config['plan'][]['Ore Mine'] = 30;
    $config['plan'][]['Arsenal'] = 20;
    $config['plan'][]['Wall'] = 20;
    $config['plan'][]['Arsenal'] = 30;
    $config['plan'][]['Farm'] = 30;
    $config['plan'][]['Tavern'] = 10;
    $config['plan'][]['Market'] = 8;

    $json = file_get_contents(__DIR__ . '/buildings.json');

    $buildingList = json_decode($json, true);

    $habitat = array();

    $habitat['Keep']['level'] = 1;
    $habitat['Lumberjack']['level'] = 1;
    $habitat['Wood Store']['level'] = 1;
    $habitat['Quarry']['level'] = 1;
    $habitat['Stone Store']['level'] = 1;
    $habitat['Ore Mine']['level'] = 1;
    $habitat['Ore Store']['level'] = 1;
    $habitat['Farm']['level'] = 1;
    $habitat['Market']['level'] = 1;
    $habitat['Library']['level'] = 1;
    $habitat['Tavern']['level'] = 1;
    $habitat['Wall']['level'] = 1;
    $habitat['Arsenal']['level'] = 1;

    $orderList = array();

    foreach ($config['plan'] as $building) {
        $buildingName = key($building);
        $buildingLevel = current($building);
        // check level

        levelUp($buildingName, $buildingLevel, $habitat, $buildingList, $orderList);


    }

    return $orderList;
}

function levelUp($buildingName, $buildingLevel, &$habitat, $buildingList, &$orderList)
{

    if ($habitat[$buildingName]['level'] < $buildingLevel) {

        foreach ($buildingList['Building'] as $key => $value) {
            $value['identifier_clean'] = substr($value['identifier'], 0, strpos($value['identifier'], "/"));
            if ($value['identifier_clean'] == $buildingName and $habitat[$buildingName]['level'] + 1 == $value['level']) {
                //print_r('call on primKey '.$value['primaryKey'].' ('.$value['identifier'].')'."\n");
                $orderList[] = array(
                    'primaryKey' => $value['primaryKey'],
                    'identifier' => $value['identifier'],
                    'bXLevelKey' => $value['order'] - 1,
                    'level' => $value['level'],
                    'buildResourceDictionary' => $value['buildResourceDictionary'],
                );
                $habitat[$buildingName]['level'] = $habitat[$buildingName]['level'] + 1;
                levelUp($buildingName, $buildingLevel, $habitat, $buildingList, $orderList);

                return;
            }

        }

    } else {
        //print_r($buildingName.' is already on '.$buildingLevel."\n");
    }

}
