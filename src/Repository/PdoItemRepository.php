<?php
declare(strict_types=1);
namespace Course\Repository;

use Course\Domain\Item;
use Course\Domain\ItemData;

final class PdoItemRepository implements ItemRepository
{
    public function __construct(private \PDO $pdo) {}
    private function item(array $row): Item
    {
        $ownerId = $row['owner_id'] === null ? null : (int) $row['owner_id'];
        return new Item((int) $row['id'], $row['title'], $row['category'], (int) $row['quantity'],
            (int) $row['price_minor'], $ownerId);
    }
    // Готовый образец: запрос без внешнего ввода, поэтому без параметров.
    public function all(): array
    {
        $sql = 'SELECT id, title, category, quantity, price_minor, owner_id FROM items ORDER BY id';
        return array_map($this->item(...), $this->pdo->query($sql)->fetchAll());
    }
    public function find(int $id): ?Item
    {
        // P04-SQL: Найти запись по id подготовленным запросом; null, если строки нет.
        $statement = $this->pdo->prepare('SELECT id, title, category, quantity, price_minor, owner_id FROM items WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row === false ? null : $this->item($row);
    }
    public function matching(string $query): array
    {
        // P04-SQL: Найти записи, заголовок которых содержит текст запроса; % и _ ищутся буквально.
        $statement = $this->pdo->prepare('SELECT id, title, category, quantity, price_minor, owner_id FROM items WHERE title LIKE :pattern ORDER BY id');
        $statement->execute(['pattern' => '%' . addcslashes($query, '%_\\') . '%']);
        return array_map($this->item(...), $statement->fetchAll());
    }
    public function create(ItemData $data, int $ownerId): Item
    {
        // P04-SQL: Добавить строку подготовленным INSERT и вернуть созданную запись с новым id.
        $statement = $this->pdo->prepare('INSERT INTO items (title, category, quantity, price_minor, owner_id)'
            . ' VALUES (:title, :category, :quantity, :price_minor, :owner_id)');
        $statement->execute([
            'title' => $data->title,
            'category' => $data->category,
            'quantity' => $data->quantity,
            'price_minor' => $data->priceMinor,
            'owner_id' => $ownerId,
        ]);
        return $this->find((int) $this->pdo->lastInsertId())
            ?? throw new \RuntimeException('Созданная запись не найдена');
    }
}
