<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $products = [
            ['name' => 'PIAGIO DIESEL FILTER BOSCH', 'qty' => 5, 'mrp' => 600.00, 'cost' => 492.00],
            ['name' => 'PIAGIO DIESEL FILTER ELEMFIL', 'qty' => 3, 'mrp' => 500.00, 'cost' => 410.00],
            ['name' => 'BAJAJ IOL FILTER', 'qty' => 20, 'mrp' => 500.00, 'cost' => 410.00],
            ['name' => 'PUROLATOR', 'qty' => 4, 'mrp' => 890.00, 'cost' => 729.80],
            ['name' => 'TOYOTA', 'qty' => 3, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'HITECH', 'qty' => 3, 'mrp' => 950.00, 'cost' => 779.00],
            ['name' => 'MAHINDRA', 'qty' => 1, 'mrp' => 1450.00, 'cost' => 1189.00],
            ['name' => 'ZIP', 'qty' => 4, 'mrp' => 1400.00, 'cost' => 1148.00],
            ['name' => 'NISSAN', 'qty' => 2, 'mrp' => 2350.00, 'cost' => 1927.00],
            ['name' => 'PREMIUM', 'qty' => 1, 'mrp' => 1500.00, 'cost' => 1230.00],
            ['name' => 'SANKO', 'qty' => 2, 'mrp' => 1500.00, 'cost' => 1230.00],
            ['name' => 'MITSUBISHI 220900', 'qty' => 1, 'mrp' => 2300.00, 'cost' => 1886.00],
            ['name' => '1230A046', 'qty' => 1, 'mrp' => 2600.00, 'cost' => 2132.00],
            ['name' => 'FULE FILTER TOYOTA 23303-64010', 'qty' => 1, 'mrp' => 2300.00, 'cost' => 1886.00],
            ['name' => 'FULE FILTER C-518', 'qty' => 2, 'mrp' => 3500.00, 'cost' => 2870.00],
            ['name' => 'WIPER 12\'\'', 'qty' => 3, 'mrp' => 1500.00, 'cost' => 1230.00],
            ['name' => 'WIPER 14\'\'', 'qty' => 3, 'mrp' => 1500.00, 'cost' => 1230.00],
            ['name' => 'WIPER 16\'\'', 'qty' => 3, 'mrp' => 1500.00, 'cost' => 1230.00],
            ['name' => 'WIPER 17\'\'', 'qty' => 2, 'mrp' => 1500.00, 'cost' => 1230.00],
            ['name' => 'WIPER 18\'\'', 'qty' => 2, 'mrp' => 1600.00, 'cost' => 1312.00],
            ['name' => 'WIPER 19\'\'', 'qty' => 3, 'mrp' => 1600.00, 'cost' => 1312.00],
            ['name' => 'WIPER 20\'\'', 'qty' => 3, 'mrp' => 1600.00, 'cost' => 1312.00],
            ['name' => 'WIPER 21\'\'', 'qty' => 3, 'mrp' => 1600.00, 'cost' => 1312.00],
            ['name' => 'WIPER 22\'\'', 'qty' => 3, 'mrp' => 1600.00, 'cost' => 1312.00],
            ['name' => 'WIPER 24\'\'', 'qty' => 3, 'mrp' => 1600.00, 'cost' => 1312.00],
            ['name' => 'WIPER 26\'\'', 'qty' => 3, 'mrp' => 1600.00, 'cost' => 1312.00],
            ['name' => 'NAYLONE CABLE 3.6*150MM', 'qty' => 200, 'mrp' => 15.00, 'cost' => 12.30],
            ['name' => 'NAYLONE CABLE 3.6*300MM', 'qty' => 100, 'mrp' => 15.00, 'cost' => 12.30],
            ['name' => 'NAYLONE CABLE 4.8*200MM', 'qty' => 100, 'mrp' => 15.00, 'cost' => 12.30],
            ['name' => 'NAYLONE CABLE 3.6*250MM', 'qty' => 100, 'mrp' => 15.00, 'cost' => 12.30],
            ['name' => 'NAYLONE CABLE 4.8*400MM', 'qty' => 100, 'mrp' => 30.00, 'cost' => 24.60],
            ['name' => 'NAYLONE CABLE 4*500MM', 'qty' => 100, 'mrp' => 30.00, 'cost' => 24.60],
            ['name' => 'MOBIL OIL SUPER 5W-30 (4L)', 'qty' => 1, 'mrp' => 17980.00, 'cost' => 14743.60],
            ['name' => 'MOBIL OIL SUPER 10W-30 (4L)', 'qty' => 2, 'mrp' => 15900.00, 'cost' => 13038.00],
            ['name' => 'MOBIL OIL SUPER 15W-40 (4L)', 'qty' => 2, 'mrp' => 15900.00, 'cost' => 13038.00],
            ['name' => 'MOBIL OIL DELVAC 15W-40 (4L)', 'qty' => 2, 'mrp' => 17900.00, 'cost' => 14678.00],
            ['name' => 'MOBIL OIL DELVAC 15W-40 (1L)', 'qty' => 4, 'mrp' => 3280.00, 'cost' => 2689.60],
            ['name' => 'MOBIL OIL DELVAC 10W-30 (1L)', 'qty' => 1, 'mrp' => 3985.00, 'cost' => 3267.70],
            ['name' => 'MOBIL OIL DELVAC 1W-30 (1L) 3IN1', 'qty' => 1, 'mrp' => 11950.00, 'cost' => 9799.00],
            ['name' => 'MOBIL OIL SUPER DS 5W-30 (1L)3IN1', 'qty' => 2, 'mrp' => 11470.00, 'cost' => 9405.40],
            ['name' => 'MOBIL OIL SUPER DC 5W-30 (1L)', 'qty' => 2, 'mrp' => 3825.00, 'cost' => 3136.50],
            ['name' => 'MOBIL OIL SUPER 15W-40 (1L)', 'qty' => 7, 'mrp' => 3985.00, 'cost' => 3267.70],
            ['name' => 'CALTEX OIL SUPER DS 15W-40 (6L)', 'qty' => 1, 'mrp' => 16320.00, 'cost' => 13382.40],
            ['name' => 'CALTEX OIL GOLD ULTRA 15W-40 (6L)', 'qty' => 3, 'mrp' => 22200.00, 'cost' => 18204.00],
            ['name' => 'CALTEX OIL HAVOILNE 15W-40 (4L)', 'qty' => 2, 'mrp' => 14280.00, 'cost' => 11709.60],
            ['name' => 'CALTEX OIL SUPER DS 15W-40 (1L)', 'qty' => 3, 'mrp' => 3925.00, 'cost' => 3218.50],
            ['name' => 'CALTEX OIL TEXAMATIC 1888 (1L)', 'qty' => 2, 'mrp' => 5250.00, 'cost' => 4305.00],
            ['name' => 'SINOPAC TILUX T500 15W-40 (5L)', 'qty' => 2, 'mrp' => 15250.00, 'cost' => 12505.00],
            ['name' => 'SINOPAC JUSTAR J500 10W-40', 'qty' => 2, 'mrp' => 13150.00, 'cost' => 10783.00],
            ['name' => 'HONDA HMMF OIL (4L)', 'qty' => 1, 'mrp' => 21000.00, 'cost' => 17220.00],
            ['name' => 'SUZUKI OIL (4L)', 'qty' => 1, 'mrp' => 21000.00, 'cost' => 17220.00],
            ['name' => 'MITSUBIZI OIL (4L)', 'qty' => 1, 'mrp' => 21000.00, 'cost' => 17220.00],
            ['name' => 'TOYOTA OIL FILTER 04152-37010', 'qty' => 2, 'mrp' => 750.00, 'cost' => 615.00],
            ['name' => 'ELEMENT OIL C-224', 'qty' => 2, 'mrp' => 950.00, 'cost' => 779.00],
            ['name' => 'ELEMENT OIL C-415', 'qty' => 2, 'mrp' => 950.00, 'cost' => 779.00],
            ['name' => 'ELEMENT OIL C-111', 'qty' => 2, 'mrp' => 950.00, 'cost' => 779.00],
            ['name' => 'ELEMENT OIL C-809', 'qty' => 2, 'mrp' => 950.00, 'cost' => 779.00],
            ['name' => 'ELEMENT OIL C-932', 'qty' => 2, 'mrp' => 730.00, 'cost' => 598.60],
            ['name' => 'ELEMENT OIL C-306', 'qty' => 2, 'mrp' => 1800.00, 'cost' => 1476.00],
            ['name' => 'ELEMENT OIL C-112', 'qty' => 2, 'mrp' => 1900.00, 'cost' => 1558.00],
            ['name' => 'OIL FILTER 15600-41010', 'qty' => 2, 'mrp' => 1300.00, 'cost' => 1066.00],
            ['name' => 'BREAK OIL LOCKHEED 500ML', 'qty' => 9, 'mrp' => 2590.00, 'cost' => 2123.80],
            ['name' => 'BREAK OIL LOCKHEED 250ML', 'qty' => 4, 'mrp' => 1450.00, 'cost' => 1189.00],
            ['name' => 'CALTEX OIL 250ML', 'qty' => 7, 'mrp' => 1445.00, 'cost' => 1184.90],
            ['name' => 'CALTEX OIL 500ML', 'qty' => 5, 'mrp' => 2490.00, 'cost' => 2041.80],
            ['name' => 'GREEN COOLANT 5L', 'qty' => 5, 'mrp' => 1800.00, 'cost' => 1476.00],
            ['name' => 'REVTRON 10W-30', 'qty' => 4, 'mrp' => 3125.00, 'cost' => 2562.50],
            ['name' => 'REVTRON 20W-50', 'qty' => 6, 'mrp' => 2890.00, 'cost' => 2369.80],
            ['name' => 'FLASH D.WATER 500ML', 'qty' => 8, 'mrp' => 145.00, 'cost' => 118.90],
            ['name' => 'FLASH D.WATER 1L', 'qty' => 10, 'mrp' => 225.00, 'cost' => 184.50],
            ['name' => 'AIR FILTER 13780-50M00', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'AIR FILTER TOYOTA (A194)', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'AIR FILTER TOYOTA KDH (A1025)', 'qty' => 1, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'AIR FILTER TOYOTA 744', 'qty' => 1, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'AIR FILTER TOYOTA A 1027', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'AIR FILTER HNDA 17220-5R0-008', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'AIR FILTER 17220-RJB-000', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'AIR FILTER SUZUKI 13780-74P00', 'qty' => 1, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'AIR FILTER SUZUKI 13780-68H00', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'AIR FILTER TOYOTA 17801-77050', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'AIR FILTER 17801-21030', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'AIR FILTER 17801-22020', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'NISSAN LUFT FILTER', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'AIR FILTER 13780-53M00', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'FULE FILTER ZD-3135', 'qty' => 2, 'mrp' => 2400.00, 'cost' => 1968.00],
            ['name' => 'TATA OIL FILTER', 'qty' => 2, 'mrp' => 2300.00, 'cost' => 1886.00],
            ['name' => 'TATA WATER SEPARATOR', 'qty' => 2, 'mrp' => 2700.00, 'cost' => 2214.00],
            ['name' => 'NISSAN OIL FILTER', 'qty' => 3, 'mrp' => 1450.00, 'cost' => 1189.00],
            ['name' => 'NISSAN FULE FILTER', 'qty' => 3, 'mrp' => 1450.00, 'cost' => 1189.00],
            ['name' => 'TOYOTA OIL FILTER 04152-40060', 'qty' => 6, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'HYUNDAI OIL FILTER', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'TOYOTA OIL FILTER 90915-7D004', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'TOYOTA OIL FILTER 90915-30002', 'qty' => 5, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'SAKURA OIL FILTER C-5816', 'qty' => 3, 'mrp' => 4680.00, 'cost' => 3837.60],
            ['name' => 'SAKURA OIL FILTER C-1502', 'qty' => 5, 'mrp' => 2650.00, 'cost' => 2173.00],
            ['name' => 'SAKURA OIL FILTER C-1701', 'qty' => 5, 'mrp' => 1760.00, 'cost' => 1443.20],
            ['name' => 'SAKURA OIL FILTER C-1404', 'qty' => 3, 'mrp' => 1250.00, 'cost' => 1025.00],
            ['name' => 'SAKURA OIL FILTER C-1204', 'qty' => 2, 'mrp' => 1250.00, 'cost' => 1025.00],
            ['name' => 'SAKURA OIL FILTER C-1109', 'qty' => 3, 'mrp' => 1250.00, 'cost' => 1025.00],
            ['name' => 'SAKURA OIL FILTER C-1005', 'qty' => 3, 'mrp' => 1500.00, 'cost' => 1230.00],
            ['name' => 'SAKURA OIL FILTER C-1025', 'qty' => 1, 'mrp' => 1500.00, 'cost' => 1230.00],
            ['name' => 'SAKURA OIL FILTER C-1142', 'qty' => 5, 'mrp' => 1500.00, 'cost' => 1230.00],
            ['name' => 'SAKURA OIL FILTER EO-1103', 'qty' => 3, 'mrp' => 2000.00, 'cost' => 1640.00],
            ['name' => 'SAKURA OIL FILTER C-10081', 'qty' => 1, 'mrp' => 2975.00, 'cost' => 2439.50],
            ['name' => 'SAKURA OIL FILTER EO-10060', 'qty' => 3, 'mrp' => 4475.00, 'cost' => 3669.50],
            ['name' => 'SAKURA OIL FILTER O-1501', 'qty' => 1, 'mrp' => 2250.00, 'cost' => 1845.00],
            ['name' => 'SAKURA OIL FILTER C-1111', 'qty' => 2, 'mrp' => 2550.00, 'cost' => 2091.00],
            ['name' => 'SAKURA OIL FILTER C-1121', 'qty' => 1, 'mrp' => 1650.00, 'cost' => 1353.00],
            ['name' => 'SAKURA OIL FILTER C-1805', 'qty' => 3, 'mrp' => 1400.00, 'cost' => 1148.00],
            ['name' => 'SAKURA OIL FILTER C-1506', 'qty' => 2, 'mrp' => 5350.00, 'cost' => 4387.00],
            ['name' => 'SAKURA OIL FILTER C-1803', 'qty' => 2, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'SAKURA OIL FILTER C-10081', 'qty' => 2, 'mrp' => 2975.00, 'cost' => 2439.50],
            ['name' => 'SAKURA OIL FILTER C-1806', 'qty' => 2, 'mrp' => 1875.00, 'cost' => 1537.50],
            ['name' => 'SAKURA OIL FILTER C-1511', 'qty' => 3, 'mrp' => 2225.00, 'cost' => 1824.50],
            ['name' => 'SAKURA OIL FILTER C-1812', 'qty' => 3, 'mrp' => 2850.00, 'cost' => 2337.00],
            ['name' => 'SAKURA OIL FILTER C-1102', 'qty' => 3, 'mrp' => 2000.00, 'cost' => 1640.00],
            ['name' => 'SAKURA OIL FILTER C-2103', 'qty' => 3, 'mrp' => 1875.00, 'cost' => 1537.50],
            ['name' => 'SAKURA OIL FILTER C-1513', 'qty' => 3, 'mrp' => 6750.00, 'cost' => 5535.00],
            ['name' => 'SAKURA OIL FILTER C-7982', 'qty' => 5, 'mrp' => 2200.00, 'cost' => 1804.00],
            ['name' => 'SAKURA OIL FILTER C-1823', 'qty' => 9, 'mrp' => 1250.00, 'cost' => 1025.00],
            ['name' => 'SAKURA OIL FILTER O-1502', 'qty' => 4, 'mrp' => 1575.00, 'cost' => 1291.50],
            ['name' => 'SAKURA OIL FILTER O-1503', 'qty' => 3, 'mrp' => 1400.00, 'cost' => 1148.00],
            ['name' => 'SAKURA OIL FILTER O-1001', 'qty' => 2, 'mrp' => 2975.00, 'cost' => 2439.50],
            ['name' => 'SAKURA OIL FILTER O-1016', 'qty' => 3, 'mrp' => 1775.00, 'cost' => 1455.50],
            ['name' => 'SAKURA OIL FILTER FC-1821', 'qty' => 3, 'mrp' => 1425.00, 'cost' => 1168.50],
            ['name' => 'SAKURA OIL FILTER FC-1004', 'qty' => 3, 'mrp' => 1760.00, 'cost' => 1443.20],
            ['name' => 'SAKURA OIL FILTER C-1006', 'qty' => 3, 'mrp' => 2150.00, 'cost' => 1763.00],
            ['name' => 'SAKURA OIL FILTER C-1503', 'qty' => 1, 'mrp' => 1150.00, 'cost' => 943.00],
            ['name' => 'SAKURA OIL FILTER FC-1702', 'qty' => 2, 'mrp' => 1650.00, 'cost' => 1353.00],
            ['name' => 'SAKURA OIL FILTER FC-1803', 'qty' => 1, 'mrp' => 2200.00, 'cost' => 1804.00],
            ['name' => 'SAKURA OIL FILTER F-1111', 'qty' => 3, 'mrp' => 2160.00, 'cost' => 1771.20],
            ['name' => 'SAKURA OIL FILTER O-1501', 'qty' => 1, 'mrp' => 2250.00, 'cost' => 1845.00],
            ['name' => 'SAKURA OIL FILTER FC-1104', 'qty' => 1, 'mrp' => 2200.00, 'cost' => 1804.00],
            ['name' => 'SAKURA OIL FILTER FC-1001', 'qty' => 5, 'mrp' => 2200.00, 'cost' => 1804.00],
            ['name' => 'SAKURA OIL FILTER C-1523', 'qty' => 2, 'mrp' => 5300.00, 'cost' => 4346.00],
            ['name' => 'SAKURA OIL FILTER C-1712', 'qty' => 3, 'mrp' => 2500.00, 'cost' => 2050.00],
            ['name' => 'SAKURA OIL FILTER C-7976', 'qty' => 3, 'mrp' => 2425.00, 'cost' => 1988.50],
            ['name' => 'SAKURA OIL FILTER C-5302', 'qty' => 2, 'mrp' => 3625.00, 'cost' => 2972.50],
            ['name' => 'SAKURA OIL FILTER C-1515', 'qty' => 4, 'mrp' => 4280.00, 'cost' => 3509.60],
            ['name' => 'SAKURA OIL FILTER C-1524', 'qty' => 2, 'mrp' => 5575.00, 'cost' => 4571.50],
            ['name' => 'SAKURA OIL FILTER O-1511', 'qty' => 3, 'mrp' => 2250.00, 'cost' => 1845.00],
            ['name' => 'VIC OIL FILTER C-406', 'qty' => 2, 'mrp' => 2250.00, 'cost' => 1845.00],
            ['name' => 'ELEMFIL FC-218 OIL FILTER', 'qty' => 3, 'mrp' => 1215.00, 'cost' => 996.30],
            ['name' => 'SF OIL SFF 8422', 'qty' => 3, 'mrp' => 3450.00, 'cost' => 2829.00],
            ['name' => 'SF-5052', 'qty' => 3, 'mrp' => 1680.00, 'cost' => 1377.60],
            ['name' => 'SFO-3349', 'qty' => 2, 'mrp' => 2980.00, 'cost' => 2443.60],
            ['name' => 'KAYSER OIL FILTER KAS-809', 'qty' => 5, 'mrp' => 920.00, 'cost' => 754.40],
            ['name' => 'LAYPARTS OIL FILTER', 'qty' => 4, 'mrp' => 1900.00, 'cost' => 1558.00],
            ['name' => 'AIR FILTER AS-1031', 'qty' => 2, 'mrp' => 5725.00, 'cost' => 4694.50],
            ['name' => 'AIR FILTER A-1039', 'qty' => 2, 'mrp' => 5725.00, 'cost' => 4694.50],
            ['name' => 'AIR FILTER AS-1708', 'qty' => 2, 'mrp' => 3750.00, 'cost' => 3075.00],
            ['name' => 'AIR FILTER AS-1712', 'qty' => 2, 'mrp' => 3750.00, 'cost' => 3075.00],
            ['name' => 'AIR FILTER A-1039', 'qty' => 1, 'mrp' => 5725.00, 'cost' => 4694.50],
            ['name' => 'AIR FILTER A-1510', 'qty' => 1, 'mrp' => 5500.00, 'cost' => 4510.00],
            ['name' => 'AIR FILTER A-1118', 'qty' => 2, 'mrp' => 3950.00, 'cost' => 3239.00],
            ['name' => 'AIR FILTER A-1502', 'qty' => 2, 'mrp' => 4300.00, 'cost' => 3526.00],
            ['name' => 'AIR FILTER A-1167', 'qty' => 2, 'mrp' => 2750.00, 'cost' => 2255.00],
            ['name' => 'AIR FILTER A-1117', 'qty' => 2, 'mrp' => 2650.00, 'cost' => 2173.00],
            ['name' => 'AIR FILTER AS-1510', 'qty' => 1, 'mrp' => 5500.00, 'cost' => 4510.00],
            ['name' => 'AIR FILTER A-1714', 'qty' => 2, 'mrp' => 7150.00, 'cost' => 5863.00],
            ['name' => 'AIR FILTER A-1007', 'qty' => 2, 'mrp' => 3200.00, 'cost' => 2624.00],
            ['name' => 'AIR FILTER KAYSER KA-170 17801-54100', 'qty' => 2, 'mrp' => 2260.00, 'cost' => 1853.20],
            ['name' => 'AIR FILTER LH113', 'qty' => 1, 'mrp' => 2260.00, 'cost' => 1853.20],
            ['name' => 'AIR FILTER SUMO AF-31008', 'qty' => 3, 'mrp' => 1700.00, 'cost' => 1394.00],
            ['name' => 'AIR FILTER TOYOTA 17801-0C010', 'qty' => 1, 'mrp' => 1800.00, 'cost' => 1476.00],
            ['name' => 'NIPPLE 6*50', 'qty' => 33, 'mrp' => 100.00, 'cost' => 82.00],
            ['name' => 'NIPPLE 8*20', 'qty' => 37, 'mrp' => 100.00, 'cost' => 82.00],
            ['name' => 'NIPPLE X', 'qty' => 37, 'mrp' => 75.00, 'cost' => 61.50],
            ['name' => 'NIPPLE Y', 'qty' => 38, 'mrp' => 75.00, 'cost' => 61.50],
            ['name' => 'PREMIER WIPER 12"', 'qty' => 3, 'mrp' => 1750.00, 'cost' => 1435.00],
            ['name' => 'PREMIER WIPER 14"', 'qty' => 3, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'PREMIER WIPER 16"', 'qty' => 3, 'mrp' => 1900.00, 'cost' => 1558.00],
            ['name' => 'PREMIER WIPER 17"', 'qty' => 2, 'mrp' => 1950.00, 'cost' => 1599.00],
            ['name' => 'PREMIER WIPER 18"', 'qty' => 3, 'mrp' => 2100.00, 'cost' => 1722.00],
            ['name' => 'PREMIER WIPER 19"', 'qty' => 2, 'mrp' => 2200.00, 'cost' => 1804.00],
            ['name' => 'PREMIER WIPER 20"', 'qty' => 3, 'mrp' => 2250.00, 'cost' => 1845.00],
            ['name' => 'PREMIER WIPER 21"', 'qty' => 1, 'mrp' => 2350.00, 'cost' => 1927.00],
            ['name' => 'PREMIER WIPER 22"', 'qty' => 2, 'mrp' => 2450.00, 'cost' => 2009.00],
            ['name' => 'PREMIER WIPER 24"', 'qty' => 3, 'mrp' => 2600.00, 'cost' => 2132.00],
            ['name' => 'PREMIER WIPER 26"', 'qty' => 3, 'mrp' => 2750.00, 'cost' => 2255.00],
            ['name' => '777 WIPER 24"', 'qty' => 2, 'mrp' => 1200.00, 'cost' => 984.00],
            ['name' => 'NLK (B)WIPER 22"', 'qty' => 2, 'mrp' => 1200.00, 'cost' => 984.00],
            ['name' => 'NLK (R)WIPER 21"', 'qty' => 2, 'mrp' => 1700.00, 'cost' => 1394.00],
            ['name' => 'DOBO WIPER 19"', 'qty' => 4, 'mrp' => 1200.00, 'cost' => 984.00],
            ['name' => 'YTK WIPER 18"', 'qty' => 2, 'mrp' => 1200.00, 'cost' => 984.00],
            ['name' => 'YACON WIPER 17"', 'qty' => 4, 'mrp' => 1200.00, 'cost' => 984.00],
            ['name' => 'YTK WIPER 16"', 'qty' => 4, 'mrp' => 1200.00, 'cost' => 984.00],
            ['name' => 'HIGH PRESHER POWDER 4KG', 'qty' => 3, 'mrp' => 2980.00, 'cost' => 2443.60],
            ['name' => 'RAIHAN AIR FRESHNER LEMON', 'qty' => 15, 'mrp' => 400.00, 'cost' => 328.00],
            ['name' => 'RAIHAN AIR FRESHNER COOL ICE', 'qty' => 17, 'mrp' => 400.00, 'cost' => 328.00],
            ['name' => 'RAIHAN AIR FRESHNER OUD', 'qty' => 16, 'mrp' => 400.00, 'cost' => 328.00],
            ['name' => 'AER SPRAY', 'qty' => 3, 'mrp' => 1600.00, 'cost' => 1312.00],
            ['name' => 'CHAMOIS TOWET (S)', 'qty' => 5, 'mrp' => 650.00, 'cost' => 533.00],
            ['name' => 'CHAMOIS TOWET (L)', 'qty' => 5, 'mrp' => 1000.00, 'cost' => 820.00],
            ['name' => 'TOWEL', 'qty' => 17, 'mrp' => 350.00, 'cost' => 287.00],
            ['name' => 'GODREJ SPRAY 220ML', 'qty' => 6, 'mrp' => 1000.00, 'cost' => 820.00],
            ['name' => 'GODREJ AER 475ML', 'qty' => 6, 'mrp' => 800.00, 'cost' => 656.00],
            ['name' => 'FLASH SPRAY475ML', 'qty' => 3, 'mrp' => 695.00, 'cost' => 569.90],
            ['name' => 'GODREJ POWER POCKET LEMON', 'qty' => 11, 'mrp' => 360.00, 'cost' => 295.20],
            ['name' => 'GODREJ POWER POCKET SEA BREEZA', 'qty' => 21, 'mrp' => 360.00, 'cost' => 295.20],
            ['name' => 'GODREJ POWER POCKET JASMINE', 'qty' => 20, 'mrp' => 360.00, 'cost' => 295.20],
            ['name' => 'GODREJ POWER POCKET ROSE', 'qty' => 9, 'mrp' => 360.00, 'cost' => 295.20],
            ['name' => 'GODREJ POWER POCKET BERRY', 'qty' => 21, 'mrp' => 360.00, 'cost' => 295.20],
            ['name' => 'GODREJ POWER POCKET LAVENDER', 'qty' => 15, 'mrp' => 360.00, 'cost' => 295.20],
            ['name' => '4X COOLANT 346ML', 'qty' => 8, 'mrp' => 1450.00, 'cost' => 1189.00],
            ['name' => 'QUICK WAX 750ML', 'qty' => 3, 'mrp' => 350.00, 'cost' => 287.00],
            ['name' => 'PLASTIC REFURBISHING AND BRIGHT 500ML', 'qty' => 2, 'mrp' => 2780.00, 'cost' => 2279.60],
            ['name' => 'BRAKE CLEANER 450ML', 'qty' => 7, 'mrp' => 1100.00, 'cost' => 902.00],
            ['name' => 'CARE WINDOW LUBRICANT 450ML', 'qty' => 3, 'mrp' => 1100.00, 'cost' => 902.00],
            ['name' => 'ENGINE SURFACE DEGREASER 500ML', 'qty' => 9, 'mrp' => 1850.00, 'cost' => 1517.00],
            ['name' => 'CRYSTAY WAX 473ML', 'qty' => 1, 'mrp' => 2600.00, 'cost' => 2132.00],
            ['name' => 'RAIN STAIN REMOVER', 'qty' => 1, 'mrp' => 7000.00, 'cost' => 5740.00],
            ['name' => 'GREAR OIL 18L', 'qty' => 1, 'mrp' => 46000.00, 'cost' => 37720.00],
            ['name' => '4XL COOLENT', 'qty' => 3, 'mrp' => 5300.00, 'cost' => 4346.00],
            ['name' => 'BRUSH', 'qty' => 3, 'mrp' => 500.00, 'cost' => 410.00],
            ['name' => 'BRUSH (S)', 'qty' => 14, 'mrp' => 50.00, 'cost' => 41.00],
            ['name' => 'DUSTPAN', 'qty' => 4, 'mrp' => 340.00, 'cost' => 278.80],
            ['name' => 'WIPER (S)', 'qty' => 4, 'mrp' => 300.00, 'cost' => 246.00],
            ['name' => 'RUBBER WIPER', 'qty' => 1, 'mrp' => 650.00, 'cost' => 533.00],
            ['name' => 'BRUSH (B)', 'qty' => 2, 'mrp' => 500.00, 'cost' => 410.00],
            ['name' => 'BRUSH (BS)', 'qty' => 2, 'mrp' => 350.00, 'cost' => 287.00],
            ['name' => 'OIL PUMP', 'qty' => 2, 'mrp' => 750.00, 'cost' => 615.00],
        ];

        foreach ($products as $index => $product) {
            // Generate SKU from name
            $sku = 'PRD-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            
            // Clean product name
            $name = trim($product['name']);
            
            // Get category string
            $categoryName = $this->getCategory($name);
            
            // Get or create category for category_id
            $categoryId = $this->getOrCreateCategory($categoryName);
            
            // Check if product already exists
            $existingProduct = DB::table('products')
                ->where('tenant_id', 3)
                ->where('sku', $sku)
                ->first();
            
            if ($existingProduct) {
                $productId = $existingProduct->id;
                // Update existing product
                DB::table('products')
                    ->where('id', $productId)
                    ->update([
                        'name' => $name,
                        'category' => $categoryName,
                        'category_id' => $categoryId,
                        'brand' => $this->getBrand($name),
                        'cost_price' => $product['cost'],
                        'selling_price' => $product['mrp'],
                        'updated_at' => now(),
                    ]);
            } else {
                // Insert new product
                $productId = DB::table('products')->insertGetId([
                    'tenant_id' => 3,
                    'business_id' => 3,
                    'sku' => $sku,
                    'name' => $name,
                    'category' => $categoryName,
                    'category_id' => $categoryId,
                    'brand' => $this->getBrand($name),
                    'unit' => 'pcs',
                    'cost_price' => $product['cost'],
                    'selling_price' => $product['mrp'],
                    'minimum_stock' => 0,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Check if inventory exists
            $existingInventory = DB::table('inventory')
                ->where('tenant_id', 3)
                ->where('product_id', $productId)
                ->whereNull('branch_id')
                ->first();
            
            if ($existingInventory) {
                // Update existing inventory
                DB::table('inventory')
                    ->where('id', $existingInventory->id)
                    ->update([
                        'quantity' => $product['qty'],
                        'updated_at' => now(),
                    ]);
            } else {
                // Insert new inventory record
                DB::table('inventory')->insert([
                    'tenant_id' => 3,
                    'business_id' => 3,
                    'product_id' => $productId,
                    'branch_id' => null,
                    'quantity' => $product['qty'],
                    'reserved_quantity' => 0,
                    'location' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete products for tenant_id 3 that were created by this migration
        // Note: This is a simple approach. In production, you might want to be more specific
        DB::table('inventory')->whereIn('product_id', function ($query) {
            $query->select('id')->from('products')->where('tenant_id', 3);
        })->delete();
        
        DB::table('products')->where('tenant_id', 3)->delete();
    }

    /**
     * Get category based on product name
     */
    private function getCategory($name): string
    {
        $name = strtoupper($name);
        
        if (str_contains($name, 'OIL') || str_contains($name, 'OIL FILTER') || str_contains($name, 'ELEMENT OIL')) {
            return 'Oil & Filters';
        }
        
        if (str_contains($name, 'AIR FILTER') || str_contains($name, 'FULE FILTER')) {
            return 'Air & Fuel Filters';
        }
        
        if (str_contains($name, 'WIPER')) {
            return 'Wipers';
        }
        
        if (str_contains($name, 'COOLANT') || str_contains($name, 'WATER')) {
            return 'Coolants & Fluids';
        }
        
        if (str_contains($name, 'SPRAY') || str_contains($name, 'AIR FRESHNER') || str_contains($name, 'TOWEL') || str_contains($name, 'WAX') || str_contains($name, 'DEGREASER') || str_contains($name, 'CLEANER') || str_contains($name, 'LUBRICANT')) {
            return 'Car Care Products';
        }
        
        if (str_contains($name, 'NIPPLE') || str_contains($name, 'CABLE')) {
            return 'Accessories';
        }
        
        if (str_contains($name, 'BRUSH') || str_contains($name, 'DUSTPAN') || str_contains($name, 'PUMP')) {
            return 'Tools & Equipment';
        }
        
        return 'General';
    }

    /**
     * Get brand based on product name
     */
    private function getBrand($name): ?string
    {
        $name = strtoupper($name);
        
        $brands = [
            'MOBIL', 'CALTEX', 'SINOPAC', 'HONDA', 'SUZUKI', 'MITSUBISHI', 'TOYOTA', 
            'NISSAN', 'HYUNDAI', 'TATA', 'SAKURA', 'VIC', 'ELEMFIL', 'KAYSER', 
            'LAYPARTS', 'PREMIER', '777', 'NLK', 'DOBO', 'YTK', 'YACON', 'RAIHAN',
            'GODREJ', 'FLASH', 'QUICK', 'CRYSTAY', 'PIAGIO', 'BAJAJ', 'PUROLATOR',
            'HITECH', 'MAHINDRA', 'ZIP', 'SANKO', 'LOCKHEED', 'REVTRON', 'SUMO'
        ];
        
        foreach ($brands as $brand) {
            if (str_contains($name, $brand)) {
                return $brand;
            }
        }
        
        return null;
    }

    /**
     * Get or create category
     */
    private function getOrCreateCategory($categoryName): int
    {
        $category = DB::table('categories')
            ->where('tenant_id', 3)
            ->where('business_id', 3)
            ->where('name', $categoryName)
            ->first();
        
        if ($category) {
            return $category->id;
        }
        
        return DB::table('categories')->insertGetId([
            'tenant_id' => 3,
            'business_id' => 3,
            'name' => $categoryName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
