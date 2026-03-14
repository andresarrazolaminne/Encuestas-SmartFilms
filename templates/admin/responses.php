<?php
$responses = $responses ?? [];
$pageTitle = $pageTitle ?? 'Respuestas';
$layoutWide = true;

// Construir columnas en orden (desde la definición del formulario)
$columns = [];
foreach ($form['definition']['sections'] ?? [] as $sec) {
    foreach ($sec['fields'] ?? [] as $field) {
        $fid = $field['id'] ?? '';
        if ($fid !== '') {
            $columns[] = ['id' => $fid, 'label' => $field['label'] ?? $fid];
        }
    }
}

ob_start();
?>
<h1>Respuestas: <?= htmlspecialchars($form['title']) ?></h1>
<p class="responses-actions">
    <a href="/admin/forms/<?= (int) $form['id'] ?>">← Editar formulario</a>
    —
    <a href="/admin">Listado</a>
    <?php if (!empty($responses)): ?>
        —
        <a href="/admin/forms/<?= (int) $form['id'] ?>/responses/export" class="btn-download">Descargar Excel (CSV)</a>
    <?php endif; ?>
</p>

<?php if (empty($responses)): ?>
    <p class="empty-state">Aún no hay respuestas para este formulario.</p>
<?php else: ?>
    <p class="responses-count"><strong><?= count($responses) ?></strong> respuesta(s)</p>
    <div class="table-wrap">
        <table class="responses-table">
            <thead>
                <tr>
                    <th class="col-id">#</th>
                    <th class="col-date">Fecha</th>
                    <?php foreach ($columns as $col): ?>
                        <th title="<?= htmlspecialchars($col['label']) ?>"><?= htmlspecialchars(mb_substr($col['label'], 0, 40)) ?><?= mb_strlen($col['label']) > 40 ? '…' : '' ?></th>
                    <?php endforeach; ?>
                    <th class="col-ip">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($responses as $i => $r): ?>
                    <tr class="<?= $i % 2 === 0 ? 'row-even' : 'row-odd' ?>">
                        <td class="col-id"><?= (int) $r['id'] ?></td>
                        <td class="col-date"><?= htmlspecialchars($r['created_at']) ?></td>
                        <?php foreach ($columns as $col):
                            $val = $r['response_data'][$col['id']] ?? '';
                            $display = is_array($val) ? implode(', ', $val) : (string) $val;
                        ?>
                            <td title="<?= htmlspecialchars($display) ?>"><?= htmlspecialchars(mb_substr($display, 0, 80)) ?><?= mb_strlen($display) > 80 ? '…' : '' ?></td>
                        <?php endforeach; ?>
                        <td class="col-ip"><?= htmlspecialchars($r['ip'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
