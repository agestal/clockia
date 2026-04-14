@include('admin.catalogos-simple._form-card', [
    'item' => $estadoPago,
    'isEdit' => $isEdit,
    'submitLabel' => $submitLabel,
    'backRoute' => route('admin.estados-pago.index'),
    'nombrePlaceholder' => 'Ejemplo: Pendiente',
    'nombreHelp' => 'Nombre claro para identificar el estado de pago.',
    'descripcionPlaceholder' => 'Descripción opcional para uso interno',
])
