#!/bin/bash

FILENAME=cards.webp
# FILENAME=cards_four.webp

y_size=96
x_size=$((y_size*3/4))
echo "$x_size"
echo "$y_size"
mkdir -p misc/tmp
# get individual card images row-by-row (=suit-by-suit, A-K + backs, jokers)
magick misc/img_raw/$FILENAME -crop "${x_size}x${y_size}" misc/tmp/card%01d.webp

arg_string=""
# easiest to manually concat the suits
# Ace positions
# +1 to get the 2s, where we want to start
SPADES_START=$((14+1))
HEARTS_START=$((28+1))
CLUBS_START=$((0+1))
DIAMONDS_START=$((42+1))
for img_index in $( seq $SPADES_START $((SPADES_START+12)) ) \
    $( seq $HEARTS_START $((HEARTS_START+12)) ) \
    $( seq $CLUBS_START $((CLUBS_START+12)) ) \
    $( seq $DIAMONDS_START $((DIAMONDS_START+12)) )
do
    # if we are on a back/joker replace with ace
    if [ $(( img_index % 14 )) -eq 13 ]; then
        ((img_index-=13))
    fi
    img_file="misc/tmp/card${img_index}.webp"
    arg_string+="${img_file} "
done
# echo $arg_string
magick montage $arg_string -tile 13x4 -resize "${x_size}x${y_size}!" -geometry +0+0 img/$FILENAME
rm -r misc/tmp
