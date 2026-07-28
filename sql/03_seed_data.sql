# include delete statements at the beginning to allow multiple executions
set sql_safe_updates = 0;
delete from product_history;
delete from purchase;
delete from category;
delete from customer;
delete from employee;
set sql_safe_updates = 1;

# using stored procedure to insert employee
# password storage requirement: hashing with sha-256
call create_employee('user_liz', 'liz@ikea.org', sha2('securepass123', 256));
call create_employee('user_bekka', 'bekka@ikea.org', sha2('temp1234', 256));

# using stored procedure to insert categories
call insert_category('Kitchen', 'Things you would find and need in a kitchen');
call insert_category('Bedroom', 'Things you would find and need in a bedroom');
call insert_category('Bathroom', 'Things you would find and need in a bathroom');
call insert_category('Decor', 'Everything you need to make your spaces whimsical!');

# using stored procedure to insert products
call insert_product('Cup', 'A glass cup for all your drinking needs', 5.00, 3, 14, 'Kitchen', null);
call insert_product('Vase', 'Flowers can brighten up any day', 10.00, 2, 20, 'Decor', null);
call insert_product('Plate', 'Made of tempered glass', 8.00, 10, 40, 'Kitchen', null);
call insert_product('Toilet Brush', 'Effectively keeps the toilet clean', 6.00, 5, 17, 'Bathroom', null);
call insert_product('Soap Dispenser', 'No spills when you refill', 4.00, 4, 7, 'Decor', null);

# using insert statement directly for customers
insert into customer (username, password_hash, first_name, last_name, email, shipping_address)
VALUES 
('user1', sha2('mypassword', 256), 'Jane', 'Doe', 'jane@gmail.com', '111 College Ave'),
('user2', sha2('secret77', 256), 'Ivy', 'Willow', 'ivy@gmail.com', '6769 Ikea Rd'),
('user3', sha2('password123', 256), 'John', 'Doe', 'John@gmail.com', '123 Uncle Dr');

# inserting sample purchase data
insert into purchase (order_id, order_date, order_status, total_dollars, customer_id)
VALUES
(current_timestamp, current_timestamp, 'Shipped', 729.98, 1);
insert into purchase (order_id, order_date, order_status, total_dollars, customer_id)
VALUES
(current_timestamp, current_timestamp, 'Order Placed', 20.02, 3);

# product 1 (multiple price + stock changes)
update product set price = 5.50 where product_id = 1;
update product set price = 6.00 where product_id = 1;
update product set actual_stock = 10 where product_id = 1;
update product set actual_stock = 6 where product_id = 1;
update product set price = 7.00 where product_id = 1;

# product 2
update product set price = 9.50 where product_id = 2;
update product set actual_stock = 18 where product_id = 2;
update product set price = 9.00 where product_id = 2;
update product set actual_stock = 15 where product_id = 2;

# product 3
update product set price = 8.50 where product_id = 3;
update product set actual_stock = 35 where product_id = 3;

# product 4 (price only)
update product set price = 6.50 where product_id = 4;

# product 5
update product set actual_stock = 3 where product_id = 5;
update product set price = 4.50 where product_id = 5;

select * from employee;
select * from category;
select * from product;
select * from customer;
select * from purchase;
select * from product_history;
