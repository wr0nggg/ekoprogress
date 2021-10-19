import datetime
import random

def GenerateSQLInserts(count, covID, delta=0, fileName="Server/Tools/SQLInserts.sql"):
  time = datetime.datetime.now()
  intervals = [
    "00:00:00",
    "06:00:00",
    "12:00:00",
    "18:00:00"
  ]
  with open(fileName, 'w') as inserts:
    for i in range(count):
      insert = ""
      insert += f"INSERT INTO records_COV_{covID}"
      insert += f"(date_time_esp, water_volume, power_consumption, water_contamination, membrane, airlift)"
      insert += f" VALUES("
      insert += "'" + time.strftime("%Y-%m-%d") + " " + intervals[(3 - (i + delta)) % 4] + "', "
      insert += f"{450.0 + random.uniform(-100.0,  100.0):6.2f}, "
      insert += f"{ 60.0 + random.uniform(  -5.0,    5.0):6.2f}, "
      insert += f"{  0.0 + random.uniform(   0.0, 3000.0):6.2f}, "
      insert += f"{0},{0});\n"
      time -= datetime.timedelta(hours=6)
      inserts.write(insert)

count = 365*4
covID = '0000'
delta = 0
GenerateSQLInserts(count, covID, delta)