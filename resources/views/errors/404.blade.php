<x-errors.layout
    code="404"
    title="Страницата не е намерена"
    :message="$exception?->getMessage() ?: 'Адресът, който потърси, не съществува или е бил преместен.'" />
