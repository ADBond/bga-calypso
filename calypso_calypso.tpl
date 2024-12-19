{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Calypso implementation : © Andy Bond <48208438+ADBond@users.noreply.github.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-->

<div id="clp-game-info" class="clp-game-info">
    <span id="clp-gametitle" class="clp-gametitle"></span><br><span id="clp-gi-round-hand"></span>
</div>

<div id="clp-public-area">
    <div id="clp-table-area">
        <div id="clp-table-centre"></div>
        <!-- BEGIN playerhand -->
        <div class="clp-player-card-play-area clp-player-card-play-area-{DIR}">
            <div class="clp-player-card-play-area-card" id="clp-player-card-play-area-card-{PLAYER_ID}"></div>
        </div>
        <!-- END playerhand -->
        <!-- BEGIN playercalypso -->
        <div
            class="clp-player-personal-area clp-player-personal-area-{DIR} clp-player-personal-area-{TRUMP_SUIT}"
            id="clp-player-personal-area-{DIR}"
        >
            <div class="clp-playername clp-playername-{DIR}">
                <a href="/player?id={PLAYER_ID}" style="color:#{PLAYER_COLOUR}">{PLAYER_NAME}</a>
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
                <div
                    class="clp-calypsopile clp-captured-card clp-calypsopile-empty clp-calypsopile-{TRUMP_SUIT}" 
                    id="clp-calypsopile-{PLAYER_ID}">
                </div>
            </div>
        </div>
        <!-- END playercalypso -->
    </div>
</div>
<div id="clp-myhand-wrap">
    <div id="clp-myhand">
    </div>
</div>
<div id="clp-score-access-table-area">
    <div id="clp-score-access-table">
        <div id="clp-score-access-by-round">
            <!-- BEGIN roundscoreaccessrow -->
            <div>
                <button id="clp-round-scores-button-{ROUND_NUMBER}" class="clp-score-button clp-score-button-inactive">
                </button>
            </div>
            <!-- END roundscoreaccessrow -->
        </div>
        <div id="clp-score-access-overall">
            <button id="clp-round-scores-button-overall" class="clp-score-button clp-score-button-inactive">
            </button>
        </div>
    </div>
</div>

<script type="text/javascript">

// Javascript HTML templates
// for cards to play to tricks
var jstpl_cardontable = '<div class="clp-card-on-table clp-face-up-card" id="clp-card-on-table-${player_id}"\
                            style="background-position:-${x}px -${y}px">\
                        </div>';
// dynamic dealer indicator
var jstpl_dealerindicator = '<div id="clp-dealerbutton" class="clp-dealerbutton"></div>';
// animating moving card to calypso
var jstpl_calypsocard = '<div class="clp-calypsocard-space clp-captured-card clp-calypsocard-${rank} clp-card-space-${suit}"\
                            id="clp-calypsocard-${player_id}-${rank}">\
                        </div>';
// for cards that remain in place after calypso goes
var jstpl_calypsocard_existing = '<div class="clp-calypsocard clp-captured-card clp-calypsocard-face-${suit}-${rank}\
                                clp-face-up-card clp-calypsocard-${rank} clp-card-space-${suit}"\
                                id="clp-calypsocard-${player_id}-${rank}">\
                            </div>';
// up in player boxes
var jstpl_playerbox_additions = '<div class="clp-playerbox-additions" class="clp-playerbox-additions">\
        <div class="clp-calypso-info">\
            <div id="clp-suit-indicator-info-${id}" class="clp-suit-icon clp-suit-icon-${trump_suit}">\
            </div>\
            <span class="clp-info-count" id="clp-info-count-${id}">${completed_calypsos}</span>\
        </div>\
        <div class="clp-teamname clp-teamname-${team_name}">${team_name_display}</div>\
    </div>';
// animate calypso count increment
var jstpl_suiticon = '<div id="clp-suit-for-score" class="clp-suit-icon clp-suit-icon-${trump_suit}"></div>';

</script>

{OVERALL_GAME_FOOTER}
