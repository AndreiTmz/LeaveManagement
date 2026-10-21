-- Seed assumes fresh DB with auto-increment starting from 1

-- DEPARTMENTS
INSERT INTO departments (id, name) VALUES
(1, 'Administration'),
(2, 'Finance'),
(3, 'HR'),
(4, 'IT'),
(5, 'Legal'),
(6, 'Marketing'),
(7, 'Operations'),
(8, 'Sales'),
(9, 'Support');

-- POSITIONS
INSERT INTO positions (id, name) VALUES
(1, 'Junior Developer'),
(2, 'Senior Developer'),
(3, 'Team Lead'),
(4, 'HR Specialist'),
(5, 'Finance Analyst'),
(6, 'Sales Executive');

-- LEAVE TYPES
INSERT INTO leave_types (id, name) VALUES
(1, 'Annual Leave'),
(2, 'Sick Leave'),
(3, 'Unpaid Leave');

-- LEAVE RULES
INSERT INTO leave_rules (position_id, leave_type_id, max_days_per_year, replacement_required) VALUES
-- Junior Developer
(1, 1, 21, 0),
(1, 2, 10, 0),
(1, 3, 30, 0),

-- Senior Developer
(2, 1, 25, 1),
(2, 2, 10, 0),
(2, 3, 30, 0),

-- Team Lead
(3, 1, 30, 1),
(3, 2, 12, 0),
(3, 3, 30, 0),

-- HR Specialist
(4, 1, 24, 1),
(4, 2, 10, 0),
(4, 3, 30, 0),

-- Finance Analyst
(5, 1, 23, 1),
(5, 2, 10, 0),
(5, 3, 30, 0),

-- Sales Executive
(6, 1, 20, 0),
(6, 2, 8, 0),
(6, 3, 30, 0);

-- EMPLOYEES
INSERT INTO employees
(first_name, last_name, email, username, password_hash, position_id, department_id, manager_id, created_at)
VALUES

-- ADMINISTRATION (Dept 1)
('Oliver', 'Bennett', 'oliver.bennett@company.com', 'obennett', '$2y$12$fTbmEEQYrPMdw0eQjcDwuOKX5n/pQMfXxz9kqCICr2RjCGIs2SwpS', 3, 1, NULL, NOW()),
('Amelia', 'Carter', 'amelia.carter@company.com', 'acarter', '$2y$12$KULCrij4oY63GKoSwbnSCu0F.nJol31n.CrkssV.OGLIAXnCTa/4K', 1, 1, 1, NOW()),
('Henry', 'Walker', 'henry.walker@company.com', 'hwalker', '$2y$12$JjLRkmjxioWgZe6iwRgPte8zEamT8EjfDD2tmwoNI1/Mp2YpXm.YO', 2, 1, 1, NOW()),

-- FINANCE (Dept 2)
('Charlotte', 'Hayes', 'charlotte.hayes@company.com', 'chayes', '$2y$12$bq4fJ6QXiC.aVF0BTfOHp.LIIXoFwu1gWLV7YN9NYHbqwDnyflbFu', 5, 2, NULL, NOW()),
('Ethan', 'Brooks', 'ethan.brooks@company.com', 'ebrooks', '$2y$12$DTNOzvypo734T67ykZPVF.U4G5HyiXvy0JBJLZmajSdOZKGXRzqX6', 1, 2, 4, NOW()),
('Grace', 'Foster', 'grace.foster@company.com', 'gfoster', '$2y$12$fqq9Rabn7skpuBAX7.NYSebIz5XmcTWquH47wlrmCT8Nep9NOu/VC', 2, 2, 4, NOW()),

-- HR (Dept 3)
('Sophia', 'Morgan', 'sophia.morgan@company.com', 'smorgan', '$2y$12$WGoiZ92pX/ZGGP6trncCxOsk4USkBe634.Dy3UvNlaztlXodMvaOy', 4, 3, NULL, NOW()),
('Lucas', 'Perry', 'lucas.perry@company.com', 'lperry', '$2y$12$TufS.PwsUDrcGQa2gNeOJegw8YsXhdkY0D1.RTJB33CPC7ZSPtsgq', 1, 3, 7, NOW()),
('Natalie', 'Price', 'natalie.price@company.com', 'nprice', '$2y$12$zmKvo4smXNj0aZXxjYTxQeLXtnQmT8qRbCUnNENcBMkxaiWnHZCsq', 2, 3, 7, NOW()),

-- IT (Dept 4)
('James', 'Cooper', 'james.cooper@company.com', 'jcooper', '$2y$12$EMOxZSOrTH7JCEBcHntIM.YIwB0K5FnUQrmJiTiBnfH4KbKdyLOLq', 3, 4, NULL, NOW()),
('Ava', 'Richardson', 'ava.richardson@company.com', 'arichardson', '$2y$12$ozArEdumVlsdChaoVO2cIunvL/NyQ4gzusdBm9ufAusF0qiNbOFIy', 1, 4, 10, NOW()),
('Benjamin', 'Ward', 'benjamin.ward@company.com', 'bward', '$2y$12$BoRvTH5xWMOXWWAGdGOEZuht6ng20BKwqXnIcta1g0j0s11b5/FBu', 2, 4, 10, NOW()),

-- LEGAL (Dept 5)
('Emily', 'Hughes', 'emily.hughes@company.com', 'ehughes', '$2y$12$2ED3QkqTMA2iRv.Fe8g6ZOJR8Xs3IRC.7KwQKC/oqzJ2EW4o4kp16', 4, 5, NULL, NOW()),
('Samuel', 'Jenkins', 'samuel.jenkins@company.com', 'sjenkins', '$2y$12$SR5fmrEf3nWLCyBC0BCEA.LoUlHma8ZHYF4JrPndedKUaPmcjXJEu', 1, 5, 13, NOW()),
('Chloe', 'Sanders', 'chloe.sanders@company.com', 'csanders', '$2y$12$K3OCIu1P6ALg1x6lfucAZOzLFFZDOfZR5nUmuMeDuv0ay0wN6zbo.', 2, 5, 13, NOW()),

-- MARKETING (Dept 6)
('Matthew', 'Ross', 'matthew.ross@company.com', 'mross', '$2y$12$jW7qUlU9up1a.nNHi6GyaOtoaG5ToVsUEW4wSl0VoFSHsPbGbRhZu', 6, 6, NULL, NOW()),
('Lily', 'Powell', 'lily.powell@company.com', 'lpowell', '$2y$12$Fw0HhP6GZ91vrCWqwYYufO.aBKPc6dNg49gyIkKmC4EWqnJCvrrPm', 1, 6, 16, NOW()),
('Alexander', 'Long', 'alexander.long@company.com', 'along', '$2y$12$4R3uSlkFxlhR/kkKNjni1OLUqJIi4yHoOFo8EjQM7otg/JdxQS.4u', 2, 6, 16, NOW()),

-- OPERATIONS (Dept 7)
('Victoria', 'Butler', 'victoria.butler@company.com', 'vbutler', '$2y$12$mW/cxXLx589daHP1apWc9u1jOnrzHUIyrPHvJSv6tdsfDWbGfy7Y.', 3, 7, NULL, NOW()),
('Daniel', 'Murphy', 'daniel.murphy@company.com', 'dmurphy', '$2y$12$HgcAoWadK.amSU/O4CKF4OQ2Q0s171XazYqA.xMeUi4MFSD/B6ony', 1, 7, 19, NOW()),
('Zoe', 'Simmons', 'zoe.simmons@company.com', 'zsimmons', '$2y$12$46dGHLJCauV2ORrGMzjOxuZ7X49vLQ0iuTy6OxmwhY4wE8AZQCl4.', 2, 7, 19, NOW()),

-- SALES (Dept 8)
('Christopher', 'Bailey', 'christopher.bailey@company.com', 'cbailey', '$2y$12$qRHuCa8uu3YpuXx.eCM/7.ei5F6hPJ3KMwSuPkNSZ1A6cIwT8Ln.y', 6, 8, NULL, NOW()),
('Hannah', 'Coleman', 'hannah.coleman@company.com', 'hcoleman', '$2y$12$cfRrcAeNZiaeEU8K06dWOuk3emXrg6IrJq.Pw8upidl9DeDz7zFnS', 1, 8, 22, NOW()),
('Jack', 'Peterson', 'jack.peterson@company.com', 'jpeterson', '$2y$12$8f3UdiuicxMTtyew3y6Rxefiu5hYrleqh21EUDpKeBqRdT4LrV0za', 2, 8, 22, NOW()),

-- SUPPORT (Dept 9)
('Madison', 'Reed', 'madison.reed@company.com', 'mreed', '$2y$12$NfVPdum5I5oHKtcCOjsKku5xXyg0q9IF4eJLc5GBPqCEh00d7s9ai', 3, 9, NULL, NOW()),
('Ryan', 'Kelly', 'ryan.kelly@company.com', 'rkelly', '$2y$12$lql0KMK36nlYdojqeuvezOQNXz5qc0ijDsuvFuJxu2GStZpg3o992', 1, 9, 25, NOW()),
('Abigail', 'Turner', 'abigail.turner@company.com', 'aturner', '$2y$12$4e.5IXij6TkFAN16STdYGuTnW/Bjq.OYcnBZVpnx8V7XTiiOxvqtq', 2, 9, 25, NOW());
