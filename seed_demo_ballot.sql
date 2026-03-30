-- Optional: one open demo election with three candidates (run after schema.sql)
USE voting_system;

INSERT INTO elections (title, description, opens_at, closes_at) VALUES (
  'Demo: Student Council election',
  'Sample open ballot so you can try voting. Staff can add real elections via SQL or a future admin UI.',
  DATE_SUB(NOW(), INTERVAL 1 DAY),
  DATE_ADD(NOW(), INTERVAL 30 DAY)
);

SET @eid = LAST_INSERT_ID();

INSERT INTO ballot_options (election_id, label, sort_order) VALUES
(@eid, 'Alex Morgan', 1),
(@eid, 'Jordan Lee', 2),
(@eid, 'Sam Rivera', 3);


