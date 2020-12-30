<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Calypso implementation : © Andy Bond <48208438+ADBond@users.noreply.github.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * Calypso game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in calypso.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    100 => array(
        'name' => totranslate('Game Length'),
        'values' => array(
            1 => array(
                'name' => totranslate('Standard, short - 1 round (4 hands)'),
                'tmdisplay' => totranslate('Standard game'),
            ),
            2 => array(
                'name' => totranslate('Medium - 2 rounds (8 hands)'),
                'tmdisplay' => totranslate('2 rounds'),
            ),
            3 => array(
                'name' => totranslate('Longer - 3 rounds (12 hands)'),
                'tmdisplay' => totranslate('3 rounds'),
            ),
            4 => array(
                'name' => totranslate('Full rotation - 4 rounds (16 hands)'),
                'description' => totranslate('4 rounds - everyone gets to be first player once'),
                'tmdisplay' => totranslate('Full rotation'),
            ),
        ),
    ),
    101 => array(
        'name' => totranslate('Renounce indicators'),
        'values' => array(
            1 => array(
                'name' => totranslate('Renounce indicators on'),
                'description' => totranslate('Renounce indicators show suits that players have failed to follow suit to'),
            ),
            2 => array(
                'name' => totranslate('Renounce indicators off'),
                'tmdisplay' => totranslate('Renounce indicators off'),
            ),
        ),
    ),
    102 => array(
        'name' => totranslate('Partnerships'),
        'values' => array(
            1 => array(
                'name' => totranslate('By table order - 1st and 3rd against 2nd and 4th'),
            ),
            2 => array(
                'name' => totranslate('By table order - 1st and 2nd against 3rd and 4th'),
            ),
            3 => array(
                'name' => totranslate('By table order - 1st and 4th against 2nd and 3rd'),
            ),
            4 => array(
                'name' => totranslate('Random'),
                'description' => totranslate('Partnerships are allocated randomly')
            ),
        )
    ),
);
