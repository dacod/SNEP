update groups set name='administrator' where name = 'admin';
ALTER TABLE  `trunks` ADD  `domain` VARCHAR( 250 ) NOT NULL