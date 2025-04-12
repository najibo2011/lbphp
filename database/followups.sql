-- Table pour le suivi des prospects
CREATE TABLE IF NOT EXISTS `followups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'non contacté',
  `last_status_change` datetime DEFAULT NULL,
  `last_interaction` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_profile_unique` (`user_id`, `profile_id`),
  KEY `user_id` (`user_id`),
  KEY `profile_id` (`profile_id`),
  KEY `list_id` (`list_id`),
  CONSTRAINT `followups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `followups_ibfk_2` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `followups_ibfk_3` FOREIGN KEY (`list_id`) REFERENCES `lists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
