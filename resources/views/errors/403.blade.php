<x-errors.layout
    code="403"
    title="Нямаш достъп"
    :message="$exception?->getMessage() ?: 'Тази страница е достъпна само за определени потребители.'" />
