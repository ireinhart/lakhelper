<?php

$player = array();
$player['password'] = $user['password'];
$player['login'] = $user['login'];
$player['world'] = $user['world'];
$player['worldId'] = $user['worldId'];

$player['loginId'] = ''; // from system login
$player['playerID'] = ''; // from world login cookie
$player['sessionID'] = ''; // from world login cookie
$player['playerHash'] = ''; // calculate with some data

// get game
$select = $sql->select();
$select->from('game');
$select->where('login="'.$player['login'].'" AND world="'.$player['world'].'"');
$selectString = $sql->getSqlStringForSqlObject($select);
// print_r($selectString);
$games = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);

if ($games->count() < 1) {
    // add game
    $insert = $sql->insert();
    $insert->into('game');
    $insert->values(array(
        'login' => $player['login'],
        'world' => $player['world'],
        'password' => $player['password'],
        'worldId' => $player['worldId'],
    ));
    $insertString = $sql->getSqlStringForSqlObject($insert);
    // print_r($insertString);
    $dbAdapter->query($insertString, $dbAdapter::QUERY_MODE_EXECUTE);

    // re read from database
    $select = $sql->select();
    $select->from('game');
    $select->where('login="'.$player['login'].'" AND world="'.$player['world'].'"');
    $selectString = $sql->getSqlStringForSqlObject($select);
    // print_r($selectString);
    $games = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
}

if ($games->count() == 1) {
    $game = $games->current();

    $player = array();
    // basic game data
    $player['login'] = $game->login;
    $player['password'] = $game->password;
    $player['world'] = $game->world;
    $player['worldId'] = $game->worldId;
    //print_r($player);

    if ($game->loginId < 1) {
        // Login at system
        // Login with login / email
        // get loginId
        $request = "http://ios-hh.lordsandknights.com/XYRALITY/WebObjects/BKLoginServer.woa/wa/LoginAction/checkValidLogin?callback=validateUserCallback&login=".$player['login']."&password=".$player['password'];
        $httpClient->setUri($request);
        $response = $httpClient->send();
        $loginResponse = json_decode(str_replace(array('validateUserCallback(', '})'), array('', '}'), $response->getBody()), true);
        // print_r($loginResponse);
        $player['loginId'] = $loginResponse['loginId'];
        $game->loginId = $player['loginId'];

        // print_r($player);

        // save loginId
        $update = $sql->update();
        $update->table('game');
        $update->where('gameId=' . $game->gameId);
        $update->set(array('loginId' => $player['loginId']));
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    } else {
        // print_r('loginId from DB');
        $player['loginId'] = $game->loginId;
    }

    if ($game->sessionID < 1) {
        // print_r('need sessionID...');
        // error_log('need sessionID...');
        $player['playerID'] = ''; // from world login cookie
        $player['sessionID'] = ''; // from world login cookie
        $player['playerHash'] = ''; // calculate with some data

        // Login at world
        // getting sessionID + playerID from cookie
        // using touchDate from connect json to create playerHash
        $request = "http://backend2.lordsandknights.com/XYRALITY/WebObjects/LKWorldServer-".$player['world'].".woa/wa/LoginAction/connect?login=".$player['login']."&password=".$player['password']."&worldId=".$player['worldId']."&clientCacheVersion=LK_LK-DE-2_4-56-46";
        $httpClient->setUri($request);
        $response = $httpClient->send();
        $connectResponse = json_decode(str_replace(array('connect(', '}]})'), array('', '}]}'), $response->getBody()), true);
        // print_r($connectResponse);
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

        $game->playerID = $player['playerID'];
        $game->sessionID = $player['sessionID'];
        $game->playerHash = $player['playerHash'];

        // save playerID, sessionID and playerHash
        $update = $sql->update();
        $update->table('game');
        $update->where('gameId=' . $game->gameId);
        $update->set(array(
            'playerID' => $player['playerID'],
            'sessionID' => $player['sessionID'],
            'playerHash' => $player['playerHash'],
        ));
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();

        // print_r($player);

    } else {
        // print_r('playerID, sessionID and playerHash from DB');
        // error_log('playerID, sessionID and playerHash from DB');
        $player['playerID'] = $game->playerID;
        $player['sessionID'] = $game->sessionID;
        $player['playerHash'] = $game->playerHash;
    }

    // set correct cookie
    $httpClient->clearCookies();
    $httpClient->addCookie('loginId', $player['loginId']);
    $httpClient->addCookie('sessionID', $player['sessionID']);
    $httpClient->addCookie('playerID', $player['playerID']);

    // read player data (simple overview) to check that session is active
    $request = "http://ios-hh.lordsandknights.com/XYRALITY/WebObjects/LKWorldServer-".$player['world'].".woa/wa/ProfileAction/playerInformation?callback=ProfileView.getPlayerInformationCallback&id=".$player['playerID']."&".$player['playerID']."=".$player['playerHash'];
    $httpClient->setUri($request);
    $response = $httpClient->send();
    $playerInformationResponse = json_decode(str_replace(array('ProfileView.getPlayerInformationCallback(', '})'), array('', '}'), $response->getBody()), true);
    // print_r($playerInformationResponse);
    // error_log(print_r($playerInformationResponse, 1));
    if (isset($playerInformationResponse['error'])) {
        // error_log('ERROR LOGIN: '.$playerInformationResponse['error']);
        // session reset
        $update = $sql->update();
        $update->table('game');
        $update->where('gameId='.$game->gameId);
        $update->set(array(
            'sessionID' => '',
            'playerHash' => '',
        ));
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
        require(__DIR__ . '/login.php');
    }

} else {
    error_log('LOGIN FAILED');
    exit();
}
