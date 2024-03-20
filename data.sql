

-- Khởi Tạo
mysql -u root
create database mydb;
-- Sử Dụng 
use mydb;

-- Bảng danh mục công việc
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);
INSERT INTO categories (name) VALUES ('Thực Tập');
INSERT INTO categories (name) VALUES ('Thử Việc');
INSERT INTO categories (name) VALUES ('Chính Thức');
INSERT INTO categories (name) VALUES ('Hợp Đồng');

-- Bảng công việc
CREATE TABLE todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    completed TINYINT(1) DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

ALTER TABLE todos
ADD COLUMN assignee VARCHAR(255);



