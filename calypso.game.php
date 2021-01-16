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
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Calypso extends Table
{
	// Trick-winning method constants
	const TRUMP_LEAD = 1;
	const FIRST_TRUMP = 2;
	const OVERTRUMP = 3;
    const PLAINSUIT = 4;

	function __construct( )
	{
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        parent::__construct();

        self::initGameStateLabels( array(
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
                         // track method of current trick winner
                         // 1=trump_lead, 2=first_trump, 3=overtrump, 4=plainsuit, see constants above
                         "winningMethod" => 27,

                         // what round are we on, and which hand in the round?
                         "roundNumber" => 31,
                         "handNumber" => 32,
                         // trick number as well, as convenient for stats/progression
                         "trickNumber" => 33,

                         // gameoptions - see gameoptions.inc.php
                         // how many rounds we play to
                         "totalRounds" => 100,
                         // are renounce indicators on or off?
                         "renounceFlags" => 101,
                         // how do we pair players for partnerships?
                         "partnerships" => 102,

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
        // TODO: colours
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // TODO: is this whole process a little unnecessarily complex? or that's just the way it is?? need a think
        // choose some player randomly to be first dealer
        $first_dealer_order_number = bga_rand(1, 4);
        // Set up personal trump suits
        // Randomly choose suit for first player, then randomly one of the other two available for next
        // the rest are then determined
        $first_player_suit = bga_rand(1, 4);
        // second players suit will be randomly selected from the opposite partnership - (spades/hearts vs clubs/diamonds)
        $second_player_suit = ($first_player_suit <= 2) ? bga_rand(3, 4) : bga_rand(1, 2);
        // choose first player randomly

        // use partnership option + player_table_no to decide mapping to these
        // 4 will be dealer, 2 their partner, then 1 and 3 the other partnership
        $player_suits = array(
            1 => $first_player_suit,
            2 => $second_player_suit,
            3 => self::getPartnerSuit($first_player_suit),
            4 => self::getPartnerSuit($second_player_suit)
        );

        // normalise player_table_order to be 1-4:
        $player_table_orders = array_map(
            function($player){return $player['player_table_order'];},
            $players
        );
        // sort by values, keeping key associations intact
        asort($player_table_orders);
        $player_table_orders = array_combine(array_keys($player_table_orders), array(1, 2, 3, 4));

        $first_dealer_id = array_search($first_dealer_order_number, $player_table_orders);
        self::setGameStateInitialValue( 'firstHandDealer', $first_dealer_id );
        self::setGameStateInitialValue( 'currentDealer', $first_dealer_id );

        // set up partnerships
        $player_orders = array(1, 2, 3, 4);
        switch(self::getGameStateValue('partnerships')){
            // 1,3 vs 2,4
            case 1:
                // default, as above
                break;
            // 1,2 vs 3,4
            case 2:
                $player_orders = array(1, 3, 2, 4);
                break;
            // 1,4 vs 2,3
            case 3:
                $player_orders = array(1, 2, 4, 3);
                break;
            // just random
            case 4:
                shuffle($player_orders);
                break;
        }
        // and rotate so that dealer is last
        $dealer = $player_orders[$first_dealer_order_number - 1];
        while($dealer != 4){
            // move everyone round the table
            $player_orders = array_map(
                function($order){return ($order % 4) + 1;},
                $player_orders
            );
            $dealer = $player_orders[$first_dealer_order_number - 1];
        }
        $new_order_index = array_combine(array(1, 2, 3, 4), $player_orders);

        $sql = "INSERT INTO player 
                (player_id, player_color, player_canal, player_name, player_avatar, player_no, trump_suit)
                VALUES ";
        // $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();

        // self::debug("new_order_index is ". implode($new_order_index));
        // self::dump("player_table_orders", $player_table_orders);

        foreach( $players as $player_id => $player ) {
            $order = $new_order_index[$player_table_orders[$player_id]];  // this is being an arse
            // $old_order = $player_table_orders[$player_id];
            $suit = $player_suits[$order];
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] ).
                    "','".addslashes( $player['player_avatar'] )."','".addslashes( $order ).
                    "','".addslashes( $suit )."')";
            // $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        
            // TODO: delete:
            // if($player["player_table_order"] == 1){
            //     self::setGameStateInitialValue( 'firstHandDealer', $player_id );
            //     self::setGameStateInitialValue( 'currentDealer', $player_id );
            // }
        }
        $sql .= implode( ',', $values);
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        
        /************ Start the game initialization *****/

        // pre-game value
        self::setGameStateInitialValue( 'roundNumber', 0 );
        self::setGameStateInitialValue( 'trickNumber', 0 );

        // Create 4 identiical decks of cards
        // see material.inc.php to confirm the labelling
        $num_decks = 4;
        $cards = array();
        foreach ( $this->suits as $suit_id => $suit ) {
            // spade, heart, club, diamond
            for ($rank = 2; $rank <= 14; $rank++) {
                //  2, 3, 4, ... K, A
                $cards[] = array ('type' => $suit_id, 'type_arg' => $rank, 'nbr' => $num_decks );
            }
        }

        $this->cards->createCards( $cards, 'deck' );

        // Init game statistics - see stats.inc.php
        self::initStat('table', 'average_calypsos_per_round', 0);
        self::initStat('table', 'average_points_per_round', 0);

        self::initStat('table', 'proportion_tricks_won_trump_lead', 0);
        self::initStat('table', 'proportion_tricks_won_first_trump', 0);
        self::initStat('table', 'proportion_tricks_won_overtrump', 0);
        self::initStat('table', 'proportion_tricks_won_plainsuit', 0);

        self::initStat('player', 'calypsos_per_round', 0);
        self::initStat('player', 'partnership_calypsos_per_round', 0);
        self::initStat('player', 'calypso_points_per_round', 0);
        self::initStat('player', 'partnership_calypso_points_per_round', 0);
        self::initStat('player', 'incomplete_calypso_cards_per_round', 0);
        self::initStat('player', 'partnership_incomplete_calypso_cards_per_round', 0);
        self::initStat('player', 'trickpile_cards_per_round', 0);
        self::initStat('player', 'partnership_trickpile_cards_per_round', 0);
        self::initStat('player', 'points_per_round', 0);
        self::initStat('player', 'partnership_points_per_round', 0);
        // self::initStat('player', 'total_cards_won', 0);

        self::initStat('player', 'personal_trumps_per_hand', 0);
        self::initStat('player', 'partner_trumps_per_hand', 0);
        self::initStat('player', 'opponent_trumps_per_hand', 0);

        self::initStat('player', 'tricks_won_total_per_hand', 0);
        self::initStat('player', 'tricks_won_trump_lead_per_hand', 0);
        self::initStat('player', 'tricks_won_first_trump_per_hand', 0);
        self::initStat('player', 'tricks_won_overtrump_per_hand', 0);

        self::reloadPlayersBasicInfos();
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

        // TODO: check there is not any naughty secret info here? don't think so
        $current_player_id = self::getCurrentPlayerId();
    
        $sql = "SELECT player_id id, player_score score, trump_suit trump_suit, ".
                "completed_calypsos completed_calypsos FROM player;";
        $result['players'] = self::getCollectionFromDb( $sql );

        foreach($result['players'] as $player_id => $info){
            $result['players'][$player_id]['trick_pile'] = self::getTrickPile($player_id); 
        }

        $result['hand'] = $this->cards->getCardsInLocation( 'hand', $current_player_id );

        $result['cardsontable'] = $this->cards->getCardsInLocation( 'cardsontable' );
        $result['cardsincalypsos'] = $this->cards->getCardsInLocation( 'calypso' );
        $result['trickpile'] = $this->cards->getCardsInLocation( 'trickpile' );

        $result['dealer'] = self::getGameStateValue('currentDealer');

        $result['handnumber'] = self::getGameStateValue('handNumber');
        $result['roundnumber'] = self::getGameStateValue('roundNumber');
        $result['totalrounds'] = self::getGameStateValue('totalRounds');

        if(self::getGameStateValue('renounceFlags') == 1){
            $sql = "SELECT renounce_id id, suit suit, player_id player_id FROM renounce_flags;";
            $player_flags = array();
            $renounce_flag_info = self::getCollectionFromDb( $sql );
            foreach ($renounce_flag_info as $id => $info) {
                $player_flags[] = $info;
            }

            $result['renounce_flags'] = $player_flags;
            // TODO: is this not a kind of bullshit way to do this?
            $result['renounce_flags_on'] = "on";
        } else{
            $result['renounce_flags_on'] = "off";
        }

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
        $total_rounds = self::getGameStateValue('totalRounds');
        $tricks_completed = self::getTrickNumber() - 1;
        return round(100.0*$tricks_completed/(13*4*$total_rounds));
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

    function getPlayerIDFromSuit($suit){
        $sql = "SELECT trump_suit, player_id FROM player WHERE trump_suit=".$suit.";";
        $query_result = self::getCollectionFromDB( $sql, true );
        return $query_result[$suit];
    }

    function getPartnerID($player_id){
        return self::getAdjacentPlayer($player_id, 2);
    }

    function getRoundPlayerNumber($player_id){
        $first_hand_dealer_id = self::getGameStateValue("firstHandDealer");
        if($first_hand_dealer_id == $player_id){
            return 4;
        }
        $position_id = $first_hand_dealer_id;
        $position = 4;
        while($position_id != $player_id){
            $position_id = self::getAdjacentPlayer($position_id);
            $position = ($position % 4) + 1;
        }
        return $position;
    }

    // TODO: I think there is an in-built for this? Can't find it for the mo tho
    function getPlayerName($player_id){
        $players = self::loadPlayersBasicInfos();
        return $players[$player_id]["player_name"];
    }

    // TODO: check here and under that there isn't a secret option number 3?
    function getPlayerPartnership($player_id) {
        // use bridge terminology
        // 3, 4 is clubs, diamonds -> 'minor' suits
        $player_suit = self::getPlayerSuit($player_id);
        if(in_array($player_suit, array(3, 4))){
            return 'minor';
        }
        return 'major';
    }

    function getPartnershipPlayers($partnership){
        if($parnership == 'minor'){
            return array(self::getPlayerIDFromSuit(3), self::getPlayerIDFromSuit(4));
        }
        return array(self::getPlayerIDFromSuit(1), self::getPlayerIDFromSuit(2));
    }

    // next player clockwise pass 1, -1 for anti-clockwise (i.e. previous player)
    // this is where order is canonically set in-game!
    function getAdjacentPlayer($existing_player_id, $direction_index=1){
        $players = self::loadPlayersBasicInfos();
        $existing_player_number = $players[$existing_player_id]["player_no"];
        foreach ( $players as $player_id => $player ) {
            // TODO: there is probably a pithier way to do this modular arithmetic and get a number in range 1-4
            $new_player_number = ($existing_player_number + $direction_index) % 4;
            $new_player_number = ($new_player_number == 0) ? 4 : $new_player_number;
            if($new_player_number == $player["player_no"]){
                return $player_id;
            }
        }
    }

    function getPlayerDirections(){
        $south_id = self::getCurrentPlayerId();
        $west_id = self::getAdjacentPlayer($south_id);
        $north_id = self::getAdjacentPlayer($west_id);
        $east_id = self::getAdjacentPlayer($north_id);
        $directions = array(
            $south_id => "S",
            $west_id => "W",
            $north_id => "N",
            $east_id => "E",
        );
        return $directions;
    }

    // TODO: this changes the dealer, and is only done between hands - reflect that in name
    function updateDealer($direction_index=1, $relevant_dealer='currentDealer') {
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
        $next_first_dealer = self::updateDealer($direction_index=-1, $relevant_dealer='firstHandDealer');
        return $next_first_dealer;
    }

    function setRenounceFlag($player_id, $suit){
        $sql = "INSERT INTO renounce_flags (player_id, suit) VALUES (".$player_id.",".$suit.");";
        self::DbQuery(
            $sql
        );
    }
    function clearRenounceFlags(){
        $sql = "DELETE FROM renounce_flags;";
        self::DbQuery(
            $sql
        );
    }

    function getAllCompletedCalypsos(){
        $self = $this;
        $partnership_order = function($player_1, $player_2) use ($self){
            return $this::getPlayerPartnership($player_1) == "minor"? -1 : 1;
        };
        $sql = "SELECT player_id id, completed_calypsos num_calypsos FROM player";
        $player_calypsos = self::getCollectionFromDB( $sql, true );
        
        uksort($player_calypsos, $partnership_order);
        return $player_calypsos;
    }

    function processCompletedTrick() {
        $best_value_player_id = self::getGameStateValue( 'currentTrickWinner' );

        $winning_method = self::getGameStateValue('winningMethod');
        self::updateWinnerMethodCount($best_value_player_id, $winning_method);

        // announce who won first, then deal with the admin of what happens to the cards
        self::notifyAllPlayers( 'trickWin', clienttranslate('${player_name} wins the trick'), array(
            'player_id' => $best_value_player_id,
            'player_name' => self::getPlayerName($best_value_player_id)
        ) );
        // TODO: if I'm going to say why trick was won, then here is ideal.

        // card gathering logic:
        // $moved_to will track where cards go, so we can send that to js
        // get all cards on table
        $cards_played = $this->cards->getCardsInLocation( 'cardsontable' );
        // move any cards to calypsos there's room for, and get rid of opponents' cards
        $moved_to_first_batch = self::sortWonCards($cards_played, $best_value_player_id);
        // check if any calypsos are completed, and if so process (remove cards, update db)
        $calypsos_completed = self::processCalypsos();
        // now check if remaining cards can be added to calypsos
        $remaining_cards = $this->cards->getCardsInLocation( 'cardsontable' );
        $moved_to_second_batch = self::sortWonCards($remaining_cards, $best_value_player_id);
        $moved_to = array_merge($moved_to_first_batch, $moved_to_second_batch);
        // any cards still on the table should be duplicates of calypso cards
        // note that fact for animation, then give them to the trick winner
        $still_remaining_cards = $this->cards->getCardsInLocation( 'cardsontable' );
        foreach($still_remaining_cards as $card){
            $moved_to[$card["location_arg"]] = array(
                "owner" => 0,
                "winner" => $best_value_player_id,
                "originating_player" => $card["location_arg"],
            );
        }
        $this->cards->moveAllCardsInLocation('cardsontable', 'trickpile', null, $best_value_player_id);

        // now we move cards where they need to go, and get next player
        self::notifyAllPlayers( 'moveCardsToWinner','', array(
            'winner_id' => $best_value_player_id,
        ) );
        self::notifyAllPlayers( 'moveCardsToCalypsos','', array(
            'player_id' => $best_value_player_id,
            'moved_to' => $moved_to,
        ) );
        if(!empty($calypsos_completed)){
            // TODO: don't need to get all, but small difference, and less fiddly, so maybe better than writing separate routine?
            // this is updated with latest figures already
            $total_calypso_counts = self::getAllCompletedCalypsos();
            foreach($calypsos_completed as $player_id){
                self::updateFastestCalypso($player_id);
                self::notifyAllPlayers(
                    'calypsoComplete',
                    clienttranslate('${player_name} has completed a calypso!'),
                    array(
                        'player_id' => $player_id,
                        'player_name' => self::getPlayerName($player_id),
                        'player_suit' => self::getPlayerSuit($player_id),
                        'num_calypsos' => $total_calypso_counts[$player_id],
                    )
                );
            }
        }
        $this->gamestate->changeActivePlayer( $best_value_player_id );
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
        // no current winner, so no associated method
        self::setGameStateValue( 'winningMethod', 0 );
    }

    function setWinner( $best_player_id, $best_card, $method ){
        self::setGameStateValue( 'currentTrickWinner', $best_player_id );
        self::setGameStateValue( 'bestCardSuit', $best_card['type'] );
        self::setGameStateValue( 'bestCardRank', $best_card['type_arg'] );
        self::setGameStateValue( 'winningMethod', $method );
    }

    function updateWinnerMethodCount($trick_winner_id, $winning_method){
        switch($winning_method){
            case self::TRUMP_LEAD:
                $column = "tricks_won_trump_lead";
                break;
            case self::FIRST_TRUMP:
                $column = "tricks_won_first_trump";
                break;
            case self::OVERTRUMP;
                $column = "tricks_won_overtrump";
                break;
            case self::PLAINSUIT;
                $column = "tricks_won_plainsuit";
                break;
            default:
                $column = "tricks_won_error_should_handle_better";
                break;
        }
        $sql = "UPDATE player SET ".$column."=".$column."+1 WHERE player_id=".$trick_winner_id.";";
        self::DbQuery($sql);
    }

    function getPlayerTrickInfo(){
        $players = self::getCollectionFromDb(
            "SELECT player_id, tricks_won_trump_lead, tricks_won_first_trump, tricks_won_overtrump, tricks_won_plainsuit
            FROM player;"
        );
        return $players;
    }

    function getTrickPile($player_id){
        return count($this->cards->getCardsInLocation( 'trickpile', $player_id ));
    }

    function setScore( $player_id, $score_delta ){
        if($score_delta < 0){
            $operator = "-";
            $score_delta = -$score_delta;
        } else{
            $operator = "+";
        }
        self::DbQuery(
            "UPDATE player SET player_score=player_score".$operator.$score_delta." WHERE player_id='".$player_id."'" 
        );
    }

    // TODO: uniformise names
    function setRoundScore( $player_id, $num_calypsos, $calypso_cards, $won_cards ){
        $round_number = self::getGameStateValue('roundNumber');
        $sql_query = "
            INSERT INTO round_scores (player_id, round_number, completed_calypsos, calypso_incomplete, won_tricks)
            VALUES
                (".$player_id.",".$round_number.",".$num_calypsos.",".$calypso_cards.",".$won_cards.");
        ";
        self::DbQuery(
            $sql_query
        );
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

    // cards from me & partner go to calypsos if possible, otherwise they remain
    // cards from opponents go to trickpile
    function sortWonCards($cards_played, $winner_player_id){
        $player_suit = self::getPlayerSuit($winner_player_id);
        $partner_suit = self::getPartnerSuit($player_suit);
        $partner_id = self::getPlayerIDFromSuit($partner_suit);

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
                        "winner" => $winner_player_id,
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
                        "winner" => $winner_player_id,
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
                $moved_to[$card["location_arg"]] = array(
                    "owner" => 0,
                    "winner" => $winner_player_id,
                    "originating_player" => $card["location_arg"],
                );
                $this->cards->moveCard( $card["id"], 'trickpile', $winner_player_id);
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
        // need to allow possibility of both partners completing calypsos in same trick
        $calypsos_completed = array();
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
                $sql = "UPDATE player SET completed_calypsos = completed_calypsos+1 WHERE player_id=".$player_id.";";
                self::DbQuery( $sql );
                $calypsos_completed[] = $player_id;
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
        return $calypsos_completed;
    }

    function getRoundScore($round_number){
        $round_score = self::getCollectionFromDB(
            "SELECT score_id, player_id, completed_calypsos, calypso_incomplete, won_tricks FROM round_scores
            WHERE round_number=".$round_number.";"
        );
        $partnership_scores_raw = self::getCollectionFromDB(
            "SELECT score_id, partnership, score FROM partnership_scores
            WHERE round_number=".$round_number.";"
        );
        $partnership_scores = array();
        foreach($partnership_scores_raw as $score_id => $details){
            $partnership_scores[$details['partnership']] = $details['score'];
        }

        $processed_score = array();

        foreach($round_score as $score_id => $score_info){
            $num_calypsos = $score_info['completed_calypsos'];
            $calypso_cards = $score_info['calypso_incomplete'];
            $won_cards = $score_info['won_tricks'];
            $processed_score[$score_info['player_id']] = array(
                'calypso_count' => $num_calypsos,
                'part_calypso_count' => $calypso_cards,
                'won_cards_count' => $won_cards,
                'partnership_score' => $partnership_scores[self::getPlayerPartnership($score_info['player_id'])],
            ) + self::countsToScores($num_calypsos, $calypso_cards, $won_cards);
        }
        return $processed_score;
    }

    function countsToScores($num_calypsos, $calypso_cards, $won_cards){
        // TODO: not sure about this - fixed and simple but not nice to read, and seems iffy.
        // score calypsos 500, 750, 1000
        $calypsos_to_score = array(
            0,
            500,
            1250,  // 500 + 750
            2250,  // 500 + 750 + 1000
            3250,  // 500 + 750 + 1000 + 1000
        );
        $part_calypso_card_value = 20;
        $won_cards_card_value = 10;

        $calypso_score = $calypsos_to_score[$num_calypsos];
        $part_calypso_score = $part_calypso_card_value * $calypso_cards;
        $won_cards_score = $won_cards_card_value * $won_cards;
        return array(
            'calypso_score' => $calypso_score,
            'part_calypso_score' => $part_calypso_score,
            'won_cards_score' => $won_cards_score,
            'total_score' => $calypso_score + $part_calypso_score + $won_cards_score,
        );
    }

    // here we actually set the scores
    function updateScores(){
        $players = self::getAllCompletedCalypsos();

        $round_number = self::getGameStateValue( 'roundNumber' );

        $partnership_scores = array(
            "minor" => 0,
            "major" => 0,
        );
        foreach ( $players as $player_id => $num_calypsos ) {

            $calypso_cards = count($this->cards->getCardsInLocation( 'calypso', $player_id ));
            $won_cards = count($this->cards->getCardsInLocation( 'trickpile', $player_id ));

            $scores_for_updating[$player_id] = self::countsToScores($num_calypsos, $calypso_cards, $won_cards)['total_score'];

            self::setRoundScore( $player_id, $num_calypsos, $calypso_cards, $won_cards );

            $partnership = self::getPlayerPartnership($player_id);
            $partnership_scores[$partnership] += $scores_for_updating[$player_id];
        }
        foreach ( $players as $player_id => $num_calypsos ) {

            $partnership = self::getPlayerPartnership($player_id);
            self::setScore( $player_id, $partnership_scores[$partnership] );
        }
        foreach ( $partnership_scores as $partnership => $score ){
            $sql_query = "
                INSERT INTO partnership_scores (round_number, partnership, score)
                VALUES
                    (".$round_number.",'".$partnership."',".$score.");
            ";
            self::DbQuery(
                $sql_query
            );
        }
    }

    function displayScores($round_number){
        // give counts and scores different classes so we can style them differently
        // e.g. text-align: left (vs right), different colours(?), weights
        function wrap_class($x, $class_name){
            return '<div class="'.$class_name.'">'.$x.'</div>';
        }
        function count_wrap($x){
            return wrap_class($x, "clp-number-entry");
        }
        function score_wrap($x){
            return wrap_class($x, "clp-score-entry");
        }
        function count_wrap_label($x){
            return wrap_class($x, "clp-number-label");
        }
        function score_wrap_label($x){
            return wrap_class($x, "clp-score-label");
        }

        $scores_for_updating = array();
        $score_table = array();

        $header_names = array( '' );
        $header_suits = array( '' );
        $calypso_counts = array( count_wrap_label(clienttranslate("Completed Calypsos")) );
        $calypso_scores = array( score_wrap_label(clienttranslate("score")) );
        
        $part_calypso_counts = array( count_wrap_label(clienttranslate("Cards in incomplete Calypsos")) );
        $part_calypso_scores = array( score_wrap_label(clienttranslate("score")) );
    
        $won_card_counts = array( count_wrap_label(clienttranslate("Remaining cards won")) );
        $won_card_scores = array( score_wrap_label(clienttranslate("score")) );

        $individual_scores = array( score_wrap_label(clienttranslate("Total individual score")) );
        
        $partnership_scores = array( score_wrap_label(clienttranslate("Total round score")) );
        // TODO: we should get the players in partnership order!
        // for each player:
        $players = self::getRoundScore($round_number);
        foreach ( $players as $player_id => $score_info ) {
            // and display header
            $suit = $this->suits[ self::getPlayerSuit($player_id)]['nametr'];
            $header_names[] = array(
                'str' => '${player_name}',
                'args' => array( 'player_name' => self::getPlayerName($player_id) ),
                'type' => 'header'
            );
            $header_suits[] = array(
                'str' => '${player_suit}',
                'args' => array( 'player_suit' => $suit),
                'type' => 'header'
            );
            
            $calypso_counts[] = count_wrap($score_info['calypso_count']);
            $calypso_scores[] = score_wrap($score_info['calypso_score']);

            $part_calypso_counts[] = count_wrap($score_info['part_calypso_count']);
            $part_calypso_scores[] = score_wrap($score_info['part_calypso_score']);

            $won_card_counts[] = count_wrap($score_info['won_cards_count']);
            $won_card_scores[] = score_wrap($score_info['won_cards_score']);

            $individual_scores[] = score_wrap($score_info['total_score']);

            $partnership_scores[] = score_wrap($score_info['partnership_score']);

            // TODO: this is also a place where partnershit will change
            $scores_for_updating[] = array('player_id' => $player_id, 'total_score' => $score_info['partnership_score']);
        }

        $score_table[] = $header_names;
        $score_table[] = $header_suits;

        $score_table[] = $calypso_counts;
        $score_table[] = $calypso_scores;
        
        $score_table[] = $part_calypso_counts;
        $score_table[] = $part_calypso_scores;
        
        $score_table[] = $won_card_counts;
        $score_table[] = $won_card_scores;

        $score_table[] = $individual_scores;
        // TODO: probably want to display this aspect betterly
        $score_table[] = $partnership_scores;

        $this->notifyAllPlayers(
            "tableWindow",
            '',
            array(
                "id" => 'roundScore',
                "title" => clienttranslate("Scores for the round"),
                "table" => $score_table,
                "closing" => clienttranslate( "Close" )
            )
        );
        $this->notifyAllPlayers(
            'scoreUpdate',
            '',
            array(
                'scores' => $scores_for_updating
            )
        );
    }

    function updateFastestCalypso($player_id){
        $fastest = self::getStat("fastest_calypso", $player_id);
        $tricks_completed = self::getTrickNumberThisRound() - 1;
        // un-set stats return 0. We want it unset, as we want it undefined if players never complete
        if($fastest == 0){
            self::initStat("player", "fastest_calypso", $tricks_completed, $player_id);
        } elseif($tricks_completed < $fastest){
            self::setStat($tricks_completed, "fastest_calypso", $player_id);
        }
        $fastest_overall = self::getStat("fastest_calypso");
        if($fastest_overall == 0){
            self::initStat("table", "fastest_calypso", $tricks_completed);
        } elseif($tricks_completed < $fastest_overall){
            self::setStat($tricks_completed, "fastest_calypso");
        }
    }

    // Hand number OVERALL
    function getHandNumber(){
        $hand_this_round = self::getGameStateValue('handNumber');
        $round_number = self::getGameStateValue('roundNumber');
        return ($round_number - 1)*4 + $hand_this_round;
    }

    // trick number OVERALL, not just by round!
    function getTrickNumber(){
        $hand_number = self::getHandNumber();
        $trick_this_round = self::getGameStateValue('trickNumber');
        return ($hand_number - 1)*13 + $trick_this_round;
    }

    function getTrickNumberThisRound(){
        $hand_number_this_round = self::getGameStateValue('handNumber');
        $trick_this_round = self::getGameStateValue('trickNumber');
        return ($hand_number_this_round - 1)*13 + $trick_this_round;
    }

    function updatePerHandStat($stat_name, $hand_value, $player_id){
        $hand_number = self::getHandNumber();
        $current_stat_val = self::getStat($stat_name, $player_id);
        $new_stat_val = 1.0*(($hand_number - 1)*$current_stat_val + $hand_value)/$hand_number;
        self::setStat($new_stat_val, $stat_name, $player_id);
        return $new_stat_val;
    }

    function updatePerRoundStat($stat_name, $round_value, $player_id){
        $round_number = self::getGameStateValue('roundNumber');
        $current_stat_val = self::getStat($stat_name, $player_id);
        $new_stat_val = 1.0*(($round_number - 1)*$current_stat_val + $round_value)/$round_number;
        self::setStat($new_stat_val, $stat_name, $player_id);
        return $new_stat_val;
    }

    function updateHandDealtStats(){
        $players = self::loadPlayersBasicInfos();
        foreach($players as $player_id => $player){
            $trump_suit = self::getPlayerSuit($player_id);
            $partner_suit = self::getPartnerSuit($trump_suit);
            $player_cards = $this->cards->getCardsInLocation("hand", $player_id);
            $personal_cards = 0;
            $partner_cards = 0;
            $opponent_cards = 0;
            foreach($player_cards as $card){
                if($card["type"] == $trump_suit){
                    $personal_cards++;
                } elseif ($card["type"] == $partner_suit) {
                    $partner_cards++;
                } else{
                    $opponent_cards++;
                }
            }
            self::updatePerHandStat("personal_trumps_per_hand", $personal_cards, $player_id);
            self::updatePerHandStat("partner_trumps_per_hand", $partner_cards, $player_id);
            self::updatePerHandStat("opponent_trumps_per_hand", $opponent_cards, $player_id);   
        }
    }

    function updatePostHandStats(){
        $hands_completed = self::getHandNumber();
        $players = self::getPlayerTrickInfo();
        $trump_lead_won_all = 0;
        $first_trump_won_all = 0;
        $overtrump_won_all = 0;
        $plainsuit_won_all = 0;
        foreach($players as $player_id => $player_trick_info){
            $total_tricks_won = $player_trick_info["tricks_won_trump_lead"] +
                    $player_trick_info["tricks_won_first_trump"] +
                    $player_trick_info["tricks_won_overtrump"] +
                    $player_trick_info["tricks_won_plainsuit"];
            self::setStat(
                $total_tricks_won/$hands_completed,
                "tricks_won_total_per_hand",
                $player_id
            );
            self::setStat(
                $player_trick_info["tricks_won_trump_lead"]/$hands_completed,
                "tricks_won_trump_lead_per_hand",
                $player_id
            );
            self::setStat(
                $player_trick_info["tricks_won_first_trump"]/$hands_completed,
                "tricks_won_first_trump_per_hand",
                $player_id
            );
            self::setStat(
                $player_trick_info["tricks_won_overtrump"]/$hands_completed,
                "tricks_won_overtrump_per_hand",
                $player_id
            );
            $trump_lead_won_all += $player_trick_info["tricks_won_trump_lead"];
            $first_trump_won_all += $player_trick_info["tricks_won_first_trump"];
            $overtrump_won_all += $player_trick_info["tricks_won_overtrump"];
            $plainsuit_won_all += $player_trick_info["tricks_won_plainsuit"];
        }
        self::setStat(1.0*$trump_lead_won_all/(13*$hands_completed), "proportion_tricks_won_trump_lead");
        self::setStat(1.0*$first_trump_won_all/(13*$hands_completed), "proportion_tricks_won_first_trump");
        self::setStat(1.0*$overtrump_won_all/(13*$hands_completed), "proportion_tricks_won_overtrump");
        self::setStat(1.0*$plainsuit_won_all/(13*$hands_completed), "proportion_tricks_won_plainsuit");
    }

    function updateRoundStats(){
        $round_number = self::getGameStateValue('roundNumber');
        $players = self::getRoundScore($round_number);
        // calypso_count' => $num_calypsos,
        //         'part_calypso_count' => $calypso_cards,
        //         'won_cards_count' => $won_cards,
        //         'partnership_score
        //calypso_score' => $calypso_score,
        // 'part_calypso_score' => $part_calypso_score,
        // 'won_cards_score' => $won_cards_score,
        // 'total_score

        $ave_calypso_counter = 0;
        $individual_points_counter = 0;
        foreach ( $players as $player_id => $score_info ) {
            $partner_id = self::getPartnerID($player_id);
            $new_stat_val = self::updatePerRoundStat("calypsos_per_round", $score_info["calypso_count"], $player_id);
            $ave_calypso_counter += $new_stat_val;
            self::updatePerRoundStat(
                "partnership_calypsos_per_round",
                $score_info["calypso_count"] + $players[$partner_id]["calypso_count"],
                $player_id
            );
            self::updatePerRoundStat("calypso_points_per_round", $score_info["calypso_score"], $player_id);
            self::updatePerRoundStat(
                "partnership_calypso_points_per_round",
                $score_info["calypso_score"] + $players[$partner_id]["calypso_score"],
                $player_id
            );
            self::updatePerRoundStat("incomplete_calypso_cards_per_round", $score_info["part_calypso_count"], $player_id);
            self::updatePerRoundStat(
                "partnership_incomplete_calypso_cards_per_round",
                $score_info["part_calypso_count"] + $players[$partner_id]["part_calypso_count"],
                $player_id
            );
            self::updatePerRoundStat("trickpile_cards_per_round", $score_info["won_cards_count"], $player_id);
            self::updatePerRoundStat(
                "partnership_trickpile_cards_per_round",
                $score_info["won_cards_count"] + $players[$partner_id]["won_cards_count"],
                $player_id
            );
            $new_stat_val = self::updatePerRoundStat("points_per_round", $score_info["total_score"], $player_id);
            $individual_points_counter += $new_stat_val;
            $new_stat_val = self::updatePerRoundStat("partnership_points_per_round", $score_info["partnership_score"], $player_id);

            // NB: this assumes each player will be in each position AT MOST ONCE, which should be the case currently
            // would throw an error if that ever changes (as initStat would be called twice)
            $player_number_for_round = self::getRoundPlayerNumber($player_id);
            switch($player_number_for_round){
                case 1:
                    $stat_name = "score_first_leader";
                    break;
                case 2:
                    $stat_name = "score_player_two";
                    break;
                case 3:
                    $stat_name = "score_player_three";
                    break;
                case 4:
                    $stat_name = "score_dealer";
                    break;
            }
            self::initStat("player", $stat_name, $score_info["total_score"], $player_id);
        }
        self::setStat($ave_calypso_counter, "average_calypsos_per_round");
        self::setStat(1.0*$individual_points_counter/4, "average_points_per_round");
    }

    function checkAllCardsExist(){
        // all cushty when I've checked :)
        // TODO: this function checks that all cards are *somewhere*
        // not sure when/if to call this - probably only during dev
        $locations = array(
            'hand',
            'cardsontable',
            'calypso',
            'deck',
            'trickpile',
            'full_calypsos',
        );
        $where_cards = $this->cards->countCardsInLocations();  // gives array location => count
        // some things to check:
        // full calypsos = 13n
        // deck = 52n
        // everything = 208
        self::dump("card_counts", $where_cards);
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
        //self::checkAllCardsExist();
        $player_id = self::getActivePlayerId();
        $currentCard = $this->cards->getCard($card_id);
        if ( !self::validPlay($player_id, $currentCard) ){
            $trick_suit = self::getGameStateValue( 'trickSuit' );
            // if they're trying to revoke, warn, and remind them of the suit they should be playing
            $trick_suit_name = $this->suits[$trick_suit]['nametr'];
            // TODO: really not convinced I've got the translation stuff right here.
            throw new BgaUserException(
                sprintf(self::_("You must follow suit if able to! Please play a %s."), $trick_suit_name)
            );
        }
        $this->cards->moveCard($card_id, 'cardsontable', $player_id);

        $current_trick_suit = self::getGameStateValue( 'trickSuit' );
        // case of the first card of the trick:
        if( $current_trick_suit == 0 ) {
            self::setGameStateValue( 'trickSuit', $currentCard['type'] );
            // set if trumps are lead
            if ( $currentCard['type'] == self::getPlayerSuit($player_id) ) {
                self::setGameStateValue( 'trumpLead', 1 );
                self::setWinner( $player_id, $currentCard, self::TRUMP_LEAD );
            } else {
                // this _should_ be irrelevant, but can't hurt
                self::setGameStateValue( 'trumpLead', 0 );
                self::setWinner( $player_id, $currentCard, self::PLAINSUIT );
            }
        } else {
            // Here we check if the played card is 'better' than what we have so far
            // if it is, then set current player as winner
            // if they follow suit:
            if ( $currentCard['type'] == $current_trick_suit ){
                // if trump lead then this ain't a winner, so do nothing
                // if trump was not lead:
                // check if trump is winning, and if not, check if this card is higher
                // set as winner only if it is
                if ( self::getGameStateValue( 'trumpLead' ) == 0 ){
                    if ( self::getGameStateValue( 'trumpPlayed' ) == 0 ){
                        if ( $currentCard['type_arg'] > self::getGameStateValue( 'bestCardRank' ) ){
                            self::setWinner( $player_id, $currentCard, self::PLAINSUIT );
                        }
                    }
                }
            } else {
                // they don't follow suit
                if(self::getGameStateValue('renounceFlags') == 1){
                    self::setRenounceFlag($player_id, $current_trick_suit);
                    self::notifyAllPlayers(
                        'renounceFlag',
                        '',
                        array(
                            "player_id" => $player_id,
                            "suit" => $current_trick_suit,
                        )
                    );
                }
                
                // if they don't play their trump don't worry - it's a loser
                // if they do...
                if ( $currentCard['type'] == self::getPlayerSuit($player_id) ){
                    // if trump not played yet then great we're winning, and set it
                    if ( self::getGameStateValue( 'trumpPlayed' ) == 0 ){
                        // TODO: here we need to implement the optional check of rank
                        self::setWinner( $player_id, $currentCard, self::FIRST_TRUMP );
                        self::setGameStateValue( 'trumpPlayed', 1 );
                    } else {
                        // if trumpPlayed - check if we're higher, in which case we're winning. Otherwise still a loser
                        if ( $currentCard['type_arg'] > self::getGameStateValue( 'bestCardRank' )){
                            self::setWinner( $player_id, $currentCard, self::OVERTRUMP );
                        }
                    }
                }
            }
        }
        $tmp = self::getStat("fastest_calypso");
        // And notify
        self::notifyAllPlayers('playCard', clienttranslate('${player_name} [${trump}] plays ${rank_displayed} ${suit_displayed}'), array (
                'i18n' => array ('suit_displayed','rank_displayed' ),'card_id' => $card_id,'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),'rank' => $currentCard ['type_arg'],
                'rank_displayed' => $this->ranks_label [$currentCard ['type_arg']],'suit' => $currentCard ['type'],
                'suit_displayed' => $this->suits [$currentCard ['type']] ['name'],
                'trump' => $tmp//$this->suits [self::getPlayerSuit($player_id)] ['name']
             ));
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
        // before we start the round, we are at hand number 0
        self::setGameStateValue( 'handNumber', 0 );
        $old_round_number = self::getGameStateValue( 'roundNumber' );
        $round_number = $old_round_number + 1;
        self::setGameStateValue( 'roundNumber', $round_number );
        $total_rounds = self::getGameStateValue( 'totalRounds');
        self::notifyAllPlayers(
            "update",
            clienttranslate('A new round of hands is starting - round ${round_number} of ${total_rounds}'),
            array("round_number" => $round_number, "total_rounds" => $total_rounds)
        );
        // Take back all cards (from any location => null) to deck, and give it a nice shuffle
        $this->cards->moveAllCardsInLocation(null, "deck");
        $this->cards->shuffle('deck');
        // and make sure no-one has any calypsos counted any more :(
        $sql = "UPDATE player SET completed_calypsos = 0;";
        self::DbQuery( $sql );
        if($round_number != 1){
            $new_dealer = self::getNextFirstDealer();
            self::setGameStateValue( 'firstHandDealer', $new_dealer );
            self::setGameStateValue( 'currentDealer', $new_dealer );
        } else{
            $new_dealer = self::getGameStateValue( 'firstHandDealer' );
        }
        $this->gamestate->nextState("");
    }

    function stNewHand() {
        $old_hand_number = self::getGameStateValue( 'handNumber' );
        $hand_number = $old_hand_number + 1;
        self::setGameStateValue( 'handNumber', $hand_number );
        // always start at trick number 1
        self::setGameStateValue( 'trickNumber', 1 );
        self::notifyAllPlayers(
            "update",
            clienttranslate('A new hand is starting - hand ${hand_number} of 4 in the current round'),
            array("hand_number" => $hand_number)
        );
        // Deal 13 cards to each player and notify them of their hand
        $players = self::loadPlayersBasicInfos();
        $player_ids = array();
        foreach ( $players as $player_id => $player ) {
            $cards = $this->cards->pickCards(13, 'deck', $player_id);
            self::notifyPlayer($player_id, 'newHand', '', array ('cards' => $cards ));

            $player_ids[] = $player_id;
        }
        // only change dealer after first hand, otherwise round setup should've handled it. Relax!
        if($hand_number != 1){
            $new_dealer = self::updateDealer();
            self::setGameStateValue( 'currentDealer', $new_dealer );
        } else{
            $new_dealer = self::getGameStateValue( 'currentDealer' );
        }
        if(self::getGameStateValue('renounceFlags') == 1){
            self::clearRenounceFlags();
            // TODO: dealHand notif should sort out revoke flags on client side
            self::notifyAllPlayers(
                'clearRenounceFlags',
                "",
                array (
                    "players" => $player_ids,
                    "suits" => [1, 2, 3, 4],
                )
            );
        }

        self::updateHandDealtStats();

        self::notifyAllPlayers(
            'dealHand',
            clienttranslate('${dealer_name}, deals a new hand of cards'),
            array (
                'dealer_name' => self::getPlayerName($new_dealer),
                'dealer_id' => $new_dealer,
                'round_number' => self::getGameStateValue( 'roundNumber' ),
                'hand_number' => $hand_number,
                'total_rounds' => self::getGameStateValue( 'totalRounds' ), 
            )
        );
        self::notifyAllPlayers( 'actionRequired', clienttranslate('${player_name} must lead a card to the first trick.'), array(
            'player_name' => self::getActivePlayerName()
        ) );
        $this->gamestate->nextState("");
    }

    function stNewTrick() {
        self::initialiseTrick();
        $this->gamestate->nextState();
    }

    function stNextPlayer() {
        if ($this->cards->countCardInLocation('cardsontable') == 4) {
            
            // count trick number here, so we can get to 13.
            $new_trick_number = self::getGameStateValue('trickNumber') + 1;
            self::setGameStateValue('trickNumber', $new_trick_number);

            // This is the end of the trick
            $this->processCompletedTrick();

            if ($this->cards->countCardInLocation('hand') == 0) {
                // End of the hand
                $this->gamestate->nextState("endHand");
            } else {
                // More tricks to play, let's get to it!
                $this->gamestate->nextState("nextTrick");
            }
        } else {
            // Standard case (not the end of the trick)
            // TODO: instead use getAdjacentPlayer to set next player, so we are free to bugger around with ordering
            // $this->gamestate->changeActivePlayer( $player_id )
            $player_id = self::activeNextPlayer();
            self::giveExtraTime($player_id);
            $this->gamestate->nextState('nextPlayer');
        }
    }

    function stEndHand() {
        // TODO: this notification should go, as that info should be in player boxes.
        $player_calypsos = self::getAllCompletedCalypsos();
        foreach ( $player_calypsos as $player_id => $num_calypsos ) {
            $player_name = self::getPlayerName($player_id);
            self::notifyAllPlayers(
                "update",
                clienttranslate('${player_name} has ${num_calypsos} completed calypso(s)'),
                array(
                    'player_name' => $player_name,
                    'num_calypsos' => $num_calypsos
                )
            );
        }

        self::updatePostHandStats();
        // TODO: at best this should say hand X is over, or just scrap it altogether
        self::notifyAllPlayers(
            "update",
            clienttranslate('Hand over!'),
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
        $round_number = self::getGameStateValue('roundNumber');
        self::updateScores();
        self::displayScores($round_number);

        self::updateRoundStats();
        $num_rounds = self::getGameStateValue( 'totalRounds' );
        
        if($round_number < $num_rounds){
            $this->gamestate->nextState('nextRound');
        } else {
            $this->gamestate->nextState('endGame');
        }
    }

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
