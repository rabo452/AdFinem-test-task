-- Create the 'task' database if it doesn't exist
CREATE DATABASE IF NOT EXISTS task;

-- Use the 'task' database
USE task;

-- Create the 'roles' table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(40) NOT NULL
);

-- Create the 'users' table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(40) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Create the 'tasks' table with the user_id foreign key referencing the users table
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(40) NOT NULL,
    description VARCHAR(255),
    status INT NOT NULL,
    user_id INT,  -- Foreign key to users table
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert initial roles (admin and participant)
INSERT INTO roles (title) VALUES ('admin'), ('participant');

INSERT INTO users (username, password, role_id) 
VALUES ('admin', SHA2('admin', 256), (SELECT id FROM roles WHERE title = 'admin' LIMIT 1));