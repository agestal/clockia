@php
    $preguntas = collect($survey['preguntas'] ?? [])->values();
    $scaleMin = (int) ($survey['escala_min'] ?? 0);
    $scaleMax = (int) ($survey['escala_max'] ?? 10);
    $permiteComentario = (bool) ($survey['permite_comentario_final'] ?? true);
    $totalSteps = $preguntas->count() + ($permiteComentario ? 1 : 0);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Encuesta de satisfaccion - {{ $encuesta->negocio?->nombre }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f4f0e9;
            color: #2c241d;
            min-height: 100vh;
            padding: 24px 16px;
        }
        .container {
            max-width: 680px;
            margin: 0 auto;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .header {
            background: #7b3f00;
            color: #fff;
            padding: 28px 32px;
        }
        .header h1 {
            margin: 0 0 8px;
            font-size: 1.45rem;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
            line-height: 1.5;
            font-size: 0.95rem;
        }
        .body {
            padding: 28px 32px 32px;
        }
        .intro {
            margin: 0 0 20px;
            color: #5c5045;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        .progress {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
        }
        .progress-bar {
            flex: 1;
            height: 8px;
            background: #ece4db;
            border-radius: 999px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #7b3f00;
            width: 0;
            transition: width 0.2s ease;
        }
        .progress-label {
            min-width: 92px;
            text-align: right;
            color: #7b6b5a;
            font-size: 0.82rem;
        }
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
        .step-title {
            margin: 0 0 8px;
            font-size: 1.15rem;
        }
        .step-description {
            margin: 0 0 24px;
            color: #7b6b5a;
            line-height: 1.55;
        }
        .rating-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(48px, 1fr));
            gap: 10px;
        }
        .rating-option {
            appearance: none;
            border: 1px solid #d9cbbd;
            background: #fff;
            color: #5c5045;
            border-radius: 8px;
            min-height: 52px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .rating-option:hover,
        .rating-option.is-selected {
            background: #7b3f00;
            border-color: #7b3f00;
            color: #fff;
        }
        .scale-hint {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            color: #8e8072;
            font-size: 0.78rem;
        }
        .comment-box {
            width: 100%;
            min-height: 130px;
            border: 1px solid #d9cbbd;
            border-radius: 8px;
            padding: 14px 16px;
            font: inherit;
            resize: vertical;
        }
        .comment-box:focus {
            outline: none;
            border-color: #7b3f00;
            box-shadow: 0 0 0 3px rgba(123, 63, 0, 0.08);
        }
        .actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 28px;
        }
        .btn {
            border: 0;
            border-radius: 8px;
            min-height: 44px;
            padding: 0 18px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-secondary {
            background: #efe6dc;
            color: #5c5045;
        }
        .btn-primary {
            background: #7b3f00;
            color: #fff;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 0.9rem;
        }
        .footer {
            padding: 16px 32px;
            background: #faf7f3;
            color: #8e8072;
            text-align: center;
            font-size: 0.78rem;
        }
        @media (max-width: 640px) {
            .header, .body, .footer {
                padding-left: 20px;
                padding-right: 20px;
            }
            .actions {
                flex-direction: column-reverse;
            }
            .actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="POST" action="{{ route('encuesta.submit', $encuesta->token) }}" class="card" data-survey-form novalidate>
            @csrf

            <div class="header">
                <h1>{{ $survey['titulo_publico'] ?? 'Comparte tu valoracion' }}</h1>
                <p>{{ $encuesta->negocio?->nombre }}</p>
            </div>

            <div class="body">
                <p class="intro">
                    Hola{{ $encuesta->reserva?->nombreResponsableEfectivo() ? ', '.$encuesta->reserva->nombreResponsableEfectivo() : '' }}.
                    {{ $survey['intro_publica'] ?? 'Nos ayuda mucho saber como ha ido la experiencia.' }}
                    @if($encuesta->reserva?->servicio)
                        Tu visita corresponde a <strong>{{ $encuesta->reserva->servicio->nombre }}</strong>
                        del {{ optional($encuesta->reserva->fecha)?->locale('es')->translatedFormat('j \d\e F \d\e Y') }}.
                    @endif
                </p>

                @if($errors->any())
                    <div class="alert-danger">
                        Revisa las respuestas antes de enviar la encuesta.
                    </div>
                @endif

                <div class="progress">
                    <div class="progress-bar"><div class="progress-fill" data-progress-fill></div></div>
                    <div class="progress-label" data-progress-label></div>
                </div>

                @foreach($preguntas as $index => $pregunta)
                    @php($fieldName = 'item_'.$pregunta['id'])
                    <section class="step @if($index === 0) active @endif" data-step data-step-index="{{ $index }}">
                        <h2 class="step-title">{{ $pregunta['etiqueta'] }}</h2>
                        @if(! empty($pregunta['descripcion']))
                            <p class="step-description">{{ $pregunta['descripcion'] }}</p>
                        @endif

                        <input type="hidden" name="{{ $fieldName }}" value="{{ old($fieldName) }}" data-rating-input>

                        <div class="rating-grid">
                            @for($value = $scaleMin; $value <= $scaleMax; $value++)
                                <button
                                    type="button"
                                    class="rating-option @if(old($fieldName) !== null && (int) old($fieldName) === $value) is-selected @endif"
                                    data-rating-value="{{ $value }}"
                                >{{ $value }}</button>
                            @endfor
                        </div>

                        <div class="scale-hint">
                            <span>{{ $scaleMin }}</span>
                            <span>{{ $scaleMax }}</span>
                        </div>

                        <div class="actions">
                            <button type="button" class="btn btn-secondary" data-prev @if($index === 0) style="visibility:hidden;" @endif>Anterior</button>
                            <button type="button" class="btn btn-primary" data-next>Continuar</button>
                        </div>
                    </section>
                @endforeach

                @if($permiteComentario)
                    <section class="step" data-step data-step-index="{{ $preguntas->count() }}">
                        <h2 class="step-title">Comentario final</h2>
                        <p class="step-description">Este campo es opcional.</p>
                        <textarea
                            name="comentario_general"
                            class="comment-box"
                            maxlength="5000"
                            placeholder="{{ $survey['comentario_placeholder'] ?? 'Si quieres, dejanos algun comentario adicional.' }}"
                        >{{ old('comentario_general') }}</textarea>

                        <div class="actions">
                            <button type="button" class="btn btn-secondary" data-prev>Anterior</button>
                            <button type="submit" class="btn btn-primary">Enviar encuesta</button>
                        </div>
                    </section>
                @else
                    <div class="step" data-step data-step-index="{{ $preguntas->count() }}">
                        <div class="actions">
                            <button type="submit" class="btn btn-primary">Enviar encuesta</button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="footer">
                {{ $encuesta->negocio?->nombre }}
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-survey-form]');

            if (!form) {
                return;
            }

            const steps = Array.from(form.querySelectorAll('[data-step]'));
            const progressFill = form.querySelector('[data-progress-fill]');
            const progressLabel = form.querySelector('[data-progress-label]');
            let currentStep = Math.max(0, steps.findIndex(step => {
                const input = step.querySelector('[data-rating-input]');
                return input && !input.value;
            }));

            if (currentStep === -1) {
                currentStep = 0;
            }

            const updateProgress = () => {
                const total = {{ max($totalSteps, 1) }};
                const current = Math.min(currentStep + 1, total);
                const percentage = total > 0 ? (current / total) * 100 : 0;
                progressFill.style.width = `${percentage}%`;
                progressLabel.textContent = `Paso ${current} de ${total}`;
            };

            const showStep = index => {
                currentStep = Math.min(Math.max(index, 0), steps.length - 1);
                steps.forEach((step, stepIndex) => step.classList.toggle('active', stepIndex === currentStep));
                updateProgress();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            steps.forEach((step, stepIndex) => {
                const input = step.querySelector('[data-rating-input]');
                const options = Array.from(step.querySelectorAll('[data-rating-value]'));
                const nextButton = step.querySelector('[data-next]');
                const prevButton = step.querySelector('[data-prev]');

                options.forEach(option => {
                    option.addEventListener('click', () => {
                        if (!input) {
                            return;
                        }

                        input.value = option.dataset.ratingValue || '';
                        options.forEach(candidate => candidate.classList.remove('is-selected'));
                        option.classList.add('is-selected');
                    });
                });

                nextButton?.addEventListener('click', () => {
                    if (input && !input.value) {
                        return;
                    }

                    showStep(stepIndex + 1);
                });

                prevButton?.addEventListener('click', () => showStep(stepIndex - 1));
            });

            updateProgress();
            showStep(currentStep);
        });
    </script>
</body>
</html>
