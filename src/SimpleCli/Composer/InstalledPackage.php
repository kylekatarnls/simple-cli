<?php

declare(strict_types=1);

namespace SimpleCli\Composer;

class InstalledPackage
{
    /**
     * @var string|null
     */
    public $name = null;

    /**
     * @var string|null
     */
    public $version = null;

    /**
     * @param array<string, string> $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
