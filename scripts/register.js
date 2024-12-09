document.addEventListener('DOMContentLoaded', (event) => {
    const registerForm = document.getElementById('register-form');
    const errorMessage = document.getElementById('error-message');
    
    // Перевірка наявності форми перед додаванням слухача події
    if (registerForm instanceof HTMLFormElement) {
        registerForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Отримання та очищення даних форми
            const formData = new FormData(this);
            const sanitizedData = new FormData();
            
            formData.forEach((value, key) => {
                // Санітарна очистка (наприклад, видалення зайвих пробілів та небажаних символів)
                sanitizedData.append(key, value.trim().replace(/<[^>]+>/g, '')); // Очищаємо від HTML-тегів
            });

            // Виконання запиту fetch
            fetch('register.php', {
                method: 'POST',
                body: sanitizedData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    if (errorMessage instanceof HTMLElement) {
                        errorMessage.textContent = sanitizeMessage(data.message);
                    } else {
                        console.error("Елемент для повідомлення про помилку не знайдений.");
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (errorMessage instanceof HTMLElement) {
                    errorMessage.textContent = 'Произошла ошибка. Попробуйте снова.';
                }
            });
        });
    } else {
        console.error("Форма з id 'register-form' не знайдена або не є елементом форми.");
    }

    // Функція для санітарної очистки повідомлення (захист від XSS)
    function sanitizeMessage(message) {
        return message.replace(/<[^>]+>/g, ''); // Очищення від HTML-тегів
    }
});
