<?php

namespace Services;

use Exception;

class GoogleBooksService
{
    private $apiUrl = 'https://www.googleapis.com/books/v1/volumes';

    /**
     * поиск книг через гугл апи
     */
    public function searchBooks(string $query): array
    {
        $url = $this->apiUrl . '?q=' . urlencode($query);
        $response = file_get_contents($url);

        if ($response === false) {
            throw new Exception('Не удалось получить ответ от Google API', 500);
        }

        $data = json_decode($response, true);

        if (!isset($data['items'])) {
            return [];
        }

        return $this->formatBooks($data['items']);
    }
    /**
     * метод для получения конкретной книги с помощью её гугл айди
     */
    public function fetchBooksDetails(string $googleBookId): array
    {
        $url = $this->apiUrl . '/' . $googleBookId;
        $response = file_get_contents($url);

        if ($response === false) {
            throw new Exception('Не удалось получить информацию о книге', 500);
        }

        $data = json_decode($response, true);

        return [
            'google_id' => $data['id'] ?? '',
            'title' => $data['volumeInfo']['title'] ?? 'Без названия',
            'description' => $data['volumeInfo']['description'] ?? $data['volumeInfo']['previewLink'] ?? 'Нет описания'
        ];
    }
    /**
     * метод для форматирования ответа по апи
     */
    private function formatBooks(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            // пропускаем на случай битых записей
            if (!isset($item['id'], $item['volumeInfo']['title'])) {
                continue;
            }

            $result[] = [
            'google_id' => $item['id'],
            'title' => $item['volumeInfo']['title'],
            'description' => $item['volumeInfo']['description'] ?? 'Нет описания'
            ];
        }

        return $result;

    }


}
