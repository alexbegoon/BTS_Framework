CREATE TABLE `email_blocked` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `email_address` varchar(256) NOT NULL,
 `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `email_messages` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `user_id` int(11) DEFAULT NULL,
 `sender_name` varchar(150) DEFAULT NULL,
 `sender_email` varchar(150) DEFAULT NULL,
 `subject` varchar(256) NOT NULL,
 `message` mediumtext NOT NULL,
 `html` tinyint(1) unsigned NOT NULL DEFAULT '0',
 `sent_count` int(11) NOT NULL DEFAULT '0',
 `processing` tinyint(1) NOT NULL DEFAULT '0',
 `tracking` tinyint(2) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `email_queue` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `message_id` int(11) NOT NULL,
 `message_type` tinyint(2) NOT NULL DEFAULT '1',
 `recipient_email` varchar(256) NOT NULL,
 `recipient_name` varchar(150) DEFAULT NULL,
 `message_data` mediumtext NOT NULL,
 `sent` tinyint(1) NOT NULL DEFAULT '0',
 `unique_id` varchar(50) NOT NULL,
 `email_id` varchar(64) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `email_tracking` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `message_id` int(11) NOT NULL,
 `queue_id` int(11) NOT NULL DEFAULT '0',
 `action_type` varchar(16) NOT NULL,
 `ip` varchar(16) NOT NULL,
 `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 UNIQUE KEY `IDX_UNIQUE_TRACK` (`queue_id`,`action_type`,`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

