document.addEventListener('DOMContentLoaded', (event) => {
    const themeToggle = document.getElementById('theme-toggle');
    
    // Перевірка наявності елемента для перемикання теми
    if (themeToggle instanceof HTMLElement) {
        const currentTheme = localStorage.getItem('theme') || 'light';
        
        // Перевірка на коректність значення поточної теми
        if (currentTheme === 'dark') {
            document.body.classList.add('dark-mode');
            themeToggle.textContent = 'Світла тема';
        } else {
            themeToggle.textContent = 'Темна тема';
        }

        themeToggle.addEventListener('click', () => {
            // Переключення класу для темної/світлої теми
            document.body.classList.toggle('dark-mode');
            
            const newTheme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
            
            // Санітарна очистка даних перед збереженням у localStorage
            const sanitizedTheme = sanitizeTheme(newTheme);
            localStorage.setItem('theme', sanitizedTheme);
            
            themeToggle.textContent = sanitizedTheme === 'dark' ? 'Світла тема' : 'Темна тема';
        });
    } else {
        console.error("Елемент з id 'theme-toggle' не знайдений.");
    }

    // Функція для санітарної очистки теми
    function sanitizeTheme(theme) {
        // Дозволені значення теми: 'dark' або 'light'
        return theme === 'dark' || theme === 'light' ? theme : 'light';
    }
});
