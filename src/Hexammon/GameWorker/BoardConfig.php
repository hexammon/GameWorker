<?php


namespace Hexammon\GameWorker;


class BoardConfig
{

    /**
     * @var string
     */
    private $type;
    /**
     * @var int
     */
    private $rowsCount;
    /**
     * @var int
     */
    private $columnsCount;

    public function __construct(string $type, int $rowsCount, int $columnsCount)
    {
        $this->type = $type;
        $this->rowsCount = $rowsCount;
        $this->columnsCount = $columnsCount;
    }

    /**
     * @return int
     */
    public function getColumnsCount(): int
    {
        return $this->columnsCount;
    }

    /**
     * @return int
     */
    public function getRowsCount(): int
    {
        return $this->rowsCount;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}