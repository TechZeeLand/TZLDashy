<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
Auth::requireAuth();

header('Content-Type: application/json');

function get_cpu_ticks(): array {
    $cpu = preg_split('/\s+/', trim(explode("\n", file_get_contents('/proc/stat'))[0]));
    $total = $cpu[1]+$cpu[2]+$cpu[3]+$cpu[4]+$cpu[5]+$cpu[6]+$cpu[7];
    return ['total'=>$total,'idle'=>$cpu[4]];
}

$s1 = get_cpu_ticks(); usleep(200000); $s2 = get_cpu_ticks();
$dtotal = $s2['total']-$s1['total']; $didle = $s2['idle']-$s1['idle'];
$cpu = $dtotal > 0 ? round(100*(1-($didle/$dtotal)),1) : 0;

preg_match_all('/(\w+):\s+(\d+)\s/', file_get_contents('/proc/meminfo'), $m);
$mem = array_combine($m[1],$m[2]);
$ram_t  = round($mem['MemTotal']/1024/1024,1);
$ram_av = round($mem['MemAvailable']/1024/1024,1);
$ram_u  = round($ram_t-$ram_av,1);
$ram_p  = $ram_t > 0 ? round($ram_u/$ram_t*100,1) : 0;

$ssd_t  = round(disk_total_space('/')/1024**3,1);
$ssd_f  = round(disk_free_space('/')/1024**3,1);
$ssd_u  = round($ssd_t-$ssd_f,1);
$ssd_p  = $ssd_t > 0 ? round($ssd_u/$ssd_t*100,1) : 0;

$raid_u=$raid_t=$raid_p=0;
foreach (['/mnt/raid','/raids','/data'] as $p) {
    if (file_exists($p) && ($rt=@disk_total_space($p)) !== false && $rt > 0) {
        $rf=disk_free_space($p);
        $raid_t=round($rt/1024**3,1); $raid_u=round(($rt-$rf)/1024**3,1);
        $raid_p=$raid_t>0?round($raid_u/$raid_t*100,1):0; break;
    }
}

$temp=0;
foreach (['/sys/class/thermal/thermal_zone0/temp','/sys/class/thermal/thermal_zone1/temp'] as $tp)
    if (file_exists($tp)){$temp=round(file_get_contents($tp)/1000,1);break;}

$net_rx=$net_tx=0;
foreach (explode("\n",file_get_contents('/proc/net/dev')) as $l) {
    if (strpos($l,':')!==false && strpos($l,'lo:')=== false) {
        if (preg_match('/:\s*(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/',$l,$mx)){
            $net_rx+=(int)$mx[1]; $net_tx+=(int)$mx[2];
        }
    }
}

echo json_encode(compact('cpu','temp','ram_u','ram_t','ram_p','ssd_u','ssd_t','ssd_p',
    'raid_u','raid_t','raid_p','net_rx','net_tx'));
