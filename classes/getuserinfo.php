<?php

class getuserinfo
{
    /**
     * @var object $db_connection The database connection
     */
    private $db_connection            = null;

    /**
     * Checks if database connection is opened and open it if not
     */
    private function databaseConnection()
    {
        // connection already opened
        if ($this->db_connection != null) {
            return true;
        } else {
            // create a database connection, using the constants from config/config.php
            try {
                // Generate a database connection, using the PDO connector
                // @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
                // Also important: We include the charset, as leaving it out seems to be a security issue:
                // @see http://wiki.hashphp.org/PDO_Tutorial_for_MySQL_Developers#Connecting_to_MySQL says:
                // "Adding the charset to the DSN is very important for security reasons,
                // most examples you'll see around leave it out. MAKE SURE TO INCLUDE THE CHARSET!"
                $this->db_connection = new PDO("pgsql:host=".DB_HOST." port=".DB_PORT." dbname=".DB_NAME." user=".DB_USER." password=".DB_PASS."");
                return true;
            // If an error is catched, database connection failed
            } catch (PDOException $e) {
                $this->errors[] = MESSAGE_DATABASE_ERROR;
                return false;
            }
        }
    }

    /**
     * checks the id/verification code combination and set the user's activation status to true (=1) in the database
     */
    public function get_info()
    {
        // if database connection opened
        if ($this->databaseConnection()) {
            // try to update user with specified information
            $query_user = $this->db_connection->prepare('SELECT * FROM directory WHERE username = :user_name AND id = :id');
            $query_user->bindValue(':user_name', $_SESSION['user_name'], PDO::PARAM_STR);
            $query_user->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_STR);
            $query_user->execute();

            if ($query_user->rowCount() > 0) {
                return $query_user->fetchObject();
            } else {
                return false;
            }
        }
    }
}