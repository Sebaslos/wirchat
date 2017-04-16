DROP TABLE IF EXISTS `room`;

CREATE TABLE `room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=latin1;

ALTER TABLE `room` ADD UNIQUE(`name`);

INSERT INTO `room` (`id`, `name`) VALUES (NULL, 'WebTechnologie');
INSERT INTO `room` (`id`, `name`) VALUES (NULL, 'Mathe');
INSERT INTO `room` (`id`, `name`) VALUES (NULL, 'ComputerGrafik');
INSERT INTO `room` (`id`, `name`) VALUES (NULL, 'Datenbank');
-- DROP TABLE IF EXISTS `user`;

-- CREATE TABLE `user` (
--     `id` int(11) NOT NULL AUTO_INCREMENT,
--     `name` varchar(128) NOT NULL,
--     PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=latin1;
