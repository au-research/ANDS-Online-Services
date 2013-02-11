USE `dbs_portal`;

CREATE TABLE `search_result_counts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `occurrence` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  `search_term` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1

