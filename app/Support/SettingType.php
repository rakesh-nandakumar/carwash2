<?php

namespace App\Support;

/**
 * Fixed, non-business, internal type discriminator for Setting values —
 * a legitimate use of a plain constants class rather than a lookup table.
 */
class SettingType
{
    public const TEXT = 'text';

    public const TEXTAREA = 'textarea';

    public const NUMBER = 'number';

    public const MONEY = 'money';

    public const PERCENT = 'percent';

    public const BOOLEAN = 'boolean';

    public const JSON = 'json';

    public const IMAGE = 'image';

    public const COLOR = 'color';

    public const SELECT = 'select';
}
