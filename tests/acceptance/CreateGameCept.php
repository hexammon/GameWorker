<?php

$I = new AcceptanceTester($scenario);
$I->am('player');
$I->wantToTest('game creating');

$I->amOnPage('/');

$I->seeInTitle('Game Worker Service Testing Page');

$I->fillField('wamp-address', 'wamp-router:9000');

$I->click('Connect');

$I->see("Hello player");

$I->fillField('number-of-rows', 8);
$I->fillField('number-of-cols', 8);
$I->selectOption('cell-type', ['value' => 'hex']);
$I->click('Create game');

$I->seeNumberOfElements('#my-games li', 1);