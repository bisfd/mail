// MIT License

// Copyright (c) 2026 SupromTeam

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

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
    CURLOPT_TIMEOUT => 30, // Таймаут 30 секунд
    CURLOPT_FOLLOWLOCATION => true // Следовать редиректам
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
    // Обрабатываем HTTP‑ошибки
    switch ($httpCode) {
        case 400:
            $response['message'] = 'Неверные параметры запроса к API';
            break;
        case 401:
            $response['message'] = 'Ошибка авторизации в API (проверьте токен)';
            break;
        case 403:
            $response['message'] = 'Доступ к API запрещён';
            break;
        case 404:
            $response['message'] = 'API endpoint не найден';
            break;
        case 500:
            $response['message'] = 'Внутренняя ошибка сервера API';
            break;
        default:
            $response['message'] = 'Ошибка API: HTTP ' . $httpCode;
    }
} else {
    // Предполагаем, что API возвращает JSON
    $apiData = json_decode($responseApi, true);

    // Проверяем, удалось ли декодировать JSON
    if ($apiData === null) {
        $response['message'] = 'Некорректный ответ от API: ' . $responseApi;
    } else {
        // Анализируем ответ API
        if (isset($apiData['result']) && $apiData['result'] === 'success') {
            // Успешное создание почты
            $response['success'] = true;
            $response['message'] = 'Почта успешно создана';
        } elseif (isset($apiData['error'])) {
            // API вернуло конкретную ошибку
            $response['message'] = 'Ошибка API: ' . $apiData['error'];
        } else {
            // Неизвестный формат ответа
            $response['message'] = 'Неизвестный ответ от API';
        }
    }
}

// Выводим JSON‑ответ для JavaScript
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
