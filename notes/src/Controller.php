<?php
declare(strict_types=1); 
namespace App;
use App\Exception\ConfigurationException;
use PDO; 

require_once("src/View.php");
require_once("src/Database.php");
require_once("src/Exception/ConfigurationException.php");
const DEFAULT_ACTION = 'list';

class Controller
{
    private static array $configuration = []; 

    private Database $database; 
    private array $request;  
    private View $view; 

    public static function initConfiguration(array $configuration):void
    {
        self::$configuration = $configuration; 
    }

    public function __construct(array $request)
    {
        if(empty(self::$configuration['db']))
        {
            throw new ConfigurationException("Configuration error");
        }
       $this -> database = new Database(self::$configuration['db']);
       
        $this -> request = $request; 
        $this -> view = new View(); 
    }

    public function run():void
    {   
        $viewParams = [];

        switch($this -> action())
            {
            case 'create':
                $page = 'create';

                $data = $this -> getRequestPost(); 
                if(!empty($data))
                {
                $this -> database -> creationNote(
                  [ 
                      'title' => $data['title'], 
                      'description' => $data['description'] 
                  ]
                ); 
                header('Location: /?before=created');
                }
                break; 
            case 'show':
                $page = 'show';

                $data = $this->getRequestGet();
                $noteId = (int) ($data['id'] ?? null);
        
                if (!$noteId) {
                  header('Location: /?error=missingNoteId');
                  exit;
                }
        
                try {
                  $note = $this->database->getNote($noteId);
                } catch (NotFoundException $e) {
                  header('Location: /?error=noteNotFound');
                  exit;
                }
        
                $viewParams = [
                  'note' => $note
                ];
                break;
            default:
            $page = 'list';
            $data = $this -> getRequestGet();  
            $viewParams =[
                'notes' =>  $notes = $this -> database -> getNotes(),
                'before' => $data['before'] ?? null,
                'error' => $data['error'] ?? null 
            ];
                break; 
            } 

        $this -> view ->render($page, $viewParams);
    }

    private function action():string 
    {
        $data = $this -> getRequestGet(); 
        return $data['action'] ?? DEFAULT_ACTION;
    }

    private function getRequestPost():array 
    {
        return $this -> request['post'] ?? []; 
    }

    private function getRequestGet(): array
    {
        return $this -> request['get'] ?? []; 
    }


}