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

<div id="clp-public-area">
    <div id="clp-table-area">
        <!-- BEGIN playerhand -->
        <div class="clp-player-card-play-area clp-player-card-play-area-{DIR}">
            <div class="clp-player-card-play-area-card" id="clp-player-card-play-area-card-{PLAYER_ID}"></div>
        </div>
        <!-- END playerhand -->
        <!-- BEGIN playercalypso -->
        <div class="clp-player-personal-area clp-player-personal-area-{DIR}" id="clp-player-personal-area-{DIR}">
            <div class="clp-playername clp-playername-{DIR}" style="color:#{PLAYER_COLOUR}">
                {PLAYER_NAME} - a very long username
            </div>
            <div class="clp-renounce-indicators">
                <!-- BEGIN renounceindicator -->
                <div class="clp-renounce-indicator clp-inactive-renounce clp-renounce-{CARD_SUIT}"
                    id="clp-renounce-{PLAYER_ID}-{CARD_SUIT}">
                </div>
                <!-- END renounceindicator -->
            </div>
            <div class="clp-dealer-indicator-area" id="clp-dealer-{PLAYER_ID}"></div>
            <div class="clp-player-all-captured-cards" id="clp-player-all-captured-cards-{PLAYER_ID}">
                <div class="clp-calypsoholder" id="clp-calypsoholder-{PLAYER_ID}"></div>
                <div class="clp-trickpile clp-captured-card clp-trickpile-empty" id="clp-trickpile-{PLAYER_ID}"></div>
            </div>
        </div>
        <!-- END playercalypso -->
    </div>

    <div id="clp-score-access-table-area">
        <table id="clp-score-access-table">
            <!-- BEGIN roundscoreaccessrow -->
            <tr>
                <td>
                    <!-- TODO: translation, maybe inject text later-->
                    <button id="clp-round-scores-button-{ROUND_NUMBER}" class="clp-score-button clp-score-button-inactive">
                        Round {ROUND_NUMBER} scores
                    </button>
                </td>
            </tr>
            <!-- END roundscoreaccessrow -->
            <tr>
                <td>
                    <!-- TODO: translation, maybe inject text later-->
                    <button id="clp-round-scores-button-overall" class="clp-score-button clp-score-button-inactive">
                        Round-by-round scores
                    </button>
                </td>
            </tr>
        </table>
    </div>
</div>

<div id="clp-myhand-wrap" class="whiteblock"> <!-- TODO: whiteblock -> custom class -->
    <h3>{MY_HAND}</h3>
    <div id="clp-myhand">
    </div>
</div>


<script type="text/javascript">

// Javascript HTML templates

var jstpl_cardontable = '<div class="clp-card-on-table clp-face-up-card" id="clp-card-on-table-${player_id}"\
                            style="background-position:-${x}px -${y}px">\
                        </div>';
var jstpl_dealerindicator = '<div id="clp-dealerbutton" class="clp-dealerbutton"></div>';
var jstpl_calypsocard = '<div class="clp-calypsocard-space clp-captured-card clp-calypsocard-${rank} clp-card-space-${suit}"\
                            id="clp-calypsocard-${player_id}-${rank}">\
                        </div>'
var jstpl_player_calypso_info = '<div class="clp-calypso-info">\
                                    <div id="clp-suit-indicator-info-${id}" class="clp-suit-icon clp-suit-icon-${trump_suit}">\
                                    </div>\
                                    <div class="clp-info-count" id="clp-info-count-${id}">${completed_calypsos}</span>\
                                </div>';
</script>

{OVERALL_GAME_FOOTER}
