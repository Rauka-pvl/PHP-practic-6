<?php
declare(strict_types=1);
namespace Course\Accounts;

use Course\Domain\ItemData;

/** Дополнение к репозиторию № 4; его существующие методы сохраняются. */
final class AdminItems
{
    public function __construct(private \PDO $pdo) {}
    public function update(int $id, ItemData $data): void
    {
        $statement = $this->pdo->prepare('UPDATE items SET title = :title, category = :category, quantity = :quantity,'
            . ' price_minor = :price_minor WHERE id = :id');
        $statement->execute([
            'title' => $data->title,
            'category' => $data->category,
            'quantity' => $data->quantity,
            'price_minor' => $data->priceMinor,
            'id' => $id,
        ]);
    }
    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM items WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
