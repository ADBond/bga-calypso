# Calypso for BGA

For devvy stuff check out [dev notes](misc/dev.md).

## Current branch to-do list

a fluid list to remember what needs to be done before pulling in and starting a new chunk of work

* ~~optional log detail (c.f. Hungarian Tarock)~~
* Start off saying who has which trump suit, and who is partnered with whom
  * ~~teams through some light colours, and put in player boxes upstairs (with trump suits)~~
* ~~delete needless notifications~~
* ~~Make sure I deal with the translation stuff properly, stop string concatenation etc.~~
  * ~~make sure parameters use i18n etc~~
* ~~'"X" deals a new hand of cards' is not translating, and not sure why...??~~
  * ~~some others maybe also now - e.g. "... must lead a card to the first trick", and "X plays Y"~~
  * ~~doesn't like strings with "." at the end - can break new hand/new round logs with those~~
  * think one error in translation text was meaning others not going through pipeline? all cushty now tho
  * completes calypso text not right, think it's similar - check back!
* ~~is starting -> starts~~
* `Round ${round_number} score` need to sub value properly

## For a different branch

## To-do list

Loosely split into core logic stuff and more nicities, but obviously some overlap. Archived items in [an archive](misc/archive.md)

### Logic

* anything else in [gathered list of to-dos](misc/todo_list) from code not covered by these/general tidying
* player preference for auto-confirm new round

### Notifications

* Make sure I deal with the translation stuff properly, stop string concatenation etc.
  * make sure parameters use i18n etc
* Start off saying who has which trump suit, and who is partnered with whom

### Dev

* have at least a proper go-through of code to tidy at least a _little_
* ~~Add a separate code licence~~, and make repo public once that's done
* Use game-level constants in place of magic numbers where possible
* JS `placeCardInCalypso` has redundant `card_id` paramter
* `global` trick can now be killed in favour of `use` which is much nicer

### Display

* Set different default colours
* Check that there's nowt that's colourblind-unfriendly
  * e.g. black + brewer #1b9e77, #d95f02, #7570b3, or something sim. for default colours
  * ~~want to think about how to nicely signify teams - see e.g. Phat for nice approach~~
* Order hand with personal trumps separated?
* trick-pile/(calypso - ~~though think it's irrelevant here~~) show small grey bits in corners?
  * slight border round face-down cards? (to distinguish calypsopile) - needs separation above so that we don't have round corners
* get rid of ... a very long username thingy

### Images

* another display image?
* suit icons better
* placeholder calypso cards better

## Things to fix

* '"X" deals a new hand of cards' is not translating, and not sure why...??
  * some others maybe also now - e.g. "... must lead a card to the first trick", and "X plays Y"
  * doesn't like strings with "." at the end - can break new hand/new round logs with those
* final scores not updating in player boxes (round 2+)(okay on refresh)
* tooltips don't get removed once added
* Hand display buggered on mobile (think overlap is culprit)

### Other UI


### Things to test

* a little browser compatibility

### Meta

* anything to adjust in gameinfos - particularly revise description & and tags, and durations

### Misc

* Zombie turn
* Check timings
* Pre-alpha checklist

### Variants

* Seems to be enough around of 'beat the leader' to include as a variant. Maybe doesn't need to be done for alpha though
  * (i.e. trumping in to trump lead must be higher. Rule as it stands seems better to me, but nice to have both, not in too much danger of configuration hell here.)
