<?php

namespace App\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;

class DateTimeImmutableMsType extends Type
{
    const NAME = 'datetime_immutable_ms';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'timestamp_ms';
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        $val = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $value);

        if (!$val) {
            $val = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        }

        if (!$val) {
            throw InvalidFormat::new($value, self::NAME, 'Y-m-d H:i:s.u');
        }

        return $val;
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeImmutable) {
            return $value->format('Y-m-d H:i:s.u');
        }

        throw InvalidType::new($value, self::NAME, ['null', 'DateTimeImmutable']);
    }
}