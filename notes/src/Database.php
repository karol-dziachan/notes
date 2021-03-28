<?php
declare(strict_types =1); 
namespace App; 

require_once("src/Exception/AppException.php");
require_once("src/Exception/StorageException.php");
require_once("src/Exception/ConfigurationException.php");
require_once("src/Exception/NotFoundException.php");

use App\Exception\StorageException; 
use App\Exception\ConfigurationException; 
use App\Exception\NotFoundException; 
use Exception; 
use Throwable;
use PDO; 

class Database
{
  private PDO $conn;

    public function __construct(array $config)
    { 
      try
      {
        $this -> validateConfig($config); 
        $this -> createConnection($config);  
      }catch(PDOException $e)
      {
        throw new StorageException("Connection error"); 
      }
        
    }

    private function createConnection(array $config):void
    {
      
        $dsn = "mysql:dbname={$config['database']};host={$config['host']}";
        $this -> conn = new PDO(
          $dsn,
          $config['user'],
          $config['password'],
          [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
          ]
        );
    }

    private function validateConfig(array $config):void 
    {
      if(
        empty($config['database'])
        || empty($config['user'])
        || empty($config['host'])
        || empty($config['password'])
      )
      {
        throw new ConfigurationException("Storage configuration exception"); 
      }
    }

    public function getNotes():array
    {
      try{
        $query = "
        SELECT id, title, created 
        FROM notes
        ";
        $result = $this->conn->query($query); 
        return  $result -> fetchAll(PDO::FETCH_ASSOC); 
      }catch(Throwable $e){
        throw new StorageException("nie udalo sie pobrac danych o notatkach");
      }
    }

    public function getNote(int $id):array
    {
      try {
        $query = "SELECT * FROM notes WHERE id = $id";
        $result = $this->conn->query($query);
        $note = $result->fetch(PDO::FETCH_ASSOC);
      } catch (Throwable $e) {
        throw new StorageException('Nie udało się pobrać notatki', 400, $e);
      }
  
      if (!$note) {
        throw new NotFoundException("Notatka o id: $id nie istnieje");
      }
  
      return $note;
    }
    public function creationNote(array $data):void
    {
      try 
      {
        $title = $this->conn->quote($data['title']); 
        $description = $this->conn->quote($data['description']); 
        $created = $this->conn->quote(date('Y-m-d H:i:s')); 

        $query = "
          INSERT INTO notes(title, description, created) 
          VALUES($title, $description, $created)
        ";
        
        $this->conn->exec($query);
      }catch(Throwable $e)
      {
        throw new StorageException("Problem in add notes", 400); 
      }
    }
}