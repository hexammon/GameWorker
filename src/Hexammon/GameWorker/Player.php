<?php

namespace Hexammon\GameWorker;

use Hexammon\HexoNards\Game\PlayerInterface;

class Player implements PlayerInterface
{
    /**
     * @var string
     */
    private $uuid;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getId(): string
    {
         return $this->uuid;
    }
}