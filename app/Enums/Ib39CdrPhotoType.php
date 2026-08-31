<?php

namespace App\Enums;

enum Ib39CdrPhotoType: string
{
    case WholeBodyWithFirearm = 'whole_body_with_firearm';
    case HalfBodyWithoutFirearm = 'half_body_without_firearm';
}
