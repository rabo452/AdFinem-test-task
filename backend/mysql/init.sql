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

-- Insert roles explicitly with ids (1 for 'admin', 2 for 'participant')
INSERT INTO roles (id, title) VALUES
    (1, 'admin'),
    (2, 'participant');

-- Insert initial admin user
INSERT INTO users (username, password, role_id)
VALUES 
    ('administrator', SHA2('administrator', 256), 1);  -- 1 refers to 'admin' role