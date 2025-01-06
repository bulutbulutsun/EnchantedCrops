-- #!mysql


-- #{ table
-- #{ create
CREATE TABLE IF NOT EXISTS cropsdata
(
    world VARCHAR(32),
    x INTEGER DEFAULT 0,
    y      INTEGER DEFAULT 0,
    z     INTEGER DEFAULT 0
    );
-- #}
-- #{ setdata
-- #  :world string
-- #  :x int
-- #  :y int
-- #  :z int
INSERT INTO cropsdata(world, x, y, z)
VALUES (:world, :x, :y, :z)
    ON DUPLICATE KEY UPDATE world    = :world,
                         x    = :x,
                         y    = :y,
                         z = :z;
-- #}
-- #{ deletedata
-- #  :world string
-- #  :x int
-- #  :y int
-- #  :z int
DELETE from cropsdata WHERE world = :world AND x = :x AND y = :y AND z = :z;
-- #}
-- #{ getdata
SELECT * FROM cropsdata;
-- #}
-- #}