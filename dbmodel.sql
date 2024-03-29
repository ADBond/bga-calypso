
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Calypso implementation : © Andy Bond <48208438+ADBond@users.noreply.github.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.

-- personal trump suit - default value indicates it has not been set yet.
ALTER TABLE `player` ADD `trump_suit` varchar(16) NOT NULL DEFAULT '0';
-- how many completed calypsos so far in a round?
ALTER TABLE `player` ADD `completed_calypsos` int(1) NOT NULL DEFAULT '0';
-- info on trick-winning data
-- by leading trump, trumping in (first), overtrumping, and plain-suit
ALTER TABLE `player` ADD `tricks_won_trump_lead` int(2) NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `tricks_won_first_trump` int(2) NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `tricks_won_overtrump` int(2) NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `tricks_won_plainsuit` int(2) NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(16) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `round_scores` (
  `score_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `round_number` int(2) NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `completed_calypsos` int(1) NOT NULL,
  `calypso_incomplete` int(2) NOT NULL,
  `won_tricks` int(3) NOT NULL,
  PRIMARY KEY (`score_id`),
  FOREIGN KEY (`player_id`)
        REFERENCES `player`(`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `partnership_scores` (
  `score_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `round_number` int(2) NOT NULL,
  `partnership` ENUM('minor', 'major'),
  `score` int(5) NOT NULL,
  PRIMARY KEY (`score_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `renounce_flags` (
  `renounce_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL,
  `suit` int(1) NOT NULL,
  PRIMARY KEY (`renounce_id`),
  FOREIGN KEY (`player_id`)
        REFERENCES `player`(`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
