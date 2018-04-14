# Create a single conference with basic questions
INSERT INTO `conference` (`id`, `slug`, `name`, `description`, `notes`, `venue`, `website`, `start_date`, `end_date`, `enable_donation`, `form`, `mailman`, `enabled`, `date_created`)
VALUES
	(3, 'akademy2018', 'Akademy 2018', 'On registration, your email address will be signed up to the akademy-attendees mailing list.', NULL, 'University of Technology (TU Wien) in Vienna, Austria', 'http://akademy.kde.org/2018/', '11/8/2018', '17/8/2018', NULL, '{\n	\"forms\": [\n		{\n		  \"field\": \"Arrival Date\",\n		  \"type\": \"date\"\n		},\n		{\n		  \"field\": \"Departure Date\",\n		  \"type\": \"date\"\n		}\n	],\n	\"submitlabel\": \"Register\"\n}', 'akademy-attendees-request@kde.org', 1, '2017-05-04 21:20:34');
