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
  * calypso.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Calypso extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        # not sure for now quite what I need to keep track of here, so start minimally-ish
        self::initGameStateLabels( array(
                         //"handDealer" => 10,
                         // Mappings of suits to players, or is that in db?
                         // keeping track of how many deals through the devk?
                         // how many hands overall??
                         // completed calypsos? or in db? or is scoring separate?
                         "trickColor" => 11,
                         // probably want some
                         //    "my_first_game_variant" => 100,
                         //    "my_second_game_variant" => 101,
                          ) );

        $this->cards = self::getNew( "module.common.deck" );
        $this->cards->init( "card" );  // db table initialisation
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "calypso";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values

        // Set current trick color to zero (= no trick color)
        self::setGameStateInitialValue( 'trickColor', 0 );

        // AB TODO: other gamestate values when I've figured out what they are!

        // Create cards
        $num_decks = 4;  // will be 4 - need to change here and in js
        $cards = array ();
        foreach ( $this->colors as $color_id => $color ) {
            // spade, heart, diamond, club
            for ($value = 2; $value <= 14; $value ++) {
                //  2, 3, 4, ... K, A
                $cards [] = array ('type' => $color_id, 'type_arg' => $value, 'nbr' => $num_decks );
            }
        }

        $this->cards->createCards( $cards, 'deck' );

        // Shuffle deck
        $this->cards->shuffle('deck');
        // Deal 13 cards to each players
        $players = self::loadPlayersBasicInfos();
        foreach ( $players as $player_id => $player ) {
            $cards = $this->cards->pickCards(13, 'deck', $player_id);
        } 

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // TODO: setup the initial game situation here
        
        // Set up personal trump suits
        // player_no: the index of player in natural playing order (starting with 1)
        // For now I will randomly choose one of the four for first player, then randomly one of the other two for next
        // the rest will be determined from that.
        // randomly pick a suit for the first player using what I assume(?) is the standard mapping
        self::debug("everything is hunky dory");
        $first_player_suit = bga_rand( 1, 4 );
        // self::dump("The first player has suit: ", $first_player_suit);
        // second players suit will be randomly selected from the opposite partnership - (spades/hearts vs clubs/diamonds)
        $second_player_suit = ($first_player_suit <= 2) ? bga_rand(3, 4) : bga_rand(1, 2);
        $player_suits = array(
            1 => $first_player_suit,
            2 => $second_player_suit,
            3 => self::getPartnerSuit($first_player_suit),
            4 => self::getPartnerSuit($second_player_suit)
        );
        $sql = "UPDATE player SET trump_suit = CASE player_no ";
        $values = array();
        foreach ( $players as $player_id => $player ) {
            $player_number = $player["player_no"];
            $trump_suit = $player_suits[$player_number];
            $values[] = "WHEN ".$player_number." THEN ".$trump_suit;
        }
        $sql .= implode( $values, ' ');
        $sql .= " ELSE 0 END;";
        self::dump("My obviously dodgy sql: ", $sql);
        self::DbQuery( $sql );
        self::reloadPlayersBasicInfos();

        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.

        // Set new 'round'

        // Activate first player to play (if anything else needed than below:)

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score, trump_suit trump_suit FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
        // AB TODO: gather remaining info once that's set-up
        // Cards in player hand
        $result['hand'] = $this->cards->getCardsInLocation( 'hand', $current_player_id );

        // Cards played on the table
        $result['cardsontable'] = $this->cards->getCardsInLocation( 'cardsontable' );

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */
    function getPartnerSuit($player_suit) {
        return array(
            1 => 2,
            2 => 1,
            3 => 4,
            4 => 3
        )[$player_suit];
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in calypso.action.php)
    */
    function playCard($card_id) {
        self::checkAction("playCard");
        $player_id = self::getActivePlayerId();
        $this->cards->moveCard($card_id, 'cardsontable', $player_id);
        // AB: TODO: check rules here
        $currentCard = $this->cards->getCard($card_id);
        // And notify
        self::notifyAllPlayers('playCard', clienttranslate('${player_name} plays ${value_displayed} ${color_displayed}'), array (
                'i18n' => array ('color_displayed','value_displayed' ),'card_id' => $card_id,'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),'value' => $currentCard ['type_arg'],
                'value_displayed' => $this->values_label [$currentCard ['type_arg']],'color' => $currentCard ['type'],
                'color_displayed' => $this->colors [$currentCard ['type']] ['name'] ));
        // Next player
        $this->gamestate->nextState('playCard');
    }
    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    function argGiveCards() {  // Temp
        return array ();
      }
    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    function stNewHand() {
        // AB TODO: I will only move to deck & shuffle at end of each _round_ rather than hand
        // leave now as a reminder, as so much other logic hasn't been implemented yet anyway
        // Take back all cards (from any location => null) to deck
        $this->cards->moveAllCardsInLocation(null, "deck");
        $this->cards->shuffle('deck');
        // Deal 13 cards to each players
        // Create deck, shuffle it and give 13 initial cards
        $players = self::loadPlayersBasicInfos();
        foreach ( $players as $player_id => $player ) {
            $cards = $this->cards->pickCards(13, 'deck', $player_id);
            // Notify player about his cards
            self::notifyPlayer($player_id, 'newHand', '', array ('cards' => $cards ));
        }
        //self::setGameStateValue('alreadyPlayedHearts', 0);
        // AB TODO: more unorganised notes - in the to-come stNewRound function will want to set num. calypsos to 0 etc
        $this->gamestate->nextState("");
    }

    function stNewTrick() {
        // New trick: active the player who wins the last trick, or the left of dealer
        // Reset trick color to 0 (= no color)
        self::setGameStateInitialValue('trickColor', 0);
        $this->gamestate->nextState();
    }

    function stNextPlayer() {
        // Active next player OR end the trick and go to the next trick OR end the hand
        if ($this->cards->countCardInLocation('cardsontable') == 4) {
            // TODO: probably want a utility function (or several!) to handle dealing with tricks!
            // This is the end of the trick
            // Move all cards to "cardswon" of the given player
            $best_value_player_id = self::activeNextPlayer(); // TODO figure out winner of trick
            // TODO: cards will be split and distributed according to logic - will be moveCards, used on filtered subsets
            $this->cards->moveAllCardsInLocation('cardsontable', 'cardswon', null, $best_value_player_id);
            // Notify
            // Note: we use 2 notifications here in order we can pause the display during the first notification
            //  before we move all cards to the winner (during the second)
            $players = self::loadPlayersBasicInfos();
            self::notifyAllPlayers( 'trickWin', clienttranslate('${player_name} wins the trick'), array(
                'player_id' => $best_value_player_id,
                'player_name' => $players[ $best_value_player_id ]['player_name']
            ) );            
            self::notifyAllPlayers( 'giveAllCardsToPlayer','', array(
                'player_id' => $best_value_player_id
            ) );
            if ($this->cards->countCardInLocation('hand') == 0) {
                // End of the hand
                $this->gamestate->nextState("endHand");
            } else {
                // End of the trick
                $this->gamestate->nextState("nextTrick");
            }
        } else {
            // Standard case (not the end of the trick)
            // => just active the next player
            $player_id = self::activeNextPlayer();
            self::giveExtraTime($player_id);
            $this->gamestate->nextState('nextPlayer');
        }
    }

    function stEndHand() {
        $this->gamestate->nextState("nextHand");
    }

    // Temp note to recall:
    // Important: All state actions game or player must end with state transition (or thrown exception).
    // Also make sure its ONLY one state transition, if you accidentally fall though after state transition
    // and do another one it will be a real mess and head scratching for long time. 
    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
