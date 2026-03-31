<?php
// Включаем отображение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Устанавливаем заголовок для JSON‑ответа
header('Content-Type: application/json');

// Получаем данные из формы
$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

// Инициализируем массив для ответа
$response = [
    'success' => false,
    'message' => ''
];

// Функция валидации логина
function validateLogin($login) {
    // Логин должен содержать только латинские буквы и цифры, длина 3–20 символов
    return preg_match('/^[a-zA-Z0-9]{3,20}$/', $login);
}

// Функция валидации пароля
function validatePassword($password) {
    // Пароль должен быть не менее 10 символов
    return strlen($password) >= 10;
}

// Проверяем валидность данных
if (!validateLogin($login)) {
    $response['message'] = 'Неверный формат логина. Должны быть латинские буквы и цифры (3–20 символов)';
    echo json_encode($response);
    exit;
}

if (!validatePassword($password)) {
    $response['message'] = 'Пароль должен содержать не менее 10 символов';
    echo json_encode($response);
    exit;
}

// Формируем полный email
$email = $login . '@bisfd.ru';

// Параметры API Reg.ru
$apiUrl = '';
$apiToken = '';

// Данные для отправки в API
$postData = [
    'username' => $email,
    'password' => $password,
    'domain' => 'bisfd.ru'
];

// Инициализируем cURL
$ch = curl_init();

// Настраиваем параметры cURL
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true, // Проверка SSL‑сертификата
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiToken,
        'Content-Type: application/x-www-form-urlencoded'
    ],
    CURLOPT_TIMEOUT => 30 // Таймаут 30 секунд
]);

// Выполняем запрос
$responseApi = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// Обрабатываем ответ API
if ($error) {
    $response['message'] = 'Ошибка подключения к API: ' . $error;
} elseif ($httpCode !== 200) {
    $response['message'] = 'Ошибка API: HTTP ' . $httpCode;
} else {
    // Предполагаем, что API возвращает JSON
    $apiData = json_decode($responseApi, true);

    if ($apiData && isset($api
