<?php 

namespace Services;

use Exception;
use Models\Book; 
use Models\Access;

class BookService {
    private $bookModel;
    private $accessModel; 

    public function __construct() {
        $this->bookModel = new Book();
        $this->accessModel = new Access();
    }
    public function getUserBooks(int $userId) {
        return $this->bookModel->findByUserId($userId);
    }
    /**
     * метод создания книги 
     */
    public function createBook(
        int $userId, 
        string $title, 
        ?string $content = null, 
        ?array $file = null
        ):int  { 

            if(empty($title)){
            throw new Exception('Не указано название книги', 400);
        }
            if(empty($content) && $file === null) {
                 throw new Exception('Нужно добавить текст книги или загрузить файл', 400);
            }
            //если файл загружен, то проверяем его размер и присваиваем переменной контент 
            if($file !== null) {
                if($file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Ошибка при загрузке файла', 400);
                }
                
                if($file['size'] === 0) {
                    throw new Exception('Файл не может быть пустым', 400);
                }

                $content = file_get_contents($file['tmp_name']);

                if($content === false) {
                    throw new Exception('Не удалось прочитать файл', 500);
                }
            }
            $bookId = $this->bookModel->create([
                'user_id' => $userId,
                'title' => $title,
                'content' => $content
            ]);

            return $bookId;

    }
    /**
     * метод возвращает название и текст книги по уникальному идентификатору. проверяю, если ли у пользователя доступ к этой книге
     */
    public function getBook(int $bookId, int $requestingUserId):array {
        $book = $this->bookModel->find($bookId);
        if (!$book) {
            throw new Exception('Такой книги не существует', 404);
        }
        if ($book['user_id'] !== $requestingUserId) {
            $access = $this->accessModel->hasAccess($book['user_id'], $requestingUserId);

            if (!$access) {
                throw new Exception('К выбранной книге нет доступа', 403);
            }
        }
        
        return [
            'id' => $book['id'],
            'title' => $book['title'],
            'content' => $book['content']
        ];
    }  
    /**
     * метод 'Сохранить книгу'
     */
    public function updateBook(
        int $bookId, 
        int $requestingUserId, 
        ?string $newTitle = null,
        ?string $newContent = null 
        ):bool {
            $book = $this->bookModel->find($bookId);

            if(!$book) {
               throw new Exception('Такой книги не существует', 404);
            }

            if($book['user_id'] !== $requestingUserId) {
                 throw new Exception('Нет прав на редактирование этой книги', 403);
            }
            $data = [];

            if($newTitle !== null) {
                $data['title'] = $newTitle;
            }

            if($newContent !== null) {
                $data['content'] = $newContent;
            }

            if (empty($data)) {
               throw new Exception('Нет данных для обновления', 400);
            }

            return $this->bookModel->update($bookId, $data);
           
    }
    
    public function deleteBook(int $bookId, int $requestingUserId): bool{
         $book = $this->bookModel->find($bookId);
        if (!$book) {
            throw new Exception('Такой книги не существует', 404);
        }
        if ($book['user_id'] !== $requestingUserId) {
            $access = $this->accessModel->hasAccess($book['user_id'], $requestingUserId);

            if (!$access) {
                throw new Exception('Нет прав на удаление этой книги', 403);
            }
        }
        return $this->bookModel->softDelete($bookId);
    }
    public function restoreBook(int $bookId, int $requestingUserId):bool  {
        $book = $this->bookModel->find($bookId);

         if (!$book) {
            throw new Exception('Такой книги не существует', 404);
        }
        if ($book['user_id'] !== $requestingUserId) {
            $access = $this->accessModel->hasAccess($book['user_id'], $requestingUserId);

            if (!$access) {
                throw new Exception('Нет прав на восстановление этой книги', 403);
            }
        }
        return $this->bookModel->restore($bookId);
    }
    
    public function saveFoundBook(int $userId, string $googleBookId):int {
            $googleService = new GoogleBooksService();
            $bookData = $googleService->fetchBooksDetails($googleBookId);

            return $this->createBook(
                $userId,
                $bookData['title'],
                $bookData['description']
            );

    }

    }