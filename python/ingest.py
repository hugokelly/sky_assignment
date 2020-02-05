import time
import random
import mysql.connector

local_db = mysql.connector.connect(host='mysql', user='root', passwd='rootpassword', database='local_db', port=3306)
local_db_cursor = local_db.cursor()

insert_sql = "INSERT INTO cpu_log (timestamp, cpuLoad, concurrency) VALUES (%d, %f, %d)"

current_unix_timestamp = int(time.time())
insert_values = []

for x in range(0, 5):
    this_timestamp_unix = current_unix_timestamp - x * 60
    random_float = random.uniform(0, 100)
    random_int = random.randint(0, 500000)
    insert_values.append((this_timestamp_unix, random_float, random_int))

local_db_cursor.executemany(insert_sql, insert_values)
local_db.commit()
local_db_cursor.close()
local_db.close()

print(mycursor.rowcount, " inserted.")