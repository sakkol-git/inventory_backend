<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum PlantGrowthType: string
{
    case HERB = 'herb';
    case SHRUB = 'shrub';
    case TREE = 'tree';
    case VINE = 'vine';
    case GRASS = 'grass';
    case AQUATIC = 'aquatic';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::HERB => 'Herb',
            self::SHRUB => 'Shrub',
            self::TREE => 'Tree',
            self::VINE => 'Vine',
            self::GRASS => 'Grass',
            self::AQUATIC => 'Aquatic',
            self::OTHER => 'Other',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(
            fn (self $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            self::cases()
        );
    }
}
