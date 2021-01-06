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


### Dev

* ~~hook new round/new hand stuff to setup new game so we aren't doubling up/confusing logic~~
  * ~~partly done but needs checking/careful eye~~
* ~~check that the all the cards exist at all times, and get dealt out over the course of a round~~
  * ~~mostly okay, but needs checking~~

### Display

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
* When someone wins trick, do I want to say why? (e.g. player lead their trump suit etc.)?? FFT
  * will be tracking this for stats, so should be easy to add in. Probably as a log option?
* Order hand with personal trumps separated?
* improve scoring table?
* access old scoring tables
* animations - let calypso cards & trick pile appear after animation is finished rather than early
* player boxes should show number of completed calypsos
* clear display on new round - not sure if there should be a manual 'click' or something to confirm

## Things to fix

### Other UI

### Things to test

### Meta

### Misc

* ~~Game progression~~

### Variants

~~Possibly.~~

* ~~See https://boardgamegeek.com/thread/514183/game-got-lost-shuffle for a variant trick-winning rule: last trump-in wins~~
* ~~From question in that thread also: variant where you must beat leader when trumping in?~~

~~Not in first pass at any rate - not sure if there's much value to this, and Culbertson at least in fact agrees with usual rule.~~
