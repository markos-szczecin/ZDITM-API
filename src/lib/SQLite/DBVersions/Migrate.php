<?php


namespace SzczecinInTouch\lib\SQLite\DBVersions;


class Migrate
{

    private $loadedVersion = null;

    /** @var aVersion[] */
    private $versionClasses = [];

    /**
     * Aktualizacja wersji bazy w dbv.php
     *
     * @return Migrate
     */
    public function updateCurrentVersion(): Migrate
    {
        if (!$this->loadedVersion) {
            return $this;
        }
        $config = file_get_contents(DBV_PATH);
        $config = preg_replace('/\(\'DB_VERSION\',\s[0-9]+\)/', "('DB_VERSION', {$this->loadedVersion})", $config);
        file_put_contents(DBV_PATH, $config);

        return $this;
    }

    /**
     * Ladowanie instancji VersionX
     */
    private function loadVersions()
    {
        $vDir = scandir(__DIR__ . '/v');
        foreach ($vDir as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $class = 'SzczecinInTouch\\lib\\SQLite\\DBVersions\\v\\' . basename($file, '.php');
            /** @var aVersion $obj */
            $obj = new $class();
            if (DB_VERSION < $obj->getVersion()) {
                $this->versionClasses[] = $obj;
            }
        }
    }

    /**
     * Aktualizacja bazy danych wg plików Version[X].php
     * @return Migrate
     */
    public function migrate(): Migrate
    {
        $this->loadVersions();
        foreach ($this->versionClasses as $class) {
            $class->query();
            $this->loadedVersion = $class->getVersion();
        }
        $this->updateCurrentVersion();

        return $this;
    }
}
