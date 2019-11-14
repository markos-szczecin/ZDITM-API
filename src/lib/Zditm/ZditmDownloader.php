<?php

namespace SzczecinInTouch\lib\Zditm;

use SzczecinInTouch\mappers\Zditm\Lines;

class ZditmDownloader
{
    public function getLines(): array
    {
        static $linesData;

        if (!$linesData) {
            $linesData = (new Lines())->getAllNumbers();
        }

        return (array) $linesData;
    }

    public function getLinesNumbers(): array
    {
        $lines = [];

        foreach ($this->getLines() as $type => $lineData)
            foreach ($lineData as $id => $line)
                $lines[$type][$id] = $line['number'];

        return $lines;
    }
}
