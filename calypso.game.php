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
                         // TODO: move dealer shit into db if there's a good reason, otherwise here is fine
                         // who dealt first this round
                         "firstHandDealer" => 10,
                         // who dealt this hand
                         "currentDealer" => 11,
                         // suit lead
                         "trickSuit" => 21,
                         // who's winning trick so far and with what
                         "currentTrickWinner" => 22,
                         "bestCardSuit" => 23,
                         "bestCardRank" => 24,
                         // did the lead player lead their personal trump?
                         "trumpLead" => 25,
                         // has someone trumped in?
                         "trumpPlayed" => 26,

                         // what round are we on, and which hand in the round?
                         // AB TODO: may want to revisit once I've fiddled with gameoptions
                         "roundNumber" => 31,
                         "handNumber" => 32,
                         "totalRounds" => 33,  // TODO: this is a gameoption thing, may not live like this 

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
        $sql .= implode( ',', $values);
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        // self::initialiseTrick();  // this should happen before each trick, so don't worry

        // AB TODO: other gamestate values when I've figured out what they are!

        // pre-game value
        self::setGameStateInitialValue( 'roundNumber', 0 );
        // set this manually right now
        self::setGameStateInitialValue( 'totalRounds', 2 );

        // Create cards
        $num_decks = 4;  // will be 4 - need to change here and in js
        $cards = array ();
        foreach ( $this->suits as $suit_id => $suit ) {
            // spade, heart, club, diamond
            for ($rank = 2; $rank <= 14; $rank ++) {
                //  2, 3, 4, ... K, A
                $cards [] = array ('type' => $suit_id, 'type_arg' => $rank, 'nbr' => $num_decks );
            }
        }

        $this->cards->createCards( $cards, 'deck' );

        // Shuffle deck
        // probably unnecessary, as this should happen as rounds start
        $this->cards->shuffle('deck');
        // Deal 13 cards to each players
        $players = self::loadPlayersBasicInfos();
        foreach ( $players as $player_id => $player ) {
            // don't deal cards, as that happens once we start a new hand!
            //$cards = $this->cards->pickCards(13, 'deck', $player_id);
            
            if($player["player_no"] == 3){
                // they are dealer now, and the first dealer!
                // AB TODO: this feels like a dirty hack. null first, and a check in newRound?
                self::setGameStateInitialValue( 'firstHandDealer', $player_id );
                self::setGameStateInitialValue( 'currentDealer', $player_id );
            }
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
        $sql .= implode( ' ', $values);
        $sql .= " ELSE 0 END;";
        self::DbQuery( $sql );
        self::reloadPlayersBasicInfos();

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
        // Cards played in calypsos
        $result['cardsincalypsos'] = $this->cards->getCardsInLocation( 'calypso' );

        $result['dealer'] = self::getGameStateValue('currentDealer');

        $result['handnumber'] = self::getGameStateValue('handNumber');
        $result['roundnumber'] = self::getGameStateValue('roundNumber');
        $result['totalrounds'] = self::getGameStateValue('totalRounds');

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
        // AB TODO: compute and return the game progression
        // should be simple arithemetic from total number of hands, usually

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

    function getPlayerSuit($player_id) {
        $sql = "SELECT player_id, trump_suit FROM player WHERE player_id=".$player_id.";";
        $query_result = self::getCollectionFromDB( $sql, true );
        return $query_result[$player_id];
    }

    function getPlayerFromSuit($suit){
        $sql = "SELECT trump_suit, player_id FROM player WHERE trump_suit=".$suit.";";
        $query_result = self::getCollectionFromDB( $sql, true );
        return $query_result[$suit];
    }

    // TODO: I think there is an in-built for this? Can't find it for the mo tho
    function getPlayerName($player_id){
        $players = self::loadPlayersBasicInfos();
        return $players[$player_id]["player_name"];
    }

    // next player clockwise pass 1, -1 for anti-clockwise (i.e. previous player)
    function getAdjacentPlayer($existing_player_id, $direction_index=1){
        $players = self::loadPlayersBasicInfos();
        $existing_player_number = $players[$existing_player_id]["player_no"];
        foreach ( $players as $player_id => $player ) {
            // TODO: there is probably a pithier way to do this modular arithmetic and get a number in range 1-4
            $new_player_number = ($existing_player_number + $direction_index) % 4;
            $new_player_number = ($new_player_number == 0) ? 4 : $new_player_number;
            if($new_player_number == $player["player_no"]){
                $new_player = $player_id;
            }
        }
        return $new_player;
    }

    // TODO: this changes the dealer, and is only done between hands - reflect that in name
    function getNextDealer($direction_index=1, $relevant_dealer='currentDealer') {
        $current_dealer = self::getGameStateValue($relevant_dealer);

        // TODO: don't need these notifications long-term
        // self::notifyAllPlayers( 'debug', clienttranslate('${player_name} was the dealer, but they are done'), array(
        //     'player_name' => self::getPlayerName($current_dealer)  // TODO: give them colour, like in playCard
        // ) );
        $new_dealer = self::getAdjacentPlayer($current_dealer, $direction_index);
        // self::notifyAllPlayers( 'debug', clienttranslate('${player_name} will be the next dealer'), array(
        //     'player_name' => self::getPlayerName($new_dealer)
        // ) );
        $first_leader = self::getAdjacentPlayer($new_dealer);
        // self::notifyAllPlayers( 'debug', clienttranslate('${leader} is first leader, ${dealer} is dealer'), array(
        //     'leader' => self::getPlayerName($first_leader),
        //     'dealer' => self::getPlayerName($new_dealer),
        // ) );
        $this->gamestate->changeActivePlayer( $first_leader );
        return $new_dealer;
    }
    // Keep this separate, as might want to rotate the other way? if not just alias
    function getNextFirstDealer() {
        // hop back by two so that we roll forward one on new hand
        // TODO: this feels horrible - is there a nice way that won't be overkill?
        $next_first_dealer = self::getNextDealer($direction_index=-1, $relevant_dealer='firstHandDealer');
        return $next_first_dealer;
    }

    function processCompletedTrick() {

        $cards_played = $this->cards->getCardsInLocation( 'cardsontable' );

        $best_value_player_id = self::getGameStateValue( 'currentTrickWinner' );

        // card gathering logic:
        // get all cards on table (above)
        $moved_to_first_batch = self::sortWonCards($cards_played, $best_value_player_id);
        // -> check if any calypsos are completed, and if so process (remove and update db)
        self::processCalypsos();
        // now check if remaining cards can be added to calypsos
        $remaining_cards = $this->cards->getCardsInLocation( 'cardsontable' );
        $moved_to_second_batch = self::sortWonCards($remaining_cards, $best_value_player_id);
        $moved_to = array_merge($moved_to_first_batch, $moved_to_second_batch);
        // any cards still on the table should be duplicates of calypso cards
        // note that fact for animation, then give them to the trick winner
        $still_remaining_cards = $this->cards->getCardsInLocation( 'cardsontable' );
        foreach($still_remaining_cards as $card){
            $moved_to[$card["location_arg"]] = array("owner" => 0, "originating_player" => $card["location_arg"],);
        }
        $this->cards->moveAllCardsInLocation('cardsontable', 'cardswon', null, $best_value_player_id);

        // Notify
        // Note: we use 2 notifications here in order we can pause the display during the first notification
        //  before we move all cards to the winner (during the second)
        $players = self::loadPlayersBasicInfos();
        self::notifyAllPlayers( 'trickWin', clienttranslate('${player_name} wins the trick'), array(
            'player_id' => $best_value_player_id,
            'player_name' => self::getPlayerName($best_value_player_id)
        ) );
        $this->gamestate->changeActivePlayer( $best_value_player_id );
        self::notifyAllPlayers( 'moveCardsToCalypsos','', array(
            'player_id' => $best_value_player_id,
            'moved_to' => $moved_to,
        ) );
    }

    function debugMessage( $message, $array=array() ){
        self::notifyAllPlayers( 'message', $message, $array );
    }

    function initialiseTrick(){ 
        // Set current trick suit to zero (= no trick suit)
        self::setGameStateInitialValue( 'trickSuit', 0 );
        // No current winner yet
        self::setGameStateInitialValue( 'currentTrickWinner', 0 );
        // Trump has not been lead yet
        self::setGameStateInitialValue( 'trumpLead', 0 );
        // and no-one has trumped in yet
        self::setGameStateInitialValue( 'trumpPlayed', 0 );
        // no winning card currently
        self::setGameStateInitialValue( 'bestCardSuit', 0 );
        self::setGameStateInitialValue( 'bestCardRank', 0 );
    }

    function setWinner( $best_player_id, $best_card ){
        self::setGameStateValue( 'currentTrickWinner', $best_player_id );
        self::setGameStateValue( 'bestCardSuit', $best_card['type'] );
        self::setGameStateValue( 'bestCardRank', $best_card['type_arg'] );
    }

    function validPlay( $player_id, $card ){
        // check that player leads or follows suit OR has no cards of lead suit
        // shortcut for debugging:
        //return true;
        global $trick_suit;  // this makes me think that there is maybe a better way to do this??
        $trick_suit = self::getGameStateValue( 'trickSuit' );
        if( $trick_suit == 0){  // i.e. first card of trick
            return true;
        }
        if( $card['type'] == $trick_suit ){
            return true;
        }
        $hand = $this->cards->getCardsInLocation( 'hand', $player_id );
        
        $suit_cards = array_filter( $hand, function($hand_card){
            global $trick_suit;
            return $hand_card['type'] == $trick_suit;
        });
        if( empty($suit_cards) ){
            return true;
        }
        return false;
    }

    function getCurrentRanks($player_id){
        $calypso_so_far = $this->cards->getCardsInLocation( 'calypso', $player_id);
        return array_map(
            function($calypso_card){return $calypso_card['type_arg'];},
            $calypso_so_far
        );
    }

    function sortWonCards($cards_played, $winner_player_id){
        $player_suit = self::getPlayerSuit($winner_player_id);
        $partner_suit = self::getPartnerSuit($player_suit);
        $partner_id = self::getPlayerFromSuit($partner_suit);

        // array keeps track of where cards went, so we can pass to js for animation
        $moved_to = array();

        foreach ($cards_played as $card){
            // take cards from our (me + part) suits that aren't already in calypsos in progress, and add them
            // if they are already there, wait - that will come later
            if ($card['type'] == $player_suit){
                $player_ranks_so_far = self::getCurrentRanks($winner_player_id);
                if (!in_array($card['type_arg'], $player_ranks_so_far)){
                    $moved_to[$card["location_arg"]] = array(
                        "originating_player" => $card["location_arg"],
                        "owner" => $winner_player_id,
                        "suit" => $card["type"],
                        "rank" => $card["type_arg"],
                        "card_id" => $card["id"],
                    );
                    $this->cards->moveCard( $card["id"], 'calypso', $winner_player_id);
                }
            } elseif ($card['type'] == $partner_suit){
                $partner_ranks_so_far = self::getCurrentRanks($partner_id);
                if (!in_array($card['type_arg'], $partner_ranks_so_far)){
                    $moved_to[$card["location_arg"]] = array(
                        "originating_player" => $card["location_arg"],
                        "owner" => $partner_id,
                        "suit" => $card["type"],
                        "rank" => $card["type_arg"],
                        "card_id" => $card["id"],
                    );
                    $this->cards->moveCard( $card["id"], 'calypso', $partner_id);
                }
            }
            else {
                // give opponents cards to player who won trick - partners can have separate piles
                $moved_to[$card["location_arg"]] = array("owner" => 0, "originating_player" => $card["location_arg"],);
                $this->cards->moveCard( $card["id"], 'woncards', $winner_player_id);
            }
        }
        return $moved_to;
    }

    function cardInCalypso($card, $player_id){
        $calypso_so_far = $this->cards->getCardsInLocation( 'calypso', $player_id);
        $ranks_so_far = array_map(
            function($calypso_card){return $calypso_card['type_arg'];},
            $calypso_so_far
        );
        return in_array($card['type_arg'], $ranks_so_far);
    }

    function processCalypsos(){

        $players = self::loadPlayersBasicInfos();
        foreach ( $players as $player_id => $player ) {
            $calypso_so_far = $this->cards->getCardsInLocation( 'calypso', $player_id);
            $ranks_so_far = array_map(
                function($calypso_card){return $calypso_card['type_arg'];},
                $calypso_so_far
            );
            $calypso_string = implode( ",", $ranks_so_far );
            // self::debugMessage( clienttranslate('${player_name} has ${calypso_string}'), array(
            //     'player_id' => $player_id,
            //     'player_name' => $players[ $player_id ]['player_name'],
            //     'calypso_string' => $calypso_string,
            // ) );
            if(sizeof($calypso_so_far) == 13){  // AB TODO: is this robust enough?
                $this->cards->moveAllCardsInLocation( 'calypso', 'full_calypsos', $player_id, $player_id );
                // AB TODO: updated db when I've updated the model to allow the field
                // AB TODO: notification and animation when this happens
            }
            $ranks_so_far = array_map(
                function($calypso_card){return $calypso_card['type_arg'];},
                $calypso_so_far
            );
            $calypso_string = implode( ",", $ranks_so_far );
            // self::debugMessage( clienttranslate('${player_name} has ${calypso_string}'), array(
            //     'player_id' => $player_id,
            //     'player_name' => self::getPlayerName($player_id),
            //     'calypso_string' => $calypso_string,
            // ) );
        }
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
        $currentCard = $this->cards->getCard($card_id);
        if ( !self::validPlay($player_id, $currentCard) ){
            $trick_suit = self::getGameStateValue( 'trickSuit' );

            throw new BgaUserException(
                self::_("You must follow suit if able to! Please play a ").$this->suits[$trick_suit]['nametr']."."
            );
        }
        $this->cards->moveCard($card_id, 'cardsontable', $player_id);
        
        $currenttrickSuit = self::getGameStateValue( 'trickSuit' );
        // case of the first card of the trick:
        if( $currenttrickSuit == 0 ) {
            self::setGameStateValue( 'trickSuit', $currentCard['type'] );
            // set if trumps are lead
            if ( $currentCard['type'] == self::getPlayerSuit($player_id) ) {
                self::setGameStateValue( 'trumpLead', 1 );
            } else {  // TODO: this _should_ be irrelevant, but can't hurt (much)
                self::setGameStateValue( 'trumpLead', 0 );
            }
            self::setWinner( $player_id, $currentCard );
        } else {
            // Here we check if the played card is 'better' than what we have so far
            // if it is, then set current player as winner
            // if they follow suit:
            if ( $currentCard['type'] == self::getGameStateValue( 'trickSuit' ) ){
                // if trump lead then this ain't a winner, so do nothing
                // if trump was not lead:
                // check if trump is winning, and if not, check if this card is higher
                // set as winner only if it is
                if ( self::getGameStateValue( 'trumpLead' ) == 0 ){
                    if ( self::getGameStateValue( 'trumpPlayed' ) == 0 ){
                        if ( $currentCard['type_arg'] > self::getGameStateValue( 'bestCardRank' ) ){
                            self::setWinner( $player_id, $currentCard );
                        }
                    }
                }
            } else { // they don't follow suit
                // if they don't play their trump don't worry - it's a loser
                // if they do
                if ( $currentCard['type'] == self::getPlayerSuit($player_id) ){
                    // if trump not played yet then great we're winning, and set it
                    if ( self::getGameStateValue( 'trumpPlayed' ) == 0 ){
                        self::setWinner( $player_id, $currentCard );
                        self::setGameStateValue( 'trumpPlayed', 1 );
                    } else { // if trumpPlayed - check if we're higher
                        if ( $currentCard['type_arg'] > self::getGameStateValue( 'bestCardRank' )){
                            self::setWinner( $player_id, $currentCard );
                        }
                    }
                }
            }
        }
        // And notify
        self::notifyAllPlayers('playCard', clienttranslate('${player_name} [${trump}] plays ${rank_displayed} ${suit_displayed}'), array (
                'i18n' => array ('suit_displayed','rank_displayed' ),'card_id' => $card_id,'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),'rank' => $currentCard ['type_arg'],
                'rank_displayed' => $this->ranks_label [$currentCard ['type_arg']],'suit' => $currentCard ['type'],
                'suit_displayed' => $this->suits [$currentCard ['type']] ['name'],
                'trump' => $this->suits [self::getPlayerSuit($player_id)] ['name']
             ));
        // self::notifyAllPlayers('Debug', clienttranslate('${player_name} [${trump}] plays ${rank_displayed} ${suit_displayed}'), array (
        // 'i18n' => array ('suit_displayed','rank_displayed' ),'card_id' => $card_id,'player_id' => $player_id,
        // 'player_name' => self::getActivePlayerName(),'rank' => $currentCard ['type_arg'],
        // 'rank_displayed' => $this->ranks_label [$currentCard ['type_arg']],'suit' => $currentCard ['type'],
        // 'suit_displayed' => $this->suits [$currentCard ['type']] ['name'],
        // 'trump' => $this->suits [self::getPlayerSuit($player_id)] ['name']
        // ));
        // Next player
        $this->gamestate->nextState('playCard');
    }


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
    function dummy(){
        throw new BgaUserException( self::_("Don't see this!") );
    }

    function stNewRound() {
        #throw new BgaUserException( self::_("New round!") );
        // before we start the round, we are at hand number 0
        self::setGameStateValue( 'handNumber', 0 );
        $old_round_number = self::getGameStateValue( 'roundNumber' );
        $round_number = $old_round_number + 1;
        self::setGameStateValue( 'roundNumber', $round_number );
        self::notifyAllPlayers(
            "update",
            clienttranslate("A new round of hands is starting - round ${round_number}"),  // TODO: number of rounds total
            array("round_number" => $round_number)
        );
        // Take back all cards (from any location => null) to deck, and give it a nice shuffle
        $this->cards->moveAllCardsInLocation(null, "deck");
        $this->cards->shuffle('deck');
        // AB TODO: num calypsos to zero
        $new_dealer = self::getNextFirstDealer();
        self::setGameStateValue( 'firstHandDealer', $new_dealer );
        self::setGameStateValue( 'currentDealer', $new_dealer );
        $this->gamestate->nextState("");
    }

    function stNewHand() {
        $old_hand_number = self::getGameStateValue( 'handNumber' );
        $hand_number = $old_hand_number + 1;
        self::setGameStateValue( 'handNumber', $hand_number );
        self::notifyAllPlayers(
            "update",
            clienttranslate("A new hand is starting - hand ${hand_number}/4 in the current round"),
            array("hand_number" => $hand_number)
        );
        // Deal 13 cards to each player and notify them of their hand
        $players = self::loadPlayersBasicInfos();
        foreach ( $players as $player_id => $player ) {
            $cards = $this->cards->pickCards(13, 'deck', $player_id);
            self::notifyPlayer($player_id, 'newHand', '', array ('cards' => $cards ));
        }
        // only change dealer after first hand, otherwise round setup should've handled it. Relax!
        if($hand_number != 1){
            $new_dealer = self::getNextDealer();
            self::setGameStateValue( 'currentDealer', $new_dealer );
        } else{
            $new_dealer = self::getGameStateValue( 'currentDealer' );
        }
        self::notifyAllPlayers(  // TODO: id is for debugging, delete!
            'dealHand',
            clienttranslate('${dealer_name}, (${dealer_id}) deals a new hand of cards'),
            array (
                'dealer_name' => self::getPlayerName($new_dealer),
                'dealer_id' => $new_dealer,
                'round_number' => self::getGameStateValue( 'roundNumber' ),
                'hand_number' => $hand_number,
                'total_rounds' => self::getGameStateValue( 'totalRounds' ), 
            )
        );
        // TODO: specialist notification here!
        self::notifyAllPlayers( 'actionRequired', clienttranslate('${player_name} must lead a card to the first trick.'), array(
            'player_name' => self::getActivePlayerName()
        ) );
        $this->gamestate->nextState("");
    }

    function stNewTrick() {
        // New trick: active the player who wins the last trick, or the left of dealer
        self::initialiseTrick();
        $this->gamestate->nextState();
    }

    function stNextPlayer() {
        // Active next player OR end the trick and go to the next trick OR end the hand
        if ($this->cards->countCardInLocation('cardsontable') == 4) {
            // This is the end of the trick
            $this->processCompletedTrick();
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
        // TODO: this notification should update how many completed calypsos each player has
        self::notifyAllPlayers(
            "update",
            clienttranslate("Hand over!"),  // TODO: number of hand
            array()
        );
        $num_hands = 4;
        
        if(self::getGameStateValue( 'handNumber' ) == $num_hands){
            $this->gamestate->nextState('endRound');
        } else {
            $this->gamestate->nextState("nextHand");
        }
    }

    function stEndRound() {
        // TODO: this noticiation should give scores for the round
        self::notifyAllPlayers(
            "update",
            clienttranslate("Round over!"),  // TODO: number of round
            array()
        );
        // TODO: score it
        $num_rounds = self::getGameStateValue( 'totalRounds' );
        
        if(self::getGameStateValue( 'roundNumber' ) < $num_rounds){
            $this->gamestate->nextState('nextRound');
        } else {
            $this->gamestate->nextState('endGame');
        }
    }

    // Temp note to recall:
    // Important: All state actions game or player must end with state transition (or thrown exception).
    // Also make sure its ONLY one state transition, if you accidentally fall though after state transition
    // and do another one it will be a real mess and head scratching for long time. 
    /*

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
