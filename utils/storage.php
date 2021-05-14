<?php

interface IFileIO
{
    function save($data);

    function load();
}

interface IStorage
{
    function add($record): string;

    function findById($id);

    function findAll(array $params = []);

    function findOne(array $params = []);

    function update(string $id, $record);

    function delete(string $id);

    function findMany(callable $condition);

    function updateMany(callable $condition, callable $updater);

    function deleteMany(callable $condition);
}

abstract class FileIO implements IFileIO
{
    protected $filepath;

    public function __construct($filename)
    {
        if (!is_readable($filename) || !is_writable($filename)) {
            throw new Exception("Data source ${filename} is invalid.");
        }
        $this->filepath = realpath($filename);
    }
}

class JsonIO extends FileIO
{
    public function load($assoc = true)
    {
        $file_content = file_get_contents($this->filepath);
        return json_decode($file_content, $assoc) ?: [];
    }

    public function save($data)
    {
        $json_content = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($this->filepath, $json_content);
    }
}

class SerializeIO extends FileIO
{
    public function load()
    {
        $file_content = file_get_contents($this->filepath);
        return unserialize($file_content) ?: [];
    }

    public function save($data)
    {
        $serialized_content = serialize($data);
        file_put_contents($this->filepath, $serialized_content);
    }
}

class Storage implements IStorage
{
    protected $contents;
    protected $io;

    public function __construct(IFileIO $io, $assoc = true)
    {
        $this->io = $io;
        $this->contents = (array)$this->io->load($assoc);
    }

    public function __destruct()
    {
        $this->io->save($this->contents);
    }

    public function add($record): string
    {
        $id = uniqid();
        if (is_array($record)) {
            $record['id'] = $id;
        } else if (is_object($record)) {
            $record->id = $id;
        }
        $this->contents[$id] = $record;
        return $id;
    }

    public function findById($id = "")
    {
        return $this->contents[$id] ?? NULL;
    }

    public function findOne(array $params = [])
    {
        $found_items = $this->findAll($params);
        $first_index = array_keys($found_items)[0] ?? NULL;
        return $found_items[$first_index] ?? NULL;
    }

    public function findAll(array $params = [])
    {
        return array_filter($this->contents, function ($item) use ($params) {
            foreach ($params as $key => $value) {
                if (((array)$item)[$key] !== $value) {
                    return FALSE;
                }
            }
            return TRUE;
        });
    }

    public function update(string $id, $record)
    {
        $this->contents[$id] = $record;
    }

    public function delete(string $id)
    {
        unset($this->contents[$id]);
    }

    public function findMany(callable $condition)
    {
        return array_filter($this->contents, $condition);
    }

    public function updateMany(callable $condition, callable $updater)
    {
        array_walk($all, function (&$item) use ($condition, $updater) {
            if ($condition($item)) {
                $updater($item);
            }
        });
    }

    public function deleteMany(callable $condition)
    {
        $this->contents = array_filter($this->contents, function ($item) use ($condition) {
            return !$condition($item);
        });
    }
}

class UserStorage extends Storage
{
    public function __construct()
    {
        parent::__construct(new JsonIO('C:\xampp\htdocs\covid-busters\storages\users.json'));
    }
}

class AppointmentStorage extends Storage
{
    public function __construct()
    {
        parent::__construct(new JsonIO('C:\xampp\htdocs\covid-busters\storages\appointments.json'));
    }

    public function addAppointment($data)
    {
        $appointment = [
            'year' => (int)$data['year'],
            'month' => (int)$data['month'],
            'day' => (int)$data['day'],
            'hour' => (int)$data['hour'],
            'minute' => (int)$data['minute'],
            'user-ids' => [],
            'max-user-num' => (int)$data['max-user-num']
        ];

        return $this->add($appointment);
    }

    public function deleteAppointment($appointment)
    {
        $this->deleteMany(function ($curr_appointment) use ($appointment) {
            return $curr_appointment['id'] == $appointment['id'];
        });
    }

    public function getAppointmentsByYearAndMonth($year = 0, $month = 0)
    {
        if ($year == 0) {
            $year = (int)date("Y");
        }

        if ($month == 0) {
            $month = (int)date("n");
        }

        return $this->findMany(function ($appointment) use ($year, $month) {
            return $appointment["year"] == (int)$year && $appointment["month"] == (int)$month;
        });
    }

    public function addUserToAppointment($appointment, $user)
    {
        if (($appointment['max-user-num'] > sizeof($appointment['user-ids'])) &&
            (array_search($user['id'], $appointment['user-ids']) === false)) {
            array_push($appointment['user-ids'], $user['id']);
            $this->update($appointment['id'], $appointment);
        }
    }

    public function removeUserFromAppointment($appointment, $user)
    {
        if (($key = array_search($user['id'], $appointment['user-ids'])) !== false) {
            unset($appointment['user-ids'][$key]);
            $this->update($appointment['id'], $appointment);
        }
    }
}
