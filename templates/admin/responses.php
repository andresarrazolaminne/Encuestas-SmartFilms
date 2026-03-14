<?php
$responses = $responses ?? [];
$pageTitle = $pageTitle ?? 'Respuestas';
ob_start();
?>
<h1>Respuestas: <?= htmlspecialchars($form['title']) ?></h1>
<p><a href="/admin/forms/<?= (int) $form['id'] ?>">← Editar formulario</a> — <a href="/admin">Listado</a></p>

<?php if (empty($responses)): ?>
    <p>Aún no hay respuestas para este formulario.</p>
<?php else: ?>
    <p><strong><?= count($responses) ?></strong> respuesta(s).</p>
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid #333;">
                <th style="text-align:left; padding: 0.5rem;">#</th>
                <th style="text-align:left; padding: 0.5rem;">Fecha</th>
                <th style="text-align:left; padding: 0.5rem;">Vista previa</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($responses as $r): ?>
                <tr style="border-bottom: 1px solid #ccc;">
                    <td style="padding: 0.5rem;"><?= (int) $r['id'] ?></td>
                    <td style="padding: 0.5rem;"><?= htmlspecialchars($r['created_at']) ?></td>
                    <td style="padding: 0.5rem;">
                        <?php
                        $data = $r['response_data'] ?? [];
                        $preview = [];
                        $n = 0;
                        foreach ($data as $k => $v) {
                            if ($n >= 3) { $preview[] = '…'; break; }
                            if ($v !== '' && $v !== [] && $v !== null) {
                                $preview[] = htmlspecialchars($k) . ': ' . htmlspecialchars(is_array($v) ? implode(', ', $v) : (string)$v);
                                $n++;
                            }
                        }
                        echo implode(' · ', $preview) ?: '—';
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
