@include('admin.catalogos-simple._form-card', [
    'item' => $estadoReserva,
    'isEdit' => $isEdit,
    'submitLabel' => $submitLabel,
    'backRoute' => route('admin.estados-reserva.index'),
    'nombrePlaceholder' => 'Ejemplo: Confirmada',
    'nombreHelp' => 'Nombre claro para identificar el estado de reserva.',
    'descripcionPlaceholder' => 'Descripción opcional para uso interno',
])
