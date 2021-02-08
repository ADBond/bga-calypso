# Calypso for BGA

For devvy stuff check out [dev notes](misc/dev.md).

## Current branch to-do list

a fluid list to remember what needs to be done before pulling in and starting a new chunk of work

* tooltips don't get removed once added
* Hand display buggered on mobile (think overlap is culprit)
* hack for inter-round score table access fails for final round - i.e. can't access final round scoring on refresh
* trim down (misc/todo_list) a decent amount
* Set different default colours
* ~~Check that there's nowt that's colourblind-unfriendly~~
  * ~~e.g. black + brewer #1b9e77, #d95f02, #7570b3, or something sim. for default colours~~
  * doesn't matter for player names so just keep them as-is. Checked okay (I think) for where it matters more.

## For a different branch

## To-do list

Loosely split into core logic stuff and more nicities, but obviously some overlap. Archived items in [an archive](misc/archive.md)

### Logic

* anything else in [gathered list of to-dos](misc/todo_list) from code not covered by these/general tidying
* player preference for auto-confirm new round
  * not for alpha

### Notifications


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
  * NFA

### Images

* another display image?
* suit icons better
* placeholder calypso cards better

## Things to fix

* tooltips don't get removed once added
* Hand display buggered on mobile (think overlap is culprit)
* hack for inter-round score table access fails for final round - i.e. can't access final round scoring on refresh

### Other UI


### Things to test

* ~~a little browser compatibility~~ just take this as it comes now, after fixing mobile

### Meta

* anything to adjust in gameinfos - particularly revise description & and tags, and durations

### Misc

* Zombie turn
* Check timings
* Pre-alpha checklist

### Variants

* Seems to be enough around of 'beat the leader' to include as a variant. Maybe doesn't need to be done for alpha though
  * (i.e. trumping in to trump lead must be higher. Rule as it stands seems better to me, but nice to have both, not in too much danger of configuration hell here.)

## Browser compatibility

Checked:

* Firefox (85.0.1)
* Edge (88.0.705.63)
* Chrome (88.0.4324.150)
