## List the historic prices for a given product
select 
    ph.product_id,
    ph.action_time,
    ph.old_price,
    ph.new_price
from product_history ph
where ph.product_id = 1   -- replace with desired product_id
  and ph.old_price is not null
order by ph.action_time;

## List the highest and lowest price within a given period
select 
    ph.product_id,
    min(ph.new_price) as lowest_price,
    max(ph.new_price) as highest_price
from product_history ph
where ph.action_time between '2026-03-01' and '2026-03-31'
  and ph.new_price is not null
group by ph.product_id;

## List the quantities sold per product within a time frame
select 
    ph.product_id,
    sum(ph.old_stock - ph.new_stock) as total_quantity_sold
from product_history ph
where ph.action_time between '2026-03-01' and '2026-03-31'
  and ph.old_stock > ph.new_stock
group by ph.product_id
having total_quantity_sold > 0;

## List the products below restocking threshold and the quantities needed
select 
    p.product_id,
    p.product_name,
    p.actual_stock,
    p.advising_threshold,
    (p.advising_threshold - p.actual_stock) as quantity_needed
from product p
where p.actual_stock < p.advising_threshold;
