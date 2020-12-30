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
            <div class="clp-playername clp-playername-{DIR}" style="color:#{PLAYER_COLOUR}">
                {PLAYER_NAME} - a very long username
            </div>
            <div class="clp-renounce-indicators" id="renounce_{PLAYER_ID}">
                <!-- BEGIN renounceindicator -->
                <div class="clp-renounce-indicator clp-inactive-renounce clp-renounce-{CARD_SUIT}" id="clp-renounce-{PLAYER_ID}-{CARD_SUIT}">
                </div>
                <!-- END renounceindicator -->
            </div>
            <div class="dealer-indicator-area" id="dealer-{PLAYER_ID}"></div>
            <div class="clp-player-all-captured-cards" id="clp-player-all-captured-cards-{PLAYER_ID}">
                <div class="clp-calypsoholder" id="clp-calypsoholder-{PLAYER_ID}">
                <!-- BEGIN calypsocard -->
                    <div class="clp-calypsocard-space clp-captured-card clp-calypsocard-{CARD_RANK} clp-card-space-{SUIT}"
                            id="calypsocard_{PLAYER_ID}_{CARD_RANK}">
                    </div>
                <!-- END calypsocard -->
                </div>
                <div class="clp-trickpile clp-captured-card clp-trickpile-empty" id="clp-trickpile-{PLAYER_ID}"></div>
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

var jstpl_cardontable = '<div class="clp-card-on-table clp-face-up-card" id="clp-card-on-table-${player_id}" style="background-position:-${x}px -${y}px">\
                        </div>';
var jstpl_dealerindicator = '<div id="dealerbutton" class="dealerbutton"></div>';
var jstpl_calypsocard = '<div class="clp-face-up-card clp-captured-card clp-calypsocard-${rank} clp-card-space-${suit}\
                             id="calypsocard_${player_id}_${rank}">\
                        </div>'

</script>

{OVERALL_GAME_FOOTER}
