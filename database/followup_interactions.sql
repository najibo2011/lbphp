-- Table pour les interactions de suivi
CREATE TABLE IF NOT EXISTS `followup_interactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `followup_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `notes` text,
  `interaction_date` date NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `followup_id` (`followup_id`),
  CONSTRAINT `followup_interactions_ibfk_1` FOREIGN KEY (`followup_id`) REFERENCES `followups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
