#!/bin/bash
# to be run in a folder with appropriately titled card image files
arg_string=""
for suit in S H C D
do
    for rank in 2 3 4 5 6 7 8 9 10 J Q K A
    do
        arg_string+="$rank$suit.png "
    done
done
echo "$arg_string"
y_size=96
x_size=$((y_size*3/4))
echo "$x_size"
echo "$y_size"
magick montage $arg_string -tile 13x4 -resize "${x_size}x${y_size}!" -geometry +0+0 cards.png
