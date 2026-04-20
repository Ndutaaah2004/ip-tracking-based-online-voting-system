-- Run if you already have students/staff tables but not elections yet:
-- mysql -u root -p voting_system < migrate_elections.sql

USE voting_system;

CREATE TABLE IF NOT EXISTS elections (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(254) NOT NULL,
  description TEXT,
  opens_at DATETIME NOT NULL,
  closes_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_election_window (opens_at, closes_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ballot_options (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  election_id INT UNSIGNED NOT NULL,
  label VARCHAR(254) NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_ballot_election FOREIGN KEY (election_id) REFERENCES elections (id) ON DELETE CASCADE,
  KEY idx_ballot_options_election (election_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS votes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  election_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  ballot_option_id INT UNSIGNED NOT NULL,
  voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_vote_student_election (election_id, student_id),
  CONSTRAINT fk_vote_election FOREIGN KEY (election_id) REFERENCES elections (id) ON DELETE CASCADE,
  CONSTRAINT fk_vote_student FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE,
  CONSTRAINT fk_vote_option FOREIGN KEY (ballot_option_id) REFERENCES ballot_options (id) ON DELETE RESTRICT,
  KEY idx_votes_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
