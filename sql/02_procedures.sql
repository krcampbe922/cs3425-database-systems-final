delimiter //
create procedure create_employee(
    in in_username varchar(15),
    in in_email char(20),
    in in_password_hash varchar(64)
)
begin
    insert into employee (username, email, password_hash, is_password_temp)
    values (in_username, in_email, in_password_hash, true);
end //
delimiter ;

delimiter //
create procedure insert_category(
    in in_category_name char(15),
    in in_description varchar(100)
)
begin
    insert into category (category_name, category_description)
    values (in_category_name, in_description);
end //
delimiter ;

delimiter //
create procedure insert_product(
    in in_name varchar(15),
    in in_description varchar(120),
    in in_price decimal(6, 2),
    in in_threshold int,
    in in_stock int,
    in in_category char(15),
    in in_image varchar(255)
)
begin
    insert into product (product_name, product_description, price, advising_threshold, actual_stock, category_name, image)
    values (in_name, in_description, in_price, in_threshold, in_stock, in_category, in_image);
end //
delimiter ;

delimiter //
create procedure log_product_update(
    in in_product_id int,
    in in_action_type char(15),
    in in_old_price decimal(4,2),
    in in_new_price decimal(4,2),
    in in_old_stock int,
    in in_new_stock int,
    in in_employee_id int, 
    in in_customer_id int, 
    in in_order_id timestamp
)
begin
    insert into product_history (action_time, action_type, old_price, new_price, old_stock, new_stock, product_id, order_id)
    VALUES (current_timestamp, in_action_type, in_old_price, in_new_price, in_old_stock, in_new_stock, in_product_id, in_order_id
    );
end //
delimiter ;

## Triggers

delimiter //
create trigger before_product_update
before update on product
for each row
begin
    if old.product_id <> new.product_id then
        signal sqlstate '45000'
        set message_text = 'the prod id is not allowed to be changed';
    end if;
end //
delimiter ;

delimiter //
create trigger before_product_delete
before delete on product
for each row
begin
    signal sqlstate '45000'
    set message_text = 'deleting products is prohibited; please use the discontinued flag instead';
end //
delimiter ;

delimiter //
create trigger after_product_update
after update on product
for each row
begin
	call log_product_update(new.product_id, 'UPDATE', old.price, new.price, old.actual_stock, new.actual_stock, null, null, null);
end //
delimiter ;

## Create procedure for checkout
  
delimiter //
create procedure checkout(
    in p_customer_id int,
    out p_order_id timestamp,
    out p_out_of_stock_product int
)
begin
    declare v_finished int default 0;
    declare v_prod_id int;
    declare v_qty int;
    declare v_stock int;
    declare v_order_time timestamp;
    # cursor to fetch items from the cart
    declare cart_cursor cursor for 
        select product_id, quantity from shopping_cart 
        where customer_id = p_customer_id;
    declare continue handler for not found set v_finished = 1;
    start transaction;
    # create a new order in the orders table
    set v_order_time = current_timestamp;
    insert into purchase (order_id, order_date, order_status, total_dollars, customer_id)
    values (v_order_time, v_order_time, 'received', 0.00, p_customer_id);
    # repeat get an item from the shopping cart
    open cart_cursor;
    cart_loop: loop
        fetch cart_cursor into v_prod_id, v_qty;
        if v_finished = 1 then 
            leave cart_loop;
        end if;
        # check stock for the item
        select actual_stock into v_stock 
        from product 
        where product_id = v_prod_id for update;
        # if sufficient
        if v_stock >= v_qty then
            # add to the order
            # (logic: order total update or linking record)
            update purchase 
            set total_dollars = total_dollars + (select price * v_qty from product where product_id = v_prod_id)
            where order_id = v_order_time;
		# update product stock
            update product 
            set actual_stock = actual_stock - v_qty 
            where product_id = v_prod_id;
        # else
        else
            # abort the transaction
            rollback;
            # set p_out_of_stock_product
            set p_out_of_stock_product = v_prod_id;
            # return
            close cart_cursor;
            leave cart_loop;
        end if;
    end loop cart_loop;
    # clear the shopping cart
    if p_out_of_stock_product is null then
        close cart_cursor;
        delete from shopping_cart where customer_id = p_customer_id;
        # commit the transaction
        commit;
        # set p_order_id
        set p_order_id = v_order_time;
    end if;
end //
delimiter ;
