<?php
/**
 * Gestor CRUD — improgyp_blog
 * @var PDO $pdo
 * @var string $csrf_token
 */
require_once __DIR__ . '/../lib/blog_helpers.php';
blog_ensure_table($pdo);

$msg = $_GET['msg'] ?? '';
$articulos = $pdo->query('SELECT id, titulo, slug, categoria, borrador, visitas, fecha, portada FROM improgyp_blog ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
$imgFallback = 'favicon-app.png?v=5';
?>
<div class="max-w-[1100px] mx-auto relative z-10">
    <?php if ($msg === 'guardado'): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl mb-6 text-sm font-bold">
        <i class="fa-solid fa-circle-check"></i> Artículo guardado.
    </div>
    <?php elseif ($msg === 'eliminado'): ?>
    <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-2xl mb-6 text-sm font-bold">Artículo eliminado.</div>
    <?php endif; ?>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-pen-nib text-orange-500"></i> Gestor de Blog
            </h2>
            <p class="text-sm text-slate-500 mt-1">Artículos publicados en home y <code class="bg-slate-100 px-1 rounded">blog.php</code>.</p>
        </div>
        <button type="button" onclick="openBlogModal()" class="bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black px-5 py-3 rounded-xl text-sm transition-colors">
            <i class="fa-solid fa-plus"></i> Nuevo artículo
        </button>
    </div>

    <div class="glass-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                <tr>
                    <th class="p-4 text-left">Portada</th>
                    <th class="p-4 text-left">Título</th>
                    <th class="p-4 text-left">Categoría</th>
                    <th class="p-4 text-left">Estado</th>
                    <th class="p-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($articulos)): ?>
                <tr><td colspan="5" class="p-10 text-center text-slate-400 font-bold">No hay artículos. Crea el primero.</td></tr>
                <?php else: foreach ($articulos as $a):
                    $img = blog_img_url($a['portada'] ?? '');
                ?>
                <tr class="hover:bg-slate-50/80">
                    <td class="p-3">
                        <img src="<?= htmlspecialchars($img) ?>" alt="" class="w-14 h-14 rounded-xl object-cover bg-slate-100 blog-thumb" width="56" height="56">
                    </td>
                    <td class="p-3 font-bold text-slate-800"><?= htmlspecialchars($a['titulo']) ?></td>
                    <td class="p-3 text-slate-500"><?= htmlspecialchars($a['categoria']) ?></td>
                    <td class="p-3">
                        <?php if ($a['borrador']): ?>
                        <span class="text-[10px] font-black uppercase bg-amber-100 text-amber-700 px-2 py-1 rounded">Borrador</span>
                        <?php else: ?>
                        <span class="text-[10px] font-black uppercase bg-emerald-100 text-emerald-700 px-2 py-1 rounded">Publicado</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-right space-x-2">
                        <button type="button" class="text-[#3A86FF] font-bold hover:underline" onclick='editBlog(<?= json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Editar</button>
                        <form method="POST" class="inline" onsubmit="return confirm('¿Eliminar este artículo?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            <input type="hidden" name="action" value="blog_eliminar">
                            <input type="hidden" name="blog_id" value="<?= (int) $a['id'] ?>">
                            <button type="submit" class="text-red-500 font-bold hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="blog-modal" class="fixed inset-0 z-[200] hidden items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-8">
        <h3 id="blog-modal-title" class="text-xl font-black text-slate-900 mb-6">Nuevo artículo</h3>

        <div class="mb-6 p-4 rounded-2xl bg-violet-50 border border-violet-100">
            <p class="text-xs font-black text-violet-800 uppercase tracking-wider mb-2"><i class="fa-solid fa-wand-magic-sparkles"></i> Asistente IA</p>
            <div class="flex flex-wrap gap-2">
                <input type="text" id="blog_ai_tema" placeholder="Tema: ej. lijadora para paneles de yeso" class="flex-1 min-w-[200px] premium-input rounded-xl px-3 py-2 text-sm border border-violet-100">
                <button type="button" id="btn-blog-ai-generar" class="bg-violet-600 hover:bg-violet-700 text-white font-black px-4 py-2 rounded-xl text-xs whitespace-nowrap">Generar borrador</button>
            </div>
            <p id="blog-ai-status" class="text-xs text-slate-500 mt-2 font-medium"></p>
        </div>

        <form method="POST" action="dashboard.php?view=blog" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <input type="hidden" name="action" value="blog_guardar">
            <input type="hidden" name="blog_id" id="blog_id" value="0">
            <input type="hidden" name="portada_actual" id="portada_actual" value="<?= htmlspecialchars($imgFallback) ?>">

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Título</label>
                <input type="text" name="titulo" id="blog_titulo" required class="w-full premium-input rounded-xl px-4 py-3 border border-slate-100 text-sm font-bold">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Categoría</label>
                    <input type="text" name="categoria" id="blog_categoria" value="General" class="w-full premium-input rounded-xl px-4 py-3 border border-slate-100 text-sm font-bold">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Tiempo lectura</label>
                    <input type="text" name="tiempo_lectura" id="blog_tiempo" value="5 min" class="w-full premium-input rounded-xl px-4 py-3 border border-slate-100 text-sm font-bold">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Resumen</label>
                <textarea name="resumen" id="blog_resumen" rows="2" class="w-full premium-input rounded-xl px-4 py-3 border border-slate-100 text-sm"></textarea>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-[10px] font-black text-slate-400 uppercase">Contenido (HTML)</label>
                    <button type="button" id="btn-blog-ai-mejorar" class="text-[10px] font-black text-violet-600 hover:text-violet-800 uppercase">Mejorar con IA</button>
                </div>
                <textarea name="contenido" id="blog_contenido" rows="6" class="w-full premium-input rounded-xl px-4 py-3 border border-slate-100 text-sm font-mono"></textarea>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Portada (imagen)</label>
                <input type="file" name="portada" accept="image/*" class="text-sm">
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="borrador" id="blog_borrador" value="1" class="rounded">
                <span class="text-sm font-bold text-slate-600">Guardar como borrador</span>
            </label>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-[#1B263B] text-white font-black py-3 rounded-xl">Guardar</button>
                <button type="button" onclick="closeBlogModal()" class="px-6 py-3 rounded-xl border border-slate-200 font-bold text-slate-600">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
const BLOG_IMG_FB = <?= json_encode($imgFallback) ?>;
document.querySelectorAll('.blog-thumb').forEach(img => {
    img.addEventListener('error', function onErr() {
        this.removeEventListener('error', onErr);
        if (this.src.indexOf(BLOG_IMG_FB) === -1) this.src = BLOG_IMG_FB;
    }, { once: true });
});

function openBlogModal() {
    document.getElementById('blog-modal-title').textContent = 'Nuevo artículo';
    document.getElementById('blog_id').value = '0';
    document.getElementById('blog_titulo').value = '';
    document.getElementById('blog_categoria').value = 'General';
    document.getElementById('blog_tiempo').value = '5 min';
    document.getElementById('blog_resumen').value = '';
    document.getElementById('blog_contenido').value = '<p></p>';
    document.getElementById('blog_borrador').checked = false;
    document.getElementById('portada_actual').value = BLOG_IMG_FB;
    document.getElementById('blog-modal').classList.remove('hidden');
    document.getElementById('blog-modal').classList.add('flex');
}

function closeBlogModal() {
    document.getElementById('blog-modal').classList.add('hidden');
    document.getElementById('blog-modal').classList.remove('flex');
}

document.getElementById('btn-blog-ai-mejorar')?.addEventListener('click', async function () {
    const contenido = document.getElementById('blog_contenido').value.trim();
    const status = document.getElementById('blog-ai-status');
    if (!contenido) {
        status.textContent = 'Escribe contenido antes de mejorar.';
        status.className = 'text-xs text-amber-600 mt-2 font-bold';
        return;
    }
    this.disabled = true;
    status.textContent = 'Mejorando redacción…';
    status.className = 'text-xs text-violet-600 mt-2 font-bold';
    try {
        const res = await fetch('api_blog_ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ action: 'mejorar', contenido, instruccion: 'Claridad, tono técnico profesional Ecuador' })
        });
        const data = await res.json();
        if (!data.ok) throw new Error(data.error || 'Error IA');
        document.getElementById('blog_contenido').value = data.contenido || contenido;
        status.textContent = 'Contenido mejorado.';
        status.className = 'text-xs text-emerald-600 mt-2 font-bold';
    } catch (e) {
        status.textContent = e.message || 'No se pudo mejorar.';
        status.className = 'text-xs text-red-600 mt-2 font-bold';
    } finally {
        this.disabled = false;
    }
});

document.getElementById('btn-blog-ai-generar')?.addEventListener('click', async function () {
    const tema = document.getElementById('blog_ai_tema').value.trim();
    const status = document.getElementById('blog-ai-status');
    const categoria = document.getElementById('blog_categoria').value.trim() || 'Herramientas';
    if (!tema) {
        status.textContent = 'Escribe un tema primero.';
        status.className = 'text-xs text-amber-600 mt-2 font-bold';
        return;
    }
    this.disabled = true;
    status.textContent = 'Generando con Gemini…';
    status.className = 'text-xs text-violet-600 mt-2 font-bold';
    try {
        const res = await fetch('api_blog_ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ action: 'generar', tema, categoria })
        });
        const data = await res.json();
        if (!data.ok) throw new Error(data.error || 'Error IA');
        document.getElementById('blog_titulo').value = data.titulo || tema;
        document.getElementById('blog_resumen').value = data.resumen || '';
        document.getElementById('blog_contenido').value = data.contenido || '<p></p>';
        document.getElementById('blog_tiempo').value = data.tiempo_lectura || '5 min';
        if (data.categoria) document.getElementById('blog_categoria').value = data.categoria;
        status.textContent = 'Borrador listo. Revisa y guarda.';
        status.className = 'text-xs text-emerald-600 mt-2 font-bold';
    } catch (e) {
        status.textContent = e.message || 'No se pudo generar.';
        status.className = 'text-xs text-red-600 mt-2 font-bold';
    } finally {
        this.disabled = false;
    }
});

async function editBlog(row) {
    const r = await fetch('dashboard.php?view=blog&ajax=articulo&id=' + row.id);
    let full = row;
    try { const j = await r.json(); if (j.ok) full = j.data; } catch (e) {}
    document.getElementById('blog-modal-title').textContent = 'Editar artículo';
    document.getElementById('blog_id').value = full.id;
    document.getElementById('blog_titulo').value = full.titulo || '';
    document.getElementById('blog_categoria').value = full.categoria || 'General';
    document.getElementById('blog_tiempo').value = full.tiempo_lectura || '5 min';
    document.getElementById('blog_resumen').value = full.resumen || '';
    document.getElementById('blog_contenido').value = full.contenido || '';
    document.getElementById('blog_borrador').checked = !!Number(full.borrador);
    document.getElementById('portada_actual').value = full.portada || BLOG_IMG_FB;
    document.getElementById('blog-modal').classList.remove('hidden');
    document.getElementById('blog-modal').classList.add('flex');
}
</script>
