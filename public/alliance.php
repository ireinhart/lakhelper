<?php

require(__DIR__ . '/bootstrap.php');

require(__DIR__ . '/login.php');

// read player data (simple overview)
$request = "http://ios-hh.lordsandknights.com/XYRALITY/WebObjects/LKWorldServer-".$player['world'].".woa/wa/ProfileAction/playerInformation?callback=ProfileView.getPlayerInformationCallback&id=".$player['playerID']."&".$player['playerID']."=".$player['playerHash'];
$httpClient->setUri($request);
$response = $httpClient->send();
$playerInformationResponse = json_decode(str_replace(array('ProfileView.getPlayerInformationCallback(', '}})'), array('', '}}'), $response->getBody()), true);
//print_r($playerInformationResponse);

// new version
$sql = new Zend\Db\Sql\Sql($dbAdapter);
$insert = $sql->insert();
$insert->into('version');        
$insert->values(array(
    'date' => new \Zend\Db\Sql\Expression("NOW()"),
    'source' => 'alliance',
    'userId' => 1,
));
$insertString = $sql->getSqlStringForSqlObject($insert);
$results = $dbAdapter->query($insertString, $dbAdapter::QUERY_MODE_EXECUTE);
$versionId = $results->getGeneratedValue();

// alliance
$alliance = array();
$alliance['allianceId'] = $playerInformationResponse['Data']['Alliance'][0]['id'];
$alliance['name'] = $playerInformationResponse['Data']['Alliance'][0]['name'];
//$alliance['description'] = $playerInformationResponse['Data']['Alliance'][0]['descriptionText'];
$alliance['points']  = $playerInformationResponse['Data']['Alliance'][0]['points'];

$sql = new Zend\Db\Sql\Sql($dbAdapter);
$select = $sql->select();
$select->from('alliance');
$select->where('allianceId='.$alliance['allianceId']);
$selectString = $sql->getSqlStringForSqlObject($select);
$previousAlliance = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);

if($previousAlliance->count()==1) {
    $previousAlliance = $previousAlliance->current();
    
    $prevAlliance =array();
    $prevAlliance['allianceId'] = $previousAlliance->allianceId;
    $prevAlliance['name'] = $previousAlliance->name;
    //$prevAlliance['description'] = $previousAlliance->description;
    $prevAlliance['points']  = $previousAlliance->points;

    if($prevAlliance != $alliance) {
        //print_r("FOUND DIFF ALLIANCE:\n");
        //print_r($prevAlliance);
        //print_r($alliance);
        $moveAllianceToHistory = 'INSERT INTO alliance_history (SELECT * FROM alliance WHERE allianceId='.$alliance['allianceId'].')';
        $dbAdapter->query($moveAllianceToHistory, $dbAdapter::QUERY_MODE_EXECUTE); 
    }
    // update (only version if no diff)
    $alliance['versionId'] = $versionId;      
    $sql = new Zend\Db\Sql\Sql($dbAdapter);
    $update = $sql->update();
    $update->table('alliance');
    $update->set($alliance);
    $update->where('allianceId='.$alliance['allianceId']);
    $statement = $sql->prepareStatementForSqlObject($update);
    $statement->execute();
} else {
    $alliance['versionId'] = $versionId;
    $sql = new Zend\Db\Sql\Sql($dbAdapter);
    $insert = $sql->insert();
    $insert->into('alliance');
    $insert->values($alliance);
    $statement = $sql->prepareStatementForSqlObject($insert);
    $statement->execute();    
}

// csv file
$filename = 'alliance_'.date('Y-m-d').'.csv';
$fh = '';

// kopfzeile
$fields = array();
$fields['nick'] = 'nick';
$fields['player_points'] = 'player_points';
$fields['player_link'] = 'player_link';
$fields['habitat'] = 'habitat';
$fields['habitat_points'] = 'habitat_points';
$fields['habitat_link'] = 'habitat_link';
$fh .= implode(';', $fields)."\n";

//print_r($playerInformationResponse);

foreach ($playerInformationResponse['Data']['Alliance'][0]['playerArray'] as $nextPlayer) {
    flush();
    //print_r($nextPlayer['nick']." (l+k://player?".$nextPlayer."&".$player['worldId'].")\n");
    $request = "http://ios-hh.lordsandknights.com/XYRALITY/WebObjects/LKWorldServer-".$player['world'].".woa/wa/ProfileAction/playerInformation?callback=ProfileView.getPlayerInformationCallback&id=".$nextPlayer."&".$player['playerID']."=".$player['playerHash'];
    $httpClient->setUri($request);
    $response = $httpClient->send();
    // print_r($response->getBody());
    $myPlayer = json_decode(str_replace(array('ProfileView.getPlayerInformationCallback(', '}})'), array('', '}}'), $response->getBody()), true);
    //print_r($myPlayer);
   /* exit;
    Array
    (
        [Data] => Array
        (
            [Player] => Array
            (
                [0] => Array

                    [nick] => ðŸ‚DanðŸ‚
                            [points] => 53830
                            [alliancePermission] => 8
                            [alliance] => 33094
                            [isOnVacation] =>
                            [id] => 75073
                            [rank] => 807
                            [entityName] => Player
*/
    $fields = array();
    $fields['nick'] = $myPlayer['Data']['Player'][0]['nick'];
    $fields['player_points'] = $myPlayer['Data']['Player'][0]['points'];
    $fields['player_link'] = 'l+k://player?'.$nextPlayer."&".$player['worldId'];

    // player
    $newPlayer = array();
    $newPlayer['playerId'] = $nextPlayer;
    $newPlayer['name'] = $myPlayer['Data']['Player'][0]['nick'];
    $newPlayer['points'] = $myPlayer['Data']['Player'][0]['points'];
    $newPlayer['isOnVacation'] = $myPlayer['Data']['Player'][0]['isOnVacation'];
    if(isset($myPlayer['Data']['Player'][0]['vacationStartDate'])) {
        $newPlayer['vacationStartDate']  = str_replace(array('T', 'Z'), array(' ', ''), $myPlayer['Data']['Player'][0]['vacationStartDate']);
    } else {
        $newPlayer['vacationStartDate'] = null;
    }
    $newPlayer['allianceId'] = $myPlayer['Data']['Player'][0]['alliance'];
    $newPlayer['alliancePermission']  = $myPlayer['Data']['Player'][0]['alliancePermission'];
    
    $sql = new Zend\Db\Sql\Sql($dbAdapter);
    $select = $sql->select();
    $select->from('player');
    $select->where('playerId='.$newPlayer['playerId']);
    $selectString = $sql->getSqlStringForSqlObject($select);
    $previousPlayer = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);

    if($previousPlayer->count()==1) {
        $previousPlayer = $previousPlayer->current();
        $prevPlayer =array();
        $prevPlayer['playerId'] = $previousPlayer->playerId;
        $prevPlayer['name'] = $previousPlayer->name;
        $prevPlayer['points'] = $previousPlayer->points;
        $prevPlayer['isOnVacation'] = $previousPlayer->isOnVacation;
        $prevPlayer['vacationStartDate'] = $previousPlayer->vacationStartDate;
        $prevPlayer['allianceId'] = $previousPlayer->allianceId;
        $prevPlayer['alliancePermission'] = $previousPlayer->alliancePermission;
        
        if($prevPlayer != $newPlayer) {
            //print_r("FOUND DIFF PLAYER:\n");
            //print_r($prevPlayer);
            //print_r($newPlayer);
            $movePlayerToHistory = 'INSERT INTO player_history (SELECT * FROM player WHERE playerId='.$newPlayer['playerId'].')';
            $dbAdapter->query($movePlayerToHistory, $dbAdapter::QUERY_MODE_EXECUTE); 
        }
        
        $newPlayer['versionId'] = $versionId;

        $sql = new Zend\Db\Sql\Sql($dbAdapter);
        $update = $sql->update();
        $update->table('player');
        $update->set($newPlayer);
        $update->where('playerId='.$newPlayer['playerId']);
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
        
    } else {
        $sql = new Zend\Db\Sql\Sql($dbAdapter);
        $insert = $sql->insert();
        $insert->into('player');
        $newPlayer['versionId'] = $versionId;
        $insert->values($newPlayer);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
    }
    
    foreach($myPlayer['Data']['Habitat'] as $burg) {
/*        (
        [player] => 75073
        [points] => 289
        [id] => 399394
        [publicHabitatType] => 0
        [name] => ðŸŒ˜2GelsenkirchenðŸŒ’
        [mapX] => 16808
        [mapY] => 16831
        [entityName] => Habitat
    )
*/
        //print_r($myPlayer->Player->habitatDictionary);
        if(!isset($burg['name'])) $burg['name'] = null;
        //print_r($burg['name'].' (l+k://coordinates?'.$burg['mapX'].','.$burg['mapY'].'&'.$player['worldId'].")\n");
        $fields['habitat'] = $burg['name'];
        $fields['habitat_points'] = $burg['points'];
        $fields['habitat_link'] = 'l+k://coordinates?'.$burg['mapX'].','.$burg['mapY'].'&'.$player['worldId'];
        $fh .= implode(';', $fields)."\n";
        
        // habitat
        $habitat = array();
        $habitat['habitatId'] = $burg['id'];
        $habitat['playerId'] = $burg['player'];
        if(trim($burg['name'])) {
            $habitat['name'] = $burg['name'];
        } else {
            $habitat['name'] = $burg['id'];
        }
        $habitat['points'] = $burg['points'];
        $habitat['mapX'] = $burg['mapX'];
        $habitat['mapY'] = $burg['mapY'];
        
        $sql = new Zend\Db\Sql\Sql($dbAdapter);
        $select = $sql->select();
        $select->from('habitat');
        $select->where('habitatId='.$habitat['habitatId']);
        $selectString = $sql->getSqlStringForSqlObject($select);
        $previousHabitat = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
        
        if($previousHabitat->count()==1) {
            $previousHabitat = $previousHabitat->current();
            $prevHabitat =array();
            $prevHabitat['habitatId'] = $previousHabitat->habitatId;
            $prevHabitat['playerId'] = $previousHabitat->playerId;
            $prevHabitat['name'] = $previousHabitat->name;
            $prevHabitat['points'] = $previousHabitat->points;
            $prevHabitat['mapX'] = $previousHabitat->mapX;
            $prevHabitat['mapY'] = $previousHabitat->mapY;

            if($prevHabitat != $habitat) {
                //print_r("FOUND DIFF HABITAT:\n");
                //print_r($prevHabitat);
                //print_r($habitat);
                $movePlayerToHistory = 'INSERT INTO habitat_history (SELECT * FROM habitat WHERE habitatId='.$habitat['habitatId'].')';
                $dbAdapter->query($movePlayerToHistory, $dbAdapter::QUERY_MODE_EXECUTE); 
            }

            $habitat['versionId'] = $versionId;

            $sql = new Zend\Db\Sql\Sql($dbAdapter);
            $update = $sql->update();
            $update->table('habitat');
            $update->set($habitat);
            $update->where('habitatId='.$habitat['habitatId']);
            $statement = $sql->prepareStatementForSqlObject($update);
            $statement->execute();
        } else {
            $sql = new Zend\Db\Sql\Sql($dbAdapter);
            $insert = $sql->insert();
            $insert->into('habitat');
            $habitat['versionId'] = $versionId;
            $insert->values($habitat);
            $statement = $sql->prepareStatementForSqlObject($insert);
            try {
                $statement->execute();
            } catch (Exception $e) {
                print_r($habitat);
                print_r($e);
            }
        }
    }
}

$mail = new Zend\Mail\Message();
$mail->setTo($player['login']);
$mail->setSubject('LK: alliance list');

$bodyPart = new Zend\Mime\Message;
$attachment = new Zend\Mime\Part($fh);
$attachment->type = 'text/csv';
$attachment->filename = $filename;
$attachment->disposition = Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
$bodyMessage = new Zend\Mime\Part('Alliance Liste von '.$alliance['name']);
$bodyMessage->type = 'text/plain';
$bodyPart->setParts(array($bodyMessage, $attachment));

$mail->setBody($bodyPart);
$transport = new Zend\Mail\Transport\Sendmail();
$transport->send($mail);
