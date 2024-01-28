##
# ------
# BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
# Calypso implementation : © Andy Bond <48208438+ADBond@users.noreply.github.com>
# *
# This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
# See http://en.boardgamearena.com/#!doc/Studio for more information.
# -----
#
# translated from original gameoptions.inc.php
#
# Calypso game options description
#
# In this file, you can define your game options (= game variants).
#
# Note: If your game has no variant, you don't have to modify this file.
#
# Note²: All options defined in this file should have a corresponding "game state labels"
#        with the same ID (see "initGameStateLabels" in calypso.game.php)
#
# !! It is not a good idea to modify this file when a game is running !!
#
#

# file to create .json config files
import json


game_options = {
    # TODO: can this mesh with variant rules, or we need a separate option set?
    # in that case we want number of rounds to be 1 for logic, but hands/round is set here somewhere
    "100": {
        "name": "Game Length",
        "values": {
            "1": {
                "name": "Standard, short - 1 round (4 hands)",
                "tmdisplay": "Standard game",
            },
            "2": {
                "name": "Medium - 2 rounds (8 hands)",
                "tmdisplay": "2 rounds",
            },
            "3": {
                "name": "Longer - 3 rounds (12 hands)",
                "tmdisplay": "3 rounds",
            },
            "4": {
                "name": "Full rotation - 4 rounds (16 hands)",
                "description": "4 rounds - everyone gets to be first player once",
                "tmdisplay": "Full rotation",
            },
        },
    },
    "101": {
        "name": "Renounce indicators",
        "values": {
            "1": {
                "name": "Renounce indicators on",
                "description": "Renounce indicators show suits that players have failed to follow suit to",
            },
            "2": {
                "name": "Renounce indicators off",
                "tmdisplay": "Renounce indicators off",
            },
        },
        "level": "additional",
    },
    "102": {
        "name": "Partnerships",
        "values": {
            "4": {
                "name": "Random",
                "description": "Partnerships are allocated randomly",
            },
            "1": {
                "name": "By table order - 1st and 3rd against 2nd and 4th",
            },
            "2": {
                "name": "By table order - 1st and 2nd against 3rd and 4th",
            },
            "3": {
                "name": "By table order - 1st and 4th against 2nd and 3rd",
            },
        },
    },
    # I like it in Hungarian Tarokk as an option, so keep it here. Just like real life!
    "103": {
        "name": "Game log detail",
        "values": {
            "1": {
                "name": "All cards played are entered into the gamelog",
                "tmdisplay": "Cards played in gamelog",
            },
            "2": {
                "name": "Only trick winners are entered into the gamelog",
                "tmdisplay": "Cards played not in gamelog",
            },
        },
    },
    "104": {
        "name": "Ruleset",
        "values": {
            "1": {
                "name": "Standard",
            },
            "2": {
                "name": "Variant",
            },
        },
        "level": "major",
    },
    # only allow other deck numbers with variant rules
    "105": {
        "name": "Number of decks",
        "values": {
            "3": {
                "name": "3",
            },
            "4": {
                "name": "4",
            },
        },
        "default": 3,
        "displaycondition": [
            {
                "type": "otheroption",
                "id": 104,
                "value": 2,
            },
        ]
    },
}

# user preference options - just aesthetic stuff
game_preferences = {
    "100": {
        "name": "Table colour",
        "needReload": True,
        "values": {
            "1": {"name": "Purple", "cssPref": "clp-up-purple"},
            "2": {"name": "Yellow", "cssPref": "clp-up-yellow"},
            "3": {"name": "Red", "cssPref": "clp-up-red"},
            "4": {"name": "Green", "cssPref": "clp-up-green"},
            "5": {"name": "Blue", "cssPref": "clp-up-blue"},
            "6": {"name": "White", "cssPref": "clp-up-white"},
            "7": {"name": "Black", "cssPref": "clp-up-black"},
            "8": {"name": "Pink", "cssPref": "clp-up-pink"},
            "9": {"name": "Cyan", "cssPref": "clp-up-cyan"},
            "10": {"name": "Dark green", "cssPref": "clp-up-darkgreen"},
            "11": {"name": "Orange", "cssPref": "clp-up-orange"},
        },
        "default": 1,
    },
    "101": {
        "name": "Card face style",
        "needReload": True,
        "values": {
            "1": {"name": "Standard two-colour", "cssPref": "clp-pack-standard"},
            "2": {"name": "Four-colour", "cssPref": "clp-pack-four-colour"},
        },
        "default": 1,
    },
    "102": {
        "name": "Highlight playable cards",
        "needReload": True,
        "values": {
            "1": {"name": "Yes", "cssPref": "clp-cards-highlight-playable"},
            "2": {"name": "No", "cssPref": "clp-cards-dont-highlight-playable"},
        },
        "default": 1,
    },
}

with open("gameoptions.json", "w+") as f:
    json.dump(game_options, f, indent=4)

with open("gamepreferences.json", "w+") as f:
    json.dump(game_options, f, indent=4)
