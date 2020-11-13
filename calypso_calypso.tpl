{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Calypso implementation : © Andy Bond <48208438+ADBond@users.noreply.github.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    calypso_calypso.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<!-- Chuck in the business from tutorial for now -->

<div id="playarea">
    <div id="playertables">

        <!-- BEGIN playerhand -->
            <div class="playertable whiteblock playertable_{DIR}">
                <div class="playertablename" style="color:#{PLAYER_COLOR}">
                    {PLAYER_NAME}
                    <div class="personal-trump" id="trump-{PLAYER_ID}"></div>
                </div>
                <div class="playertablecard" id="playertablecard_{PLAYER_ID}">
                </div>
            </div>
        <!-- END playerhand -->
    </div>
    <div id="playercalypso">
        <!-- BEGIN playercalypso -->
            <div class="calypso whiteblock calypso_{DIR}">
                <div class="playertablename" style="color:#{PLAYER_COLOR}">
                    {PLAYER_NAME}
                </div>
                <div class="playercalypso" id="playercalypso_{PLAYER_ID}">
                    <!-- BEGIN calypsocard -->
                        <div class="calypsocard card-{CARD_RANK}" id="calypsocard_{PLAYER_ID}_{CARD_RANK}"></div>
                    <!-- END calypsocard -->
                </div>
            </div>
        <!-- END playercalypso -->
    </div>
</div>

<div id="myhand_wrap" class="whiteblock">
    <h3>{MY_HAND}</h3>
    <div id="myhand">
    </div>
</div>


<script type="text/javascript">

// Javascript HTML templates

var jstpl_cardontable = '<div class="cardontable" id="cardontable_${player_id}" style="background-position:-${x}px -${y}px">\
                        </div>';
var jstpl_cardincalypso = '<div class="cardontable" id="cardincalypso_${player_id}_${value}" style="background-position:-${x}px -${y}px">\
                        </div>';
/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/

</script>  

{OVERALL_GAME_FOOTER}
