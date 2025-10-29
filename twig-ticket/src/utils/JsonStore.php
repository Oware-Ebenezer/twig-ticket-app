<?php

namespace Eben\TwigTicketapp\Utils;

class JsonStore
{
    private string $path;

    public function __construct(string $filePath)
    {
        $this->path = $filePath;

        $dir = dirname($filePath);

        // ✅ Create directory if it doesn’t exist
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // ✅ Create file if it doesn’t exist
        if (!file_exists($filePath)) {
            file_put_contents($filePath, json_encode([]));
        }
    }

    private function read(): array
    {
        $data = file_get_contents($this->path);
        return json_decode($data, true) ?? [];
    }

    private function write(array $data): void
    {
        file_put_contents($this->path, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function getAll(): array
    {
        return $this->read();
    }

    public function add(array $data): void
    {
        $items = $this->read();
        $items[] = $data;
        $this->write($items);
    }

    public function find(string $id): ?array
    {
        foreach ($this->read() as $item) {
            if ($item['id'] === $id) return $item;
        }
        return null;
    }

    public function update(string $id, array $patch): ?array
    {
        $items = $this->read();
        foreach ($items as &$item) {
            if ($item['id'] === $id) {
                $item = array_merge($item, $patch);
                $this->write($items);
                return $item;
            }
        }
        return null;
    }

    public function delete(string $id): bool
    {
        $items = $this->read();
        $filtered = array_filter($items, fn($i) => $i['id'] !== $id);
        if (count($items) === count($filtered)) return false;
        $this->write(array_values($filtered));
        return true;
    }
}
