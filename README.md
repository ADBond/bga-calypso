# Calypso for BGA

For devvy stuff check out [dev notes](misc/dev.md).

## Current branch to-do list

a fluid list to remember what needs to be done before pulling in and starting a new chunk of work

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
  * tidy up z-index attributes we peppered everywhere but no longer need
  * cardontable, myhand, hand-card, cardontable. think those are it - check that animations still run smoothly when removely
* ~~wrong suits showing in player boxes!!~~
* animate round end clearing?
  * ~~trickpiles not cleared~~
* ~~total scores in round-by-round~~
* ~~potentially 'secret' info in trickpile size??~~
* ~~Score tables suit icons~~
* ~~score buttons not updated on refresh while waiting for new round~~

## For a different branch

## To-do list

Loosely split into core logic stuff and more nicities, but obviously some overlap. Archived items in [an archive](misc/archive.md)

### Logic

* anything else in [gathered list of to-dos](misc/todo_list) from code not covered by these/general tidying

### Notifications

* Make sure I deal with the translation stuff properly, stop string concatenation etc.
  * make sure parameters use i18n etc
* Start off saying who has which trump suit, and who is partnered with whom

### Dev

* ~~rename those damn variables to keep a shred of self-consistency check below, do as we go~~
  *  ~~a bunch of stuff could be cleared up on 'type', e.g. are they _id's_, or descriptors, or what?~~
  * leaving this to general refactoring
  * have at least a proper go-through to tidy at least a _little_
* ~~Add a separate code licence~~, and make repo public once that's done
* Use game-level constants in place of magic numbers where possible
* JS `placeCardInCalypso` has redundant `card_id` paramter

### Display

* ~~see last trick?~~
  * ~~maybe optional, as this is not possible exactly in real life, but might be useful for e.g. turn-based~~
  * not for alpha. revisit if desired at a later stage.
* optional log detail (c.f. Hungarian Tarock)
* ~~notifications including suit icon~~
* Set different default colours
* Check that there's nowt that's colourblind-unfriendly
  * e.g. black + brewer #1b9e77, #d95f02, #7570b3, or something sim. for default colours
  * ~~want to think about how to nicely signify teams - see e.g. Phat for nice approach~~
  * teams through some light colours, and put in player boxes upstairs (with trump suits)
* ~~ When someone wins trick, do I want to say why? (e.g. player lead their trump suit etc.)?? FFT~~
  * ~~will be tracking this for stats, so should be easy to add in. Probably as a log option?~~ overkill - add if requested
* Order hand with personal trumps separated?
* ~~animations - let calypso cards & trick pile appear after animation is finished rather than early~~
* trick-pile/(calypso - ~~though think it's irrelevant here~~) show small grey bits in corners?
  * slight border round face-down cards? (to distinguish calypsopile) - needs separation above so that we don't have round corners

### Images

* ~~Game box concept okay, just refining~~
* ~~Need something else for icon~~
  * ~~Maybe icon is fine actually~~
* ~~Banner needs completing (fining edges, proper colours exported)~~
* another display image?
* ~~card back(s) finalise~~
* suit icons better
* placeholder calypso cards better
* ~~dealer icon final version needed~~

## Things to fix

* ~~Fix dealer button animation~~
* ~~New cards in calypso get eaten by calypso animation - need to handle this!~~
  * ~~maybe calypso animation should head to player boxes to help signify scoring being there~~
* '"X" deals a new hand of cards' is not translating, and not sure why...??
  * some others maybe also now - e.g. "... must lead a card to the first trick", and "X plays Y"
  * doesn't like strings with "." at the end - can break new hand/new round logs with those
* final scores not updating in player boxes (round 2+)(okay on refresh)

### Other UI

* ~~throw an exception if non-active player clicks on card, rather than silently failing?~~

### Things to test

* ~~new round (genuine)/things all work nicely in multi-round games~~
* multiple players complete calypsos in one trick
* a little browser compatibility

### Meta

* anything to adjust in gameinfos - particularly revise description & and tags, and durations

### Misc

* Zombie turn

### Variants

* Seems to be enough around of 'beat the leader' to include as a variant. Maybe doesn't need to be done for alpha though
  * (i.e. trumping in to trump lead must be higher. Rule as it stands seems better to me, but nice to have both, not in too much danger of configuration hell here.)
