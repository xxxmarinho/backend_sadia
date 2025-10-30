<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, GET");

$api_token = "1ePZaY3M5kfVkOzrMARBXhdYUPuAAgpO1DWVHLI94lF3cHCagYbL23Gj6owk";
$endpoint = "https://api.invictuspay.app.br/api/public/v1/transactions?api_token=$api_token";

// Aceita valor tanto via POST quanto GET
$price = $_POST['price'] ?? $_GET['valor'] ?? 0;
$name = $_POST['name'] ?? $_GET['nome'] ?? 'Cliente';
$email = $_POST['email'] ?? $_GET['email'] ?? 'teste@teste.com';
$cpf = $_POST['cpf'] ?? $_GET['cpf'] ?? '00000000000';
$phone = $_POST['phone'] ?? $_GET['phone'] ?? '11999999999';

// Converte e garante formato correto
$price = floatval(str_replace(',', '.', $price));
$formattedPrice = number_format($price, 2, '.', '');

if ($price <= 0) {
    echo json_encode([
        'error' => 'Valor inválido recebido',
        'debug_price' => $price
    ]);
    exit;
}

// Seleciona o hash da oferta com base no preço
switch ($formattedPrice) {
    case '27.90':
        $offer_hash = 'ueob0';
        break;
    case '31.90':
        $offer_hash = 'ocu84';
        break;
    case '23.90':
        $offer_hash = 'm19beppsal';
        break;
    default:
        echo json_encode([
            'error' => 'Preço inválido',
            'debug_price' => $formattedPrice
        ]);
        exit;
}

// Monta o corpo da transação
$data = [
    "amount" => intval($price * 100),
    "offer_hash" => $offer_hash,
    "payment_method" => "pix",
    "installments" => 1,
    "customer" => [
        "name" => $name,
        "email" => $email,
        "phone_number" => $phone,
        "document" => $cpf
    ],
    "cart" => [
        [
            "product_hash" => "abcd1234",
            "title" => "Compra Sadia",
            "price" => intval($price * 100),
            "quantity" => 1,
            "operation_type" => 1,
            "tangible" => false
        ]
    ],
    "expire_in_days" => 1,
    "transaction_origin" => "api",
    "postback_url" => "http://localhost:8000/site_sadia/pix.php"
];

// Inicializa o cURL pra Invictus
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'Erro cURL: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Retorna a resposta final
echo $response;
?>
