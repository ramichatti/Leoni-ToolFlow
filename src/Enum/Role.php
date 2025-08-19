<?php

namespace App\Enum;

enum Role: string
{
    case ROLE_MAINTENANCE = 'ROLE_MAINTENANCE';
    case ROLE_ADMIN = 'ROLE_ADMIN';
    
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
    
    public static function getNames(): array
    {
        return array_column(self::cases(), 'name');
    }
    
    public static function getChoices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->name] = $case->value;
        }
        return $choices;
    }
} 