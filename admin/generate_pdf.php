<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once('../config.php'); // Подключение к базе

 // Подключаем автозагрузчик Composer для FPDI и FPDF
 require_once('../vendor/autoload.php');
 
 use setasign\Fpdi\Fpdi; // Использование класса FPDI
 // Удалены require_once 'fpdf.php' и require_once 'fpdi.php', так как они подключаются через автолоадер
 error_log("generate_pdf.php: Запущен скрипт, проверка прав администратора.");



// Проверяем, что пользователь – администратор
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Для AJAX можно вернуть JSON, здесь простое сообщение
    die(json_encode(["status" => "error", "message" => "Access Denied. Admins only."]));
}

// Проверяем, что передан order_id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id'])) {
    error_log("generate_pdf.php: Неверный запрос или отсутствует order_id.");
    die(json_encode(["status" => "error", "message" => "Invalid request."]));
}

$orderId = $_POST['order_id'];
error_log("generate_pdf.php: Получен order_id = " . $orderId);

// Получаем данные заказа из таблицы orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    error_log("generate_pdf.php: Заказ с id " . $orderId . " не найден.");
    die(json_encode(["status" => "error", "message" => "Order not found."]));
}

// Получаем список товаров для заказа из таблицы order_items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
error_log("generate_pdf.php: Найдено товаров: " . count($items));

// Определяем следующий номер накладной из таблицы invoices
$stmt = $conn->query("SELECT MAX(invoice_number) AS max_invoice FROM invoices");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$nextInvoiceNumber = ($row && $row['max_invoice']) ? $row['max_invoice'] + 1 : 1;
error_log("generate_pdf.php: Следующий номер накладной: " . $nextInvoiceNumber);

// Инициализируем FPDI и загружаем PDF-шаблон
$pdf = new Fpdi();
$pdf->AddPage();
$templatePath = '../template.pdf'; // Используем готовый шаблон template.pdf
if (!file_exists($templatePath)) {
    error_log("generate_pdf.php: Файл шаблона не найден по пути: " . $templatePath);
    die(json_encode(["status" => "error", "message" => "PDF template file not found."]));
} else {
    error_log("generate_pdf.php: Файл шаблона найден по пути: " . $templatePath);
}
$pageCount = $pdf->setSourceFile($templatePath);
$tplIdx = $pdf->importPage(1);
$pdf->useTemplate($tplIdx);
$invoicesDir = realpath('../invoices');
if (!$invoicesDir || !is_writable($invoicesDir)) {
    error_log("generate_pdf.php: Каталог invoices отсутствует или недоступен для записи. Используем путь: " . '../invoices');
    die(json_encode(["status" => "error", "message" => "Invoices directory missing or not writable."]));
} else {
    error_log("generate_pdf.php: Каталог invoices найден и доступен для записи: " . $invoicesDir);
}

// Устанавливаем шрифт и цвет текста
$pdf->SetFont('Helvetica', '', 12);
$pdf->SetTextColor(0, 0, 0);

// Вставляем шапку накладной с данными заказа
$pdf->SetXY(20, 30);
$pdf->Write(0, "Накладная №: " . $nextInvoiceNumber);
$pdf->SetXY(20, 40);
$pdf->Write(0, "Клиент: " . $order['customer_name']);
$pdf->SetXY(20, 50);
$pdf->Write(0, "Телефон: " . $order['phone']);
$pdf->SetXY(20, 60);
$pdf->Write(0, "Дата заказа: " . $order['created_at']);

// Вставляем заголовок таблицы с товарами
$pdf->SetXY(20, 80);
$pdf->Cell(50, 10, "Наименование", 0, 0);
$pdf->Cell(30, 10, "Артикул", 0, 0);
$pdf->Cell(20, 10, "Кол-во", 0, 0);
$pdf->Cell(30, 10, "Цена", 0, 0);
$pdf->Cell(30, 10, "Сумма", 0, 1);

$y = 90;
$totalOrder = 0;
foreach ($items as $item) {
    $lineTotal = $item['quantity'] * $item['unit_price'];
    $totalOrder += $lineTotal;
    $pdf->SetXY(20, $y);
    $pdf->Cell(50, 10, $item['product_name'], 0, 0);
    $pdf->Cell(30, 10, $item['article'], 0, 0);
    $pdf->Cell(20, 10, $item['quantity'], 0, 0);
    $pdf->Cell(30, 10, number_format($item['unit_price'], 2), 0, 0);
    $pdf->Cell(30, 10, number_format($lineTotal, 2), 0, 1);
    $y += 10;
}

// Рассчитываем итоговую сумму, НДС (20%) и общую сумму к оплате
$vat = $totalOrder * 0.20;
$grandTotal = $totalOrder + $vat;
$pdf->SetXY(20, $y + 10);
$pdf->Write(0, "Итого: " . number_format($totalOrder, 2));
$pdf->SetXY(20, $y + 20);
$pdf->Write(0, "НДС 20%: " . number_format($vat, 2));
$pdf->SetXY(20, $y + 30);
$pdf->Write(0, "Всего к оплате: " . number_format($grandTotal, 2));

// Задаем путь для сохранения сгенерированного PDF-файла
$pdfPath = '../invoices/invoice_' . $nextInvoiceNumber . '.pdf';
$pdf->Output('F', $pdfPath);
error_log("generate_pdf.php: PDF успешно создан и сохранен по пути: " . $pdfPath);

// Сохраняем информацию о сгенерированной накладной в таблице invoices
$stmt = $conn->prepare("INSERT INTO invoices (order_id, invoice_number, pdf_path, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$orderId, $nextInvoiceNumber, $pdfPath]);
error_log("generate_pdf.php: Информация о накладной сохранена в базе данных.");

// Возвращаем JSON-ответ для AJAX (если необходимо)
echo json_encode(["status" => "success", "message" => "PDF накладная успешно создана.", "pdf_path" => $pdfPath]);
exit;
?>
