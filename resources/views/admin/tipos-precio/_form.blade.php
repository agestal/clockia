@include('admin.catalogos-simple._form-card', [
    'item' => $tipoPrecio,
    'isEdit' => $isEdit,
    'submitLabel' => $submitLabel,
    'backRoute' => route('admin.tipos-precio.index'),
    'nombrePlaceholder' => 'Ejemplo: Precio fijo',
    'nombreHelp' => 'Nombre claro para identificar el tipo de precio.',
    'descripcionPlaceholder' => 'Descripción opcional para uso interno',
])
