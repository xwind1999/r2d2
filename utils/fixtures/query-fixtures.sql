DROP TEMPORARY TABLE IF EXISTS export_box;
DROP TEMPORARY TABLE IF EXISTS export_experience;
DROP TEMPORARY TABLE IF EXISTS export_component;
DROP TEMPORARY TABLE IF EXISTS export_partner;

CREATE TEMPORARY TABLE export_box AS (
    select uuid
    from box
    where golden_id in
          ('851518', '851469', '848592', '733694', '552132', '847577', '1796', '2061', '872404', '905234', '847557',
           '2143', '848490', '2161', '870009', '884728', '873304',
           '1043346', '1045720', '1045741', '1045846')
);

CREATE TEMPORARY TABLE export_experience AS (
    SELECT e.uuid, partner_uuid
    FROM experience e
             JOIN (
        SELECT g.uuid AS uuid
        FROM (
                 SELECT (
                            row_number() OVER (PARTITION BY be.box_golden_id ORDER BY e.uuid)
                            )         AS rn,
                        box_golden_id,
                        e.uuid        AS uuid,
                        count(c.uuid) as num_components
                 FROM experience e
                          JOIN box_experience be ON be.experience_uuid = e.uuid
                          JOIN experience_component ec on e.uuid = ec.experience_uuid
                          JOIN component c on ec.component_uuid = c.uuid
                          JOIN partner p2 ON e.partner_uuid = p2.uuid
                          JOIN export_box cb ON cb.uuid = be.box_uuid
                 WHERE e.status = 'active'
                   AND be.is_enabled = 1
                   AND p2.status = 'partner'
                   AND p2.cease_date IS NULL
                   AND c.inventory != ''
                   AND c.is_reservable = 1
                   AND ec.is_enabled = 1
                   AND c.is_manageable = 1
                   AND c.status = 'active'
                   AND c.duration_unit = 'Nights'
                   AND c.duration IS NOT NULL
                 GROUP BY be.box_golden_id, e.uuid
                 HAVING num_components > 0
                 ORDER BY be.box_golden_id
             ) g
        WHERE g.rn <= 200
    ) rows_exp ON rows_exp.uuid = e.uuid
    GROUP BY e.uuid
);

CREATE TEMPORARY TABLE export_component AS (
    SELECT distinct c.uuid
    FROM component c
             JOIN experience_component ec ON ec.component_uuid = c.uuid
             JOIN export_experience ce ON ce.uuid = ec.experience_uuid
    GROUP BY c.uuid
);

CREATE TEMPORARY TABLE export_partner AS (
    SELECT distinct p.uuid
    FROM partner p
             JOIN export_experience ce ON ce.partner_uuid = p.uuid
    GROUP BY p.uuid
);

-- box
select b.*
from box b
         JOIN export_box eb on b.uuid = eb.uuid
group by b.uuid;

-- experience
select e.*
from experience e
         JOIN export_experience ee on e.uuid = ee.uuid
group by e.uuid;

-- component
select c.*
from component c
         JOIN export_component ec on c.uuid = ec.uuid
group by c.uuid;

-- partner
select p.*
from partner p
         JOIN export_partner ep on p.uuid = ep.uuid
group by p.uuid;

-- box_experience
SELECT be.*
FROM box_experience be
         JOIN export_box cb ON cb.uuid = be.box_uuid
         JOIN export_experience ce ON ce.uuid = be.experience_uuid
group by be.box_uuid, be.experience_uuid;

-- experience_component
SELECT ec.*
FROM experience_component ec
         JOIN export_experience ce ON ce.uuid = ec.experience_uuid
         JOIN export_component cc ON cc.uuid = ec.component_uuid
group by ec.experience_uuid, ec.component_uuid;
