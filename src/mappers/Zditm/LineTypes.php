<?php


namespace SzczecinInTouch\mappers\Zditm;


class LineTypes
{
    const TRAM = 0;
    const BUS = 3;

    private static $lineTypesMap = [
        self::TRAM => 'tram',
        self::BUS => 'bus'
    ];

    /**
     * @param int $type
     *
     * @return string
     */
    public static function getLineTypeName(int $type): string
    {
        return self::$lineTypesMap[$type] ?? '';
    }
}
