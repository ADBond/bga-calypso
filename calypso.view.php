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
 * calypso.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in calypso_calypso.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_calypso_calypso extends game_view
  {
    function getGameName() {
        return "calypso";
    }    
  	function build_page( $viewArgs )
  	{
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        $template = self::getGameName() . "_" . self::getGameName();

        $directions = $this->game->getPlayerDirections();

        $this->page->begin_block( $template, "renounceindicator" ); // Nested block must be declared first
        $this->page->begin_block( $template, "playerhand" );
        $this->page->begin_block( $template, "playercalypso" );
        foreach ( $players as $player_id => $info ) {
            $this->page->reset_subblocks( "renounceindicator" );
            $this->page->reset_subblocks( "calypsocard" );
            $trump_suit = $this->game->getPlayerSuit($player_id);
            if($this->game->getGameStateValue('renounceFlags') == 1){
                for ($suit = 1; $suit <= 4; $suit ++) {
                    $this->page->insert_block(
                        "renounceindicator",
                        array(
                            "PLAYER_ID" => $player_id,
                            "CARD_SUIT" => $suit,
                        )
                    );
                }
            }

            $this->page->insert_block(
                "playerhand",
                array (
                    "PLAYER_ID" => $player_id,
                    "DIR" => $directions[$player_id],
                )
            );
            $this->page->insert_block(
                "playercalypso",
                array (
                    "PLAYER_ID" => $player_id, "DIR" => $directions[$player_id],
                    "PLAYER_COLOUR" => $players[$player_id]["player_color"],
                    "PLAYER_NAME" => $players[$player_id]["player_name"],
                )
            );
        }

        $total_rounds = $this->game->getGameStateValue("totalRounds");
        $this->page->begin_block( $template, "roundscoreaccessrow" );
        for ($round = 1; $round <= $total_rounds; $round++){
            $this->page->insert_block(
                "roundscoreaccessrow",
                array (
                    "ROUND_NUMBER" => $round,
                )
            );
        }

        // this will make our My Hand text translatable
        $this->tpl['MY_HAND'] = self::_("My hand");

        /*********** Do not change anything below this line  ************/
  	}
  }
  

