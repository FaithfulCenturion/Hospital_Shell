
function actualizarTiempoEspera() {
    const elements = document.querySelectorAll('.tiempo-espera');

    elements.forEach(el => {
        const fechaLlegadaStr = el.getAttribute('data-fecha-llegada');
        if (!fechaLlegadaStr) return;

        const llegada = new Date(fechaLlegadaStr.replace(' ', 'T')); // to ISO format
        const ahora = new Date(); // client local time
        const diffMs = ahora - llegada;

        const diffMins = Math.floor(diffMs / 60000);
        const horas = Math.floor(diffMins / 60);
        const minutos = diffMins % 60;

        let texto = '';
        if (horas > 0) texto += `${horas}h `;
        texto += `${minutos} min`;

        el.textContent = texto;
    });
}

// Run once when page loads
actualizarTiempoEspera();

// Optional: update every minute
setInterval(actualizarTiempoEspera, 60000);

