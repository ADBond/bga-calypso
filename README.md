# Calypso for BGA

Run this, to make life easier:

```
cp pre-commit .git/hooks/
```

## Current branch to-do list

a fluid list to remember what needs to be done before pulling in and starting a new chunk of work

* some of the css classes are names ridiculously. Once things are stable fix that.
  * ~~on that note also align on wontricks/cardswon etc - good to get it sorted earlier (!).~~
  * ~~Use Culbertson terminology - she ways 'trick pile' which I think works quite nicely~~
  * ~~on similar note, revoke flags/indicators -> renounce indicators is a nicer terminology~~
## For a different branch

* improve scoring table?
* access old scoring tables
* optional renounce flags

## To-do list

Loosely split into core logic stuff and more nicities, but obviously some overlap

### Logic

* ~~assign trump suits to players (and dealer also?)~~
* ~~implement trick-play rules~~
  * ~~implement trick-winner~~
  * ~~enforce suit-following (maybe make as switch for devving)~~
* ~~collecting calypsos (sort-of, maybe?? need to display in-progress to check better)~~
* ~~display in-progress calypsos~~ (and completed? won discards?)
* ~~track dealer etc make sure dealer is updated properly at end of hand~~
* ~~scoring~~
* number of rounds/games set via gameoptions
* team settings via gameoptions?
* stats
* anything else in [gathered list of to-dos](misc/todo_list) from code not covered by these/general tidying
* ~~Fix js bug at end of a hand that needs refresh~~

### Notifications

* Make sure I deal with the translation stuff properly, stop string concatenation etc.
  * make sure parameters use i18n etc
* Start off saying who has which trump suit, and who is partnered with whom

### Dev

* ~~hook new round/new hand stuff to setup new game so we aren't doubling up/confusing logic~~
  * partly done but needs checking/careful eye
* ~~rename those damn variables to keep a shred of self-consistency~~ check below, do as we go
  *  a bunch of stuff could be cleared up on 'type', e.g. are they _id's_, or descriptors, or what?
* ~~check that the all the cards exist at all times, and get dealt out over the course of a round~~
  * mostly okay, but needs checking
* ~~Add a separate code licence~~, and make repo public once that's done

### Display

* ~~Animate completed calypso & removing it w/o refresh~~
* ~~Little piles for won cards to be sink for any misc won cards, instead of player panels~~
  * ~~Maybe cards should move to player, and then to wherever (to make it clear who won the trick?)~~
* game art
* see last trick?
* optional log detail (c.f. Hungarian Tarock)
* ~~Nicer game area, like in e.g. GrossTarock? (have a crude version, can iterate on that in general improvements)~~
* ~~Fix up calypso displays. Maybe smaller?~~
* Set different default colours
* ~~Show who is dealer~~, ~~who dealt first in round etc. Let's not do this~~
  * ~~animate this changing?~~
* ~~Display game state info somewhere (round `x` of `y`, hand `w` of `z`)~~
  * ~~done but needs a bit of zhuzhing up~~
* ~~Say the game name somewhere? Or something distinctive. Covered under the above zhuzhing~~
* Check that there's nowt that's colourblind-unfriendly
  * e.g. black + brewer #1b9e77, #d95f02, #7570b3, or something sim. for default colours
  * want to think about how to nicely signify teams - see e.g. Phat for nice approach
* When someone wins trick, do I want to say why? (e.g. player lead their trump suit etc.)?? FFT
* ~~Renounce indicators~~
  * as a gameoption setting?
* ~~Direction stuff - should be relative to player! Don't forget to update this!~~
* Order hand with personal trumps separated?
* Fix tooltips??

### Other UI

* throw an exception if non-active player clicks on card, rather than silently failing?

### Things to test

* new round (genuine)
* multiple players complete calypsos in one trick
* a little browser compatibility

### Meta

* anything to adjust in gameinfos - particularly revise description & and tags, and durations

### Misc

* Zombie turn
* Game progression

### Variants

~~Possibly.~~

* ~~See https://boardgamegeek.com/thread/514183/game-got-lost-shuffle for a variant trick-winning rule: last trump-in wins~~
* ~~From question in that thread also: variant where you must beat leader when trumping in?~~

Not in first pass at any rate - not sure if there's much value to this, and Culbertson at least in fact agrees with usual rule.
