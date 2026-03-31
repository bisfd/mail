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

// Получаем элементы формы
const form = document.getElementById('emailForm');
const loginInput = document.getElementById('login');
const passwordInput = document.getElementById('password');
const messageDiv = document.getElementById('message');

// Функция для отображения сообщений
function showMessage(text, isSuccess) {
    messageDiv.textContent = text;
    messageDiv.className = isSuccess ? 'message success' : 'message error';
}

// Валидация логина
function validateLogin(login) {
    const regex = /^[a-zA-Z0-9]{3,20}$/;
    return regex.test(login);
}

// Валидация пароля
function validatePassword(password) {
    return password.length >= 10;
}

// Обработчик отправки формы
form.addEventListener('submit', function(event) {
    event.preventDefault(); // Отменяем стандартную отправку формы

    // Получаем значения полей
    const login = loginInput.value.trim();
    const password = passwordInput.value;

    // Очищаем предыдущее сообщение
    messageDiv.textContent = '';
    messageDiv.className = '';

    // Проверяем валидность данных
    if (!validateLogin(login)) {
        showMessage('Логин должен содержать только латинские буквы и цифры (3–20 символов)', false);
        return;
    }

    if (!validatePassword(password)) {
        showMessage('Пароль должен содержать не менее 10 символов', false);
        return;
    }

    // Формируем данные для отправки
    const formData = new FormData();
    formData.append('login', login);
    formData.append('password', password);

    // Отправляем запрос на сервер
    fetch('https://api.mail.bisfd.ru/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(`Почта ${login}@bisfd.ru успешно создана!`, true);
            form.reset(); // Очищаем форму
        } else {
            showMessage(`Ошибка: ${data.message}`, false);
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showMessage('Произошла ошибка при отправке запроса', false);
    });
});
