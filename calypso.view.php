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
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/

        // Stuff straight from Hearts tutorial --- for the cards played to tricks
        $template = self::getGameName() . "_" . self::getGameName();
        
        $directions = array( 'S', 'W', 'N', 'E' );
        
        // this will inflate our player block with actual players data
        
        $this->page->begin_block( $template, "calypsocard" ); // Nested block must be declared first
        $this->page->begin_block( $template, "player" );
        foreach ( $players as $player_id => $info ) {
            $this->page->reset_subblocks( "calyspocard" );
            $trump_suit = $this->game->getPlayerSuit($player_id);
            for ($value = 2; $value <= 14; $value ++) {
                //  2, 3, 4, ... K, A
                $this->page->insert_block(
                    "calypsocard",
                    array(
                        "PLAYER_ID" => $player_id,
                        #"PLAYER_NAME" => $players [$player_id] ['player_name'],
                        #"PLAYER_COLOR" => $players [$player_id] ['player_color'],
                        #"DIR" => $dir
                        "CARD_RANK" => $value
                    )
                );
            }
                // TODO: this e.g. needs to ba adapted
                // need to remember what the shift is doing though eh?
                // and adapt suitably, but should be quids in i reckon
                // // Important: nested block must be reset here, otherwise the second player miniboard will
                // //  have 8 card_place, the third will have 12 card_place, and so one...
                // $this->page->reset_subblocks( 'card_place' ); 

                // for( $i=1; $i<=4; $i++ ) {
                // $this->page->insert_block( "card_place", array( 
                //         'PLAYER_ID' => $player_id,
                //         'PLACE_ID' => $i
                //         )
                //     );
                // }
            $dir = array_shift($directions);
            $this->page->insert_block("player", array ("PLAYER_ID" => $player_id,
                    "PLAYER_NAME" => $players [$player_id] ['player_name'],
                    "PLAYER_COLOR" => $players [$player_id] ['player_color'],
                    "DIR" => $dir ));
        }
        // this will make our My Hand text translatable
        $this->tpl['MY_HAND'] = self::_("My hand");

        /*

        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */
        
        /*
        
        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock --> 
        //          ... my HTML code ...
        //      <!-- END myblock --> 
        

        $this->page->begin_block( "calypso_calypso", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array( 
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }
        
        */



        /*********** Do not change anything below this line  ************/
  	}
  }
  

