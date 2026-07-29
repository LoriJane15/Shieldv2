<?php

namespace App\Http\Requests\Mblrc;

/**
 * Same field rules as creation; separated so future edit-only constraints
 * (e.g. immutable classified_id) live here.
 */
class UpdateFormerRebelRequest extends StoreFormerRebelRequest {}
