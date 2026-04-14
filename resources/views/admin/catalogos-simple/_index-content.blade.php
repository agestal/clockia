@include('admin.partials.flash-messages')

<div class="card shadow-sm border-0">
    <div class="card-body border-bottom">
        <form method="GET" action="{{ $indexRoute }}" class="js-basic-validation-form">
            <div class="form-row align-items-end">
                <div class="form-group col-md-6 col-lg-5 mb-md-0">
                    <label for="search">Buscar</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        maxlength="255"
                        class="form-control"
                        placeholder="Buscar por nombre o descripción"
                    >
                </div>

                <div class="form-group col-md-3 col-lg-2 mb-md-0">
                    <label for="sort">Ordenar por</label>
                    <select id="sort" name="sort" class="form-control">
                        <option value="nombre" @selected($sort === 'nombre')>Nombre</option>
                        <option value="created_at" @selected($sort === 'created_at')>Alta</option>
                        <option value="updated_at" @selected($sort === 'updated_at')>Actualización</option>
                    </select>
                </div>

                <div class="form-group col-md-3 col-lg-2 mb-md-0">
                    <label for="direction">Dirección</label>
                    <select id="direction" name="direction" class="form-control">
                        <option value="asc" @selected($direction === 'asc')>Ascendente</option>
                        <option value="desc" @selected($direction === 'desc')>Descendente</option>
                    </select>
                </div>

                <div class="form-group col-md-12 col-lg-3 mb-0 d-flex justify-content-lg-end mt-3 mt-lg-0">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search mr-1"></i>
                        Filtrar
                    </button>

                    <a href="{{ $indexRoute }}" class="btn btn-light border">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="text-center">{{ $countHeading }}</th>
                    <th class="text-nowrap">Actualizado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr data-row-id="{{ $item->id }}">
                        <td class="align-top" style="min-width: 320px;">
                            <div class="js-inline-wrapper">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="pr-2">
                                        <div class="font-weight-semibold js-inline-display">
                                            {{ $item->nombre }}
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="btn btn-xs btn-light border js-inline-edit-toggle"
                                        data-target="inline-form-{{ $moduleKey }}-{{ $item->id }}"
                                    >
                                        <i class="fas fa-pen"></i>
                                    </button>
                                </div>

                                <form
                                    id="inline-form-{{ $moduleKey }}-{{ $item->id }}"
                                    action="{{ route($inlineRouteName, $item) }}"
                                    method="POST"
                                    class="js-inline-form d-none mt-2"
                                    novalidate
                                >
                                    @csrf
                                    @method('PATCH')

                                    <div class="input-group input-group-sm">
                                        <input
                                            type="text"
                                            name="nombre"
                                            value="{{ $item->nombre }}"
                                            maxlength="255"
                                            minlength="2"
                                            required
                                            class="form-control js-inline-input"
                                        >

                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                            <button type="button" class="btn btn-light border js-inline-cancel">Cancelar</button>
                                        </div>
                                    </div>

                                    <div class="invalid-feedback d-block js-inline-error d-none mt-1"></div>
                                </form>
                            </div>
                        </td>
                        <td class="text-muted align-top">
                            {{ $item->descripcion ? \Illuminate\Support\Str::limit($item->descripcion, 90) : 'Sin descripción' }}
                        </td>
                        <td class="text-center align-top">
                            <span class="badge badge-light border">{{ $item->{$countField} }}</span>
                        </td>
                        <td class="text-nowrap text-muted align-top">
                            {{ optional($item->updated_at)->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-right text-nowrap align-top">
                            <a href="{{ route($showRouteName, $item) }}" class="btn btn-xs btn-light border">Ver</a>
                            <a href="{{ route($editRouteName, $item) }}" class="btn btn-xs btn-light border">Editar</a>
                            <button
                                type="button"
                                class="btn btn-xs btn-outline-danger js-delete-button"
                                data-action="{{ route($destroyRouteName, $item) }}"
                                data-message="Vas a eliminar {{ $deleteMessagePrefix }} &quot;{{ e($item->nombre) }}&quot;."
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
        <div class="card-footer bg-white">
            {{ $items->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>

@include('admin.partials.delete-modal')

@include('admin.catalogos-simple._index-scripts')
