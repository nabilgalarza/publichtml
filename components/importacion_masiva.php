<?php
/**
 * Asistente de importación masiva — catálogo en vivo (modelo: un código por medida).
 */
$import_fotos_count = improgyp_import_staging_count($pdo, improgyp_import_batch_id());
?>
<div id="import-wizard" class="max-w-4xl mx-auto space-y-8 relative z-10">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="bg-slate-50 border border-slate-100 rounded-3xl p-6 flex gap-4 items-start">
        <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-[#1B263B] text-xl shrink-0 border border-slate-100">
            <i class="fa-solid fa-file-import"></i>
        </div>
        <div>
            <p class="font-black text-slate-900 text-sm uppercase tracking-tight">Carga masiva — un código por medida</p>
            <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                Cada fila del CSV = un producto (ej. lija #80 → <code>20LIJ09</code>, lija #100 → <code>20LIJ10</code>).
                Foto con el <strong>mismo nombre que el código</strong> (<code>20LIJ09.webp</code>).
                CSV <strong>sin columna ID</strong> para productos nuevos.
            </p>
            <p class="text-[10px] text-amber-700 mt-2 font-bold">
                Las fotos en cola (<?= (int) $import_fotos_count ?>) se enlazan al pulsar <strong>Importar al catálogo</strong> — hasta entonces no aparecen en Inventario.
            </p>
        </div>
    </div>

    <div class="flex items-center justify-center gap-2 flex-wrap">
        <?php foreach ([1 => 'Subir datos', 2 => 'Fotos (opc.)', 3 => 'Confirmar', 4 => 'Listo'] as $n => $label): ?>
        <div data-import-step-ind="<?= (int) $n ?>" class="px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest <?= $n === 1 ? 'bg-[#1B263B] text-white' : 'bg-slate-100 text-slate-400' ?>">
            <?= (int) $n ?>. <?= htmlspecialchars($label) ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div data-import-paso="1" class="glass-card p-8 space-y-6">
        <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">1. Paquete o CSV</h2>

        <div class="bg-[#1B263B]/5 border border-[#1B263B]/10 rounded-2xl p-5 space-y-3">
            <p class="text-xs font-black text-[#1B263B] uppercase tracking-widest">Opción rápida — ZIP</p>
            <p class="text-sm text-slate-600">Un ZIP con un <code>.csv</code> y las fotos (carpeta <code>fotos/</code> o sueltas). Máx. ~80&nbsp;MB; muchas fotos pueden tardar 1–3 minutos.</p>
            <label id="import-zip-label" class="block bg-white border-2 border-dashed border-[#1B263B]/30 rounded-2xl p-8 text-center cursor-pointer hover:border-[#1B263B] transition-all relative">
                <input type="file" id="import-zip-input" accept=".zip,application/zip" class="absolute inset-0 opacity-0 cursor-pointer">
                <i class="fa-solid fa-file-zipper text-3xl text-[#1B263B] mb-2"></i>
                <p class="font-bold text-slate-600" id="import-zip-nombre">Seleccionar ZIP (CSV + fotos)</p>
                <p class="text-xs text-slate-400 mt-2 hidden" id="import-zip-ayuda"></p>
            </label>
        </div>

        <p class="text-center text-[10px] font-black text-slate-300 uppercase tracking-widest">o solo CSV</p>

        <p class="text-sm text-slate-500">
            Columnas: Nombre, Codigo, Marca, Categoria, Unidad_Precios, Descripcion_Larga.
            <a href="dashboard.php?action=exportar_plantilla_impled" class="text-[#3A86FF] font-bold underline">Plantilla IMPLED</a>
            · <a href="dashboard.php?action=exportar_ejemplo_csv" class="text-[#3A86FF] font-bold underline">Ejemplo general</a>
        </p>
        <label class="block bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl p-10 text-center cursor-pointer hover:border-[#1B263B] transition-all relative">
            <input type="file" id="import-csv-input" accept=".csv" class="absolute inset-0 opacity-0 cursor-pointer">
            <i class="fa-solid fa-file-csv text-3xl text-[#1B263B] mb-3"></i>
            <p class="font-bold text-slate-600" id="import-csv-nombre">Seleccionar CSV</p>
        </label>
        <div id="import-preview-box" class="hidden bg-white border border-slate-100 rounded-2xl p-5"></div>
        <div id="import-paso1-actions" class="hidden space-y-3">
            <button type="button" id="btn-importar-ahora" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-4 rounded-2xl text-sm uppercase tracking-widest">
                Importar al catálogo ahora
            </button>
            <button type="button" id="btn-ir-resumen-directo" class="w-full bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-4 rounded-2xl text-sm uppercase tracking-widest">
                Revisar resumen antes de importar
            </button>
            <button type="button" id="btn-ir-fotos" class="w-full bg-white border-2 border-slate-200 text-slate-600 hover:bg-slate-50 font-black py-3 rounded-2xl text-xs uppercase tracking-widest">
                Añadir más fotos (opcional)
            </button>
        </div>
    </div>

    <div data-import-paso="2" class="glass-card p-8 space-y-6 hidden">
        <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">2. Más fotos (opcional)</h2>
        <p class="text-sm text-slate-500">Nombre del archivo = código del producto (ej. <code>20LIJ09.webp</code>).</p>
        <label class="block bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl p-10 text-center cursor-pointer hover:border-[#1B263B] transition-all relative">
            <input type="file" id="import-fotos-input" multiple accept="image/jpeg,image/png,image/webp,image/gif" class="absolute inset-0 opacity-0 cursor-pointer">
            <i class="fa-solid fa-images text-3xl text-[#1B263B] mb-3"></i>
            <p class="font-bold text-slate-600" id="import-fotos-status">Opcional: sube fotos aquí</p>
        </label>
        <ul id="import-fotos-warnings" class="hidden text-xs text-amber-700 list-disc list-inside"></ul>
        <button type="button" id="btn-solo-fotos" class="text-[10px] font-black uppercase tracking-widest text-[#3A86FF] hover:underline">
            Solo actualizar fotos de productos que ya existen (sin CSV)
        </button>
        <button type="button" id="btn-ir-resumen" class="hidden w-full bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-4 rounded-2xl text-sm uppercase tracking-widest">
            Ver resumen e importar
        </button>
    </div>

    <div data-import-paso="3" class="glass-card p-8 space-y-6 hidden">
        <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">3. Confirmar</h2>
        <div id="import-preview-box-resumen" class="bg-slate-50 rounded-2xl p-5 text-sm text-slate-600">
            Revisa el resumen antes de importar al catálogo.
        </div>
        <label class="flex items-start gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-100 cursor-pointer">
            <input type="checkbox" id="import-permitir-id" class="mt-1">
            <span class="text-xs text-amber-900 font-bold">Mi CSV incluye columna ID y quiero actualizar productos existentes por ese número.</span>
        </label>
        <button type="button" id="btn-importar" class="w-full bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-5 rounded-2xl text-sm uppercase tracking-widest">
            Importar al catálogo
        </button>
    </div>

    <div data-import-paso="4" class="glass-card p-8 space-y-6 hidden">
        <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">4. Listo</h2>
        <div id="import-resultado" class="hidden bg-emerald-50 border border-emerald-100 rounded-2xl p-6"></div>
        <a href="?view=catalogo" class="block w-full text-center bg-white border border-slate-200 text-[#1B263B] font-black py-4 rounded-2xl text-sm uppercase tracking-widest hover:bg-slate-50">
            Ver catálogo
        </a>
        <button type="button" id="btn-nueva-import" class="w-full text-slate-400 font-bold text-xs uppercase tracking-widest py-2">
            Hacer otra importación
        </button>
    </div>

    <div class="text-center">
        <button type="button" id="btn-vaciar-fotos-cola" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-rose-500">
            Vaciar fotos en cola (sin borrar productos)
        </button>
    </div>
</div>
<script src="js/import_masivo.js?v=5"></script>
