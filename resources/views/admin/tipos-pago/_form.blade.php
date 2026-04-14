@include('admin.catalogos-simple._form-card', [
    'item' => $tipoPago,
    'isEdit' => $isEdit,
    'submitLabel' => $submitLabel,
    'backRoute' => route('admin.tipos-pago.index'),
    'nombrePlaceholder' => 'Ejemplo: Tarjeta',
    'nombreHelp' => 'Nombre claro para identificar el tipo de pago.',
    'descripcionPlaceholder' => 'Descripción opcional para uso interno',
])
