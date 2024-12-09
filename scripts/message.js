document.addEventListener('DOMContentLoaded', (event) => {
    const message = document.getElementById('message');
    
    // Перевірка на існування елементу та його тип
    if (message instanceof HTMLElement) {
        // Перевірка, чи не порожній контент у повідомленні
        const messageContent = message.innerHTML.trim();
        if (messageContent) {
            setTimeout(() => {
                // Приховування повідомлення після 5 секунд
                message.style.display = 'none';
            }, 5000);
        } else {
            console.warn("Повідомлення порожнє або некоректне.");
        }
    } else {
        console.error("Елемент з id 'message' не знайдено або він не є HTML елементом.");
    }
});
