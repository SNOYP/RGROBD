document.addEventListener('DOMContentLoaded', (event) => {
    document.getElementById('login-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);

        // Делаем запрос с данными формы на сервер
        fetch('login.php', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',  // Указываем, что ожидаем JSON
            }
        })
        .then(response => response.json()) // Получаем и парсим ответ в формате JSON
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect; // Перенаправляем на нужную страницу
            } else {
                // Выводим сообщение об ошибке, если неуспешно
                document.getElementById('error-message').textContent = data.message;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('error-message').textContent = 'Произошла ошибка. Попробуйте снова.';
        });
    });

    document.getElementById('guest-login').addEventListener('click', function(event) {
        event.preventDefault();

        // Формируем запрос для гостевой авторизации
        const formData = new FormData();
        formData.append('guest', 'true');

        fetch('login.php', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',  // Указываем, что ожидаем JSON
            }
        })
        .then(response => response.json()) // Получаем и парсим ответ в формате JSON
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect; // Перенаправляем на нужную страницу
            } else {
                // Выводим сообщение об ошибке
                document.getElementById('error-message').textContent = data.message;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('error-message').textContent = 'Произошла ошибка. Попробуйте снова.';
        });
    });
});
