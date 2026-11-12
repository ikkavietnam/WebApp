<?php
/**
 * URL : http://192.168.110.235:8086/trainning/chkPermission
 * Vị trí file: www/trainning/chkPermission/index.php
 *
 * Kiểm tra quyền đọc / ghi của:
 *   \\192.168.110.235\docker\WebStandard\www\App\src\core\log\PROCESS\PROLOG.log
 *
 * !!! XÓA FILE NÀY SAU KHI KIỂM TRA XONG (lộ thông tin hệ thống) !!!
 */

header('Content-Type: text/html; charset=utf-8');

// Nếu đã biết đường dẫn trong container, điền vào đây (vd: /var/www/html/App/src/core/log/PROCESS/PROLOG.log)
$MANUAL_PATH = '';

$REL = '/App/src/core/log/PROCESS/PROLOG.log';

// Các đường dẫn ứng viên (script dùng đường dẫn đầu tiên có thư mục tồn tại)
$candidates = array_filter([
    $MANUAL_PATH,
    dirname(__DIR__, 2) . $REL,                         // www/trainning/chkPermission -> www/App/...
    rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . $REL, // document root + App/...
    '/var/www/html' . $REL,
    '/var/www' . $REL,
]);

$logFile = null;
foreach ($candidates as $c) {
    if (is_dir(dirname($c))) { $logFile = $c; break; }
}
$logFile = $logFile ?? reset($candidates);
$logDir  = dirname($logFile);

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function yn($b) { return $b ? '<b style="color:#0a7d2c">CÓ</b>' : '<b style="color:#c62828">KHÔNG</b>'; }
function permStr($p) { return $p === false ? 'n/a' : substr(sprintf('%o', $p), -4); }
function ownerStr($p) {
    $o = @fileowner($p); $g = @filegroup($p);
    $on = ($o !== false && function_exists('posix_getpwuid')) ? (posix_getpwuid($o)['name'] ?? $o) : $o;
    $gn = ($g !== false && function_exists('posix_getgrgid')) ? (posix_getgrgid($g)['name'] ?? $g) : $g;
    return ($on === false ? 'n/a' : $on) . ':' . ($gn === false ? 'n/a' : $gn);
}

// ====== Thực hiện test thực tế khi bấm nút ======
$result = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'read') {
        $h = @fopen($logFile, 'r');
        if ($h === false) {
            $result = ['ok' => false, 'msg' => 'ĐỌC THẤT BẠI', 'err' => error_get_last()['message'] ?? ''];
        } else {
            $content = stream_get_contents($h, 2000, max(0, (int)filesize($logFile) - 2000));
            fclose($h);
            $result = ['ok' => true, 'msg' => 'ĐỌC THÀNH CÔNG (hiển thị tối đa 2000 byte cuối)', 'data' => $content];
        }
    }

    if ($action === 'write') {
        if (!is_dir($logDir) && !@mkdir($logDir, 0775, true)) {
            $result = ['ok' => false, 'msg' => 'KHÔNG TẠO ĐƯỢC THƯ MỤC', 'err' => error_get_last()['message'] ?? ''];
        } else {
            $line = '[' . date('Y-m-d H:i:s') . '] CHKPERMISSION: test ghi log' . PHP_EOL;
            $n = @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
            $result = ($n === false)
                ? ['ok' => false, 'msg' => 'GHI THẤT BẠI', 'err' => error_get_last()['message'] ?? '']
                : ['ok' => true, 'msg' => "GHI THÀNH CÔNG ($n byte)", 'data' => $line];
        }
    }
    clearstatcache();
}

$dirExists  = is_dir($logDir);
$fileExists = file_exists($logFile);
$phpUser = function_exists('posix_geteuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? posix_geteuid()) : get_current_user();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Kiểm tra quyền PROLOG.log</title>
<style>
  body{font-family:Segoe UI,Arial,sans-serif;margin:30px;max-width:900px;color:#222}
  table{border-collapse:collapse;width:100%;margin-bottom:20px}
  td,th{border:1px solid #ccc;padding:7px 10px;text-align:left;font-size:14px}
  th{background:#f2f2f2;width:260px}
  pre{background:#111;color:#0f0;padding:12px;overflow:auto;white-space:pre-wrap}
  button{padding:8px 18px;font-size:14px;cursor:pointer;margin-right:8px}
  .ok{color:#0a7d2c}.fail{color:#c62828}
  code{background:#f5f5f5;padding:2px 5px}
</style>
</head>
<body>
<h2>Kiểm tra quyền đọc / ghi PROLOG.log</h2>

<h3>1. Môi trường</h3>
<table>
  <tr><th>PHP chạy bằng user</th><td><?= e($phpUser) ?></td></tr>
  <tr><th>PHP version / SAPI</th><td><?= e(PHP_VERSION) ?> / <?= e(PHP_SAPI) ?></td></tr>
  <tr><th>open_basedir</th><td><?= e(ini_get('open_basedir') ?: '(không giới hạn)') ?></td></tr>
  <tr><th>Thư mục script</th><td><?= e(__DIR__) ?></td></tr>
  <tr><th>DOCUMENT_ROOT</th><td><?= e($_SERVER['DOCUMENT_ROOT'] ?? '') ?></td></tr>
</table>

<h3>2. Thư mục log</h3>
<table>
  <tr><th>Đường dẫn đang kiểm tra</th><td><code><?= e($logDir) ?></code></td></tr>
  <tr><th>Tồn tại</th><td><?= yn($dirExists) ?></td></tr>
  <tr><th>Đọc được</th><td><?= yn(is_readable($logDir)) ?></td></tr>
  <tr><th>Ghi được</th><td><?= yn(is_writable($logDir)) ?></td></tr>
  <tr><th>Quyền / Chủ sở hữu</th><td><?= e(permStr(@fileperms($logDir))) ?> &nbsp; <?= e(ownerStr($logDir)) ?></td></tr>
</table>

<h3>3. File PROLOG.log</h3>
<table>
  <tr><th>Đường dẫn đang kiểm tra</th><td><code><?= e($logFile) ?></code></td></tr>
  <tr><th>Tồn tại</th><td><?= yn($fileExists) ?></td></tr>
  <tr><th>Đọc được</th><td><?= yn(is_readable($logFile)) ?></td></tr>
  <tr><th>Ghi được</th><td><?= yn($fileExists ? is_writable($logFile) : is_writable($logDir)) ?>
      <?= $fileExists ? '' : '(file chưa có, xét theo quyền thư mục)' ?></td></tr>
  <tr><th>Quyền / Chủ sở hữu</th><td><?= $fileExists ? e(permStr(@fileperms($logFile))) . ' &nbsp; ' . e(ownerStr($logFile)) : 'n/a' ?></td></tr>
  <tr><th>Dung lượng</th><td><?= $fileExists ? e(number_format((int)filesize($logFile))) . ' byte' : 'n/a' ?></td></tr>
</table>

<h3>4. Test thực tế</h3>
<form method="post">
  <button name="action" value="read">Thử ĐỌC file</button>
  <button name="action" value="write">Thử GHI 1 dòng vào file</button>
</form>

<?php if ($result): ?>
  <p class="<?= $result['ok'] ? 'ok' : 'fail' ?>"><b><?= e($result['msg']) ?></b></p>
  <?php if (!empty($result['err'])): ?><p class="fail">Lỗi PHP: <?= e($result['err']) ?></p><?php endif; ?>
  <?php if (isset($result['data'])): ?><pre><?= e($result['data']) ?></pre><?php endif; ?>
<?php endif; ?>

<h3>Đường dẫn dò thử</h3>
<ul>
<?php foreach ($candidates as $c): ?>
  <li><code><?= e($c) ?></code> — thư mục <?= is_dir(dirname($c)) ? '<span class="ok">tồn tại</span>' : '<span class="fail">không có</span>' ?></li>
<?php endforeach; ?>
</ul>

<p style="color:#c62828"><b>Lưu ý:</b> xóa file này sau khi kiểm tra xong.</p>
</body>
</html>