/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Calypso implementation : © Andy Bond <48208438+ADBond@users.noreply.github.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * calypso.js
 *
 * Calypso user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"  // for stock class
],
function (dojo, declare) {
    return declare("bgagame.calypso", ebg.core.gamegui, {
        constructor: function(){

            this.cardwidth = 72;
            this.cardheight = 96;

            // see material.inc.php
            this.spades = 1;
            this.hearts = 2;
            this.clubs = 3;
            this.diamonds = 4;

        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            // constants
            this.pack_files = {
                1: "cards.webp",
                2: "cards_four.webp",
            };
            this.suits_translate_lookup = {
                1: _("Spades"), 
                2: _("Hearts"), 
                3: _("Clubs"), 
                4: _("Diamonds"), 
            }
            this.suits_translate_lookup_sing = {
                1: _("Spade"),
                2: _("Heart"),
                3: _("Club"),
                4: _("Diamond"),
            }
            for( let player_id in gamedatas.players )
            {
                let player = gamedatas.players[player_id];
                let player_trump = player["trump_suit"];

                if(player_id == gamedatas.dealer){
                    let dealer_area_id = 'clp-dealer-' + player_id;
                    dojo.place(this.format_block('jstpl_dealerindicator', {
                        player_id : player_id
                    }), dealer_area_id);
                }
                this.setTrickPile(player_id, player["trick_pile"]);
                this.setupCalypsoArea(player_id, player_trump);
            }

            this.playerHand = new ebg.stock();
            this.playerHand.create( this, $('clp-myhand'), this.cardwidth, this.cardheight );
            // suit info relative to current player suitable for hand ordering
            // suit_ranking has player, partner, player matching colour, other suit
            let suit_ranking = gamedatas["suits_by_status"];
            // reverse order so that player suit is last (highest index)
            suit_ranking.reverse();

            const num_decks = 4;
            // 101 is pack face option
            const pack_file = 'img/' + this.pack_files[this.prefs[101].value];
            for (let suit = 1; suit <= 4; suit++) {
                for (let rank = 2; rank <= 14; rank++) {
                    for (let deck = 1; deck <= num_decks; deck++){
                        // Build card type id
                        let card_type_id = this.getCardUniqueId(suit, rank, deck);
                        let card_type = this.getCardUniqueType(suit, rank);
                        let card_weight = this.getCardWeight(suit, rank, suit_ranking);

                        // args are id, weight (for hand-sorting), img url,
                        // and img position (within the url sprite)
                        this.playerHand.addItemType(
                            card_type_id, card_weight, g_gamethemeurl + pack_file, card_type
                        );
                    }
                }
            }
            this.playerHand.centerItems = true;
            this.playerHand.image_items_per_row = 13;
            this.playerHand.setOverlap( 70, 0 );
            this.playerHand.extraClasses = "clp-hand-card";

            dojo.connect( this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged' );

            // Cards in player's hand
            for ( let i in gamedatas.hand) {
                let card = gamedatas.hand[i];
                let suit = card.type;
                let rank = card.type_arg;
                this.addCardToPlayerHand(suit, rank, card.id);
            }
            this.setHandActiveness(this.isCurrentPlayerActive(), gamedatas.playable_cards);

            // Cards played on table
            for (i in gamedatas.cardsontable) {
                let card = gamedatas.cardsontable[i];
                let suit = card.type;
                let rank = card.type_arg;
                let player_id = card.location_arg;
                this.playCardOnTable(player_id, suit, rank, card.id);
            }

            // Cards in calypsos
            for (i in gamedatas.cardsincalypsos) {
                let card = gamedatas.cardsincalypsos[i];
                let suit = card.type;
                let rank = card.type_arg;
                let player_id = card.location_arg;
                this.placeCardInCalypso(player_id, suit, rank);
            }
            const team_lookup = {
                [this.spades]: "major",
                [this.hearts]: "major",
                [this.clubs]: "minor",
                [this.diamonds]: "minor",
            };
            const team_lookup_display = {
                [this.spades]: _("Major suits team"),
                [this.hearts]: _("Major suits team"),
                [this.clubs]: _("Minor suits team"),
                [this.diamonds]: _("Minor suits team"),
            };
            // set player info on the object so we can use it for tooltips
            this.player_infos = gamedatas.players;
            for( player_id in gamedatas.players )
            {
                const player = gamedatas.players[player_id];
                const player_board_div = $(`player_board_${player_id}`);
                this.setCalypsoPile(player_id, player["completed_calypsos"]);
                
                dojo.place(
                    this.format_block(
                        'jstpl_playerbox_additions',
                        {
                            team_name: team_lookup[player["trump_suit"]],
                            team_name_display: team_lookup_display[player["trump_suit"]],
                            ...player,
                        }
                    ),
                    player_board_div
                );
                
            }
            // score table buttons
            const totalrounds = gamedatas.totalrounds;
            const currentround = gamedatas.roundnumber;
            for(let round_number = 1; round_number < currentround; round_number++){
                this.activateScoreButton(round_number, gamedatas.roundscoretable[round_number]);
            }
            const awaiting_new_round = (
                ["gameEnd", "awaitNewRound"].includes(gamedatas.gamestate["name"])
            );

            if(awaiting_new_round){
                this.activateScoreButton(currentround, gamedatas.roundscoretable[currentround]);
            }
            if(currentround != 1 | awaiting_new_round){
                this.activateOverallScoreButton(gamedatas.overallscoretable);
            }
            // button text!
            for(let round_number = 1; round_number <= totalrounds; round_number++){
                $(`clp-round-scores-button-${round_number}`).textContent = dojo.string.substitute(
                    _("Round ${round_number} scores"),
                    {round_number: round_number}
                );
            }
            $("clp-round-scores-button-overall").textContent = _("Round-by-round scores");

            // renounce flags, if the game option is active
            if(gamedatas.renounce_flags_on == "on"){
                for (i in gamedatas.renounce_flags) {
                    let info = gamedatas.renounce_flags[i];
                    this.setRenounceFlag(
                        info.player_id,
                        info.suit,
                        info.trump_suit,
                        info.player_name
                    );
                }
            }

            this.updateGameStatus(gamedatas.handnumber, currentround, totalrounds);

            variant = gamedatas.gamevariant;
            title = ((variant == "Standard Calypso") ? "Calypso" : "All Fours Calypso");
            $("clp-gametitle").innerText = title;
            this.setupNotifications();
            // tooltips ahoy:
            this.refreshTooltips();
            // set the empty animation queue:
            this.clearQueue();
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            // only action is classing the hand so that active hands have cursor, card highlight on hover
            switch( stateName )
            {            
                case 'playerTurn':
                    break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            switch( stateName )
            {
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            if( this.isCurrentPlayerActive() )
            {
                switch( stateName )
                {
                    case 'awaitNewRound':
                        this.addActionButton( 'clp-confirm-new-round', _('Ready for next round'), 'confirmNewRound');
                        break;
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */
        // Get card unique identifier based on its suit and rank
        getCardUniqueType : function(suit, rank) {
            return (suit - 1) * 13 + (rank - 2);
        },
        // this is only when we distinguish between identical copies, by their deck
        getCardUniqueId : function(suit, rank, deck) {
            return (deck - 1) * 52 + (suit - 1) * 13 + (rank - 2);
        },
        // get 'weight' of card given ordering of suits
        getCardWeight: function(suit, rank, suit_ranking) {
            // last in array has highest weight
            return (suit_ranking.indexOf(suit) - 1) * 13 + (rank - 2);
        },
        // calypso display area populated
        setupCalypsoArea : function(player_id, suit) {
            for (let rank = 2; rank <= 14; rank++) {
                dojo.place(this.format_block('jstpl_calypsocard', {
                    rank : rank,
                    suit : suit,
                    player_id : player_id
                }), 'clp-calypsoholder-' + player_id);
            }
        },

        addCardToPlayerHand(suit, rank, card_id){
            const unique_type = this.getCardUniqueType(suit, rank);
            this.playerHand.addToStockWithId(unique_type, card_id);
        },

        setHandActiveness(active, playable_cards=[]){
            const hand_div_id = "clp-myhand";
            if(active){
                dojo.addClass(hand_div_id, "clp-active-hand");
                dojo.removeClass(hand_div_id, "clp-inactive-hand");
                this.highlightPlayable(hand_div_id, true, playable_cards);
            } else{
                dojo.removeClass(hand_div_id, "clp-active-hand");
                dojo.addClass(hand_div_id, "clp-inactive-hand");
                this.highlightPlayable(hand_div_id, false);
            }
        },

        highlightPlayable(hand_div_id, make_playable, playable_cards=[]){
            // add css class to playable / not playable cards if make_playable is true
            // else remove all classes
            // actual behaviour covered by css, as it is a user pref
            const card_els = $(hand_div_id).children;
            const regex_card_id = /clp-myhand_item_(?<card_id>\d+)/m;
            const playable_card_ids = Object.values(playable_cards).map(
                (card) => card.id
            );
            [...card_els].forEach(
                (card_el) => {
                    let card_el_id = card_el.id;
                    let r_match = card_el_id.match(regex_card_id)
                    let card_id = r_match.groups.card_id;
                    if (make_playable) {
                        if (playable_card_ids.includes(card_id)) {
                            dojo.addClass(card_el_id, "clp-hand-card-playable");
                        } else {
                            dojo.addClass(card_el_id, "clp-hand-card-unplayable");
                        }
                    } else {
                        dojo.removeClass(card_el_id, "clp-hand-card-playable");
                        dojo.removeClass(card_el_id, "clp-hand-card-unplayable");
                    }
                }
            );
        },

        playCardOnTable : function(player_id, suit, rank, card_id) {
            dojo.place(this.format_block('jstpl_cardontable', {
                x : this.cardwidth * (rank - 2),
                y : this.cardheight * (suit - 1),
                player_id : player_id
            }), 'clp-player-card-play-area-card-' + player_id);

            if (player_id != this.player_id) {
                // Move card from their general area
                this.placeOnObject('clp-card-on-table-' + player_id, 'clp-player-all-captured-cards-' + player_id);
            } else {
                // div id is generated automatically from stock when we create hand
                // via this.addToStockById()
                if ($('clp-myhand_item_' + card_id)) {
                    this.placeOnObject('clp-card-on-table-' + player_id, 'clp-myhand_item_' + card_id);
                    this.playerHand.removeFromStockById(card_id);

                    this.setHandActiveness(false);
                }
            }

            // In any case: move it to its final destination
            let anim = this.slideToObject('clp-card-on-table-' + player_id, 'clp-player-card-play-area-card-' + player_id);
            anim.play();
        },

        placeCardInCalypso : function(player_id, suit, rank) {
            const card_el_id = `clp-calypsocard-${player_id}-${rank}`;
            dojo.addClass( card_el_id, `clp-calypsocard-face-${suit}-${rank}`);
            dojo.addClass( card_el_id, 'clp-face-up-card' );
            dojo.removeClass( card_el_id, 'clp-calypsocard-space' );
        },
        
        removeCardFromCalypso : function(player_id, suit, rank) {
            const card_el_id = `clp-calypsocard-${player_id}-${rank}`;
            dojo.removeClass( card_el_id, `clp-calypsocard-face-${suit}-${rank}`);
            dojo.removeClass( card_el_id, 'clp-face-up-card' );
            dojo.addClass( card_el_id, 'clp-calypsocard-space' );
        },

        setTrickPile : function(player_id, value) {
            const cards_el_id = `clp-trickpile-${player_id}`;
            if(value > 0){
                dojo.addClass( cards_el_id, 'clp-trickpile-full' );
                dojo.removeClass( cards_el_id, 'clp-trickpile-empty' );
            } else {
                dojo.removeClass( cards_el_id, 'clp-trickpile-full' );
                dojo.addClass( cards_el_id, 'clp-trickpile-empty' );
            }
            this.refreshTooltips();
        },

        setCalypsoPile: function(player_id, value) {
            const cards_el_id = `clp-calypsopile-${player_id}`;
            if(value > 0){
                dojo.addClass( cards_el_id, 'clp-calypsopile-full' );
                dojo.removeClass( cards_el_id, 'clp-calypsopile-empty' );
            } else {
                dojo.removeClass( cards_el_id, 'clp-calypsopile-full' );
                dojo.addClass( cards_el_id, 'clp-calypsopile-empty' );
            }
            this.refreshTooltips();
        },

        getRenounceFlagUniqueClass : function(suit, trump_suit, player_name) {
            return `clp-active-renounce-${player_name}-${trump_suit}-${suit}`;
        },

        setRenounceFlag : function(player_id, suit, player_trump, player_name){
            const renounce_el_id = `clp-renounce-${player_id}-${suit}`;
            dojo.addClass( renounce_el_id, 'clp-active-renounce' );
            // encode info for tooltip
            dojo.addClass(
                renounce_el_id,
                this.getRenounceFlagUniqueClass(suit, player_trump, player_name)
            );
            dojo.removeClass( renounce_el_id, 'clp-inactive-renounce' );
            this.refreshTooltips();
        },

        clearRenounceFlags: function(players, suits){
            for (player_id in players) {
                let player = players[player_id];
                for(j in suits) {
                    let suit = suits[j];
                    let renounce_el_id = `clp-renounce-${player_id}-${suit}`;
                    dojo.removeClass( renounce_el_id, 'clp-active-renounce' );
                    // detailed class for toolTips
                    dojo.removeClass(
                        renounce_el_id,
                        this.getRenounceFlagUniqueClass(
                            suit,
                            player.trump_suit,
                            player.player_name
                        )
                    );
                    dojo.addClass( renounce_el_id, 'clp-inactive-renounce' );
                }
            }
            this.refreshTooltips();
        },

        clearCalypsos: function(player_ids){
            // runs on new round
            let all_player_animations = [];
            for (player of player_ids){
                let player_id = player["id"];
                let suit = player["suit"];
                let animation = this.animateCalypso(player_id, suit, [], to_prefix="clp-trickpile", play=false, delay=100);
                all_player_animations.push(animation);
            }
            return dojo.fx.combine(all_player_animations);
        },

        clearCalypsoPiles: function(player_ids){
            for (player of player_ids){
                let player_id = player["id"];
                this.setCalypsoPile(player_id, 0);
            }
        },

        clearTrickPiles: function(player_ids){
            let animations = [];
            for (player of player_ids){
                let player_id = player["id"];
                let anim = this.slideTemporaryObject(
                    '<div class="clp-trickpile-full clp-trickpile" style="z-index:30"></div>',
                    "clp-table-centre",
                    `clp-trickpile-${player_id}`, "clp-table-centre",
                );
                anim.duration = 400;
                animations.push(anim);
            }
            return dojo.fx.combine(animations);
        },

        changeDealer : function(new_dealer_id) {
            const new_dealer_area_id = 'clp-dealer-' + new_dealer_id;
            this.attachToNewParent( 'clp-dealerbutton', new_dealer_area_id );
        },

        updateGameStatus: function(handnumber, roundnumber, totalrounds) {
            // don't need to translate game title
            $("clp-gi-round-hand").innerHTML =  dojo.string.substitute(
                 _("Round ${roundnumber} of ${totalrounds}") +
                    " - " + _("Hand ${handnumber} of 4"),
                {
                    roundnumber: roundnumber,
                    handnumber: handnumber,
                    totalrounds: totalrounds,
                } 
            );
        },

        refreshTooltips: function() {
            this.addTooltipToClass( "clp-dealerbutton", _( "This player is the dealer for this hand" ), "" );
            this.addTooltipToClass( "clp-trickpile-full", _( "This player has some cards in their trick-pile" ), "" );
            this.addTooltipToClass( "clp-trickpile-empty", _( "This player has no cards in their trick-pile" ), "" );
            this.addTooltipToClass( "clp-calypsopile-full", _( "This player has completed one or more calypsos this round" ), "" );
            this.addTooltipToClass( "clp-active-renounce", _( "This player failed to follow this suit this hand" ), "" );
            this.refreshRenounceTooltips();
            const elements_without_tooltips = dojo.query(".clp-inactive-renounce, .clp-calypsopile-empty");
            for(let element of elements_without_tooltips){
                this.removeTooltip(element["id"]);
            }
            
        },
        refreshRenounceTooltips: function() {
            player_infos = this.player_infos;
            for( player_id in player_infos) {
                const player = player_infos[player_id];
                for (let [suit_index, suit_name_trans] of Object.entries(this.suits_translate_lookup)) {
                    const class_name = this.getRenounceFlagUniqueClass(
                        suit_index,
                        player['trump_suit'],
                        player['player_name']
                    );
                    const tooltip = dojo.string.substitute(
                        _("The ${trump_suit_singular} player (${name}) has not followed suit to ${renounce_suit}"),
                        {
                            trump_suit_singular: this.suits_translate_lookup_sing[player['trump_suit']],
                            name: player['player_name'],
                            renounce_suit: this.suits_translate_lookup[suit_index]
                        }
                    );
                    this.addTooltipToClass( class_name, tooltip, "" );
                }
            };
        },

        // borrowed/modified from W. Michael Shirk's Grosstarock implementation
        // saved a lot of pain in trying to hack something together!
        showResultDialog: function (score_table, title=null) {
            wrap_translation = (text_entry) => {
                if(typeof text_entry === 'object' && text_entry.hasOwnProperty('for_round_number')){
                    return dojo.string.substitute(
                        _("Round ${round_number} score"),
                        {round_number: text_entry["for_round_number"]["round_number"]}
                    );
                }
                // keys from backend -> actually displayed (and translatable) text
                const lookup = {
                    "calypso_count": _("Completed calypsos"),
                    "incomplete_calypso_count": _("Cards in incomplete calypsos"),
                    "trickpile_count": _("Cards in trickpile"),
                    "individual_score": _("Total individual score"),
                    "partnership_score": _("Total round score (partnership)"),
                    "score": _("score"),
                    "total_score": _("Total score"),
                };
                return lookup[text_entry] || text_entry;
            }
            // put entries inside containers with given class, so we can style freely
            wrap_class = (table_entry) => {
                if(typeof table_entry === 'object' && table_entry.hasOwnProperty('to_wrap')){
                    let items = table_entry["to_wrap"];
                    // maybe having it concatenated here is good so client knows to translate properly?
                    return '<div class=\"' + items.class_name + '\">' + wrap_translation(items.string_key) + '</div>';
                }
                return table_entry;
            }
            score_table = score_table.map((row) => row.map(wrap_class));
            let scoring_dialog = this.displayTableWindow(
                "roundScore",
                title,
                score_table,
                "",
                this.format_string_recursive(
                    '<div id="tableWindow_actions"><a id="close_btn" class="bgabutton bgabutton_blue">${close}</a></div>',
                    { close: _("Close") }
                )
            )
			scoring_dialog.show()
        },

        showResultDialogByRound: function(round_number, score_table){
            const title = dojo.string.substitute(
                _("Round ${round_number} score"),
                {round_number: round_number}
            );
            this.showResultDialog(score_table, title);
        },

        activateScoreButton: function(round_number, score_table){
            const round_button_id = `clp-round-scores-button-${round_number}`;
            $(round_button_id).onclick = (
                () => this.showResultDialogByRound(round_number, score_table)
            );
            dojo.addClass( round_button_id, 'clp-score-button-active' );
            dojo.removeClass( round_button_id, 'clp-score-button-inactive' );
        },

        activateOverallScoreButton: function(overall_score){
            const overall_scores_button_id = 'clp-round-scores-button-overall';
            $(overall_scores_button_id).onclick = (
                () => this.showResultDialog(
                    overall_score, _("Round-by-round score summary"))
            );
            dojo.addClass( overall_scores_button_id, 'clp-score-button-active' );
            dojo.removeClass( overall_scores_button_id, 'clp-score-button-inactive' );
        },

        // animates clearing calypso both for completing calypso, and at end of round when we clear the table
        // to_prefix flags for us which one
        // play=true means we add it to the animation queue rather than just returning it
        animateCalypso: function(player_id, player_suit, fresh_ranks, to_prefix="clp-calypsopile", play=true, delay=30){
            // make some modifications that we will undo at the end of the method
            // for correct animation calculation
            dojo.addClass("clp-public-area", "clp-no-transform");
            let animations = [];
            let anim;
            let current_delay = 0;
            for (let rank = 2; rank <= 14; rank++) {
                let card_el_id = `clp-calypsocard-${player_id}-${rank}`;
                // create animation for cards in the calypso
                if(!dojo.hasClass(card_el_id, "clp-calypsocard-space")){
                    anim = this.slideToObject(card_el_id, `${to_prefix}-${player_id}` );
                    dojo.connect(anim, 'onEnd', function(node) {
                        dojo.destroy(node);
                    });
                } else{
                    // dummy animation for blank spaces (unfilled slots) so that timings
                    // stay synced for all calypsos
                    anim = dojo.animateProperty({
                        node: card_el_id,
                        properties: {}
                    });
                }
                
                anim.delay = current_delay;
                current_delay += delay;
                anim.duration = 400;
                // place existing card or blank space as appropriate, for underneath
                // these are the moving items that will get swept away
                if(fresh_ranks.includes(rank)){
                    dojo.connect(
                        anim, "onBegin",
                        () => {
                            dojo.place(this.format_block('jstpl_calypsocard_existing', {
                                rank : rank,
                                suit : player_suit,
                                player_id : player_id
                            }), 'clp-calypsoholder-' + player_id);
                        }
                    );
                } else{
                    dojo.connect(
                        anim, "onBegin",
                        () => this.removeCardFromCalypso(player_id, player_suit, rank)
                    );
                    dojo.place(this.format_block('jstpl_calypsocard', {
                        rank : rank,
                        suit : player_suit,
                        player_id : player_id
                    }), 'clp-calypsoholder-' + player_id);
                }
                animations.push(anim);
            }

            dojo.removeClass("clp-public-area", "clp-no-transform");
            return dojo.fx.combine(animations);
        },

        // animation queue handling - in case we need to zhuzh it up
        addToQueue : function(anim, description = "") {
            this.animation_queue.push(anim);
            // description queue is for aid in debugging
            this.description_queue.push(description);
        },
        playQueue : function() {
            dojo.fx.chain(this.animation_queue).play();
        },
        clearQueue : function() {
            this.animation_queue = [];
            this.description_queue = [];
        },

        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        onPlayerHandSelectionChanged : function() {
            const items = this.playerHand.getSelectedItems();
            const action = 'playCard';
            if (items.length > 0) {
                if (this.checkAction(action, true)) {
                    // Can play a card
                    let card_id = items[0].id;
                    this.ajaxcall(
                        "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
                        {
                            id: card_id,
                            lock: true
                        },
                        this,
                        function (result) {
                        },
                        function (is_error) {
                        }
                    );
                    this.playerHand.unselectAll();
                } else {
                    this.showMessage(_("It is not your turn to play a card!"), "error");
                    this.playerHand.unselectAll();
                }
            }
        },

        confirmNewRound: function(){
            if (this.checkAction("confirmNewRound", true)) {
                this.ajaxcall(
                    "/" + this.game_name + "/" + this.game_name + "/confirmNewRound.html",
                    {
                        lock: true
                    },
                    this,
                    function (result) {
                    },
                    function (is_error) {
                    }
                );
            }
        },

        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your calypso.game.php file.
        
        */
        setupNotifications : function() {
            dojo.subscribe('newGame', this, "notif_newGame");

            dojo.subscribe('newHandBegin', this, "notif_newHandBegin");
            dojo.subscribe('newRound', this, "notif_newRound");

            // the actual cards that a player receives
            dojo.subscribe('newCards', this, "notif_newCards");
            // admin around hand/dealer changing
            dojo.subscribe('dealHand', this, "notif_dealHand");
            dojo.subscribe('playableCards', this, "notif_playableCards");
            // playing a card - the main game action occurring
            dojo.subscribe('playCard', this, "notif_playCard");
            // handles setting renounce flags when a player renounces
            dojo.subscribe('renounceFlag', this, "notif_renounceFlag");

            // just for starting off a hand, v. thin
            dojo.subscribe('actionRequired', this, "notif_actionRequired");
            // the various sets of things that (can) happen upon a trick finishing
            dojo.subscribe( 'trickWin', this, "notif_trickWin" );
            this.notifqueue.setSynchronous( 'trickWin', 1000 );
            dojo.subscribe( 'moveCardsToWinner', this, "notif_moveCardsToWinner" );
            dojo.subscribe( 'moveCardsToCalypsos', this, "notif_moveCardsToCalypsos" );
            // need to leave enough time for _all_ cards to get to calypsos, otherwise they stick around
            dojo.subscribe( 'calypsoComplete', this, "notif_calypsoComplete" );
            dojo.subscribe( 'playAllAnimations', this, "notif_playAllAnimations" );
            // updating scores/display score tables
            dojo.subscribe( 'scoreDisplay', this, "notif_scoreDisplay" );
            dojo.subscribe( 'scoreUpdate', this, "notif_scoreUpdate" );
        },

        notif_newGame: function(notif) {
            // currently just displaying log information, but could always add some animation here
        },

        notif_newRound: function(notif) {
            const player_ids = notif.args.player_ids;
            for(let player of player_ids){
                let player_id = player["id"];
                const player_count_element = `clp-info-count-${player_id}`;
                $(player_count_element).textContent = 0;
            }
            if(notif.args.round_number != 1){
                this.clearCalypsoPiles(player_ids);
                let cleanup_animation = this.clearCalypsos(player_ids);
                dojo.connect(
                    cleanup_animation, "onEnd",
                    dojo.hitch(this, () => {
                        this.clearTrickPiles(player_ids);
                        player_ids.forEach(player_id => this.setTrickPile(player_id["id"], 0));
                        this.refreshTooltips();
                    })
                );
                cleanup_animation.play();
            }
        },

        notif_newCards : function(notif) {
            this.playerHand.removeAll();
            for ( let i in notif.args.cards) {
                let card = notif.args.cards[i];
                let suit = card.type;
                let rank = card.type_arg;
                this.addCardToPlayerHand(suit, rank, card.id);
            }
            this.playerHand.updateDisplay();
        },

        notif_dealHand : function(notif) {
            this.changeDealer(notif.args.dealer_id);
            this.updateGameStatus(notif.args.hand_number, notif.args.round_number, notif.args.total_rounds);
            if(notif.args.renounce_flags_clear){
                this.clearRenounceFlags(notif.args.players, notif.args.suits);
            }
        },

        notif_playableCards: function(notif) {
            this.setHandActiveness(true, notif.args.playable_cards);
        },

        notif_playCard : function(notif) {
            this.playCardOnTable(notif.args.player_id, notif.args.suit, notif.args.rank, notif.args.card_id);
        },

        notif_renounceFlag: function(notif) {
            this.setRenounceFlag(
                notif.args.player_id, notif.args.suit,
                notif.args.player_trump, notif.args.player_name
            );
        },

        notif_trickWin : function(notif) {
            // We do nothing here (just wait in order players can view the 4 cards played before they're gone.
        },
        notif_calypsoComplete : function(notif) {
            // put any cards to the new calypso in their RIGHTFUL PLACE
            let fresh_cards = Object.values(notif.args.cards_to_fresh_calypso);
            let fresh_ranks = fresh_cards.map(card => +card["rank"]);

            const player_id = notif.args.player_id;
            // for each card in calypso, get rid of it, but not too much
            let anim_calypso = this.animateCalypso(
                player_id, notif.args.player_suit, fresh_ranks, to_prefix="clp-calypsopile"
            );
            
            const player_count_element = `clp-info-count-${player_id}`;
            const new_num_calypsos = notif.args.num_calypsos;
            const calypso_pile_func = () => this.setCalypsoPile(player_id, new_num_calypsos);

            // have transform woes if we try to use calypsopile
            const anim_origin = "clp-table-centre";
            let anim = this.slideTemporaryObject(
                this.format_block('jstpl_suiticon', {
                    trump_suit : notif.args.player_suit,
                 }),
                anim_origin,
                anim_origin,
                player_count_element
            );
            dojo.connect(anim, 'onEnd', function(node) {
                $(player_count_element).textContent = new_num_calypsos;
            });
            dojo.connect(anim_calypso, 'onEnd', function(node) {
                calypso_pile_func();
            })
            anim.duration = 800;
            this.addToQueue(anim_calypso, "calypsos -> calypso pile");
            this.addToQueue(anim, "suit icon to scoreboard to clue players");
        },

        notif_actionRequired : function(notif) {
            // waiting for first player to play a card
        },

        notif_scoreDisplay: function(notif) {
            this.showResultDialogByRound(notif.args.round_number, notif.args.table);
            this.activateScoreButton(notif.args.round_number, notif.args.table);
            this.activateOverallScoreButton(notif.args.overall_score);
        },

        notif_scoreUpdate : function(notif) {
            notif.args.scores.forEach(
                score_info => (
                    this.scoreCtrl[score_info.player_id].incValue(score_info.total_score)
                )
            );
        },

        notif_moveCardsToWinner : function(notif) {
            // Move all cards on table to winners' card space, ready to be sent on
            // construct the animation, and add it to the queue
            const winner_id = notif.args.winner_id;
            let anims = [];
            for ( let player_id in this.gamedatas.players) {
                let anim = this.slideToObject(
                    'clp-card-on-table-' + player_id,
                    'clp-player-card-play-area-card-' + winner_id
                );
                anims.push(anim);
            }
            // queue should already be clear, but can't hurt to make sure
            this.clearQueue();
            // start a new animation queue with these animations combined as a single one (simultaneous)
            this.addToQueue(dojo.fx.combine(anims), "move all cards to winner");
        },

        notif_moveCardsToCalypsos : function(notif) {
            // cardinal positions - how rotated are they (in degrees, sunwise)?
            const degrees_lookup = {
                'S': 0,
                'W': 90,
                'N': 180,
                'E': -90,
            };
            function getRotationDegrees(element) {
                // given a destination, how much should we rotate to end up pointing the right way
                // find the player area which contains the destination element, and get the id
                let id = element.closest(".clp-player-personal-area").id;
                // id is e.g. clp-player-personal-area-N, so extract cardinal point
                let cardinal_dir = id.at(-1);
                return degrees_lookup[cardinal_dir];
            };
            let anims = [];
            // duration of rotation animation in ms - 350 default.
            const rotate_dur = 100;
            const winner_id = notif.args.player_id;
            // this has the admin on where all the cards come from, but more importantly go to
            const moved_to = notif.args.moved_to;
            // count all cards to stagger them slightly - a bit nicer
            // helps it not seem like cards are disappearing
            let card_count = 0;
            const card_anim_delay = 100;
            const flip_half_dur = 100;
            // loop through the cards of the trick:
            for ( let player in moved_to) {
                let anim;
                let send_to_id = moved_to[player]["owner"];
                let send_from_player_id = moved_to[player]["originating_player"];
                let send_from_el_id = `clp-card-on-table-${send_from_player_id}`;
                let send_from_el = $(send_from_el_id);

                if(send_to_id === 0){
                    // card is just going to trick pile
                    let send_to_el = $('clp-trickpile-' + winner_id);
                    let rot_deg = getRotationDegrees(send_to_el);
                    let anim_rot = new dojo.Animation({
                        curve: [0, rot_deg],
                        onAnimate: (v) => {
                            send_from_el.style.transform = 'rotate(' + v + 'deg)';
                        },
                        duration: rotate_dur,
                        delay: card_count * card_anim_delay,
                    });
                    let anim_slide = this.slideToObject(send_from_el, send_to_el);
                    let anim_flip_1 = new dojo.Animation({
                        curve: [1, 0],
                        onAnimate: (v) => {
                            // needs to still be rotated!
                            send_from_el.style.transform = 'rotate( ' + rot_deg + 'deg) scaleX(' + v + ') ';
                        },
                        duration: flip_half_dur,
                    });
                    dojo.connect(anim_flip_1, 'onEnd', (node) => {
                        send_from_el.classList.add("clp-trickpile-nearly");
                        send_from_el.classList.remove("clp-face-up-card");
                    })
                    let anim_flip_2 = new dojo.Animation({
                        curve: [0, 1],
                        onAnimate: (v) => {
                            // needs to still be rotated!
                            send_from_el.style.transform = 'rotate( ' + rot_deg + 'deg) scaleX(' + v + ') ';
                        },
                        duration: flip_half_dur,
                    });
                    dojo.connect(anim_flip_2, 'onEnd', (node) => {
                        dojo.destroy(send_from_el);
                        this.setTrickPile(winner_id, 1);
                    });

                    anim = dojo.fx.chain([anim_rot, anim_slide, anim_flip_1, anim_flip_2]);
                } else{
                    // card goes to the one of the winning partnerships' calypsos
                    let calypso_player_id = send_to_id;
                    let rank = moved_to[player]["rank"];
                    let suit = moved_to[player]["suit"];
                    let send_to_el = $(`clp-calypsocard-${calypso_player_id}-${rank}`);

                    let anim_rot = new dojo.Animation({
                        // crazy rotation for testing
                        curve: [0, getRotationDegrees(send_to_el)],
                        onAnimate: (v) => {
                            send_from_el.style.transform = 'rotate(' + v + 'deg)';
                        } ,
                        duration: rotate_dur,
                        delay: card_count * card_anim_delay,
                    });
                    let anim_slide = this.slideToObject(
                        send_from_el,
                        send_to_el,
                    );

                    dojo.connect(anim_slide, 'onEnd', (node) => {
                        dojo.destroy(send_from_el);
                        this.placeCardInCalypso(send_to_id, suit, rank);
                    });
                    anim = dojo.fx.chain([anim_rot, anim_slide]);
                }
                // next card - this will set delays for subsequent animations
                card_count += 1;
                anims.push(anim);
            }
            // add combined animation to the queue
            this.addToQueue(dojo.fx.combine(anims), "move cards from winner space to destinations");
        },

        notif_playAllAnimations : function(notif) {
            this.playQueue();
            // can't hurt to clear just in case
            this.clearQueue();
        },

        notif_newHandBegin : function(notif) {
            // just says a new hand is starting
        }

   });             
});
