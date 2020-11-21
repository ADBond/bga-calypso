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

#### logic bug

Resolved? triple-check

Logic error here:

```
AndyB3 wins the trick
AndyB3 [heart] plays 9 heart
AndyB1 [club] plays 7 club
AndyB0 [spade] plays 2 club
AndyB2 [diamond] plays K heart
```

Should be fixed now, but worth trying to recreate to check

#### dealer crap

~~Everyone is getting double cards right now~~

#### hand end/setup mismatch

Something goes wrong at the end of the hand, related to stock (js) - fine on refresh though

#### dealer ordering

Right now dealer leads to first trick, not forehand. (or rather am not correctly setting first dealer :/)

### player box

See e.g. backgammon, 99 (rounds-based), Jaipur (seal of excellence) for games that have extra info in the player panels. How?

* https://en.doc.boardgamearena.com/Counter#Adding_stuff_to_player.27s_panel
