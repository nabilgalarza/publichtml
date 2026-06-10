<script>
async function impulsarProductoIA(nombre, btn) {
    if (!nombre || !btn) return;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
    btn.disabled = true;
    try {
        const response = await fetch('dashboard.php?ajax=impulsar_producto', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ nombre: nombre })
        });
        const raw = await response.text();
        let data;
        try {
            data = JSON.parse(raw);
        } catch (parseErr) {
            console.error('Impulsar: respuesta no JSON', raw.slice(0, 200));
            alert('Error del servidor. Revisa la consola o vuelve a iniciar sesión.');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            return;
        }
        if (data.status === 'success') {
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Listo';
            setTimeout(() => location.reload(), 1200);
        } else {
            alert(data.error || 'No se pudo impulsar');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    } catch (e) {
        console.error('Impulsar:', e);
        alert('Error de conexión');
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }
}

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-impulsar-fantasma');
    if (!btn || btn.disabled) return;
    e.preventDefault();
    const nombre = btn.getAttribute('data-nombre');
    if (!nombre) return;
    impulsarProductoIA(nombre, btn);
});
</script>
