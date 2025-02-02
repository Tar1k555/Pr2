function showSection(sectionId) {
    // Ховаємо всі секції
    document.querySelectorAll('.content-section').forEach(section => {
        section.style.display = 'none';
    });

    // Відображаємо вибрану секцію
    document.getElementById(sectionId).style.display = 'block';
}
// Функція для відображення моделей залежно від вибраної марки
function showModels(brand) {
    const models = {
        audi: ['A3', 'A4', 'A6'],
        bmw: ['X1', 'X3', 'X5'],
        ford: ['Focus', 'Fiesta', 'Mustang'],
        hyundai: ['i20', 'Elantra', 'Tucson'],
        mercedes: ['A-Class', 'B-Class', 'C-Class'],
        nissan: ['Altima', 'Sentra', 'Maxima'],
        opel: ['Astra', 'Corsa', 'Insignia'],
        peugeot: ['208', '308', '3008'],
        toyota: ['Corolla', 'Camry', 'Yaris'],
        volkswagen: ['Golf', 'Passat', 'Tiguan']
    };

    // Отображення контейнера для моделей
    const modelSelect = document.getElementById('model');
    const modelsContainer = document.getElementById('models-container');

    // Очищення попередніх моделей
    modelSelect.innerHTML = '';

    // Якщо вибрана марка, відображаємо список моделей
    if (brand !== '') {
        const selectedModels = models[brand];
        selectedModels.forEach(model => {
            let option = document.createElement('option');
            option.value = model;
            option.textContent = model;
            modelSelect.appendChild(option);
        });
        modelsContainer.style.display = 'block'; // Показуємо контейнер з моделями
    } else {
        modelsContainer.style.display = 'none'; // Якщо марка не вибрана, приховуємо контейнер моделей
    }
}

// Функція для замовлення послуги
function orderService(serviceName) {
    alert('Ви замовили: ' + serviceName);
    document.getElementById('vehicle-selection').style.display = 'block'; // Показуємо вибір авто
}

// Функція для відкриття або закриття секції з послугами
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    section.style.display = section.style.display === 'none' ? 'block' : 'none';
}