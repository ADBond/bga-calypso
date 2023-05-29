#!/bin/bash
# definitely worthwhile making this _another_ bash script to fill up this folder

# order L->R is same as order in user-prefs

# better to do this less manually, but let's consider that an upgrade
magick montage misc/img_raw/card_suits_small.png misc/img_raw/card_suits_small_four.png -background none \
    -tile 2x1 -geometry +0+0 img/card_suits.png
magick montage misc/img_raw/cards_layout.png misc/img_raw/cards_layout_four.png -background none \
    -tile 2x1 -geometry +0+0 img/cards_layout.png
