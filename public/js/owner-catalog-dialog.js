const dialog = document.getElementById('owner-catalog-dialog');
const title = dialog.querySelector('h2');
const frame = dialog.querySelector('iframe');

for (const button of document.querySelectorAll('[data-catalog-url]')) {
    button.addEventListener('click', () => {
        const name = button.textContent.trim();
        title.textContent = name;
        frame.title = name;
        frame.src = button.dataset.catalogUrl;
        dialog.showModal();
    });
}

dialog.addEventListener('close', () => {
    frame.src = 'about:blank';
});
