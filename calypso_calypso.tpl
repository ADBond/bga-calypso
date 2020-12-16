{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Calypso implementation : © Andy Bond <48208438+ADBond@users.noreply.github.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-->

<div id="clp-gameinfo"></div>

<div id="playarea">
    <div id="tablearea">
        <!-- BEGIN playerhand -->
            <div class="playertable whiteblock playertable_{DIR}">
                <div class="playertablename" id="area-name-{PLAYER_ID}" style="color:#{PLAYER_COLOR}">
                    <div id="area-name-{PLAYER_ID}">{PLAYER_NAME}</div><div id="area-dealer-{PLAYER_ID}"></div>
                    <div class="personal-trump" id="trump-{PLAYER_ID}"></div>
                    <div class="dealer-indicator-area" id="dealer-{PLAYER_ID}"></div>
                </div>
                <div class="playertablecard" id="playertablecard_{PLAYER_ID}">
                </div>
            </div>
        <!-- END playerhand -->
        <!-- BEGIN playercalypso -->
            <div class="calypso calypso_{DIR}">
                <!-- 
                TODO here: don't need player name here - have that outside so it's not rotated
                need to implement revoke and wontricks stuff
                -->
                <div class="playertablename" style="color:#{PLAYER_COLOR}">
                    {PLAYER_NAME}
                </div>
                <div class="revoke-indicators" id="revoke_{PLAYER_ID}">
                    <!-- BEGIN revokeindicator -->
                    <div class="revoke-indicator revoke-{CARD_SUIT}" id="revoke_{CARD_SUIT}_{PLAYER_ID}">
                    </div>
                    <!-- END revokeindicator -->
                </div>
                <div class="playercalypso"
                     id="playercalypso_{PLAYER_ID}">
                    <!-- BEGIN calypsocard -->
                        <div class="calypsocard captured-card calypsocard-{CARD_RANK} card-space-{SUIT}"
                             id="calypsocard_{PLAYER_ID}_{CARD_RANK}">
                        </div>
                    <!-- END calypsocard -->
                    <div class="wontricks captured-card" id="wontricks_{PLAYER_ID}"></div>
                </div>
            </div>
        <!-- END playercalypso -->
    </div>
</div>

<div id="myhand_wrap" class="whiteblock"> <!-- TODO: whiteblock -> custom class -->
    <h3>{MY_HAND}</h3>
    <div id="myhand">
    </div>
</div>


<script type="text/javascript">

// Javascript HTML templates

var jstpl_cardontable = '<div class="cardontable" id="cardontable_${player_id}" style="background-position:-${x}px -${y}px">\
                        </div>';
var jstpl_dealerindicator = '<div id="dealerbutton"></div>';

</script>

{OVERALL_GAME_FOOTER}
