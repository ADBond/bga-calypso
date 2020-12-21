# Miscellaneous notes

The code is already littered with all sorts of rubbish, so more general reference/notey stuff can go here

## PHP

Some simple reminders as I'm new to PHP. Hoping typing these things out will mean I'll never actually have to refer to this.
Undoubtably will butcher some subtleties here.

### `self` vs `this`

`self` is for accessing static methods within a class, whereas `$this` is for accessing member variables.
If you think about the dollar sign (or lack thereof) then this makes sense, really.

### Values

Don't expect a big deal if your variable is not set (or e.g. mistyped) - expect a mysterious error.

## BGA

### Debugging

~~Not yet got this working in a useful way.~~ Working, though inelegant. Should re-read docs for better version

### Suit mapping

I don't know if this is a standard convention, but it's the one from the Hearts tutorial,
and there's nothing in this game that warrants using my own system.

Might be nice to refactor so the code is more readable and these values are hidden, but that's not a 'now' job.

1. Spades
2. Hearts
3. Clubs
4. Diamonds

### bugs

#### calypso completed

Not cleared after completing, needs refresh. Think I've already noted this somewhere

#### calypso z-index

~~The z-index is going crazy high, and thus overlapping popups! (obviously string/numeric issue)~~

#### calypso display

~~some cards are showing up as 2's only - this is when cards are captured, not on setup display -refresh clears it. maybe only duplicate cards?? probs not tho

all in round 2??
    -- okay this is if I do the func = X, args = y, blah blah, X(...y); thing. so don't do that~~


#### trick pile

~~Showing up fine i think when we win tricks, but on refresh (i.e. `setupGame`) then _anyone_ who's won a trick has a pile, even if all their cards went to calypsos. That is _literally_ incorrect.~~
