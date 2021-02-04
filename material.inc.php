<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Calypso implementation : © Andy Bond <48208438+ADBond@users.noreply.github.com>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * Calypso game material description
 *
 */

$this->suits = array(
  1 => array( 'name' => clienttranslate('spade'),
              'nametr' => self::_('spade') ),
  2 => array( 'name' => clienttranslate('heart'),
              'nametr' => self::_('heart') ),
  3 => array( 'name' => clienttranslate('club'),
              'nametr' => self::_('club') ),
  4 => array( 'name' => clienttranslate('diamond'),
              'nametr' => self::_('diamond') )
);

$this->ranks_label = array(
  2 => '2',
  3 => '3',
  4 => '4',
  5 => '5',
  6 => '6',
  7 => '7',
  8 => '8',
  9 => '9',
  10 => '10',
  11 => clienttranslate('J'),
  12 => clienttranslate('Q'),
  13 => clienttranslate('K'),
  14 => clienttranslate('A')
);

// $this->score_labels = array(
//   "calypso_count",
//   "incomplete_calypso_count",
//   "trickpile_count",
//   "individual_score",
//   "partnership_score",

// )
