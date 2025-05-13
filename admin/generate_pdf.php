
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

session_start();
require_once('../config.php');
require_once('../vendor/autoload.php');

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

if (!isset($conn) || !$conn) {
    die(json_encode(['status' => 'error', 'message' => 'Ошибка подключения к базе данных.']));
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die(json_encode(['status' => 'error', 'message' => 'Доступ запрещён. Только для администраторов.']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Некорректный запрос.']));
}

$orderId = (int)$_POST['order_id'];

// Проверка, не существует ли уже накладная для этого заказа
$stmt = $conn->prepare("SELECT * FROM invoices WHERE order_id = ?");
$stmt->execute([$orderId]);
if ($stmt->rowCount() > 0) {
    die(json_encode(['status' => 'error', 'message' => 'Для этого заказа накладная уже существует.']));
}

// Основная информация о заказе
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    die(json_encode(['status' => 'error', 'message' => 'Заказ не найден.']));
}

// Получаем товары из order_items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Если в order_items нет записей, то используем order как 'товар' (под структуру вашей базы)
if (empty($items)) {
    $items[] = [
        'product_name' => $order['product_name'],
        'article'      => $order['article'],
        'quantity'     => $order['quantity'],
        'unit_price'   => $order['price'],
    ];
}

$stmt = $conn->query("SELECT MAX(invoice_number) AS max_invoice FROM invoices");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$nextInvoiceNumber = ($row && $row['max_invoice']) ? $row['max_invoice'] + 1 : 1;

// Считаем итог
$totalOrder = 0;
foreach ($items as $item) {
    $totalOrder += $item['quantity'] * $item['unit_price'];
}
$vat = $totalOrder * 0.20;
$grandTotal = $totalOrder + $vat;

// Генерируем HTML-документ для PDF
$html = '
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Накладная № ' . $nextInvoiceNumber . '</title>
<style>
body { font-family: DejaVu Sans, sans-serif; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px;}
td, th { border: 1px solid #bbb; padding: 6px 8px; font-size:13px; }
th { background: #f6f6f6; }
.header { font-size: 18px; margin: 10px 0 0 0; }
.info { font-size: 13px; margin-bottom: 2px; }
.right { text-align: right; }
</style>
</head>
<body>
<div class="header"><b>Накладная № ' . $nextInvoiceNumber . '</b></div>
<div class="info">Клиент: ' . htmlspecialchars($order['customer_name']) . '</div>
<div class="info">Телефон: ' . htmlspecialchars($order['phone']) . '</div>
<div class="info">Адрес: ' . htmlspecialchars($order['address']) . '</div>
<div class="info">Дата заказа: ' . date("d.m.Y", strtotime($order['created_at'])) . '</div>
<br>
<table>
    <thead>
        <tr>
            <th>Наименование</th>
            <th>Артикул</th>
            <th>Кол-во</th>
            <th>Цена</th>
            <th>Сумма</th>
        </tr>
    </thead>
    <tbody>';

foreach ($items as $item) {
    $lineTotal = $item['quantity'] * $item['unit_price'];
    $html .= '<tr>
        <td>' . htmlspecialchars($item['product_name']) . '</td>
        <td>' . htmlspecialchars($item['article']) . '</td>
        <td class="right">' . (int)$item['quantity'] . '</td>
        <td class="right">' . number_format($item['unit_price'], 2, ',', ' ') . '</td>
        <td class="right">' . number_format($lineTotal, 2, ',', ' ') . '</td>
    </tr>';
}

$html .= '</tbody>
</table>
<table style="width:60%; margin-left:auto; font-size: 15px;">
<tr><td class="right">Итого:</td><td class="right"><b>' . number_format($totalOrder, 2, ',', ' ') . '</b></td></tr>
<tr><td class="right">НДС 20%:</td><td class="right"><b>' . number_format($vat, 2, ',', ' ') . '</b></td></tr>
<tr><td class="right">Всего к оплате:</td><td class="right"><b>' . number_format($grandTotal, 2, ',', ' ') . '</b></td></tr>
</table>
</body>
</html>
';


try {
    $mpdf = new Mpdf([
        'format' => 'A4',
        'margin_top' => 40,
        'margin_bottom' => 20,
        'margin_left' => 12,
        'margin_right' => 12,
        'mode' => 'utf-8',
        'default_font' => 'dejavusans',
        'default_font_size' => 12,
        'tempDir' => __DIR__ . '/../tmp',
    ]);

    $mpdf->autoScriptToLang = true;
    $mpdf->autoLangToFont = true;
    $mpdf->SetDisplayMode('fullpage');

    $mpdf->WriteHTML($html);

    $fileName = "../invoices/invoice_" . $nextInvoiceNumber . ".pdf";
    $mpdf->Output($fileName, Destination::FILE);

    $stmt = $conn->prepare("INSERT INTO invoices (order_id, invoice_number, pdf_path, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$orderId, $nextInvoiceNumber, $fileName]);

    echo json_encode(['status' => 'success', 'message' => 'Накладная сгенерирована.', 'file' => $fileName]);
} catch (\Throwable $e) { // Ловим ВСЕ ошибки, не только MpdfException
    // Для отладки — видно весь текст, файл и строку, и trace!
    $errorInfo = [
        'status' => 'error',
        'message' => 'Ошибка PDF: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
    // можно еще писать в лог, если нужно
    // file_put_contents(__DIR__.'/pdf_error.log', print_r($errorInfo,1)."\n", FILE_APPEND);
    echo json_encode($errorInfo);
}
