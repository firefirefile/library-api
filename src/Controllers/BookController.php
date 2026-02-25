<?php 

namespace Controllers;

use Exception; 
use Services\BookService;
use Services\GoogleBooksService;

class BookController {
    private $bookService;
    private $googleService; 

    public function __construct()
    {
        $this->bookService = new BookService();
        $this->googleService = new GoogleBooksService();
    }

     /**
     * GET /books - список книг авторизованного пользователя
     */
    public function getUserBooks(int $userId):void {

        try {
            $books = $this->bookService->getUserBooks($userId);

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $books
            ]);
        } catch(Exception $e) {
            $code = $e->getCode();
            if (!is_int($code)) {
                $code = 500;
            }
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e -> getMessage()
            ]);
            
        }
    }
    /**
     * POST /books - создать книгу
     */
    public function createBook(int $userId):void {
        try {
        // поддерживает json и part form data 
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'multipart/form-data') !== false) {
            $input = $_POST;
        } else {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
        }

        $title = $input['title'] ?? '';
        $content = $input['content'] ?? null;
        $file = $_FILES['book_file'] ?? null;

        
            $bookId = $this->bookService->createBook($userId, $title, $content, $file);
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'book_id' => $bookId
            ]);
            
        } catch(Exception $e) {
            $code = $e->getCode();
            if (!is_int($code)) {
                $code = 400;
            }
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e -> getMessage()
            ]);

        }
    }

     /**
     * GET /books/{id} - открыть книгу
     */
    public function getBook(int $userId, int $bookId):void {
        try
        {
        $book = $this->bookService->getBook(  $bookId, $userId);
        http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $book
            ]);

        } catch(Exception $e) {
            $code = $e->getCode();
            if (!is_int($code)) {
                $code = 404;
            }
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e -> getMessage()
            ]);

        }
    }
    /**
     * PUT /books/{id} - обновить книгу
     */
    public function updateBook( int $userId, int $bookId):void {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $newTitle = $input['title'] ?? null;
            $newContent = $input['content'] ?? null;

            $result = $this->bookService->updateBook($bookId, $userId, $newTitle, $newContent);
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Book was updated'
            ]);

        } catch(Exception $e) {
            $code = $e->getCode();
            if (!is_int($code)) {
                $code = 400;
            }
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e -> getMessage()
            ]);
        }
    }
     /**
     * DELETE /books/{id} - удалить книгу
     */
    public function deleteBook(int $userId, int $bookId, ):void {
        try {
            $result = $this->bookService->deleteBook($bookId, $userId);

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Book was deleted'
            ]);
        } catch(Exception $e) {
            $code = $e->getCode();
            if (!is_int($code)) {
                $code = 400;
            }
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e -> getMessage()
            ]);
        }

    }
      /**
     * POST /books/{id}/restore - восстановить книгу
     */
    public function restoreBook(int $userId, int $bookId):void {
        try {
            $result = $this->bookService->restoreBook($bookId, $userId);

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Book was restored'
            ]);
        } catch(Exception $e) {
            $code = $e->getCode();
            if (!is_int($code)) {
                $code = 403;
            }
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e -> getMessage()
            ]);
        }

    }
    /**
     * GET /users/{id}/books - книги другого пользователя
     */
    public function getUserBooksByAccess(int $userId, int $ownerId ):void {
        try {
            $books = $this->bookService->getUserBooksByAccess($ownerId, $userId);

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $books
            ]);
        } catch(Exception $e) {
            $code = $e->getCode();
            if (!is_int($code)) {
                $code = 403;
            }
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e -> getMessage()
            ]);
        }

    }

//методы для гугл сервис 

    /**
     * GET /books/search?q=... - поиск через Google
     */
    public function searchGoogleBooks():void {
        try {
            $query = $_GET['q'] ?? '';

            if(empty($query)) {
                throw new Exception('Search query is required', 400);
            }

            $books = $this->googleService->searchBooks($query);

        http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $books
            ]);
        }   catch(Exception $e) {
            $code = $e->getCode();
            if (!is_int($code)) {
                $code = 500;
            }
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e -> getMessage()
            ]);
        }

    }
    /**
     * POST /books/import - сохранить найденную книгу
     */
    public function saveFoundBook(int $userId):void {

    try{
        $input = json_decode(file_get_contents('php://input'), true);
        $googleBookId = $input['google_id'] ?? '';

        if(empty($googleBookId)) {
                throw new Exception('Google book ID is required', 400);
            }
        $bookId = $this->bookService->saveFoundBook($userId, $googleBookId);
         http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'book_id' => $bookId
            ]);

    } catch (Exception $e) {
        $code = $e->getCode();
        if (!is_int($code)) {
            $code = 500;
        }
        http_response_code($code);
        header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e -> getMessage()
            ]);
    }


    }

}