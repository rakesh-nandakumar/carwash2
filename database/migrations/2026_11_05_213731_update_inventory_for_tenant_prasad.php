<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tenantId = 3; // Prasad tenant ID
        $businessId = 3; // Prasad business ID

        $inventoryData = [
            ['name' => 'PIAGGIO DIESEL FILTER BOSCH', 'qty' => 3, 'cost' => 480],
            ['name' => 'PIAGGIO OIL FILTER ELEMENT', 'qty' => 0, 'cost' => 400],
            ['name' => 'BAJAJ OIL FILTER', 'qty' => 20, 'cost' => 400],
            ['name' => 'PUROLATOR OIL FILTER', 'qty' => 4, 'cost' => 712],
            ['name' => 'TOYOTA OIL FILTER C-112', 'qty' => 3, 'cost' => 1480],
            ['name' => 'HITECH OIL FILTER', 'qty' => 2, 'cost' => 760],
            ['name' => 'MAHINDRA OIL FILTER', 'qty' => 3, 'cost' => 1160],
            ['name' => 'ZIP OIL FILTER', 'qty' => 4, 'cost' => 1120],
            ['name' => 'NISSAN OIL FILTER C-209', 'qty' => 0, 'cost' => 1880],
            ['name' => 'PREMIUM OIL FILTER', 'qty' => 0, 'cost' => 1200],
            ['name' => 'SANKO OIL FILTER C-209', 'qty' => 0, 'cost' => 1200],
            ['name' => 'MITSUBISHI 220900 OIL FILTER', 'qty' => 1, 'cost' => 1840],
            ['name' => 'OIL FILTER 1230A046', 'qty' => 1, 'cost' => 2080],
            ['name' => 'FUEL FILTER TOYOTA 23303-64010', 'qty' => 0, 'cost' => 1840],
            ['name' => 'OIL FILTER C-518', 'qty' => 2, 'cost' => 2800],
            ['name' => 'WIPER 12" SPARCO', 'qty' => 3, 'cost' => 1200],
            ['name' => 'WIPER 14" SPARCO', 'qty' => 3, 'cost' => 1200],
            ['name' => 'WIPER 16" SPARCO', 'qty' => 3, 'cost' => 1200],
            ['name' => 'WIPER 17" SPARCO', 'qty' => 3, 'cost' => 1200],
            ['name' => 'WIPER 18" SPARCO', 'qty' => 1, 'cost' => 1280],
            ['name' => 'WIPER 19" SPARCO', 'qty' => 3, 'cost' => 1280],
            ['name' => 'WIPER 20" SPARCO', 'qty' => 3, 'cost' => 1280],
            ['name' => 'WIPER 21" SPARCO', 'qty' => 3, 'cost' => 1280],
            ['name' => 'WIPER 22" SPARCO', 'qty' => 3, 'cost' => 1280],
            ['name' => 'WIPER 24" SPARCO', 'qty' => 3, 'cost' => 1280],
            ['name' => 'WIPER 26" SPARCO', 'qty' => 3, 'cost' => 1280],
            ['name' => 'NAYLONE CABLE 3.6*150MM', 'qty' => 200, 'cost' => 12],
            ['name' => 'NAYLONE CABLE 3.6*300MM', 'qty' => 100, 'cost' => 12],
            ['name' => 'NAYLONE CABLE 4.8*200MM', 'qty' => 100, 'cost' => 12],
            ['name' => 'NAYLONE CABLE 3.6*250MM', 'qty' => 100, 'cost' => 12],
            ['name' => 'NAYLONE CABLE 4.8*400MM', 'qty' => 100, 'cost' => 24],
            ['name' => 'NAYLONE CABLE 4*500MM', 'qty' => 100, 'cost' => 24],
            ['name' => 'MOBIL OIL SUPER 5W-30 (4L)', 'qty' => 0, 'cost' => 14384],
            ['name' => 'MOBIL OIL SUPER 10W-30 (4L)', 'qty' => 0, 'cost' => 12720],
            ['name' => 'MOBIL OIL SUPER 15W-40 (4L)', 'qty' => 0, 'cost' => 12720],
            ['name' => 'MOBIL OIL DELVAC 15W-40 (4L)', 'qty' => 0, 'cost' => 14320],
            ['name' => 'MOBIL OIL DELVAC 15W-40 (1L)', 'qty' => 0, 'cost' => 2624],
            ['name' => 'MOBIL OIL SUPER DS 10W-30 (1L)', 'qty' => 0, 'cost' => 3188],
            ['name' => 'MOBIL OIL SUPER DS 10W-30 (1L) 3IN1', 'qty' => 0, 'cost' => 9560],
            ['name' => 'MOBIL OIL SUPER DS 5W-30 (1L) 3IN1', 'qty' => 0, 'cost' => 9176],
            ['name' => 'MOBIL OIL SUPER DC 5W-30 (1L)', 'qty' => 0, 'cost' => 3060],
            ['name' => 'MOBIL OIL SUPER 15W-40 (1L)', 'qty' => 0, 'cost' => 3188],
            ['name' => 'CALTEX OIL SUPER DS 15W-40 (6L)', 'qty' => 1, 'cost' => 13056],
            ['name' => 'CALTEX OIL GOLD ULTRA 15W-40 (6L)', 'qty' => 0, 'cost' => 17760],
            ['name' => 'CALTEX OIL HAVOLINE 15W-40 (4L)', 'qty' => 1, 'cost' => 11424],
            ['name' => 'CALTEX OIL SUPER DS 15W-40 (1L)', 'qty' => 3, 'cost' => 3140],
            ['name' => 'CALTEX OIL TEXAMATIC 1888 (1L)', 'qty' => 2, 'cost' => 4200],
            ['name' => 'SINOPAC TILUX T500 15W-40 (5L)', 'qty' => 0, 'cost' => 12200],
            ['name' => 'SINOPAC JUSTAR 1500 10W-40', 'qty' => 0, 'cost' => 10520],
            ['name' => 'HONDA HMMF OIL (4L)', 'qty' => 1, 'cost' => 16800],
            ['name' => 'SUZUKI OIL (4L)', 'qty' => 1, 'cost' => 16800],
            ['name' => 'MITSUBISHI OIL (4L)', 'qty' => 1, 'cost' => 16800],
            ['name' => 'ELEMENT OIL FILTER 04152-37010', 'qty' => 2, 'cost' => 600],
            ['name' => 'ELEMENT OIL C-224', 'qty' => 2, 'cost' => 760],
            ['name' => 'ELEMENT OIL C-415', 'qty' => 2, 'cost' => 760],
            ['name' => 'ELEMENT OIL C-111', 'qty' => 2, 'cost' => 760],
            ['name' => 'ELEMENT OIL C-809', 'qty' => 2, 'cost' => 760],
            ['name' => 'ELEMENT OIL C-932', 'qty' => 2, 'cost' => 584],
            ['name' => 'ELEMENT OIL C-306', 'qty' => 2, 'cost' => 1440],
            ['name' => 'ELEMENT OIL C-112', 'qty' => 2, 'cost' => 1520],
            ['name' => 'ELEMENT OIL FILTER 15600-41010', 'qty' => 0, 'cost' => 1040],
            ['name' => 'BREAK OIL LOCKHEED 500ML', 'qty' => 9, 'cost' => 2072],
            ['name' => 'BREAK OIL LOCKHEED 250ML', 'qty' => 2, 'cost' => 1160],
            ['name' => 'CALTEX OIL 250ML', 'qty' => 7, 'cost' => 1156],
            ['name' => 'CALTEX OIL 500ML', 'qty' => 5, 'cost' => 1992],
            ['name' => 'GREEN COOLANT 5L', 'qty' => 1, 'cost' => 1440],
            ['name' => 'REVTRON 10W-30', 'qty' => 4, 'cost' => 2500],
            ['name' => 'REVTRON 20W-50', 'qty' => 3, 'cost' => 2312],
            ['name' => 'REVTRON 20W-40', 'qty' => 3, 'cost' => 2312],
            ['name' => 'FLASH D.WATER 500ML', 'qty' => 6, 'cost' => 116],
            ['name' => 'FLASH D.WATER 1L', 'qty' => 10, 'cost' => 180],
            ['name' => 'AIR FILTER 13780-50M00', 'qty' => 2, 'cost' => 1480],
            ['name' => 'AIR FILTER TOYOTA (A194)', 'qty' => 2, 'cost' => 1480],
            ['name' => 'AIR FILTER TOYOTA KDH (A1025)', 'qty' => 1, 'cost' => 1480],
            ['name' => 'AIR FILTER TOYOTA 744', 'qty' => 1, 'cost' => 1480],
            ['name' => 'AIR FILTER TOYOTA A 1027', 'qty' => 2, 'cost' => 1480],
            ['name' => 'AIR FILTER HONDA 17220-5R0-008', 'qty' => 2, 'cost' => 1480],
            ['name' => 'AIR FILTER 17220-RJB-000', 'qty' => 2, 'cost' => 1480],
            ['name' => 'AIR FILTER SUZUKI 13780-74P00', 'qty' => 1, 'cost' => 1480],
            ['name' => 'AIR FILTER SUZUKI 13780-68H00', 'qty' => 2, 'cost' => 1480],
            ['name' => 'AIR FILTER TOYOTA 17801-77050', 'qty' => 2, 'cost' => 1480],
            ['name' => 'AIR FILTER 17801-21030', 'qty' => 2, 'cost' => 1480],
            ['name' => 'AIR FILTER 17801-22020', 'qty' => 2, 'cost' => 1480],
            ['name' => 'NISSAN LUFT FILTER', 'qty' => 2, 'cost' => 1480],
            ['name' => 'AIR FILTER 13780-53M00', 'qty' => 1, 'cost' => 1480],
            ['name' => 'FULE FILTER ZD-3135', 'qty' => 0, 'cost' => 1920],
            ['name' => 'TATA OIL FILTER', 'qty' => 2, 'cost' => 1840],
            ['name' => 'TATA WATER SEPARATOR', 'qty' => 2, 'cost' => 2160],
            ['name' => 'NISSAN OIL FILTER', 'qty' => 3, 'cost' => 1160],
            ['name' => 'NISSAN FULE FILTER', 'qty' => 3, 'cost' => 1160],
            ['name' => 'TOYOTA OIL FILTER 04152-40060', 'qty' => 6, 'cost' => 1480],
            ['name' => 'HYUNDAI OIL FILTER', 'qty' => 2, 'cost' => 1480],
            ['name' => 'TOYOTA OIL FILTER 90915-TD004', 'qty' => 2, 'cost' => 1480],
            ['name' => 'TOYOTA OIL FILTER 90915-30002', 'qty' => 5, 'cost' => 1480],
            ['name' => 'SAKURA OIL FILTER C-5816', 'qty' => 1, 'cost' => 3744],
            ['name' => 'SAKURA OIL FILTER C-1502', 'qty' => 4, 'cost' => 2120],
            ['name' => 'SAKURA OIL FILTER C-1701', 'qty' => 2, 'cost' => 1408],
            ['name' => 'SAKURA OIL FILTER C-1404', 'qty' => 3, 'cost' => 1000],
            ['name' => 'SAKURA OIL FILTER C-1204', 'qty' => 1, 'cost' => 1000],
            ['name' => 'SAKURA OIL FILTER C-1109', 'qty' => 0, 'cost' => 1000],
            ['name' => 'SAKURA OIL FILTER C-1005', 'qty' => 0, 'cost' => 1200],
            ['name' => 'SAKURA OIL FILTER C-1025', 'qty' => 0, 'cost' => 1200],
            ['name' => 'SAKURA OIL FILTER C-1142', 'qty' => 5, 'cost' => 1200],
            ['name' => 'SAKURA OIL FILTER EO-1103', 'qty' => 3, 'cost' => 1600],
            ['name' => 'SAKURA OIL FILTER C-10081', 'qty' => 3, 'cost' => 2380],
            ['name' => 'SAKURA OIL FILTER EO-10060', 'qty' => 1, 'cost' => 3580],
            ['name' => 'SAKURA OIL FILTER O-1501', 'qty' => 2, 'cost' => 1800],
            ['name' => 'SAKURA OIL FILTER C-1111', 'qty' => 1, 'cost' => 2040],
            ['name' => 'SAKURA OIL FILTER C-1121', 'qty' => 0, 'cost' => 1320],
            ['name' => 'SAKURA OIL FILTER O-7802', 'qty' => 3, 'cost' => 1480],
            ['name' => 'SAKURA OIL FILTER C-112', 'qty' => 3, 'cost' => 2120],
            ['name' => 'SAKURA OIL FILTER C-1806', 'qty' => 0, 'cost' => 1500],
            ['name' => 'SAKURA OIL FILTER C-1814', 'qty' => 0, 'cost' => 1500],
            ['name' => 'SAKURA OIL FILTER C-1805', 'qty' => 1, 'cost' => 1120],
            ['name' => 'SAKURA OIL FILTER C-1506', 'qty' => 0, 'cost' => 4280],
            ['name' => 'SAKURA OIL FILTER C-1803', 'qty' => 1, 'cost' => 1480],
            ['name' => 'SAKURA OIL FILTER C-1806', 'qty' => 0, 'cost' => 1500],
            ['name' => 'SAKURA OIL FILTER C-1511', 'qty' => 3, 'cost' => 1780],
            ['name' => 'SAKURA OIL FILTER C-1812', 'qty' => 0, 'cost' => 2280],
            ['name' => 'SAKURA OIL FILTER C-1102', 'qty' => 3, 'cost' => 1600],
            ['name' => 'SAKURA OIL FILTER C-2103', 'qty' => 2, 'cost' => 1500],
            ['name' => 'SAKURA OIL FILTER C-1513', 'qty' => 1, 'cost' => 5400],
            ['name' => 'SAKURA OIL FILTER C-7982', 'qty' => 5, 'cost' => 1760],
            ['name' => 'SAKURA OIL FILTER C-1823', 'qty' => 9, 'cost' => 1000],
            ['name' => 'SAKURA OIL FILTER O-1502', 'qty' => 4, 'cost' => 1260],
            ['name' => 'SAKURA OIL FILTER O-1503', 'qty' => 3, 'cost' => 1120],
            ['name' => 'SAKURA OIL FILTER O-1001', 'qty' => 2, 'cost' => 2380],
            ['name' => 'SAKURA OIL FILTER O-1016', 'qty' => 3, 'cost' => 1420],
            ['name' => 'SAKURA OIL FILTER FC-1821', 'qty' => 1, 'cost' => 1140],
            ['name' => 'SAKURA OIL FILTER FC-1004', 'qty' => 3, 'cost' => 1408],
            ['name' => 'SAKURA OIL FILTER C-1006', 'qty' => 3, 'cost' => 1720],
            ['name' => 'SAKURA OIL FILTER C-1503', 'qty' => 1, 'cost' => 920],
            ['name' => 'SAKURA OIL FILTER FC-1702', 'qty' => 2, 'cost' => 1320],
            ['name' => 'SAKURA OIL FILTER FC-1803', 'qty' => 0, 'cost' => 1760],
            ['name' => 'SAKURA OIL FILTER F-1111', 'qty' => 3, 'cost' => 1728],
            ['name' => 'SAKURA OIL FILTER O-1501', 'qty' => 2, 'cost' => 1800],
            ['name' => 'SAKURA OIL FILTER FC-1104', 'qty' => 0, 'cost' => 1760],
            ['name' => 'SAKURA OIL FILTER FC-1001', 'qty' => 3, 'cost' => 1760],
            ['name' => 'SAKURA OIL FILTER C-1523', 'qty' => 2, 'cost' => 4240],
            ['name' => 'SAKURA OIL FILTER C-1712', 'qty' => 3, 'cost' => 2000],
            ['name' => 'SAKURA OIL FILTER C-7976', 'qty' => 3, 'cost' => 1940],
            ['name' => 'SAKURA OIL FILTER C-5302', 'qty' => 2, 'cost' => 2900],
            ['name' => 'SAKURA OIL FILTER C-1515', 'qty' => 4, 'cost' => 3424],
            ['name' => 'SAKURA OIL FILTER C-1524', 'qty' => 2, 'cost' => 4460],
            ['name' => 'SAKURA OIL FILTER O-1511', 'qty' => 3, 'cost' => 1800],
            ['name' => 'VIC OIL FILTER C-406', 'qty' => 2, 'cost' => 1800],
            ['name' => 'ELEMFIL FC-208 OIL FILTER', 'qty' => 3, 'cost' => 972],
            ['name' => 'SF OIL SFF 8422', 'qty' => 3, 'cost' => 2760],
            ['name' => 'SF-5052', 'qty' => 3, 'cost' => 1344],
            ['name' => 'SFO-3349', 'qty' => 2, 'cost' => 1344],
            ['name' => 'KAYSER OIL FILTER KAS-809', 'qty' => 5, 'cost' => 2384],
            ['name' => 'LAYPARTS OIL FILTER', 'qty' => 4, 'cost' => 736],
            ['name' => 'AIR FILTER AS-1031', 'qty' => 2, 'cost' => 4580],
            ['name' => 'AIR FILTER A-1039', 'qty' => 3, 'cost' => 4580],
            ['name' => 'AIR FILTER AS-1708', 'qty' => 2, 'cost' => 3000],
            ['name' => 'AIR FILTER AS-1712', 'qty' => 2, 'cost' => 3000],
            ['name' => 'AIR FILTER A-1510', 'qty' => 2, 'cost' => 3000],
            ['name' => 'AIR FILTER A-1118', 'qty' => 2, 'cost' => 3160],
            ['name' => 'AIR FILTER A-1502', 'qty' => 2, 'cost' => 3440],
            ['name' => 'AIR FILTER A-1167', 'qty' => 2, 'cost' => 2200],
            ['name' => 'AIR FILTER A-1117', 'qty' => 1, 'cost' => 2120],
            ['name' => 'AIR FILTER A-1714', 'qty' => 2, 'cost' => 5720],
            ['name' => 'AIR FILTER A-1007', 'qty' => 2, 'cost' => 2560],
            ['name' => 'AIR FILTER KAYSER KA-170 17801-54100', 'qty' => 2, 'cost' => 1808],
            ['name' => 'AIR FILTER LH113', 'qty' => 1, 'cost' => 1808],
            ['name' => 'AIR FILTER SUMO AF-31008', 'qty' => 3, 'cost' => 1360],
            ['name' => 'AIR FILTER TOYOTA 17801-0C010', 'qty' => 1, 'cost' => 1440],
            ['name' => 'NIPPLE 6*50', 'qty' => 35, 'cost' => 80],
            ['name' => 'NIPPLE 8*20', 'qty' => 37, 'cost' => 80],
            ['name' => 'NIPPLE X', 'qty' => 33, 'cost' => 60],
            ['name' => 'NIPPLE Y', 'qty' => 32, 'cost' => 60],
            ['name' => 'PREMIER WIPER 12"', 'qty' => 3, 'cost' => 1400],
            ['name' => 'PREMIER WIPER 14"', 'qty' => 3, 'cost' => 1480],
            ['name' => 'PREMIER WIPER 16"', 'qty' => 3, 'cost' => 1520],
            ['name' => 'PREMIER WIPER 17"', 'qty' => 2, 'cost' => 1560],
            ['name' => 'PREMIER WIPER 18"', 'qty' => 2, 'cost' => 1680],
            ['name' => 'PREMIER WIPER 19"', 'qty' => 2, 'cost' => 1760],
            ['name' => 'PREMIER WIPER 20"', 'qty' => 3, 'cost' => 1800],
            ['name' => 'PREMIER WIPER 21"', 'qty' => 1, 'cost' => 1880],
            ['name' => 'PREMIER WIPER 22"', 'qty' => 2, 'cost' => 1960],
            ['name' => 'PREMIER WIPER 24"', 'qty' => 2, 'cost' => 2080],
            ['name' => 'PREMIER WIPER 26"', 'qty' => 3, 'cost' => 2200],
            ['name' => '777 WIPER 24"', 'qty' => 0, 'cost' => 960],
            ['name' => 'NLK (B) WIPER 22"', 'qty' => 0, 'cost' => 960],
            ['name' => 'NLK (R) WIPER 21"', 'qty' => 0, 'cost' => 1360],
            ['name' => 'DOBO WIPER 19"', 'qty' => 0, 'cost' => 960],
            ['name' => 'YTK WIPER 18"', 'qty' => 0, 'cost' => 960],
            ['name' => 'YACON WIPER 17"', 'qty' => 0, 'cost' => 960],
            ['name' => 'YTK WIPER 16"', 'qty' => 0, 'cost' => 960],
            ['name' => 'HIGH PRESHER POWDER 4KG', 'qty' => 2, 'cost' => 2384],
            ['name' => 'RAIHAN AIR FRESHNER LEMON', 'qty' => 12, 'cost' => 320],
            ['name' => 'RAIHAN AIR FRESHNER COOL ICE', 'qty' => 11, 'cost' => 320],
            ['name' => 'RAIHAN AIR FRESHNER OUD', 'qty' => 13, 'cost' => 320],
            ['name' => 'CHAMOIS TOWET (S)', 'qty' => 3, 'cost' => 520],
            ['name' => 'CHAMOIS TOWET (L)', 'qty' => 4, 'cost' => 800],
            ['name' => 'TOWEL', 'qty' => 14, 'cost' => 280],
            ['name' => 'GODREJ SPRAY 220ML', 'qty' => 0, 'cost' => 800],
            ['name' => 'GODREJ AER 475ML', 'qty' => 0, 'cost' => 640],
            ['name' => 'CAR CLICK GEL', 'qty' => 3, 'cost' => 560],
            ['name' => 'GODREJ POWER POCKET LEMON', 'qty' => 3, 'cost' => 288],
            ['name' => 'GODREJ POWER POCKET SEA BREEZA', 'qty' => 19, 'cost' => 288],
            ['name' => 'GODREJ POWER POCKET JASMINE', 'qty' => 4, 'cost' => 288],
            ['name' => 'GODREJ POWER POCKET ROSE', 'qty' => 0, 'cost' => 288],
            ['name' => 'GODREJ POWER POCKET BERRY', 'qty' => 17, 'cost' => 288],
            ['name' => 'GODREJ POWER POCKET LAVENDER', 'qty' => 14, 'cost' => 288],
            ['name' => '4X COOLANT 346ML', 'qty' => 0, 'cost' => 1160],
            ['name' => 'QUICK WAX 750ML', 'qty' => 0, 'cost' => 280],
            ['name' => 'PLASTIC REFURBISHING AND BRIGHT 500ML', 'qty' => 1, 'cost' => 2224],
            ['name' => 'BRAKE CLEANER 450ML', 'qty' => 1, 'cost' => 880],
            ['name' => 'CARE WINDOW LUBRICANT 450ML', 'qty' => 3, 'cost' => 880],
            ['name' => 'ENGINE SURFACE DEGREASER 500ML', 'qty' => 8, 'cost' => 1480],
            ['name' => 'CRYSTAL WAX 473ML', 'qty' => 2, 'cost' => 2080],
            ['name' => 'RAIN STAIN REMOVER', 'qty' => 0, 'cost' => 5600],
            ['name' => 'GEAR OIL 18L (46000/-)', 'qty' => 18, 'cost' => 2044.45],
            ['name' => '4XL COOLENT 4L', 'qty' => 6, 'cost' => 4200],
            ['name' => 'RUBBER WIPER', 'qty' => 1, 'cost' => 520],
            ['name' => 'BRUSH (B)', 'qty' => 4, 'cost' => 400],
            ['name' => 'OIL PUMP', 'qty' => 1, 'cost' => 600],
            ['name' => 'FORM CLEAN', 'qty' => 12, 'cost' => 1320],
            ['name' => 'PITCH STOP CLEANER', 'qty' => 4, 'cost' => 1000],
            ['name' => 'UNDER BODY PROTECTION WAX', 'qty' => 22, 'cost' => 1760],
            ['name' => 'ENGINE SURFACE DEGREASER 650ML', 'qty' => 10, 'cost' => 1160],
        ];

        foreach ($inventoryData as $item) {
            // Calculate selling price with 22% markup (same as existing migration)
            $sellingPrice = round($item['cost'] * 1.22, 2);

            // Generate SKU from product name (uppercase, replace spaces with underscores)
            $sku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '_', $item['name']), 0, 50));

            // Check if product exists for this tenant
            $existingProduct = DB::table('products')
                ->where('tenant_id', $tenantId)
                ->where('name', $item['name'])
                ->first();

            if ($existingProduct) {
                // Update existing product cost_price and selling_price
                DB::table('products')
                    ->where('id', $existingProduct->id)
                    ->update([
                        'cost_price' => $item['cost'],
                        'selling_price' => $sellingPrice,
                        'updated_at' => now(),
                    ]);

                // Update or insert inventory record
                $existingInventory = DB::table('inventory')
                    ->where('tenant_id', $tenantId)
                    ->where('business_id', $businessId)
                    ->where('product_id', $existingProduct->id)
                    ->whereNull('branch_id')
                    ->first();

                if ($existingInventory) {
                    DB::table('inventory')
                        ->where('id', $existingInventory->id)
                        ->update([
                            'quantity' => $item['qty'],
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('inventory')->insert([
                        'tenant_id' => $tenantId,
                        'business_id' => $businessId,
                        'product_id' => $existingProduct->id,
                        'branch_id' => null,
                        'quantity' => $item['qty'],
                        'reserved_quantity' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                // Insert new product
                $productId = DB::table('products')->insertGetId([
                    'tenant_id' => $tenantId,
                    'business_id' => $businessId,
                    'sku' => $sku,
                    'name' => $item['name'],
                    'cost_price' => $item['cost'],
                    'selling_price' => $sellingPrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insert inventory record for new product
                DB::table('inventory')->insert([
                    'tenant_id' => $tenantId,
                    'business_id' => $businessId,
                    'product_id' => $productId,
                    'branch_id' => null,
                    'quantity' => $item['qty'],
                    'reserved_quantity' => 0,
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
        // Note: This migration cannot be easily reversed as it updates existing data
        // Consider restoring from backup if needed
    }
};
