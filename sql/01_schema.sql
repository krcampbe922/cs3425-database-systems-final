drop table if exists employee;
create table employee(
    employee_id int(3) unique not null PRIMARY KEY auto_increment,
    username varchar(15),
    email char(20) not null,
    password_hash varchar(64) comment 'SHA-256 password hash',
    is_password_temp boolean default true
);

drop table if exists category;
create table category(
	category_name char(15) unique not null PRIMARY KEY,
    category_description varchar(40)
);

drop table if exists product;
create table product(
	product_id int(4) unique not null PRIMARY KEY auto_increment,
    product_name varchar(15) not null,
    product_description varchar(40),
    price decimal(6, 2) not null check (price >= 0),
    advising_threshold int(2) not null check (advising_threshold >= 0),
    actual_stock int(4) default 0 check (actual_stock >= 0),
    image varchar(255),
    discontinued boolean default false,
    category_name char(15) references category(category_name)
		on delete set null
        on update cascade
);

drop table if exists customer;
create table customer(
	customer_id int(3) unique not null PRIMARY KEY auto_increment,
    username varchar(15),
    password_hash varchar(64) comment 'SHA-256 password hash',
    first_name char(12),
    last_name char(12),
    email char(20),
    shipping_address varchar(70) not null
);

drop table if exists purchase;
create table purchase(
	order_id timestamp default current_timestamp not null PRIMARY KEY,
    order_date timestamp default current_timestamp not null,
    order_status varchar(15) default 'Received',
    total_dollars decimal(5,2) not null check (total_dollars >= 0),
    customer_id int(3) references customer(customer_id)
		on delete cascade
        on update cascade
);

drop table if exists product_history;
create table product_history(
	action_time timestamp unique not null PRIMARY KEY,
    action_type char(15) not null,
    old_price decimal(4,2),
    new_price decimal(4,2),
    old_stock int(3),
    new_stock int(3) not null,
    product_id int(4) references product(product_id)
		on delete set null
        on update cascade,
    order_id timestamp references purchase(order_id)
		on delete set null
        on update cascade
);
