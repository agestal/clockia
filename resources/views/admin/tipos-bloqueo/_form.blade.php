@include('admin.catalogos-simple._form-card', [
    'item' => $tipoBloqueo,
    'isEdit' => $isEdit,
    'submitLabel' => $submitLabel,
    'backRoute' => route('admin.tipos-bloqueo.index'),
    'nombrePlaceholder' => 'Ejemplo: Mantenimiento',
    'nombreHelp' => 'Nombre claro para identificar el tipo de bloqueo.',
    'descripcionPlaceholder' => 'Descripción opcional para uso interno',
])
