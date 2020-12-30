{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Calypso implementation : © Andy Bond <48208438+ADBond@users.noreply.github.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-->

<div id="clp-game-info" class="clp-game-info"></div>

<div id="clp-table-area">
    <!-- BEGIN playerhand -->
        <div class="clp-player-card-play-area clp-player-card-play-area-{DIR}">
            <div class="clp-player-card-play-area-card" id="clp-player-card-play-area-card-{PLAYER_ID}">
            </div>
        </div>
    <!-- END playerhand -->
    <!-- BEGIN playercalypso -->
        <div class="calypso calypso_{DIR}">
            <div class="clp-playername clp-playername_{DIR}" style="color:#{PLAYER_COLOUR}">
                {PLAYER_NAME} - a very long username
            </div>
            <div class="renounce-indicators" id="renounce_{PLAYER_ID}">
                <!-- BEGIN renounceindicator -->
                <div class="renounce-indicator inactive-renounce renounce-{CARD_SUIT}" id="renounce_{PLAYER_ID}_{CARD_SUIT}">
                </div>
                <!-- END renounceindicator -->
            </div>
            <div class="dealer-indicator-area" id="dealer-{PLAYER_ID}"></div>
            <div class="playercalypso" id="playercalypso_{PLAYER_ID}">
                <div class="calypsoholder" id="calypsoholder_{PLAYER_ID}">
                <!-- BEGIN calypsocard -->
                    <div class="calypsocard captured-card calypsocard-{CARD_RANK} card-space-{SUIT}"
                            id="calypsocard_{PLAYER_ID}_{CARD_RANK}">
                    </div>
                <!-- END calypsocard -->
                </div>
                <div class="trickpile captured-card trick-pile-empty" id="trickpile_{PLAYER_ID}"></div>
            </div>
        </div>
    <!-- END playercalypso -->
</div>


<div id="clp-myhand-wrap" class="whiteblock"> <!-- TODO: whiteblock -> custom class -->
    <h3>{MY_HAND}</h3>
    <div id="clp-myhand">
    </div>
</div>


<script type="text/javascript">

// Javascript HTML templates

var jstpl_cardontable = '<div class="clp-card-on-table" id="clp-card-on-table-${player_id}" style="background-position:-${x}px -${y}px">\
                        </div>';
var jstpl_dealerindicator = '<div id="dealerbutton" class="dealerbutton"></div>';
var jstpl_calypsocard = '<div class="calypsocard captured-card calypsocard-${rank} card-space-${suit}\
                             id="calypsocard_${player_id}_${rank}">\
                        </div>'

</script>

{OVERALL_GAME_FOOTER}
