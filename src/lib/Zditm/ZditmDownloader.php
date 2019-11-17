<?php

namespace SzczecinInTouch\lib\Zditm;

use SzczecinInTouch\mappers\Zditm\Lines;

class ZditmDownloader
{
    /**
     * Dane o liniach autobusowych i tramwajowych
     *
     * @return array
     */
    private function getLines(): array
    {
        static $linesData;

        if (!$linesData) {
            $linesData = (new Lines())->getAllNumbers();
        }

        return (array) $linesData;
    }

    /**
     * Nr lini pogrupowane na tramwaje i autobusy
     *
     * @return array
     */
    public function getLinesNumbers(): array
    {
        $lines = [];

        foreach ($this->getLines() as $type => $lineData)
            foreach ($lineData as $id => $line)
                $lines[$type][$id] = $line['number'];

        return $lines;
    }
}
