# Calypso for BGA

## TODO list

Loosely split into core logic stuff and more nicities, but obviously some overlap

### Logic

* ~~assign trump suits to players~~ (and dealer also?)
* ~~implement trick-play rules~~
  * ~~implement trick-winner~~
  * ~~enforce suit-following (maybe make as switch for devving)~~
* ~~collecting calypsos (sort-of, maybe?? need to display in-progress to check better)~~
* ~~display in-progress calypsos~~ (and completed? won discards?)
* track dealer etc
* scoring
* number of rounds/games set via gameoptions
* stats
* any other TODOs from code not covered by these/general tidying
* Fix js bug at end of a hand that needs refresh

### Dev

* hook new round/new hand stuff to setup new game so we aren't doubling up/confusing logic
* rename those damn variables to keep a shred of self-consistency
* check that the all the cards exist at all times, and get dealt out over the course of a round

### Display

* Animate completed calypso & removing it w/o refresh
* game art
* Nicer game area, like in e.g. GrossTarock?
* Set different default colours
* Show who is dealer, who dealt first in round etc.
  * animate this changing?

### Meta

* anything to adjust in gameinfos - particularly revise description & and tags, and durations
