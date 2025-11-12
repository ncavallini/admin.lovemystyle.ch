<?php
declare(strict_types=1);

require_once __DIR__ . "/../actions_init.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $db = DBConnection::get_db_connection();

    // --- Input ---
    $saleId       = $_POST['sale_id']        ?? '';
    $discountRaw  = $_POST['discount']       ?? '0';
    $discountType = $_POST['discount_type']  ?? 'CHF'; // client-proposed type (may be ignored in storno)
    $negativeFlag = $_POST['negative']       ?? '0';   // '1' if storno/reso, else '0'

    if ($saleId === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing sale_id']);
        exit;
    }

    // Normalizza numero (accetta "12,34")
    $discountRaw = is_string($discountRaw) ? str_replace(',', '.', $discountRaw) : $discountRaw;
    $discount    = round((float)$discountRaw, 2);
    if ($discount < 0) $discount = 0.0;

    // Normalizza tipo
    $discountType = ($discountType === '%') ? '%' : 'CHF';

    // Leggi vendita esistente
    $stmt = $db->prepare("SELECT sale_id, discount, discount_type FROM sales WHERE sale_id = :id");
    $stmt->execute([':id' => $saleId]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Sale not found']);
        exit;
    }

    $isNegative = ($negativeFlag === '1');
    $currentType = (string)($sale['discount_type'] ?? 'CHF');

    // Regole per storno:
    // - Se storno ed il tipo corrente NON è CHF -> non si può modificare
    // - Se storno ed il tipo corrente è CHF -> si può modificare SOLO l'importo; il tipo resta CHF
    if ($isNegative) {
        if ($currentType !== 'CHF') {
            http_response_code(409);
            echo json_encode([
                'ok' => false,
                'error' => 'Durante lo storno lo sconto in % non è modificabile.',
                'sale_id' => $saleId,
                'discount_type' => $currentType
            ]);
            exit;
        }
        // forza a CHF durante storno
        $discountType = 'CHF';
    }

    // Esegui update
    $stmt = $db->prepare("
        UPDATE sales
           SET discount = :discount,
               discount_type = :discount_type
         WHERE sale_id = :sale_id
    ");
    $stmt->execute([
        ':sale_id'       => $saleId,
        ':discount'      => $discount,
        ':discount_type' => $discountType
    ]);

    echo json_encode([
        'ok' => true,
        'sale_id' => $saleId,
        'discount' => $discount,
        'discount_type' => $discountType,
        'is_negative' => $isNegative
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Internal error',
        'detail' => $e->getMessage()
    ]);
}
