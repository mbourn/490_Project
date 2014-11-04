/*CREATE DATABASE 490_db;*/

CREATE TABLE Network
(
  c_id INT NOT NULL AUTO_INCREMENT,
  c_of INT,
  f_name varchar(255),
  l_name varchar(255) NOT NULL,
  email varchar(255),
  phone1 varchar(255),
  phone2 varchar(255),
  addr varchar(255),
  twtr varchar(255),
  i_m varchar(255),
  pic_path varchar(255),
  l_url varchar(255),
  PRIMARY KEY (c_id),
  FOREIGN KEY (c_of) REFERENCES Users(u_id)
);

CREATE TABLE Users
(
  u_id INT AUTO_INCREMENT PRIMARY KEY,
  f_name varchar(255),
  l_name varchar(255)
);

/*CREATE USER 'from_web' IDENTIFIED BY 'Z!s2D#r4%';*/
GRANT INSERT, SELECT, ALTER, DELETE ON 490_db.Users TO 'from_web';
GRANT INSERT, SELECT, ALTER, DELETE ON 490_db.Network TO 'from_web';
FLUSH PRIVILEGES;

