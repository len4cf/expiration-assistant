<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Active = 'active';
    case Consumed = 'consumed';
    case Discarded = 'discarded';
}
