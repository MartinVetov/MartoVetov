<?php

namespace App\Http\Requests\Provider;

class UpdateProviderProfileRequest extends StoreProviderProfileRequest
{
    //  Правилата съвпадат със създаването; отделният клас пази намерението ясно
    //  и позволява по-късно да се разминат, без да се пипа контролерът.
}
