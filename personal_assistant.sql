create database personal_assistant;

use personal_assistant;

CREATE table family(
    id int primary key AUTO_INCREMENT,
    name varchar(150) UNIQUE,
    creation_date date,
    head_phone VARCHAR(15),
    family_security_code VARCHAR(255) UNIQUE NOT NULL, -- Store as hashed/encrypted
    balance DECIMAL(15,2) DEFAULT '0.00',
    financial_goal DECIMAL(15,2) DEFAULT '0.00',
    family_description text,
    profile_pic VARCHAR(100) DEFAULT 'default-pp.jpeg',
    last_login DATETIME DEFAULT NULL
    );
    

CREATE table users(
    id int primary key AUTO_INCREMENT,
    name varchar(150),
    birthdate date,
    phone VARCHAR(20) UNIQUE, -- International format
    username varchar(100) UNIQUE,
    password VARCHAR(255) NOT NULL, -- Store hashed passwords
    balance DECIMAL(15,2) DEFAULT '0.00',
    financial_goal DECIMAL(15,2) DEFAULT '0.00',
    profile_pic VARCHAR(100) DEFAULT 'default-pp.jpeg',
    family_id int,
    family_position ENUM('Dad','Mom','Son','Daughter','Grandmom','Grandpa','Nephew','Niece','Brother','Sister'),
    last_login DATETIME  DEFAULT NULL,
    
    constraint family_belonged foreign key (family_id)  references family(id) on delete set NULL);
    
CREATE TABLE family_members (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- A unique ID for each entry in this table
    family_id INT NOT NULL,  -- The ID of the family (foreign key from the 'family' table)
    user_id INT NOT NULL,    -- The ID of the user (foreign key from the 'users' table)
    join_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- The date and time when the user joined the family
    FOREIGN KEY (family_id) REFERENCES family(id) ON DELETE CASCADE,  -- Ensures family_id refers to a valid family
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,  -- Ensures user_id refers to a valid user
    UNIQUE(family_id, user_id)  -- Ensure that a user can only belong to the family once
);


CREATE table tasks(
    id int primary key AUTO_INCREMENT,
    task_name varchar(150),
    deadline datetime,
    task_duration bigint,
    task_description text,
    task_status ENUM("COMPLETED","PENDING"),
    task_assigner int,
    task_importance ENUM("Important","Ordinary") DEFAULT "Ordinary",
    task_assignee int,
    task_resource_filename VARCHAR(100) /* DEFAULT 'default-file.jpeg'*/,
    repeat_status  ENUM("Yes","No") DEFAULT "No",
    repeat_interval int,
    repeat_unit  ENUM("DAY","WEEK",'MONTH','YEAR'),
    reminder_status  ENUM("Yes","No") DEFAULT "No",
    reminder_interval datetime,
    family_id int,
    
    constraint task_assigner foreign key (task_assigner)  references users(id) on delete set NULL,
	constraint task_assignee foreign key (task_assignee)  references users(id) on delete set NULL,
    constraint family_tasks foreign key (family_id)  references family(id) on delete set NULL
    
);

create table favourite_sites(
    id int primary key AUTO_INCREMENT,
    website_name varchar(150),	
    website_url varchar(150),
    website_description text,
    website_category ENUM('Sport','News','Business','Leisure','Games','School','Work'),
    website_user int,
    family_id int,
    
    constraint website_user foreign key (website_user)  references users(id) on delete set NULL,
    constraint family_sites foreign key (family_id)  references family(id) on delete set NULL
);

CREATE table projects(
    id int primary key AUTO_INCREMENT,
    project_name varchar(150),
    deadline datetime,/*date it should be finished by*/
    project_duration bigint,
    project_description text,
    project_goals text,
    project_overseer int,
    reminder_status  ENUM("Yes","No") DEFAULT "No",
    reminder_interval datetime,
    family_id int,
    
    constraint project_overseer foreign key (project_overseer)  references users(id) on delete set NULL,
    constraint family_project foreign key (family_id)  references family(id) on delete set NULL
    
);


CREATE TABLE financial_category (
  category_id INT PRIMARY KEY AUTO_INCREMENT,
  category_name VARCHAR(20) NOT NULL,
  category ENUM('expense', 'income', 'asset', 'liability') NOT NULL, 
  category_type ENUM('Parent', 'Child') DEFAULT 'Child',
  category_user_id INT,
  family_id INT,
  parent_category_id INT NULL, -- Foreign key to relate with parent categories

  FOREIGN KEY (parent_category_id) REFERENCES financial_category(category_id) ON DELETE SET NULL,
  
  CONSTRAINT fk_category_user FOREIGN KEY (category_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_family FOREIGN KEY (family_id) REFERENCES family(id) ON DELETE SET NULL
);

 



CREATE TABLE transactions (
  transaction_id int primary key AUTO_INCREMENT,
  category_id int,
  user_id int,
  total_amount decimal(7,2),
  transaction_time datetime,
  note text,
  family_id int,
  CONSTRAINT fk_finance_category FOREIGN KEY (category_id) REFERENCES financial_category (category_id) on delete set NULL,
  CONSTRAINT transaction_handler FOREIGN KEY (user_id) REFERENCES users (id) on delete set NULL
) ;

CREATE TABLE budget (
  budget_id int primary key AUTO_INCREMENT,
  category_id int,
  user_id int,
  family_id int,
  budget_amount decimal(7,2),
  budget_month ENUM('January', 'February', 'March', 'April','May','June','July','August','September','October','November','December'),
  budget_amount_remaining decimal(7,2),
  note text,
  CONSTRAINT fk_budget_finance_category FOREIGN KEY (category_id) REFERENCES financial_category (category_id) on delete set NULL,
  CONSTRAINT budget_holder FOREIGN KEY (user_id) REFERENCES users (id) on delete set NULL,
    constraint family_budget foreign key (family_id)  references family(id) on delete set NULL
) ;

CREATE table finance_projects(
    id int primary key AUTO_INCREMENT,
    project_name varchar(150),
    deadline datetime,/*date it should be finished by*/
    project_duration bigint,
    project_description text,
    project_goals text,
    project_overseer int,
    family_id int,
    reminder_status  ENUM("Yes","No") DEFAULT "No",
    reminder_interval datetime,
    
    constraint project_handler foreign key (project_overseer)  references users(id) on delete set NULL,
    constraint family_finance_project foreign key (family_id)  references family(id) on delete set NULL
    
);


CREATE table Diary(
    id int primary key AUTO_INCREMENT,
    diary_title varchar(20),
    diary_entry text,
    entry_date date,/*date it should be finished by*/
    diary_owner int,
    
    
    constraint diary_owner foreign key (diary_owner)  references users(id) on delete set NULL
    
);

CREATE TABLE resources (
  resource_id int primary key AUTO_INCREMENT,
  item_name varchar(20),
  item_price decimal(8,2),
  user_id int,
  category_id int,
  family_id int,
  item_description text,
  personal_notes text,
  cashflow decimal(8,2) DEFAULT '0.00',
  
  CONSTRAINT fk_resource_category FOREIGN KEY (category_id) REFERENCES financial_category (category_id) on delete set NULL,
  CONSTRAINT resource_owner FOREIGN KEY (user_id) REFERENCES users (id) on delete set NULL,
    constraint family_resources foreign key (family_id)  references family(id) on delete set NULL
) ;



CREATE TABLE menu (
  dish_id int primary key AUTO_INCREMENT,
  dish_name varchar(30) ,
  dish_details json ,  -- {dish category preparation process dietary restrictions ingredientsused[name:quantity] day:,time:}
  user_id int,
  family_id int,
  
  CONSTRAINT menu_owner FOREIGN KEY (user_id) REFERENCES users (id) on delete set NULL,
    constraint family_menu foreign key (family_id)  references family(id) on delete set NULL
) ;


CREATE TABLE inventory (
  inventory_id int primary key AUTO_INCREMENT,
  ingredient_name varchar(30) DEFAULT NULL,
  price_per_unit decimal(6,2),
  user_id int,
  family_id int,
  total_quantity int DEFAULT NULL,
  
  CONSTRAINT inventory_owner FOREIGN KEY (user_id) REFERENCES users (id) on delete set NULL,
    constraint family_inventory foreign key (family_id)  references family(id) on delete set NULL
) ;



-- Scheduled Event for Repeating Tasks
DELIMITER //

CREATE EVENT repeat_tasks_event
ON SCHEDULE EVERY 1 DAY
DO 
BEGIN
    DECLARE exit handler FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;

    INSERT INTO tasks (
        task_name, 
        deadline, 
        task_duration, 
        task_description, 
        task_status, 
        task_assigner, 
        task_importance, 
        task_assignee, 
        task_resource_filename, 
        repeat_status, 
        repeat_interval, 
        repeat_unit, 
        reminder_status, 
        reminder_interval
    )
    SELECT 
        task_name,
        CASE 
            WHEN repeat_unit = 'DAY' THEN DATE_ADD(deadline, INTERVAL repeat_interval DAY)
            WHEN repeat_unit = 'WEEK' THEN DATE_ADD(deadline, INTERVAL repeat_interval WEEK)
            WHEN repeat_unit = 'MONTH' THEN DATE_ADD(deadline, INTERVAL repeat_interval MONTH)
            WHEN repeat_unit = 'YEAR' THEN DATE_ADD(deadline, INTERVAL repeat_interval YEAR)
        END AS new_deadline,
        task_duration,
        task_description,
        'PENDING' AS task_status,
        task_assigner,
        task_importance,
        task_assignee,
        task_resource_filename,
        repeat_status,
        repeat_interval,
        repeat_unit,
        reminder_status,
        CASE 
            WHEN repeat_unit = 'DAY' THEN DATE_ADD(reminder_interval, INTERVAL repeat_interval DAY)
            WHEN repeat_unit = 'WEEK' THEN DATE_ADD(reminder_interval, INTERVAL repeat_interval WEEK)
            WHEN repeat_unit = 'MONTH' THEN DATE_ADD(reminder_interval, INTERVAL repeat_interval MONTH)
            WHEN repeat_unit = 'YEAR' THEN DATE_ADD(reminder_interval, INTERVAL repeat_interval YEAR)
        END AS new_reminder_interval
    FROM tasks
    WHERE repeat_status = 'Yes' AND task_status = 'COMPLETED'
    ON DUPLICATE KEY UPDATE task_status = VALUES(task_status);

    COMMIT;
END //

DELIMITER ;
