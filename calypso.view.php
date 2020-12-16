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
        
        // TODO: width should probably be set in JS as we can scale to card size there
        // width of card is 72
        $card_width = 72;
        // how much offset before displaying next card
        $each_card_offset = 25;
        $overall_width = 13*$each_card_offset + ($card_width - $each_card_offset);

        // TODO: better name
        // sprite position
        $card_height = 96;

        $this->page->begin_block( $template, "revokeindicator" ); // Nested block must be declared first
        $this->page->begin_block( $template, "calypsocard" ); // Nested block must be declared first
        $this->page->begin_block( $template, "playerhand" );
        $this->page->begin_block( $template, "playercalypso" );
        foreach ( $players as $player_id => $info ) {
            $this->page->reset_subblocks( "revokeindicator" );
            $this->page->reset_subblocks( "calypsocard" );
            $trump_suit = $this->game->getPlayerSuit($player_id);
            for ($suit = 1; $suit <= 4; $suit ++) {
                $this->page->insert_block(
                    "revokeindicator",
                    array(
                        "PLAYER_ID" => $player_id,
                        "CARD_SUIT" => $suit,
                    )
                );
            }
            for ($rank = 2; $rank <= 14; $rank ++) {
                //  2, 3, 4, ... K, A
                $offset_value = ($rank - 2) * $each_card_offset;
                $this->page->insert_block(
                    "calypsocard",
                    array(
                        "PLAYER_ID" => $player_id,
                        "OFFSET" => $offset_value,
                        "CARD_RANK" => $rank,
                        "Y_OFFSET" => $card_height * ($trump_suit - 1),
                    )
                );
            }

            // TODO: not sure if these should be part of the same player block or not...
            $this->page->insert_block(
                "playerhand",
                array (
                    "PLAYER_ID" => $player_id,
                    "PLAYER_NAME" => $players [$player_id] ['player_name'],
                    "PLAYER_COLOR" => $players [$player_id] ['player_color'],
                    "DIR" => $directions[$player_id],
                )
            );
            $this->page->insert_block(
                "playercalypso",
                array (
                    "PLAYER_ID" => $player_id, "DIR" => $directions[$player_id],
                    #"WIDTH" => $overall_width
                )
            );
        }
        // this will make our My Hand text translatable
        $this->tpl['MY_HAND'] = self::_("My hand");

        /*********** Do not change anything below this line  ************/
  	}
  }
  

