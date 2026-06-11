<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
if (!Auth::check()) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

header('Content-Type: application/json');
header('Cache-Control: no-store');

// ── CPU usage (%) ────────────────────────────────────────────────────────────
function getCpuPercent(): float {
    static $prev = null;
    $stat = @file_get_contents('/proc/stat');
    if (!$stat) return 0.0;
    if (!preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/m', $stat, $m)) return 0.0;
    $idle  = (int)$m[4] + (int)$m[5];
    $total = (int)$m[1] + (int)$m[2] + (int)$m[3] + $idle;
    if ($prev !== null) {
        $dt = $total - $prev['total'];
        $di = $idle  - $prev['idle'];
        $pct = $dt > 0 ? round(100 * (1 - $di / $dt), 1) : 0.0;
    } else {
        $pct = 0.0;
    }
    $prev = ['total' => $total, 'idle' => $idle];
    return $pct;
}

// ── CPU temperature ──────────────────────────────────────────────────────────
function getCpuTemp(): float {
    $paths = [
        '/sys/class/thermal/thermal_zone0/temp',
        '/sys/class/hwmon/hwmon0/temp1_input',
        '/sys/class/hwmon/hwmon1/temp1_input',
        '/sys/class/hwmon/hwmon2/temp1_input',
    ];
    foreach ($paths as $p) {
        $v = @file_get_contents($p);
        if ($v !== false && is_numeric(trim($v))) {
            $t = (float)trim($v);
            return round($t > 1000 ? $t / 1000 : $t, 1);
        }
    }
    return 0.0;
}

// ── RAM ──────────────────────────────────────────────────────────────────────
function getRam(): array {
    $info = @file_get_contents('/proc/meminfo');
    if (!$info) return ['used' => 0, 'total' => 0];
    preg_match('/MemTotal:\s+(\d+)/', $info, $mt);
    preg_match('/MemAvailable:\s+(\d+)/', $info, $ma);
    $total = isset($mt[1]) ? round((int)$mt[1] / 1024 / 1024, 2) : 0;
    $avail = isset($ma[1]) ? round((int)$ma[1] / 1024 / 1024, 2) : 0;
    $used  = round($total - $avail, 2);
    return ['used' => max(0, $used), 'total' => $total];
}

// ── Disk usage ───────────────────────────────────────────────────────────────
function getDiskUsage(string $path): array {
    if (!is_dir($path)) return ['used' => 0, 'total' => 0, 'pct' => 0];
    $total = @disk_total_space($path);
    $free  = @disk_free_space($path);
    if ($total === false || $free === false || $total == 0) return ['used' => 0, 'total' => 0, 'pct' => 0];
    $used = $total - $free;
    return [
        'used'  => round($used  / 1e9, 1),
        'total' => round($total / 1e9, 1),
        'pct'   => round(100 * $used / $total, 1),
    ];
}

// ── Main storage: find the largest non-root mounted filesystem ──────────────
function getMainStorageDisk(): array {
    // Common data paths to check first
    $candidates = ['/data', '/mnt/data', '/mnt/storage', '/storage', '/home', '/opt'];
    $rootDisk   = getDiskUsage('/');

    foreach ($candidates as $path) {
        if (!is_dir($path)) continue;
        $d = getDiskUsage($path);
        // Only use if it's a different device from root and has meaningful size (> 5 GB)
        if ($d['total'] > 5 && $d['total'] !== $rootDisk['total']) {
            return $d;
        }
    }

    // Fall back: pick the largest from /proc/mounts that isn't tmpfs/devtmpfs/sysfs/proc
    $mounts = @file('/proc/mounts', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $best   = ['used' => 0, 'total' => 0, 'pct' => 0];
    foreach ($mounts as $line) {
        $parts = explode(' ', $line);
        if (count($parts) < 3) continue;
        $type  = $parts[2];
        $mnt   = $parts[1];
        if (in_array($type, ['tmpfs','devtmpfs','sysfs','proc','cgroup','cgroup2',
                              'devpts','debugfs','hugetlbfs','mqueue','fusectl','pstore'])) continue;
        if ($mnt === '/') continue;
        $d = getDiskUsage($mnt);
        if ($d['total'] > $best['total']) $best = $d;
    }
    // If nothing found that's significantly different, just return root
    return ($best['total'] > 0 && $best['total'] !== $rootDisk['total']) ? $best : $rootDisk;
}

// ── Network bytes ────────────────────────────────────────────────────────────
function getNetBytes(): array {
    $info = @file_get_contents('/proc/net/dev');
    if (!$info) return ['rx' => 0, 'tx' => 0];
    $rx = $tx = 0;
    foreach (explode("\n", $info) as $line) {
        if (strpos($line, ':') === false) continue;
        [, $data] = explode(':', $line, 2);
        $cols = preg_split('/\s+/', trim($data));
        $iface = trim(explode(':', $line)[0]);
        // Skip loopback and virtual interfaces
        if (in_array($iface, ['lo']) || strpos($iface, 'docker') !== false
            || strpos($iface, 'veth') !== false || strpos($iface, 'br-') !== false) continue;
        $rx += (int)($cols[0] ?? 0);
        $tx += (int)($cols[8] ?? 0);
    }
    return ['rx' => $rx, 'tx' => $tx];
}

$cpu     = getCpuPercent();
$temp    = getCpuTemp();
$ram     = getRam();
$ssd     = getDiskUsage('/');
$storage = getMainStorageDisk();
$net     = getNetBytes();

echo json_encode([
    'cpu'         => $cpu,
    'temp'        => $temp,
    'ram_u'       => $ram['used'],
    'ram_t'       => $ram['total'],
    'ssd_u'       => $ssd['used'],
    'ssd_t'       => $ssd['total'],
    'ssd_p'       => $ssd['pct'],
    'storage_u'   => $storage['used'],
    'storage_t'   => $storage['total'],
    'storage_p'   => $storage['pct'],
    'net_rx'      => $net['rx'],
    'net_tx'      => $net['tx'],
    'ts'          => time(),
]);
