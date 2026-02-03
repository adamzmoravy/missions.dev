<?php

namespace Karolina;

Class Environment {

    private $loadedEnvVariables = array();
    public $dotenv;

    public function __construct ($dotenv = null) {

        $this->dotenv = $dotenv;

    }

    public function loadEnvVariablesByHostname ($hostname) {

        $hostname = strtolower($hostname);

        try {
            $dsn = "mysql:host=".getenv('database_hostname').";charset=utf8mb4;";
            $dsn .= "dbname=environments";

            $pdo = new \PDO($dsn, getenv('database_user'), getenv('database_pass'));
            $stmt = $pdo->prepare('SELECT * 
				FROM variables 
		        LEFT JOIN hosts 
		            ON (hosts.collection=variables.collection) 
				WHERE hostname = :hostname');

            // Default
            $default = 'default';
            $stmt->bindParam(':hostname', $default);
            $stmt->execute();
            $resultDefault = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Hostname
            $stmt->bindParam(':hostname', $hostname);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->loadEnvVariablesFromArray(array_merge($resultDefault, $result));

        } catch (\PDOException $e) {
            if (getenv('environment') == 'development') {
                echo 'Connection failed: ' . $e->getMessage();
            }
        }
        catch (\Exception $e) {
            if (getenv('environment') == 'development') {
                echo 'Connection failed: ' . $e->getMessage();
            }
        }

    }

    private function loadEnvVariablesFromArray (array $variables) {

        foreach ($variables as $key => $variable) {
            $this->loadedEnvVariables[$key] = $variable;
        }

    }


    public function setEnvVariablesFromArray (array $variables) {

        foreach ($variables as $name => $value) {
            $this->setEnvVariable($name, $value);
        }

    }

    private function setEnvVariable ($varible, $value) {

        putenv("$varible=$value");

    }

    public function setLoadedEnvVariables () {

        // Set loaded
        foreach ($this->loadedEnvVariables as $variable) {
            $this->setEnvVariable($variable['name'], $variable['value']);
        }

        if ($this->dotenv != null) {
            $this->dotenv->safeLoad();
        }


    }



}