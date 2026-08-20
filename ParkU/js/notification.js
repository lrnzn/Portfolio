document.addEventListener('DOMContentLoaded', (event) => {
            const messageElement = document.getElementById('status-notification');
            if (messageElement) {
                setTimeout(() => {
                    messageElement.style.display = 'none';
                }, 3000);
            }
        });