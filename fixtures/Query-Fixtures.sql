SET session group_concat_max_len=150000;

SET @boxes :=
        '851518,851469,848592,733694,552132,847577,1796,2061,872404,905234,847557,2143,848490,2161,870009,884728,873304, 
        1043346,1045720,1045741,1045846';
        
SET @experiences := (SELECT GROUP_CONCAT(distinct e.golden_id) FROM experience e JOIN (SELECT be.box_golden_id, GROUP_CONCAT(e.golden_id) as ids FROM experience e
                 JOIN box_experience be ON be.experience_golden_id = e.golden_id WHERE FIND_IN_SET(box_golden_id, @boxes COLLATE utf8mb4_general_ci)
          AND e.status = 'active'
          AND be.is_enabled = 1
          AND e.partner_golden_id IN (SELECT p.golden_id FROM partner p WHERE p.cease_date IS NULL)
            GROUP BY be.box_golden_id) gr ON FIND_IN_SET(e.golden_id, gr.ids) BETWEEN 1 AND 30);
            
SET @components := (SELECT GROUP_CONCAT(c.golden_id)
                   FROM component c
                            JOIN experience_component ec ON ec.component_golden_id = c.golden_id
                   WHERE ec.is_enabled = 1
                     AND c.is_reservable = 1
                     AND c.status = 'active'
                     AND c.partner_golden_id IN (SELECT p.golden_id FROM partner p WHERE p.cease_date IS NULL)
                     AND FIND_IN_SET(experience_golden_id, @experiences COLLATE utf8mb4_general_ci));
                     
SET @partners = (SELECT GROUP_CONCAT(p.golden_id)
                 FROM partner p
                 WHERE 
                     p.status = 'partner' AND
                     p.cease_date IS NULL AND
                     p.golden_id IN (SELECT partner_golden_id
                                                 FROM experience e
                                                 WHERE FIND_IN_SET(e.golden_id, @experiences COLLATE utf8mb4_general_ci))
                    OR p.golden_id IN (SELECT partner_golden_id
                                                 FROM component c
                                           WHERE FIND_IN_SET(c.golden_id, @components COLLATE utf8mb4_general_ci))
					);
                     
-- box
select golden_id as id, brand as sellableBrand, country as sellableCountry, status, 0 as 'listPrice.amount', currency as 'listPrice.currencyCode', 'mev' as type, box.updated_at as updatedAt from box where FIND_IN_SET(golden_id, @boxes COLLATE utf8mb4_general_ci);

-- experience
SELECT e.golden_id as id, e.partner_golden_id as partner, e.name, e.description, 'experience' as type, e.status, e.people_number as productPeopleNumber, e.updated_at as updatedAt
FROM experience e WHERE FIND_IN_SET(e.golden_id, @experiences COLLATE utf8mb4_general_ci);

-- component
SELECT c.golden_id as id, c.partner_golden_id as partner, c.name, c.description, ifnull(c.inventory, '') as stockAllotment, ifnull(c.duration, '') as productDuration, ifnull(c.duration_unit, '') as productDurationUnit, ifnull(c.room_stock_type, '') as roomStockType, c.is_sellable as isSellable, c.is_reservable as isReservable, c.status, 'component' as type, c.updated_at as updatedAt FROM component c WHERE FIND_IN_SET(c.golden_id, @components COLLATE utf8mb4_general_ci);

-- partners
SELECT p.golden_id as id,  p.status as type, p.currency as currencyCode, ifnull(p.cease_date, '') as partnerCeaseDate, p.is_channel_manager_active as isChannelManagerEnabled, ifnull(p.updated_at, '') as updatedAt FROM partner p WHERE FIND_IN_SET(p.golden_id, @partners COLLATE utf8mb4_general_ci);

-- price
SELECT e.golden_id as 'product.id', CAST(e.price as DECIMAL(9,2)) 'averageValue.amount', p.currency as 'averageValue.currencyCode', ifnull(e.commission_type, '') as 'averageCommissionType', CAST(e.commission as DECIMAL(5,2)) 'averageCommission', e.updated_at as 'updatedAt' FROM experience e JOIN partner p ON p.golden_id = e.partner_golden_id WHERE FIND_IN_SET(e.golden_id, @experiences COLLATE utf8mb4_general_ci);

-- box_experience
SELECT be.box_golden_id as 'parentProduct', be.experience_golden_id as 'childProduct', be.is_enabled as 'isEnabled', 'Box-Experience' as 'relationshipType', be.updated_at as 'updatedAt' FROM box_experience be WHERE FIND_IN_SET(be.box_golden_id, @boxes COLLATE utf8mb4_general_ci) AND FIND_IN_SET(be.experience_golden_id, @experiences COLLATE utf8mb4_general_ci);

-- experience_component
SELECT ec.experience_golden_id as 'parentProduct', ec.component_golden_id as 'childProduct', ec.is_enabled as 'isEnabled', 'Experience-Component' as 'relationshipType', ec.updated_at as 'updatedAt' FROM experience_component ec WHERE FIND_IN_SET(ec.experience_golden_id, @experiences COLLATE utf8mb4_general_ci) AND FIND_IN_SET(ec.component_golden_id, @components COLLATE utf8mb4_general_ci);
