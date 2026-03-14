<?php

/**
 * Acceso a formularios: listar por usuario, crear, obtener, actualizar.
 */
class FormRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** @return list<array> */
    public function listByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, slug, title, created_at FROM forms WHERE created_by = ? ORDER BY updated_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM forms WHERE id = ? AND created_by = ?');
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['config'] = json_decode($row['config'], true) ?: [];
        $row['definition'] = json_decode($row['definition'], true) ?: ['sections' => []];
        return $row;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM forms WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['config'] = json_decode($row['config'], true) ?: [];
        $row['definition'] = json_decode($row['definition'], true) ?: ['sections' => []];
        return $row;
    }

    public function slugExists(string $slug, ?int $excludeFormId = null): bool
    {
        if ($excludeFormId !== null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM forms WHERE slug = ? AND id != ?');
            $stmt->execute([$slug, $excludeFormId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT 1 FROM forms WHERE slug = ?');
            $stmt->execute([$slug]);
        }
        return (bool) $stmt->fetch();
    }

    public function create(int $userId, string $slug, string $title): int
    {
        return $this->createWithDefinition($userId, $slug, $title, self::defaultDefinition());
    }

    /** Crear formulario con una definición (y config) ya definidos. */
    public function createWithDefinition(int $userId, string $slug, string $title, array $definition, ?array $config = null): int
    {
        $config = $config ?? self::defaultConfig();
        $stmt = $this->pdo->prepare('INSERT INTO forms (created_by, slug, title, config, definition) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $slug, $title, json_encode($config), json_encode($definition)]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $form = $this->findById($id, $userId);
        if (!$form) {
            return false;
        }
        $slug = $data['slug'] ?? $form['slug'];
        $title = $data['title'] ?? $form['title'];
        $config = isset($data['config']) ? (is_string($data['config']) ? json_decode($data['config'], true) : $data['config']) : $form['config'];
        $definition = isset($data['definition']) ? (is_string($data['definition']) ? json_decode($data['definition'], true) : $data['definition']) : $form['definition'];
        if (!is_array($config)) {
            $config = $form['config'];
        }
        if (!is_array($definition)) {
            $definition = $form['definition'];
        }
        $stmt = $this->pdo->prepare('UPDATE forms SET slug = ?, title = ?, config = ?, definition = ? WHERE id = ? AND created_by = ?');
        $stmt->execute([$slug, $title, json_encode($config), json_encode($definition), $id, $userId]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM forms WHERE id = ? AND created_by = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    public static function defaultConfig(): array
    {
        return [
            'theme' => [
                'logoUrl' => '',
                'headerText' => '',
                'headerBackground' => '#6b21a8',
                'headerTextColor' => '#ffffff',
                'background' => '#f5f5f5',
                'backgroundImage' => '',
                'primaryColor' => '#6b21a8',
                'textColor' => '#1f2937',
                'fontFamily' => 'Inter, sans-serif',
                'borderRadius' => '8px',
                'containerMaxWidth' => '560px',
                'buttonBackground' => '#6b21a8',
                'buttonTextColor' => '#ffffff',
                'buttonBorderRadius' => '8px',
            ],
            'responsePage' => [
                'enabled' => true,
                'title' => '¡Gracias por participar!',
                'message' => 'Tu respuesta ha sido registrada.',
                'redirectUrl' => null,
            ],
        ];
    }

    public static function defaultDefinition(): array
    {
        return [
            'sections' => [
                [
                    'id' => 'sec_1',
                    'title' => 'Sección 1',
                    'order' => 1,
                    'fields' => [],
                ],
            ],
        ];
    }
}
