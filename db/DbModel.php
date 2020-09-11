<?php   

namespace siil78\phpmvc\db;

use siil78\phpmvc\Model;
use siil78\phpmvc\Application;

/**
 * Abstraktní třída definuje komunikaci modelu s databází
 */

abstract class DbModel extends Model {

    //každý model musí definovat jméno tabulky v db
    abstract public static function tableName(): string;

    //každý model musí definovat svoje atributy pro db
    abstract public function attributes(): array;

    /**
     * Jaký sloupec databáze je primárním klíčem.
     *
     * @return string
     */
    abstract public function primaryKey(): string;

    public function save() {

        $tableName = $this->tableName();
        $attributes = $this->attributes();
        //vytvoř pole hodnot atributů ve formátu ":value" pro statement do db
        $params = array_map(fn($attr) => ":$attr", $attributes);

        //atributy (=sloupce databáze) jsou vypsány pomocí funkce implode
        //analogicky VALUES jsou získány z pole params
        $statement = self::prepare("INSERT INTO $tableName(".implode(',', $attributes).") VALUES(".implode(',', $params).")");
        
        foreach ($attributes as $attribute) {
            //bindValue připojí do statementu skutečnou hodnotu
            $statement->bindValue(":$attribute", $this->{$attribute});
        }
        $statement->execute();   
        
        return true;
    }

    /**
     * Undocumented function
     *
     * @param  array $where
     * např. [email => email@email.com, firstname => zura]
     * @return mixed
     */
    public static function findOne($where) 
    {
        //static:: volá tableName jako instance třídy
        $tableName = static::tableName();
        $attributes = array_keys($where);
        //příklad SQL SELECT * FROM $tableName WHERE email = :email AND firstname = :firstname
        //tento tvar získáme pomocí následujícího
        $sql = implode("AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));
        $statement = self::prepare("SELECT * FROM $tableName WHERE $sql");
        foreach ($where as $key => $item) {
            $statement->bindValue(":$key", $item);
        }
        $statement->execute();

        //vrací objekt třídy, kde sloupce tabulky korespondují s atributy třídy
        return $statement->fetchObject(static::class);
    }

    /**
     * Pomocná funkce pro sql statement prepare
     *
     * @param mixed $sql 
     * @return PDOStatement|bool
     */
    public static function prepare($sql) {
        return Application::$app->db->pdo->prepare($sql);
    }

}