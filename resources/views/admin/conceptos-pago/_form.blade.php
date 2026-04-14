@include('admin.catalogos-simple._form-card', [
    'item' => $conceptoPago,
    'isEdit' => $isEdit,
    'submitLabel' => $submitLabel,
    'backRoute' => route('admin.conceptos-pago.index'),
    'nombrePlaceholder' => 'Ejemplo: Señal',
    'nombreHelp' => 'Nombre claro para identificar el concepto del pago.',
    'descripcionPlaceholder' => 'Descripción opcional para uso interno',
])
