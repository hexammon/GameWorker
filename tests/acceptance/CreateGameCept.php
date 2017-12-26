<?php

$I = new AcceptanceTester($scenario);
$I->am('player');
$I->wantToTest('game creating');

$I->amOnPage('/');

$I->seeInTitle('Game Worker Service Testing Page');

$I->fillField('wamp-address', 'wamp-router:9000');

$I->click('Connect');

$I->waitForText("Hello game-dispatcher");

$I->fillField('number-of-rows', 8);
$I->fillField('number-of-cols', 8);
$I->selectOption('[name=cell-type]', 'hex');
$I->click('Create game');

$I->click('Show games');

$I->seeNumberOfElements('#games li', 1);