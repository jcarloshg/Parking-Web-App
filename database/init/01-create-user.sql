CREATE USER IF NOT EXISTS 'parking_user'@'%' IDENTIFIED BY 'parking_password';
GRANT ALL PRIVILEGES ON parking_db.* TO 'parking_user'@'%';
FLUSH PRIVILEGES;
