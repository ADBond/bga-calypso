# Archive of README to-do list

### Logic

* ~~assign trump suits to players (and dealer also?)~~
* ~~implement trick-play rules~~
  * ~~implement trick-winner~~
  * ~~enforce suit-following (maybe make as switch for devving)~~
* ~~collecting calypsos (sort-of, maybe?? need to display in-progress to check better)~~
* ~~display in-progress calypsos (and completed? (separate bullet) won discards?)~~
* ~~track dealer etc make sure dealer is updated properly at end of hand~~
* ~~scoring~~
* ~~number of rounds/games set via gameoptions~~
* ~~team settings via gameoptions~~
* ~~stats~~
* ~~Fix js bug at end of a hand that needs refresh~~
* ~~stop games ending in ties~~

### Translations

* ~~Make sure I deal with the translation stuff properly, stop string concatenation etc.~~
  * ~~make sure parameters use i18n etc~~
* ~~Start off saying who has which trump suit, and who is partnered with whom~~


### Dev

* ~~hook new round/new hand stuff to setup new game so we aren't doubling up/confusing logic~~
  * ~~partly done but needs checking/careful eye~~
* ~~check that the all the cards exist at all times, and get dealt out over the course of a round~~
  * ~~mostly okay, but needs checking~~
* ~~rename those damn variables to keep a shred of self-consistency check below, do as we go~~
  *  ~~a bunch of stuff could be cleared up on 'type', e.g. are they _id's_, or descriptors, or what?~~
  * leaving this to general refactoring

### Display

* ~~game art - broken this out to its own section now mate~~
* ~~Animate completed calypso & removing it w/o refresh~~
* ~~Little piles for won cards to be sink for any misc won cards, instead of player panels~~
  * ~~Maybe cards should move to player, and then to wherever (to make it clear who won the trick?)~~
* ~~Nicer game area, like in e.g. GrossTarock? (have a crude version, can iterate on that in general improvements)~~
* ~~Fix up calypso displays. Maybe smaller?~~
* ~~Show who is dealer~~, ~~who dealt first in round etc. Let's not do this~~
  * ~~animate this changing?~~
* ~~Display game state info somewhere (round `x` of `y`, hand `w` of `z`)~~
  * ~~done but needs a bit of zhuzhing up~~
* ~~Say the game name somewhere? Or something distinctive. Covered under the above zhuzhing~~
* ~~improve scoring table?~~
* ~~access old scoring tables~~
* ~~player boxes should show number of completed calypsos~~
* ~~clear display on new round - not sure if there should be a manual 'click' or something to confirm~~
* ~~see last trick?~~
  * ~~maybe optional, as this is not possible exactly in real life, but might be useful for e.g. turn-based~~
  * not for alpha. revisit if desired at a later stage.
* ~~notifications including suit icon~~
* ~~ When someone wins trick, do I want to say why? (e.g. player lead their trump suit etc.)?? FFT~~
  * ~~will be tracking this for stats, so should be easy to add in. Probably as a log option?~~ overkill - add if requested
* ~~animations - let calypso cards & trick pile appear after animation is finished rather than early~~
* ~~get rid of ... a very long username thingy~~
* ~~Set different default colours~~
* ~~Check that there's nowt that's colourblind-unfriendly~~
  * ~~e.g. black + brewer #1b9e77, #d95f02, #7570b3, or something sim. for default colours~~
  * ~~want to think about how to nicely signify teams - see e.g. Phat for nice approach~~

### Images

* ~~Game box concept okay, just refining~~
* ~~Need something else for icon~~
  * ~~Maybe icon is fine actually~~
* ~~Banner needs completing (fining edges, proper colours exported)~~
* ~~card back(s) finalise~~
* ~~dealer icon final version needed~~

## Things to fix

* ~~Fix tooltips?? (renounce and dealer button)~~
* ~~Tooltips don't work when we dynamically switch class - they only attach to elements that have that class at the time of calling~~
* ~~Lose renounce indicators on refresh :/~~
* ~~Wipe accumulated calypsos on new round w/o refresh~~
* ~~clear display on new round - not sure if there should be a manual 'click' or something to confirm~~
* ~~multi-calypso trick means leaves copies of leftover cards in all calypsos (e.g. 2x cal + 2sp -> 2sp, 2ht in calypso. fine on refresh, so js issue, but comes through wrong in notif)~~
* ~~Fix dealer button animation~~
* ~~New cards in calypso get eaten by calypso animation - need to handle this!~~
  * ~~maybe calypso animation should head to player boxes to help signify scoring being there~~
* ~~'"X" deals a new hand of cards' is not translating, and not sure why...??~~
  * ~~some others maybe also now - e.g. "... must lead a card to the first trick", and "X plays Y"~~
  * ~~doesn't like strings with "." at the end - can break new hand/new round logs with those~~
* ~~final scores not updating in player boxes (round 2+)(okay on refresh)~~
* ~~tooltips don't get removed once added~~
* ~~Hand display buggered on mobile (think overlap is culprit)~~
* ~~hack for inter-round score table access fails for final round - i.e. can't access final round scoring on refresh~~

### Other UI

* ~~throw an exception if non-active player clicks on card, rather than silently failing?~~

### Things to test

* ~~new round (genuine)/things all work nicely in multi-round games~~
* ~~multiple players complete calypsos in one trick~~
* ~~a little browser compatibility~~ just take this as it comes now, after fixing mobile

### Meta

### Misc

* ~~Game progression~~

### Variants

~~Possibly.~~

* ~~See https://boardgamegeek.com/thread/514183/game-got-lost-shuffle for a variant trick-winning rule: last trump-in wins~~
* ~~From question in that thread also: variant where you must beat leader when trumping in?~~

~~Not in first pass at any rate - not sure if there's much value to this, and Culbertson at least in fact agrees with usual rule.~~

## Branch stuff (incomplete)

### PR 10

* ~~animations - let calypso cards & trick pile appear after animation is finished rather than early~~
  * ~~and sim for trickpile~~
  * ~~and for calypsopile - needs checking~~
  * ~~cards in calypso let's try and slip under existing cards in animation - but not worth it now if it's a huge pain (it is. park it.)~~
* ~~Fix dealer button animation - not really fixed but removed. Fine for now~~
* ~~New cards in calypso get eaten by calypso animation - need to handle this!~~
  * details to fiddle with - maybe make calypso a _teeny_ bit quicker to go off??
  * ~~ditch transformation on calypso cards before they go off? (so they go in the right direction?)~~
  * ~~calypsos to trickpile (or possibly their own _new_ space??)~~
  * ~~maybe calypso animation should head to player boxes to help signify scoring being there - parking this idea~~
* ~~tooltips for calypsopile (only when full)~~
* ~~Ditch calypso completed summaries at end of hands~~
* ~~something a little jazzier for updating score in player board? maybe - have a look at some other games~~
* ~~z-index crap? (current)~~ ~~played cards go under trickpile (even empty), and calypso (even empty). Has that always been so?~~
  * ~~certainly not new, current dev branch (9af4f0c) already has this problem, just not noticed previously~~
  * ~~animated element belongs to destination - that's where the z-index needs to be set!~~
  * ~~tidy up z-index attributes we peppered everywhere but no longer need~~
  * ~~cardontable, myhand, hand-card, cardontable. think those are it - check that animations still run smoothly when removely~~
* ~~wrong suits showing in player boxes!!~~
* ~~animate round end clearing?~~
  * ~~trickpiles not cleared~~
* ~~total scores in round-by-round~~
* ~~potentially 'secret' info in trickpile size??~~
* ~~Score tables suit icons~~
* ~~score buttons not updated on refresh while waiting for new round~~

### PR 11

* ~~optional log detail (c.f. Hungarian Tarock)~~
* ~~Start off saying who has which trump suit, and who is partnered with whom~~
  * ~~teams through some light colours, and put in player boxes upstairs (with trump suits)~~
  * ~~put all playerbox stuff in single div~~
  * ~~style that div my man~~
* ~~delete needless notifications~~
* ~~Make sure I deal with the translation stuff properly, stop string concatenation etc.~~
  * ~~make sure parameters use i18n etc~~
* ~~'"X" deals a new hand of cards' is not translating, and not sure why...??~~
  * ~~some others maybe also now - e.g. "... must lead a card to the first trick", and "X plays Y"~~
  * ~~doesn't like strings with "." at the end - can break new hand/new round logs with those~~
  * ~~think one error in translation text was meaning others not going through pipeline? all cushty now tho~~
  * ~~completes calypso text not right, think it's similar - check back!~~
* ~~score table translations - use materials ~~ Actually don't just do in js
* ~~is starting -> starts~~
* ~~`Round ${round_number} score` need to sub value properly~~
* ~~check translation stuff in score-box after round now that we've fiddled~~

### PR 12

* ~~tooltips don't get removed once added~~
* ~~Hand display buggered on mobile (think overlap is culprit)~~
  * ~~believe this is an artifact of hand height being too small - happens also on window shrink~~
* ~~hack for inter-round score table access fails for final round - i.e. can't access final round scoring on refresh~~
  * need to check, but can wait til i'm doing a job-lot
* ~~trim down (./misc/todo_list) a decent amount~~
  * started with 54 - get down to at most 30
* ~~Set different default colours~~
* ~~Check that there's nowt that's colourblind-unfriendly~~
  * ~~e.g. black + brewer #1b9e77, #d95f02, #7570b3, or something sim. for default colours~~
  * doesn't matter for player names so just keep them as-is. Checked okay (I think) for where it matters more.

