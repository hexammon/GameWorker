<?php

$I = new AcceptanceTester($scenario);
$I->am('player');
$I->wantToTest('game creating');

$I->amOnPage('/');

$I->seeInTitle('Game Worker Service Testing Page');

$I->fillField('wamp-address', 'wamp-router:9000');

$I->click('Connect');

$I->see("Hello player");

//$I->click('create game');