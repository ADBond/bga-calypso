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

// TODO: migrate to .json files??

$game_options = array(

    // TODO: can this mesh with variant rules, or we need a separate option set?
    // in that case we want number of rounds to be 1 for logic, but hands/round is set here somewhere
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
        'level' => 'additional',
    ),
    102 => array(
        'name' => totranslate('Partnerships'),
        'values' => array(
            4 => array(
                'name' => totranslate('Random'),
                'description' => totranslate('Partnerships are allocated randomly')
            ),
            1 => array(
                'name' => totranslate('By table order - 1st and 3rd against 2nd and 4th'),
            ),
            2 => array(
                'name' => totranslate('By table order - 1st and 2nd against 3rd and 4th'),
            ),
            3 => array(
                'name' => totranslate('By table order - 1st and 4th against 2nd and 3rd'),
            ),
        )
    ),
    // I like it in Hungarian Tarokk as an option, so keep it here. Just like real life!
    103 => array(
        'name' => totranslate('Game log detail'),
        'values' => array(
            1 => array(
                'name' => totranslate('All cards played are entered into the gamelog'),
                'tmdisplay' => totranslate('Cards played in gamelog')
            ),
            2 => array(
                'name' => totranslate('Only trick winners are entered into the gamelog'),
                'tmdisplay' => totranslate('Cards played not in gamelog')
            ),
        )
    ),
    104 => array(
        'name' => totranslate('Ruleset'),
        'values' => array(
            1 => array(
                'name' => totranslate('Standard'),
            ),
            2 => array(
                'name' => totranslate('Variant'),
            ),
        ),
        'level' => 'major',
    ),
    // only allow other deck numbers with variant rules
    105 => array(
        'name' => totranslate('Number of decks'),
        'values' => array(
            3 => array(
                'name' => totranslate('3'),
            ),
            4 => array(
                'name' => totranslate('4'),
            ),
        ),
        'default': 3,
        'displaycondition' => array(
            'type' => 'otheroption',
            'id' => '104',
            'value' => ['2'],
        )
    )
);

# user preference options - just aesthetic stuff
$game_preferences = array(
    100 => array(
            'name' => totranslate('Table colour'),
            'needReload' => true,
            'values' => array(
                1 => array( 'name' => totranslate( 'Purple' ), 'cssPref' => 'clp-up-purple' ),
                2 => array( 'name' => totranslate( 'Yellow' ), 'cssPref' => 'clp-up-yellow' ),
                3 => array( 'name' => totranslate( 'Red' ), 'cssPref' => 'clp-up-red' ),
                4 => array( 'name' => totranslate( 'Green' ), 'cssPref' => 'clp-up-green' ),
                5 => array( 'name' => totranslate( 'Blue' ), 'cssPref' => 'clp-up-blue' ),
                6 => array( 'name' => totranslate( 'White' ), 'cssPref' => 'clp-up-white' ),
                7 => array( 'name' => totranslate( 'Black' ), 'cssPref' => 'clp-up-black' ),
                8 => array( 'name' => totranslate( 'Pink' ), 'cssPref' => 'clp-up-pink' ),
                9 => array( 'name' => totranslate( 'Cyan' ), 'cssPref' => 'clp-up-cyan' ),
                10 => array( 'name' => totranslate( 'Dark green' ), 'cssPref' => 'clp-up-darkgreen' ),
                11 => array( 'name' => totranslate( 'Orange' ), 'cssPref' => 'clp-up-orange' ),
            ),
            'default' => 1
    ),
    101 => array(
            'name' => totranslate('Card face style'),
            'needReload' => true,
            'values' => array(
                1 => array( 'name' => totranslate('Standard two-colour'), 'cssPref' => 'clp-pack-standard' ),
                2 => array( 'name' => totranslate('Four-colour'), 'cssPref' => 'clp-pack-four-colour' ),
            ),
            'default' => 1
    ),
    102 => array(
            'name' => totranslate('Highlight playable cards'),
            'needReload' => true,
            'values' => array(
                1 => array( 'name' => totranslate('Yes'), 'cssPref' => 'clp-cards-highlight-playable' ),
                2 => array( 'name' => totranslate('No'), 'cssPref' => 'clp-cards-dont-highlight-playable' ),
            ),
            'default' => 1

    )
);
