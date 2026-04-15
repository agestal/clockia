<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Encuesta de satisfaccion - {{ $encuesta->negocio?->nombre }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f6f8;
            color: #333;
            min-height: 100vh;
            padding: 24px 16px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            padding: 28px 32px;
            color: #fff;
        }
        .header h1 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .header p {
            font-size: 0.88rem;
            opacity: 0.85;
        }
        .body {
            padding: 28px 32px;
        }
        .intro {
            text-align: center;
            margin-bottom: 28px;
            color: #555;
            font-size: 0.92rem;
            line-height: 1.6;
        }
        .item {
            margin-bottom: 28px;
            padding-bottom: 24px;
            border-bottom: 1px solid #f0f0f0;
        }
        .item:last-of-type {
            border-bottom: none;
            margin-bottom: 12px;
        }
        .item-label {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 4px;
        }
        .item-desc {
            font-size: 0.82rem;
            color: #888;
            margin-bottom: 12px;
        }
        .rating-row {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .rating-row input[type="radio"] {
            display: none;
        }
        .rating-row label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            color: #666;
            background: #fafafa;
            user-select: none;
        }
        .rating-row label:hover {
            border-color: #007bff;
            color: #007bff;
            background: #f0f7ff;
        }
        .rating-row input[type="radio"]:checked + label {
            background: #007bff;
            border-color: #007bff;
            color: #fff;
        }
        .rating-hints {
            display: flex;
            justify-content: space-between;
            margin-top: 6px;
            font-size: 0.72rem;
            color: #aaa;
        }
        .comment-section {
            margin-top: 8px;
        }
        .comment-section label {
            display: block;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }
        .comment-section textarea {
            width: 100%;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px;
            font-size: 0.9rem;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
            transition: border-color 0.15s ease;
        }
        .comment-section textarea:focus {
            outline: none;
            border-color: #007bff;
        }
        .submit-btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.15s ease;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .footer {
            text-align: center;
            padding: 16px 32px;
            background: #f8f9fa;
            font-size: 0.75rem;
            color: #aaa;
        }
        .error-msg {
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 4px;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.88rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="POST" action="{{ url('/encuesta/' . $encuesta->token) }}">
            @csrf

            <div class="card">
                <div class="header">
                    <h1>Tu opinion nos importa</h1>
                    <p>{{ $encuesta->negocio?->nombre }}</p>
                </div>

                <div class="body">
                    <div class="intro">
                        Hola{{ $encuesta->reserva?->nombre_responsable ? ', '.$encuesta->reserva->nombre_responsable : '' }}.
                        Nos encantaria conocer tu experiencia con
                        <strong>{{ $encuesta->reserva?->servicio?->nombre }}</strong>
                        del {{ optional($encuesta->reserva?->fecha)?->locale('es')->translatedFormat('j \d\e F \d\e Y') }}.
                    </div>

                    @if($errors->any())
                        <div class="alert-danger">
                            Por favor, valora todos los aspectos antes de enviar.
                        </div>
                    @endif

                    @foreach($items as $item)
                        <div class="item">
                            <div class="item-label">{{ $item->etiqueta }}</div>
                            @if($item->descripcion)
                                <div class="item-desc">{{ $item->descripcion }}</div>
                            @endif

                            <div class="rating-row">
                                @for($i = 0; $i <= 10; $i++)
                                    <input
                                        type="radio"
                                        name="item_{{ $item->id }}"
                                        id="item_{{ $item->id }}_{{ $i }}"
                                        value="{{ $i }}"
                                        @checked(old("item_{$item->id}") == (string) $i)
                                    >
                                    <label for="item_{{ $item->id }}_{{ $i }}">{{ $i }}</label>
                                @endfor
                            </div>
                            <div class="rating-hints">
                                <span>Muy mal</span>
                                <span>Excelente</span>
                            </div>

                            @error("item_{$item->id}")
                                <div class="error-msg">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach

                    <div class="comment-section">
                        <label for="comentario_general">Comentario adicional (opcional)</label>
                        <textarea
                            name="comentario_general"
                            id="comentario_general"
                            placeholder="Cuentanos cualquier cosa que nos ayude a mejorar..."
                        >{{ old('comentario_general') }}</textarea>
                    </div>

                    <button type="submit" class="submit-btn">Enviar valoracion</button>
                </div>

                <div class="footer">
                    {{ $encuesta->negocio?->nombre }} &middot; Encuesta de satisfaccion
                </div>
            </div>
        </form>
    </div>
</body>
</html>
