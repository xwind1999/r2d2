SELECT
bo.booking_reference as 'goldenId',
bo.box_id as 'boxId',
bo.experience_id as 'experienceId',
exp.price as 'experiencePrice',
bo.currency as 'currency',
bo.voucher_number as 'voucher',
bo.arrival_date as 'arrivalDate',
bo.end_date as 'endDate',
bo.additional_comment as 'additionalComment',
compress(bo.customer_data) AS 'customerData',
compress(ec.Components) AS 'components',
-- compress(boed.data) AS 'components',
string_agg(oi.amount, ';') as 'roomPrice',
string_agg(oi.item_type, ';') as 'roomType',
string_agg(oi.[begin], ';') as 'beginRoomDate',
string_agg(oi.[end], ';') as 'endRoomDate'
FROM JarvisBooking.BookingOrder bo
    LEFT JOIN R2D2.Experience as exp
        ON exp.golden_id = bo.experience_id
    LEFT JOIN JarvisBooking.OrderItem oi
        ON bo.id = oi.order_id AND oi.item_type in  ('extra_room','extra_night')
    LEFT JOIN CatalogService.ExperienceContent ec
        ON bo.experience_id = ec.ExperienceId
    LEFT JOIN JarvisBooking.BookingOrderExperienceDetails boed
        ON bo.id = boed.order_id
WHERE
bo.arrival_date > (DATEADD(month, -3, DATEADD(day, +14, getdate())))
AND bo.created_at < '2020-10-30 00:12:53' -- considering first import day
AND bo.status = 'complete'
AND bo.booking_type = 'stay'
AND bo.request_type = 'Confirm'
AND bo.channel != 'iresa'
AND boed.order_id is null
AND UPPER(ec.Lang) = bo.language
AND ec.Universe = 'STA'
GROUP by bo.booking_reference, bo.box_id, bo.experience_id, exp.price, bo.currency, bo.voucher_number, bo.arrival_date, bo.end_date,
         bo.additional_comment, bo.customer_data, ec.Components
