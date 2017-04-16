<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service;

use jschreuder\SpotDesk\Collection\StatusCollection;
use jschreuder\SpotDesk\Entity\Status;
use jschreuder\SpotDesk\Value\StatusTypeValue;

class StatusService
{
    /** @var  \PDO */
    private $db;

    /** @var  StatusCollection */
    private $_statuses;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    private function arrayToStatus(array $row): Status
    {
        return new Status($row['status'], StatusTypeValue::get($row['type']));
    }

    public function getStatus(string $status): Status
    {
        return $this->getStatuses()[$status];
    }

    public function getStatuses(): StatusCollection
    {
        if (is_null($this->_statuses)) {
            $query = $this->db->prepare("
                SELECT * FROM `statuses`
            ");
            $query->execute();

            $statusCollection = new StatusCollection();
            while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
                $statusCollection->push($this->arrayToStatus($row));
            }

            $this->_statuses = $statusCollection;
        }
        return $this->_statuses;
    }
}
