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
            console.log( "Starting game setup" );
            
            // Setting up player boards
            for( let player_id in gamedatas.players )
            {
                let player = gamedatas.players[player_id];
                         
                // TODO: Setting up players boards if needed
                let player_trump = player["trump_suit"];
                let trump_lookup = {
                    1: "spades", 2: "hearts", 3: "clubs", 4: "diamonds"
                };
                // TODO: this should be a nice little icon rather than text.
                $("trump-" + player_id).textContent = trump_lookup[player_trump];

                console.log("dealer is:")
                console.log(gamedatas.dealer);
                if(player_id == gamedatas.dealer){
                    //$("area-dealer-" + player_id).textContent = "(D)";  // TODO: remove? or double up?
                    let dealer_area_id = 'dealer-' + player_id;
                    dojo.place(this.format_block('jstpl_dealerindicator', {
                        player_id : player_id
                    }), dealer_area_id);
                    this.addTooltipHtml( "dealerbutton", _( "This player is the dealer for this hand" ) )
                } else{
                    $("area-dealer-" + player_id).textContent = "";
                }
                
            }
            
            // TODO: Set up your game interface here, according to "gamedatas"
            // Player hand
            this.playerHand = new ebg.stock(); // new stock object for hand
            this.playerHand.create( this, $('myhand'), this.cardwidth, this.cardheight );

            // TODO: this whole bit may need some thinking about for quad-deck:
            this.playerHand.image_items_per_row = 13; // 13 images per row
            // Create cards types:
            const num_decks = 4;  // this will be four later, but let's not go to quickly
            for (let suit = 1; suit <= 4; suit++) {
                for (let rank = 2; rank <= 14; rank++) {
                    for (let deck = 1; deck <= num_decks; deck++){
                        // Build card type id
                        let card_type_id = this.getCardUniqueId(suit, rank, deck);
                        let card_type = this.getCardUniqueType(suit, rank);
                        // args are id, weight (for hand-sorting), img url, and img position
                        // Not sure for the moment if it is important for ids to be distinct here,
                        // but a sensible default answer seems to be 'yes'
                        this.playerHand.addItemType(card_type_id, card_type, g_gamethemeurl + 'img/cards.jpg', card_type);
                    }
                }
            }
            // TODO: center hand in the div if wanted:
            this.playerHand.centerItems = true;

            dojo.connect( this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged' );

            // Cards in player's hand
            for ( let i in this.gamedatas.hand) {
                let card = this.gamedatas.hand[i];
                let suit = card.type;
                let rank = card.type_arg;
                this.playerHand.addToStockWithId(this.getCardUniqueType(suit, rank), card.id);
            }

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

            this.updateGameStatus(this.gamedatas.handnumber, this.gamedatas.roundnumber, this.gamedatas.totalrounds);
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            
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
/*               
                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
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

        playCardOnTable : function(player_id, suit, rank, card_id) {
            // player_id => direction
            dojo.place(this.format_block('jstpl_cardontable', {
                // these ranks relate to getting the right card from sprite
                x : this.cardwidth * (rank - 2),
                y : this.cardheight * (suit - 1),
                player_id : player_id
            }), 'playertablecard_' + player_id);

            if (player_id != this.player_id) {
                // Some opponent played a card
                // Move card from player panel
                this.placeOnObject('cardontable_' + player_id, 'overall_player_board_' + player_id);
            } else {
                // You played a card. If it exists in your hand, move card from there and remove
                // corresponding item

                if ($('myhand_item_' + card_id)) {
                    this.placeOnObject('cardontable_' + player_id, 'myhand_item_' + card_id);
                    this.playerHand.removeFromStockById(card_id);
                }
            }

            // In any case: move it to its final destination
            this.slideToObject('cardontable_' + player_id, 'playertablecard_' + player_id).play();
        },

        placeCardInCalypso : function(player_id, suit, rank, card_id) {
            let x = this.cardwidth * (rank - 2);
            let y = this.cardheight * (suit - 1);

            let card_el_id = `calypsocard_${player_id}_${rank}`;
            console.log(card_el_id);

            // TODO: this should stay in css - use class manipulation
            dojo.style(card_el_id,
                {
                    'backgroundPosition': `-${x}px -${y}px`,
                    'z-index': `${rank + 14}`,
                }
            )
            dojo.addClass( card_el_id, 'cardincalypso' );  // TODO: this in .tpl file??

        },

        changeDealer : function(new_dealer_id) {
            const new_dealer_area_id = 'dealer-' + new_dealer_id;

            console.log("dealer posirtion gows to: ", new_dealer_area_id);
            this.slideToObject('dealerbutton', new_dealer_area_id).play();
        },

        updateGameStatus: function(handnumber, roundnumber, totalrounds) {
            console.log("update that banner!");
            console.log("have hand " + handnumber + " and round " + roundnumber + " of total " + totalrounds);
            // TODO: do I want to markup any of this for styling?
            $("clp-gameinfo").innerHTML =  dojo.string.substitute(
                _("Calypso: Round ${roundnumber} of ${totalrounds}, hand ${handnumber} of 4."),
                {
                    roundnumber: roundnumber,
                    handnumber: handnumber,
                    totalrounds: totalrounds,
                } 
            );
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

            if (items.length > 0) {
                let action = 'playCard';
                if (this.checkAction(action, true)) {
                    // Can play a card
                    let card_id = items[0].id;
                    this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/" + action + ".html", {
                        id: card_id,
                        lock: true
                    }, this, function (result) {
                    }, function (is_error) {
                    });

                    this.playerHand.unselectAll();
                } else {
                    this.playerHand.unselectAll();
                }
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

            // the actual cards that a player receives
            dojo.subscribe('newHand', this, "notif_newHand");
            // admin around hand/dealer changing
            dojo.subscribe('dealHand', this, "notif_dealHand");
        
            dojo.subscribe('playCard', this, "notif_playCard");

            dojo.subscribe( 'trickWin', this, "notif_trickWin" );
            dojo.subscribe('actionRequired', this, "notif_actionRequired");
            this.notifqueue.setSynchronous( 'trickWin', 1000 );
            dojo.subscribe( 'moveCardsToCalypsos', this, "notif_moveCardsToCalypsos" );
            dojo.subscribe( 'calypsoComplete', this, "notif_calypsoComplete" );
            dojo.subscribe( 'scoreUpdate', this, "notif_scoreUpdate" );
        },

        notif_newHand : function(notif) {
            this.playerHand.removeAll();
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
            // TODO: animate the dealer button moving here
            console.log("in deals");
            console.log(notif);
            this.changeDealer(notif.args.dealer_id);
            this.updateGameStatus(notif.args.hand_number, notif.args.round_number, notif.args.total_rounds);
        },

        notif_playCard : function(notif) {
            this.playCardOnTable(notif.args.player_id, notif.args.suit, notif.args.rank, notif.args.card_id);
        },

        notif_trickWin : function(notif) {
            // We do nothing here (just wait in order players can view the 4 cards played before they're gone.
            // Actually,
            // What was I about to say above ^ ????
        },
        notif_calypsoComplete : function(notif) {
            // TODO: Here we should animate removing all those cumbersome calypso cards, ready to start anew!
            // maybe best to do in a layout ting as may need to refactor some of that stuff :/
        },
        notif_actionRequired : function(notif) {
            // nothing needed here
        },

        notif_scoreUpdate : function(notif) {
            console.log(notif.args.scores);
            notif.args.scores.forEach(
                score_info => (
                    this.scoreCtrl[score_info.player_id].toValue(score_info.total_score)
                )
            );
        },

        // This is what happens after trick - we need to modify!
        obsolete_notif_giveAllCardsToPlayer : function(notif) {
            // Move all cards on table to given table, then destroy them
            const winner_id = notif.args.player_id;
            for ( let player_id in this.gamedatas.players) {
                let anim = this.slideToObject('cardontable_' + player_id, 'overall_player_board_' + winner_id);
                dojo.connect(anim, 'onEnd', function(node) {
                    dojo.destroy(node);
                });
                anim.play();
            }
        },
        notif_moveCardsToCalypsos : function(notif) {
            // Move all cards on table to given table, then destroy them
            const winner_id = notif.args.player_id;
            const moved_to = notif.args.moved_to;
            console.log(moved_to);
            for ( let player in moved_to) {
                let send_to_id = moved_to[player]["owner"];
                let send_from_id = moved_to[player]["originating_player"];
                let anim;
                console.log(`player ${send_from_id} and what happens is ${send_to_id}`)
                if(send_to_id === 0){
                    console.log("this card is rubbish");
                    // TODO: fix this!! send_ti_id is null here, obviously
                    anim = this.slideToObject('cardontable_' + send_from_id, 'overall_player_board_' + winner_id);
                } else{
                    console.log("but this card goes to the special place")
                    let calypso_player_id = moved_to[player]["owner"];
                    let rank = moved_to[player]["rank"];
                    let suit = moved_to[player]["suit"];
                    let card_id = moved_to[player]["card_id"];
                    anim = this.slideToObject('cardontable_' + send_from_id, `calypsocard_${calypso_player_id}_${rank}`);
                    this.placeCardInCalypso(send_to_id, suit, rank, card_id);
                }
                dojo.connect(anim, 'onEnd', function(node) {
                    dojo.destroy(node);
                });
                anim.play();
            }
        },
        // TODO: from this point and below, you can write your game notifications handling methods
        
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
