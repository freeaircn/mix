# Sql

---
### 1. DTS
```
SELECT type, COUNT(*) FROM app_dts WHERE station_id = '2' AND place_at IN ('suspend', 'working') GROUP BY type


SELECT place_at, COUNT(*) FROM app_dts WHERE station_id = '2' AND place_at IN ('suspend', 'working') GROUP BY place_at


SELECT DATE_FORMAT(created_at, '%Y-%m') as date, COUNT(id) FROM app_dts WHERE station_id = '2' AND place_at IN ('suspend', 'working') GROUP BY DATE_FORMAT(created_at, '%Y-%m')


SELECT DATE_FORMAT(updated_at, '%Y-%m') as date, COUNT(id) FROM app_dts WHERE station_id = '2' AND place_at IN ('resolve', 'close') GROUP BY DATE_FORMAT(updated_at, '%Y-%m')


SELECT level, COUNT(level) FROM app_dts WHERE station_id = '2' AND type = '2' AND place_at IN ('suspend', 'working') GROUP BY level


SELECT place_at, COUNT(id) FROM app_dts WHERE station_id = '2' AND place_at IN ('suspend', 'working') AND DATE_SUB(CURDATE(), INTERVAL 10 DAY) >= DATE(created_at) GROUP BY place_at


SELECT cause, COUNT(cause) FROM app_dts WHERE station_id = '2' AND place_at IN ('resolve', 'close') GROUP BY cause



SELECT app_dts_attachment.*, app_dts.title, app_dept.name AS station FROM app_dts_attachment, app_dts, app_dept WHERE app_dts_attachment.station_id IN ('2') AND app_dts_attachment.dts_id = app_dts.dts_id AND app_dts_attachment.station_id = app_dept.id;



SELECT log_date, meter_id, fak FROM app_meter WHERE station_id = '2' AND log_time = '23:59:00' AND meter_id = '3' ORDER BY log_date;




```
