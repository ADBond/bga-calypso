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
            console.log('calypso constructor');

            // TODO: may want to tweak these numbers - from Hearts (w=72, h=96)
            this.cardwidth = 72;
            this.cardheight = 96;

            // Here, you can init the global letiables of your user interface
            // Example:
            // this.myGlobalValue = 0;

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
            console.log("here is what i can see");
            console.log(gamedatas);
            for( let player_id in gamedatas.players )
            {
                let player = gamedatas.players[player_id];
                let player_trump = player["trump_suit"];
                let trump_lookup = {
                    1: "spades", 2: "hearts", 3: "clubs", 4: "diamonds"
                };

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

            const num_decks = 4;
            for (let suit = 1; suit <= 4; suit++) {
                for (let rank = 2; rank <= 14; rank++) {
                    for (let deck = 1; deck <= num_decks; deck++){
                        // Build card type id
                        let card_type_id = this.getCardUniqueId(suit, rank, deck);
                        let card_type = this.getCardUniqueType(suit, rank);
                        // args are id, weight (for hand-sorting), img url, and img position
                        // Not sure for the moment if it is important for ids to be distinct here,
                        // but a sensible default answer seems to be 'yes'
                        // TODO: here is where we might want to separate out trumps!
                        // i.e. (other two, alternating colour) (my partners trumps) (my trumps)
                        this.playerHand.addItemType(
                            card_type_id, card_type, g_gamethemeurl + 'img/cards.jpg', card_type
                        );
                    }
                }
            }
            this.playerHand.centerItems = true;
            this.playerHand.image_items_per_row = 13;
            // TODO: decide on overlap or not
            this.playerHand.setOverlap( 70, 0 );
            this.playerHand.extraClasses = "clp-hand-card";

            // this.playerHand.onItemCreate = dojo.hitch( this, 'setupNewHandCard' ); 

            dojo.connect( this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged' );

            // Cards in player's hand
            for ( let i in this.gamedatas.hand) {
                let card = this.gamedatas.hand[i];
                let suit = card.type;
                let rank = card.type_arg;
                let unique_type = this.getCardUniqueType(suit, rank);
                this.playerHand.addToStockWithId(unique_type, card.id);
                let card_el = this.playerHand.getItemDivId(card.id);
                console.log("handy");
                console.log(card.id);
                console.log(card_el);
                // TODO: probably delete this function
                // TODO: set hoverable here? but then also need to do it when your turn comes around
                // this.setupNewHandCard(card_el);
            }
            this.setHandActiveness(this.isCurrentPlayerActive());

            // Cards played on table
            for (i in this.gamedatas.cardsontable) {
                let card = this.gamedatas.cardsontable[i];
                let suit = card.type;
                let rank = card.type_arg;
                let player_id = card.location_arg;
                console.log("on the table has: " + suit + ", " + rank + ", and...");
                this.playCardOnTable(player_id, suit, rank, card.id);
            }

            // Cards in calypsos
            console.log("display the calypsos");
            for (i in this.gamedatas.cardsincalypsos) {
                let card = this.gamedatas.cardsincalypsos[i];
                let suit = card.type;
                let rank = card.type_arg;
                let player_id = card.location_arg;
                console.log("calypso has: " + suit + ", " + rank + ", and...");
                this.placeCardInCalypso(player_id, suit, rank, card.id);
            }
            console.log("completed calypo counts");
            for( player_id in gamedatas.players )
            {
                const player = gamedatas.players[player_id];
                const player_board_div = $(`player_board_${player_id}`);
                dojo.place( this.format_block('jstpl_player_calypso_info', player ), player_board_div );
                this.setCalypsoPile(player_id, player["completed_calypsos"]);
            }
            const totalrounds = this.gamedatas.totalrounds;
            const currentround = this.gamedatas.roundnumber;
            // TODO: need fancier checking here, in case we are in awaitNewRound
            for(let round_number = 1; round_number < currentround; round_number++){
                this.activateScoreButton(round_number, this.gamedatas.roundscoretable[round_number]);
            }
            const awaiting_new_round = (gamedatas.gamestate["name"] == "awaitNewRound");
            console.log("have a butchers at this:");
            console.log(awaiting_new_round);
            console.log(gamedatas.gamestate);
            if(awaiting_new_round){
                this.activateScoreButton(currentround, this.gamedatas.roundscoretable[currentround]);
            }
            if(currentround != 1 | awaiting_new_round){
                const overall_scores_button_id = 'clp-round-scores-button-overall';
                $(overall_scores_button_id).onclick = (
                    () => this.showResultDialog(
                        0, this.gamedatas.overallscoretable, _("Round-by-round score summary"))
                );
                dojo.addClass( overall_scores_button_id, 'clp-score-button-active' );
                dojo.removeClass( overall_scores_button_id, 'clp-score-button-inactive' );
            }

            console.log("are the renounce flags on?");
            console.log(this.gamedatas.renounce_flags_on);
            if(this.gamedatas.renounce_flags_on == "on"){
                console.log("show me those flags!");
                console.log(this.gamedatas.renounce_flags);
                for (i in this.gamedatas.renounce_flags) {
                    let info = this.gamedatas.renounce_flags[i];
                    this.setRenounceFlag(info.player_id, info.suit);
                }
            }

            this.updateGameStatus(this.gamedatas.handnumber, currentround, totalrounds);
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            // tooltips ahoy:
            console.log("attaching tooltips to classes...");
            this.refreshTooltips();
            console.log("...and that my friend is sorted");

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+ stateName );
            
            switch( stateName )
            {
            
                case 'playerTurn':
                    this.setHandActiveness(this.isCurrentPlayerActive());
                    break;
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
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
        // TODO: Can we make this a bit nicer to deal with e.g. w/classes?
        getCardUniqueType : function(suit, rank) {
            return (suit - 1) * 13 + (rank - 2);
        },
        getCardUniqueId : function(suit, rank, deck) {
            return (deck - 1) * 52 + (suit - 1) * 13 + (rank - 2);
        },

        setupCalypsoArea : function(player_id, suit) {
            for (let rank = 2; rank <= 14; rank++) {
                // let card_el_id = `calypsocard_${player_id}_${rank}`;
                dojo.place(this.format_block('jstpl_calypsocard', {
                    rank : rank,
                    suit : suit,
                    player_id : player_id
                }), 'clp-calypsoholder-' + player_id);
                // dojo.removeClass(card_el_id, "clp-face-up-card");
            }
        },

        setupNewHandCard: function( card_div_id ) {
           // function for when cards are made in players' hand
            console.log("hand card");
            console.log(card_div_id);
            // console.log(card_type_id);
            // console.log(card_id)
    
            // dojo.attr(card_id, "style", "");
            let current_z = dojo.style(card_div_id, "z-index");
            console.log(current_z)
            dojo.setStyle(card_div_id, "z-index", current_z + 20);
            dojo.style(card_div_id, "color", "red");
            dojo.style(card_div_id, "opacity", "");
            console.log("element is:")
            console.log($(card_div_id));
        },
        
        setHandActiveness(active){
            let hand_div_id = "clp-myhand";
            if(active){
                dojo.addClass(hand_div_id, "clp-active-hand");
                dojo.removeClass(hand_div_id, "clp-inactive-hand");
            } else{
                dojo.removeClass(hand_div_id, "clp-active-hand");
                dojo.addClass(hand_div_id, "clp-inactive-hand");
            }
        },

        playCardOnTable : function(player_id, suit, rank, card_id) {
            dojo.place(this.format_block('jstpl_cardontable', {
                x : this.cardwidth * (rank - 2),
                y : this.cardheight * (suit - 1),
                z : 40,  // TODO: this is not doing the job - check clp-card-on-table and trickpile together
                player_id : player_id
            }), 'clp-player-card-play-area-card-' + player_id);

            // would be nicer to do as class, but makes animation weird somehow
            dojo.style(`clp-card-on-table-${player_id}`, "top", "0");
            // dojo.style(`clp-card-on-table-${player_id}`, "border", "solid 1px black");
            // dojo.addClass('clp-card-on-table-' + player_id, "clp-selected-card");
            console.log("the style");
            console.log($(`clp-card-on-table-${player_id}`));
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
            // dojo.connect(anim, 'onStart', function(node) {
            //     dojo.addClass('node', "clp-selected-card");
            //     console.log("drop tyles");
            // });

            // dojo.connect(anim, 'onEnd', function(node) {
            //     dojo.removeClass('node', "clp-selected-card");
            //     console.log("drop tyles");
            // });
            // for debugging z-index stuff:
            // anim.duration = 30000;
            anim.play();
            
            // dojo.removeClass('clp-card-on-table-' + player_id, "clp-selected-card");
        },

        placeCardInCalypso : function(player_id, suit, rank, card_id) {
            // const x = this.cardwidth * (rank - 2);
            // const y = this.cardheight * (suit - 1);

            const card_el_id = `clp-calypsocard-${player_id}-${rank}`;
            console.log("just a simple card going into a calypso - what could be better than that?");
            console.log(card_el_id);

            // TODO: this should stay in css - use class manipulation
            // dojo.style(card_el_id,
            //     {
            //         'backgroundPosition': `-${x}px -${y}px`,
            //         'z-index': `${+rank + 14}`,
            //     }
            // )
            dojo.addClass( card_el_id, `clp-calypsocard-face-${suit}-${rank}`);
            dojo.addClass( card_el_id, 'clp-face-up-card' );
            dojo.removeClass( card_el_id, 'clp-calypsocard-space' );
        },

        setTrickPile : function(player_id, value) {
            const cards_el_id = `clp-trickpile-${player_id}`;
            console.log(cards_el_id);
            // TODO maybe a scaled thing here? (e.g. a few cards, 10-20, etc?) not sure if I dig that though
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
            console.log("shiny new pile");
            console.log(cards_el_id);
            if(value > 0){
                dojo.addClass( cards_el_id, 'clp-calypsopile-full' );
                dojo.removeClass( cards_el_id, 'clp-calypsopile-empty' );
            } else {
                dojo.removeClass( cards_el_id, 'clp-calypsopile-full' );
                dojo.addClass( cards_el_id, 'clp-calypsopile-empty' );
            }
            this.refreshTooltips();
        },

        setRenounceFlag : function(player_id, suit){
            const renounce_el_id = `clp-renounce-${player_id}-${suit}`;
            console.log("this is happening: " + renounce_el_id);
            dojo.addClass( renounce_el_id, 'clp-active-renounce' );
            dojo.removeClass( renounce_el_id, 'clp-inactive-renounce' );
            this.refreshTooltips();
        },

        clearRenounceFlags: function(players, suits){
            console.log("clear me");
            for (i in players) {
                let player = players[i];
                for(j in suits) {
                    let suit = suits[j];
                    let renounce_el_id = `clp-renounce-${player}-${suit}`;
                    console.log(renounce_el_id);
                    dojo.removeClass( renounce_el_id, 'clp-active-renounce' );
                    dojo.addClass( renounce_el_id, 'clp-inactive-renounce' );
                }
            }
            this.refreshTooltips();
        },

        clearCalypsos: function(player_ids){
            console.log("clear it up!");
            let all_player_animations = [];
            for (player of player_ids){
                let player_id = player["id"];
                let suit = player["suit"];
                // for(let rank=2; rank <= 14; rank++){
                //     let card_el_id = `clp-calypsocard-${player_id}-${rank}`;
                //     console.log(card_el_id);
                //     dojo.removeClass( card_el_id, 'clp-face-up-card' );
                //     dojo.addClass( card_el_id, 'clp-calypsocard-space' );
                //     dojo.removeClass( card_el_id, `clp-calypsocard-face-${suit}-${rank}`)
                // }
                // animations = this.animateCalypso(player_id, suit, [], to_prefix="clp-trickpile", play=false, delay=1000);
                animations = this.animateCalypso(player_id, suit, [], to_prefix="clp-trickpile", play=false, delay=100);
                all_player_animations.push(dojo.fx.combine(animations));
            }
            let combined_animation = dojo.fx.combine(all_player_animations);
            dojo.connect(combined_animation, 'onEnd', function(node) {
                dojo.removeClass(`clp-trickpile-${player_id}`, "clp-very-high");
                console.log("don't worry it has happened!");
                // TODO: still clips a litlle, might be removed too early
            });
            return dojo.fx.combine(all_player_animations);
            // for (player of player_ids){
            //     let player_id = player["id"];
            // }
        },
        clearCalypsoPiles: function(player_ids){
            console.log("all gone my friend, all gone. like the turning of the tides...");
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
                // dojo.connect(
                //     anim, "onPlay",
                //     dojo.hitch(this, () => this.setTrickPile(player_id, 0) )
                // );
                // anim.duration = 30000;
                // this.setTrickPile(player_id, 0);
                anim.duration = 400;
                animations.push(anim);
            }
            return dojo.fx.combine(animations);
        },

        changeDealer : function(new_dealer_id) {
            const new_dealer_area_id = 'clp-dealer-' + new_dealer_id;
            console.log("new dealer");
            console.log(new_dealer_area_id);
            
            // let old_element_id = $('clp-dealerbutton').parentElement.id;
            // console.log(old_element_id)
            // dojo.destroy('clp-dealerbutton');
            // dojo.place(this.format_block('jstpl_dealerindicator', {
            //     player_id : player_id
            // }), old_element_id);
            // // need to have transforms disabled while we do the animation, or co-ords get screwed up
            // // for(dir of ["N", "E", "S", "W"]){
            // //     let div_id = `clp-player-personal-area-${dir}`;
            // //     dojo.addClass(div_id, 'clp-no-transform');
            // //     $(div_id).offsetHeight;
            // // }
            // // this is the div that is the parent of our tpl
            // // dojo.addClass("game_play_area", 'clp-no-transform');
            // // $("game_play_area").offsetHeight;
            // this.slideToObject('clp-dealerbutton', new_dealer_area_id).play();
            this.attachToNewParent( 'clp-dealerbutton', new_dealer_area_id );

            // for(dir of ["N", "E", "S", "W"]){
            //     let div_id  = `clp-player-personal-area-${dir}`;
            //     dojo.removeClass(div_id , 'clp-no-transform');
            // }
            // dojo.removeClass("clp-table-area", 'clp-no-transform');
            
            // dojo.removeClass("game_play_area", 'clp-no-transform');
            // anim.play();
        },

        updateGameStatus: function(handnumber, roundnumber, totalrounds) {
            console.log("update that banner!");
            console.log("have hand " + handnumber + " and round " + roundnumber + " of total " + totalrounds);
            // TODO: look here for your js translation needs!
            $("clp-game-info").innerHTML =  dojo.string.substitute(
                '<div class="clp-gametitle">' + _("Calypso") + "</div>" + 
                    "<br>" + _("Round ${roundnumber} of ${totalrounds}") +
                    "<br>" + _("Hand ${handnumber} of 4"),
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
            // TODO: specialise to suits?
            this.addTooltipToClass( "clp-active-renounce", _( "This player failed to follow this suit this hand" ), "" );
        },
        // displayRoundScores: function(round_number){
        //     console.log("trigerring this chappy!");
        //     this.ajaxcall(
        //         "/" + this.game_name + "/" + this.game_name + "/displayScoresWrapper.html",
        //         {
        //             round_number: round_number,
        //             lock: true,
        //         },
        //         this,
        //         function (result) {
        //         },
        //         function (is_error) {
        //         }
        //     );
        //     console.log("worked like a blooming charm!");
        // },

        // borrowed/modified from W. Michael Shirk's Grosstarock implementation
        // saved a lot of pain in trying to hack something together!
        // TODO: fix API - don't need round_number if title supplied!
        showResultDialog: function (round_number, score_table, title=null) {
            if(title === null){
                title = _("Scores for round ") + round_number;
            }
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
            console.log(scoring_dialog);
			scoring_dialog.show()
        },

        activateScoreButton: function(round_number, score_table){
            const round_button_id = `clp-round-scores-button-${round_number}`;
            console.log(round_button_id);
            $(round_button_id).onclick = (
                () => this.showResultDialog(round_number, score_table)
            );
            dojo.addClass( round_button_id, 'clp-score-button-active' );
            dojo.removeClass( round_button_id, 'clp-score-button-inactive' );
        },

        animateCalypso: function(player_id, player_suit, fresh_ranks, to_prefix="clp-calypsopile", play=true, delay=30){
            // make some modifications that we will undo at the end of the method
            // for correct animation calculation
            dojo.addClass("clp-public-area", "clp-no-transform");
            // to stop calypso cards clipping through trickpile
            // TODO: think about this mate, not sure here
            dojo.addClass(`clp-trickpile-${player_id}`, "clp-very-high");
            let animations = [];
            let anim;
            let current_delay = 0;
            for (let rank = 2; rank <= 14; rank++) {
                let card_el_id = `clp-calypsocard-${player_id}-${rank}`;
                // dojo.addClass("clp-public-area", "clp-no-transform");
                // let anim = this.slideToObject(card_el_id, `player_board_${player_id}` );
                if(!dojo.hasClass(card_el_id, "clp-calypsocard-space")){
                    anim = this.slideToObject(card_el_id, `${to_prefix}-${player_id}` );
                    dojo.connect(anim, 'onEnd', function(node) {
                        dojo.destroy(node);
                        // dojo.removeClass(`clp-trickpile-${player_id}`, "clp-very-high");
                        console.log("don't worry it has happened!");
                        // TODO: still clips a litlle, might be removed too early
                    });
                    // dojo.connect(anim, 'onEnd', function(node) {
                    //     dojo.destroy(node);
                    // dojo.removeClass("clp-public-area", "clp-no-transform");
                    //
                    // });
                } else{
                    // dummy animation so that timings still work
                    anim = dojo.animateProperty({
                        node: card_el_id,
                        properties: {}
                    });
                }
                if(play){
                    anim.play();
                } else{
                    anim.delay = current_delay;
                    current_delay += delay;
                    anim.duration = 400;
                }
                animations.push(anim);
                // dojo.removeClass("clp-public-area", "clp-no-transform");
                // let anim = this.slideToObject(card_el_id, null );
                

                // TODO: can we instead call this.setupCalypsoArea outside of loop? need to check animation
                // console.log("yeeeah boiii");
                if(fresh_ranks.includes(rank)){
                    // console.log("success " + rank);
                    dojo.place(this.format_block('jstpl_calypsocard_existing', {
                        rank : rank,
                        suit : player_suit,
                        player_id : player_id
                    }), 'clp-calypsoholder-' + player_id);
                } else{
                    // console.log("no success " + rank);
                    dojo.place(this.format_block('jstpl_calypsocard', {
                        rank : rank,
                        suit : player_suit,
                        player_id : player_id
                    }), 'clp-calypsoholder-' + player_id);
                }
            }
            dojo.removeClass("clp-public-area", "clp-no-transform");
            return animations;
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
                    console.log("it;s this");
                    console.log(this.playerHand.getItemDivId(card_id));
                    let card_div_id = this.playerHand.getItemDivId(card_id);
                    this.playerHand.unselectAll();
                    // dojo.addClass(this.playerHand.getItemDivId(card_id), "clp-selected-card");
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

        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/calypso/calypso/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your calypso.game.php file.
        
        */
        setupNotifications : function() {
            console.log('notifications subscriptions setup');

            // generic stuff, mostly for dev
            dojo.subscribe('debug', this, "notif_debug");
            dojo.subscribe('update', this, "notif_update");

            dojo.subscribe('newRound', this, "notif_newRound");
            // the actual cards that a player receives
            dojo.subscribe('newHand', this, "notif_newHand");
            // admin around hand/dealer changing
            dojo.subscribe('dealHand', this, "notif_dealHand");
        
            dojo.subscribe('playCard', this, "notif_playCard");

            dojo.subscribe('renounceFlag', this, "notif_renounceFlag");
            dojo.subscribe('clearRenounceFlags', this, "notif_clearRenounceFlags");

            dojo.subscribe( 'trickWin', this, "notif_trickWin" );
            dojo.subscribe('actionRequired', this, "notif_actionRequired");
            this.notifqueue.setSynchronous( 'trickWin', 1000 );
            dojo.subscribe( 'moveCardsToWinner', this, "notif_moveCardsToWinner" );
            this.notifqueue.setSynchronous( 'moveCardsToWinner', 600 );
            dojo.subscribe( 'moveCardsToCalypsos', this, "notif_moveCardsToCalypsos" );
            this.notifqueue.setSynchronous( 'moveCardsToCalypsos', 700 );
            dojo.subscribe( 'calypsoComplete', this, "notif_calypsoComplete" );
            
            dojo.subscribe( 'scoreDisplay', this, "notif_scoreDisplay" );
            dojo.subscribe( 'scoreUpdate', this, "notif_scoreUpdate" );
        },

        notif_newRound: function(notif) {
            const player_ids = notif.args.player_ids;
            console.log(player_ids);
            for(let player of player_ids){
                let player_id = player["id"];
                const player_count_element = `clp-info-count-${player_id}`;
                console.log(player_count_element);
                $(player_count_element).textContent = 0;
            }
            if(notif.args.round_number != 1){
                // console.log("clear calypsos...");
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
                // dojo.fx.chain(
                //     [
                //         this.clearCalypsos(player_ids),
                //         this.clearTrickPiles(player_ids)
                //     ]
                // ).play();
            }
        },

        notif_newHand : function(notif) {
            this.playerHand.removeAll();
            // refresh tooltips helps for after new-round animations
            //this.playerHand.updateDisplay();
            
            console.log(notif.args.cards);
            for ( let i in notif.args.cards) {
                let card = notif.args.cards[i];
                let suit = card.type;
                let rank = card.type_arg;
                this.playerHand.addToStockWithId(this.getCardUniqueType(suit, rank), card.id);
            }
            
            this.playerHand.updateDisplay();
        },

        notif_dealHand : function(notif) {
            // console.log("in deals");
            // console.log(notif);
            this.changeDealer(notif.args.dealer_id);
            this.updateGameStatus(notif.args.hand_number, notif.args.round_number, notif.args.total_rounds);
        },

        notif_clearRenounceFlags: function(notif) {
            // console.log("clearing houd");
            // console.log(notif);
            this.clearRenounceFlags(notif.args.players, notif.args.suits);
        },

        notif_playCard : function(notif) {
            this.playCardOnTable(notif.args.player_id, notif.args.suit, notif.args.rank, notif.args.card_id);
        },

        notif_renounceFlag: function(notif) {
            this.setRenounceFlag(notif.args.player_id, notif.args.suit);
        },

        notif_trickWin : function(notif) {
            // We do nothing here (just wait in order players can view the 4 cards played before they're gone.
            // Actually,
            // What was I about to say above ^ ????
        },
        notif_calypsoComplete : function(notif) {
            // console.log(notif.args);
            // console.log("cally");
            
            // put any cards to the new calypso in their RIGHTFUL PLACE
            // console.log("here's your man with the cards");
            // console.log(notif.args.cards_to_fresh_calypso);
            // console.log(notif.args);
            let fresh_cards = Object.values(notif.args.cards_to_fresh_calypso);
            let fresh_ranks = fresh_cards.map(card => +card["rank"]);
            // console.log(fresh_cards);
            // console.log(fresh_ranks);
            const player_id = notif.args.player_id;
            // for each card in calypso, get rid of it, but not too much
            this.animateCalypso(player_id, notif.args.player_suit, fresh_ranks, to_prefix="clp-calypsopile");
            
            const player_count_element = `clp-info-count-${player_id}`;
            const new_num_calypsos = notif.args.num_calypsos;
            // console.log(player_count_element)
            // TODO: should this be delayed/animated?
            this.setCalypsoPile(player_id, new_num_calypsos);

            // have transform woes if we try to use calypsopile
            // const anim_origin = `clp-calypsopile-${player_id}`;
            const anim_origin = "clp-table-centre";
            // dojo.addClass("game_play_area", 'clp-no-transform');
            let anim = this.slideTemporaryObject(
                this.format_block('jstpl_suiticon', {
                    trump_suit : notif.args.player_suit,
                 }),
                anim_origin,
                anim_origin,
                player_count_element
            );

            // this.placeOnObject(suit_id, 'clp-table-centre');
            // let anim = this.slideToObject(suit_id, player_count_element);
            dojo.connect(anim, 'onEnd', function(node) {
                // dojo.destroy(node);
                $(player_count_element).textContent = new_num_calypsos;
            });
            anim.duration = 300;
            anim.play();
            // just trying out chaining in principle:
            // let anim_1 = dojo.animateProperty(
            //     {
            //         node: player_count_element,
            //         properties: {
            //             fontSize: 60,
            //         }
            //     }
            // );
            // // anim_1.duration = 20000;
            // let anim_2 = dojo.animateProperty(
            //     {
            //         node: player_count_element,
            //         properties: {
            //             fontSize: 22,
            //         }
            //     }
            // );
            // // anim_2.duration = 20000;
            // let combined = dojo.fx.chain([anim_1, anim_2]);
            // console.log("combined");
            // console.log(combined);
            // // combined.duration = 30000;
            // combined.play();
            // dojo.addClass(player_count_element, "clp-score-big");
            // dojo.removeClass(player_count_element, "clp-score-big");
            // dojo.place(this.format_block('jstpl_tempscore', {
            //     new_count : new_num_calypsos
            // }), 'clp-game-info');
            // anim = this.slideToObject( "clp-new-tmp-count", player_count_element);
            // dojo.connect(anim, 'onEnd', function(node) {
            //     dojo.destroy(node);
            // });
            // anim.play();
        },
        notif_actionRequired : function(notif) {
            // nothing needed here
        },


        notif_scoreDisplay: function(notif) {
            this.showResultDialog(notif.args.round_number, notif.args.table);
            this.activateScoreButton(notif.args.round_number, notif.args.table);
            // TODO: again this should be farmed out, ideally to a generalised function?
            // see setup
            const overall_scores_button_id = 'clp-round-scores-button-overall';
            $(overall_scores_button_id).onclick = (
                () => this.showResultDialog(
                    notif.args.round_number, notif.args.overall_score, _("Round-by-round score summary"))
            );
            dojo.addClass( overall_scores_button_id, 'clp-score-button-active' );
            dojo.removeClass( overall_scores_button_id, 'clp-score-button-inactive' );
        },

        notif_scoreUpdate : function(notif) {
            // TODO here need real total, not just round total. adjust in php
            notif.args.scores.forEach(
                score_info => (
                    this.scoreCtrl[score_info.player_id].toValue(score_info.total_score)
                )
            );
        },

        notif_moveCardsToWinner : function(notif) {
            // Move all cards on table to winners' card space, ready to be sent on
            const winner_id = notif.args.winner_id;
            for ( let player_id in this.gamedatas.players) {
                let anim = this.slideToObject(
                    'clp-card-on-table-' + player_id,
                    'clp-player-card-play-area-card-' + winner_id
                );
                // dojo.connect(anim, 'onEnd', function(node) {
                //     dojo.destroy(node);
                // });
                anim.play();
            }
        },

        notif_moveCardsToCalypsos : function(notif) {
            // function finishAnim(anim) {
            //     dojo.connect(anim, 'onEnd', function(node) {
            //         dojo.destroy(node);
            //     });
            //     anim.play();
            // }
            const winner_id = notif.args.player_id;
            const moved_to = notif.args.moved_to;
            console.log(moved_to);
            for ( let player in moved_to) {
                let send_to_id = moved_to[player]["owner"];
                let send_from_id = moved_to[player]["originating_player"];
                let anim, final_func, final_args;
                // console.log(`player ${send_from_id} and what happens is ${send_to_id}`);
                
                if(send_to_id === 0){
                    // card is just going to trick pile
                    // this.placeOnObject('clp-card-on-table-' + send_from_id, 'clp-trickpile-' + winner_id);
                    anim = this.slideToObject('clp-card-on-table-' + send_from_id, 'clp-trickpile-' + winner_id);
                    dojo.connect(anim, 'onEnd', (node) => {
                        dojo.destroy(node);
                        this.setTrickPile(winner_id, 1);
                    });
                    // final_func = this.setTrickPile;
                    // final_args = [winner_id, true];
                } else{
                    // console.log("stuff to see");
                    // console.log(moved_to);
                    // card goes to the one of the winning partnerships' calypsos
                    let calypso_player_id = moved_to[player]["owner"];
                    let rank = moved_to[player]["rank"];
                    let suit = moved_to[player]["suit"];
                    let card_id = moved_to[player]["card_id"];
                    // this.placeOnObject('clp-card-on-table-' + send_from_id, `clp-calypsocard-${calypso_player_id}-${rank}`);
                    anim = this.slideToObject(
                        'clp-card-on-table-' + send_from_id,
                        `clp-calypsocard-${calypso_player_id}-${rank}`
                    );
                    dojo.connect(anim, 'onEnd', (node) => {
                        dojo.destroy(node);
                        this.placeCardInCalypso(send_to_id, suit, rank, card_id);
                    });
                    // final_func = this.placeCardInCalypso;
                    // final_args = [send_to_id, suit, rank, card_id];
                }
                // TODO: this function is what needs fiddling with - play animation after we've set flags
                // on destinations. That's backwards mate!
                // final_func(...final_args);
                
                // anim.duration = 30000;
                anim.play();
            }
            
        },
        
        notif_debug : function(notif) {
            console.log("debug message received ;)")
            // dummy
        },

        notif_update : function(notif) {
            // AB TODO: do we need to do anything here?
            // is there any point in this? Probably no
            // I imagine it will be best to split this out into individual notifications
            // e.g. newHand (animate dealer button etc)
        }
        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
   });             
});
