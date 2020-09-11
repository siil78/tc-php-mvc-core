<?php

namespace siil78\phpmvc\db;

use siil78\phpmvc\Application;


class Database {

    public \PDO $pdo;

    /**
     * Class constructor.
     * nastavení konektoru k databázi
     */
    public function __construct(array $config)
    {
        $dsn = $config['dsn'] ?? '';
        $user = $config['user'] ?? '';
        $password = $config['password'] ?? '';
        
        $this->pdo = new \PDO($dsn, $user, $password);
        //nastavení chování oznamování chyb při komunikaci s db
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function applyMigrations()
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $newMigrations = [];

        //získej soubory s migracemi z adr. migrations  
        //vrací pole s názvy soubrorů      
        $files = scandir(Application::$ROOT_DIR.'/migrations');
        $toApplyMigrations = array_diff($files, $appliedMigrations);
        foreach ($toApplyMigrations as $migration) {
            if ($migration === '.' || $migration === '..') {
                continue;
            }
            //importuj třídu migrace
            require_once Application::$ROOT_DIR.'/migrations/'.$migration;
            //získej název třídy migrace
            $className = pathinfo($migration, PATHINFO_FILENAME);
            $instance = new $className();
            $this->log("Applying migration $migration");
            $instance->up();
            $this->log("Applied migration $migration");
            //přidej migraci do pole newMigrations
            $newMigrations[] = $migration;
        }

        //ulož do db provedené migrace
        if (!empty($newMigrations)) {
            $this->saveMigrations($newMigrations);
        } else {
            $this->log("All migrations are applied");
        }
    }

    public function createMigrationsTable()
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations(
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=INNODB;
            ");
    }

    public function getAppliedMigrations()
    {
        $statement = $this->pdo->prepare("SELECT migration FROM migrations");
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function saveMigrations(array $migrations) 
    {        
        //uprav název migrace do formátu pro zápis do db
        $str = implode(",", (array_map(fn($m) => "('$m')", $migrations))); 
        
        //ulož provedené migrace do db
        $statement = $this->pdo->prepare("INSERT INTO migrations(migration) VALUES $str");
        $statement->execute();
    }

    //pomocná metoda pro prepare
    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }

    protected function log($message) 
    {
        echo '['.date('Y-m-d H:i:s').'] - '.$message.PHP_EOL;
    }
}