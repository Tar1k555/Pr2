function showSection(sectionId) {
    // Ховаємо всі секції
    document.querySelectorAll('.content-section').forEach(section => {
        section.style.display = 'none';
    });

    // Відображаємо вибрану секцію
    document.getElementById(sectionId).style.display = 'block';
}