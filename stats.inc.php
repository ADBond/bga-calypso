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
 * stats.inc.php
 *
 * Calypso game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = array(

    // Statistics global to table
    "table" => array(

        "average_calypsos_per_round" => array(
            "id" => 10,
            "name" => totranslate("Total calypsos per round"),
            "type" => "float",
        ),
        "average_points_per_round" => array(
            "id" => 11,
            "name" => totranslate("Average individual score per round"),
            "type" => "float",
        ),

        // may as well keep analagous ids as for player stats
        "fastest_calypso" => array(
            "id" => 35,
            "name" => totranslate("Fastest calypso (by number of tricks)"),
            "type" => "int"
        ),

        "proportion_tricks_won_trump_lead" => array(
            "id" => 41,
            "name" => totranslate("Proportion of tricks won by leading trumps"),
            "type" => "float",
        ),
        "proportion_tricks_won_first_trump" => array(
            "id" => 42,
            "name" => totranslate("Proportion of tricks won by trumping in first"),
            "type" => "float",
        ),
        "proportion_tricks_won_overtrump" => array(
            "id" => 43,
            "name" => totranslate("Proportion of tricks won by overtrumping"),
            "type" => "float",
        ),
        "proportion_tricks_won_plainsuit" => array(
            "id" => 44,
            "name" => totranslate("Proportion of tricks won by highest plainsuit card"),
            "type" => "float",
        ),

    ),
    
    // Statistics existing for each player
    "player" => array(
        // per round statistics
        "calypsos_per_round" => array(
            "id" => 10,
            "name" => totranslate("Average calypsos per round (individual)"),
            "type" => "float"
        ),
        "calypso_points_per_round" => array(
            "id" => 12,
            "name" => totranslate("Average calypso points per round (individual)"),
            "type" => "float",
        ),
        "incomplete_calypso_cards_per_round" => array(
            "id" => 14,
            "name" => totranslate("Average incomplete calypso cards per round (individual)"),
            "type" => "float",
        ),
        "trickpile_cards_per_round" => array(
            "id" => 16,
            "name" => totranslate("Average trickpile cards per round (individual)"),
            "type" => "float",
        ),
        "points_per_round" => array(
            "id" => 18,
            "name" => totranslate("Average points per round (individual)"),
            "type" => "float",
        ),
        "partnership_points_per_round" => array(
            "id" => 19,
            "name" => totranslate("Average points per round (partnership)"),
            "type" => "float",
        ),
        
        // "total_cards_won" => array(
        //     "id" => 20,
        //     "name" => totranslate("Total captured cards per round (individual)"),
        //     "type" => "float",
        // ),

        "personal_trumps_per_hand" => array(
            "id" => 30,
            "name" => totranslate("Average personal trumps dealt per hand"),
            "type" => "float",
        ),
        "partner_trumps_per_hand" => array(
            "id" => 31,
            "name" => totranslate("Average partner trumps dealt per hand"),
            "type" => "float",
        ),
        "opponent_trumps_per_hand" => array(
            "id" => 32,
            "name" => totranslate("Average opponent trumps dealt per hand"),
            "type" => "float",
        ),

        "fastest_calypso" => array(
            "id" => 35,
            "name" => totranslate("Fastest calypso (by number of tricks)"),
            "type" => "int"
        ),

        "tricks_won_total_per_hand" => array(
            "id" => 40,
            "name" => totranslate("Average tricks won per hand"),
            "type" => "float",
        ),
        "tricks_won_trump_lead_per_hand" => array(
            "id" => 41,
            "name" => totranslate("Average tricks won by leading trumps per hand"),
            "type" => "float",
        ),
        "tricks_won_first_trump_per_hand" => array(
            "id" => 42,
            "name" => totranslate("Average tricks won by trumping in first per hand"),
            "type" => "float",
        ),
        "tricks_won_overtrump_per_hand" => array(
            "id" => 43,
            "name" => totranslate("Average tricks won by overtrumping per hand"),
            "type" => "float",
        ),
    )
);
