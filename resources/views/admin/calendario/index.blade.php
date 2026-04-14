@extends('layouts.app')

@section('title', 'Calendario de reservas')

@section('content_header_extra')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0" style="font-size:1.4rem;">Calendario de reservas</h1>
        </div>
    </div>
@stop

@section('content_body')
    <style>
        .cal-wrapper { max-width: 960px; margin: 0 auto; }
        .cal-topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
        .cal-topbar-left { display: flex; align-items: center; gap: 10px; }
        .cal-select { appearance: none; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 10px center; border: 1px solid #ddd; border-radius: 8px; padding: 7px 30px 7px 12px; font-size: 0.88rem; font-weight: 500; color: #333; cursor: pointer; }
        .cal-select:focus { outline: none; border-color: #007bff; box-shadow: 0 0 0 3px rgba(0,123,255,0.1); }
        .cal-nav { display: flex; align-items: center; gap: 6px; }
        .cal-nav-btn { width: 34px; height: 34px; border-radius: 8px; border: 1px solid #ddd; background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #555; transition: all 0.15s; }
        .cal-nav-btn:hover { background: #f0f0f0; border-color: #bbb; }
        .cal-month-label { font-size: 1rem; font-weight: 600; color: #333; min-width: 160px; text-align: center; text-transform: capitalize; }
        .cal-today-btn { border: 1px solid #ddd; background: #fff; border-radius: 8px; padding: 5px 14px; font-size: 0.78rem; color: #555; cursor: pointer; transition: all 0.15s; }
        .cal-today-btn:hover { background: #f5f5f5; border-color: #bbb; }

        /* ─── Summary bar ─── */
        .cal-summary { background: #fff; border: 1px solid #e0e0e0; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
        .cal-summary-item { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: #555; }
        .cal-summary-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .dot-free { background: #34c759; }
        .dot-partial { background: #ff9500; }
        .dot-full { background: #ff3b30; }
        .dot-closed { background: #e0e0e0; }

        /* ─── Calendar grid ─── */
        .cal-grid { background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; }
        .cal-header { display: grid; grid-template-columns: repeat(7, 1fr); background: #f8f9fa; border-bottom: 1px solid #e0e0e0; }
        .cal-header-cell { padding: 10px 4px; text-align: center; font-size: 0.72rem; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.04em; }
        .cal-body { display: grid; grid-template-columns: repeat(7, 1fr); }
        .cal-cell { min-height: 80px; border-right: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0; padding: 6px 8px; position: relative; cursor: pointer; transition: background 0.15s; }
        .cal-cell:nth-child(7n) { border-right: none; }
        .cal-cell:hover { background: #f8f9ff; }
        .cal-cell--other { opacity: 0.35; pointer-events: none; }
        .cal-cell--today { background: #f0f7ff; }
        .cal-cell--today .cal-day-num { color: #007bff; font-weight: 700; }
        .cal-day-num { font-size: 0.82rem; font-weight: 500; color: #333; margin-bottom: 4px; }
        .cal-day-indicator { display: flex; align-items: center; gap: 4px; margin-top: 2px; }
        .cal-day-bar { height: 4px; border-radius: 2px; flex: 1; }
        .bar-free { background: #34c759; }
        .bar-partial { background: #ff9500; }
        .bar-full { background: #ff3b30; }
        .bar-closed { background: #eee; }
        .cal-day-stats { font-size: 0.62rem; color: #999; margin-top: 3px; line-height: 1.3; }
        .cal-day-count { position: absolute; top: 5px; right: 7px; background: #eee; color: #666; font-size: 0.6rem; font-weight: 600; padding: 1px 5px; border-radius: 8px; }
        .cal-day-count--active { background: #007bff; color: #fff; }

        /* ─── Modal ─── */
        .cal-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: all 0.2s; }
        .cal-modal-overlay.active { opacity: 1; visibility: visible; }
        .cal-modal { background: #fff; border-radius: 14px; width: 90%; max-width: 600px; max-height: 80vh; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,0.2); transform: translateY(10px); transition: transform 0.2s; }
        .cal-modal-overlay.active .cal-modal { transform: translateY(0); }
        .cal-modal-header { padding: 16px 20px; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between; }
        .cal-modal-header h3 { margin: 0; font-size: 1rem; font-weight: 600; color: #333; text-transform: capitalize; }
        .cal-modal-close { width: 30px; height: 30px; border-radius: 50%; border: none; background: #f0f0f0; color: #666; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; }
        .cal-modal-close:hover { background: #e0e0e0; }
        .cal-modal-body { flex: 1; overflow-y: auto; padding: 12px 0; }
        .cal-modal-empty { text-align: center; padding: 30px; color: #bbb; font-size: 0.88rem; }

        .reserva-row { display: flex; align-items: flex-start; gap: 12px; padding: 10px 20px; border-bottom: 1px solid #f5f5f5; transition: background 0.1s; }
        .reserva-row:last-child { border-bottom: none; }
        .reserva-row:hover { background: #fafbfc; }
        .reserva-time { font-size: 0.82rem; font-weight: 600; color: #333; white-space: nowrap; min-width: 80px; }
        .reserva-info { flex: 1; min-width: 0; }
        .reserva-title { font-size: 0.85rem; font-weight: 500; color: #333; }
        .reserva-meta { font-size: 0.75rem; color: #888; margin-top: 2px; }
        .reserva-status { font-size: 0.65rem; font-weight: 600; padding: 2px 7px; border-radius: 4px; white-space: nowrap; }
        .status-confirmada { background: #d4edda; color: #155724; }
        .status-pendiente { background: #fff3cd; color: #856404; }
        .status-completada { background: #e2e3e5; color: #383d41; }

        /* ─── Loading ─── */
        .cal-loading { display: flex; align-items: center; justify-content: center; padding: 60px; color: #bbb; }
    </style>

    <div class="cal-wrapper">
        <div class="cal-topbar">
            <div class="cal-topbar-left">
                <select id="negocio_id" class="cal-select">
                    @foreach($negocios as $negocio)
                        <option value="{{ $negocio->id }}">{{ $negocio->nombre }}</option>
                    @endforeach
                </select>
                <button class="cal-today-btn" id="btn-today">Hoy</button>
            </div>
            <div class="cal-nav">
                <button class="cal-nav-btn" id="btn-prev"><i class="fas fa-chevron-left" style="font-size:0.7rem;"></i></button>
                <span class="cal-month-label" id="month-label"></span>
                <button class="cal-nav-btn" id="btn-next"><i class="fas fa-chevron-right" style="font-size:0.7rem;"></i></button>
            </div>
        </div>

        <div class="cal-summary" id="cal-summary">
            <div class="cal-summary-item"><span class="cal-summary-dot dot-free"></span> Libre</div>
            <div class="cal-summary-item"><span class="cal-summary-dot dot-partial"></span> Parcial</div>
            <div class="cal-summary-item"><span class="cal-summary-dot dot-full"></span> Completo</div>
            <div class="cal-summary-item"><span class="cal-summary-dot dot-closed"></span> Cerrado</div>
            <div class="cal-summary-item" id="resource-label" style="margin-left:auto;font-weight:600;color:#333;"></div>
        </div>

        <div class="cal-grid">
            <div class="cal-header">
                <div class="cal-header-cell">Lun</div>
                <div class="cal-header-cell">Mar</div>
                <div class="cal-header-cell">Mié</div>
                <div class="cal-header-cell">Jue</div>
                <div class="cal-header-cell">Vie</div>
                <div class="cal-header-cell">Sáb</div>
                <div class="cal-header-cell">Dom</div>
            </div>
            <div class="cal-body" id="cal-body">
                <div class="cal-loading"><i class="fas fa-circle-notch fa-spin mr-2"></i> Cargando...</div>
            </div>
        </div>
    </div>

    {{-- Day detail modal --}}
    <div class="cal-modal-overlay" id="modal-overlay">
        <div class="cal-modal">
            <div class="cal-modal-header">
                <h3 id="modal-title"></h3>
                <button class="cal-modal-close" id="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="cal-modal-body" id="modal-body"></div>
        </div>
    </div>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const negocioEl = document.getElementById('negocio_id');
            const bodyEl = document.getElementById('cal-body');
            const labelEl = document.getElementById('month-label');
            const resourceLabel = document.getElementById('resource-label');
            const overlay = document.getElementById('modal-overlay');
            const modalTitle = document.getElementById('modal-title');
            const modalBody = document.getElementById('modal-body');

            const today = new Date();
            let year = today.getFullYear();
            let month = today.getMonth() + 1;
            const todayStr = today.toISOString().split('T')[0];

            async function loadCalendar() {
                bodyEl.innerHTML = '<div class="cal-loading"><i class="fas fa-circle-notch fa-spin mr-2"></i> Cargando...</div>';

                try {
                    const res = await fetch(`{{ route('admin.calendario.data') }}?negocio_id=${negocioEl.value}&year=${year}&month=${month}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();

                    labelEl.textContent = data.month_name;
                    resourceLabel.textContent = `${data.total_resources} ${data.resource_label}`;

                    renderGrid(data);
                } catch (err) {
                    bodyEl.innerHTML = '<div class="cal-loading" style="color:#c00;">Error al cargar</div>';
                }
            }

            function renderGrid(data) {
                const firstDay = new Date(year, month - 1, 1);
                let startDow = firstDay.getDay(); // 0=sun
                startDow = startDow === 0 ? 6 : startDow - 1; // convert to 0=mon

                const daysInMonth = new Date(year, month, 0).getDate();
                const prevMonthDays = new Date(year, month - 1, 0).getDate();

                let html = '';

                // Previous month padding
                for (let i = startDow - 1; i >= 0; i--) {
                    html += `<div class="cal-cell cal-cell--other"><span class="cal-day-num">${prevMonthDays - i}</span></div>`;
                }

                // Current month
                for (let d = 1; d <= daysInMonth; d++) {
                    const dateStr = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                    const day = data.days[dateStr];
                    const isToday = dateStr === todayStr;

                    if (!day) {
                        html += `<div class="cal-cell"><span class="cal-day-num">${d}</span></div>`;
                        continue;
                    }

                    const barClass = { free: 'bar-free', partial: 'bar-partial', full: 'bar-full', closed: 'bar-closed' }[day.status] || 'bar-closed';
                    const todayClass = isToday ? ' cal-cell--today' : '';

                    let statsHtml = '';
                    if (day.is_operational && day.total_slots > 0) {
                        statsHtml = `<div class="cal-day-stats">${day.free_slots} libre${day.free_slots !== 1 ? 's' : ''} / ${day.total_slots}</div>`;
                    } else if (!day.is_operational) {
                        statsHtml = '<div class="cal-day-stats">Cerrado</div>';
                    }

                    const countHtml = day.reservas_count > 0 ? `<span class="cal-day-count cal-day-count--active">${day.reservas_count}</span>` : '';

                    html += `<div class="cal-cell${todayClass}" data-date="${dateStr}" data-operational="${day.is_operational}">
                        <span class="cal-day-num">${d}</span>
                        ${countHtml}
                        <div class="cal-day-indicator"><div class="cal-day-bar ${barClass}"></div></div>
                        ${statsHtml}
                    </div>`;
                }

                // Next month padding
                const totalCells = startDow + daysInMonth;
                const remaining = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
                for (let i = 1; i <= remaining; i++) {
                    html += `<div class="cal-cell cal-cell--other"><span class="cal-day-num">${i}</span></div>`;
                }

                bodyEl.innerHTML = html;

                // Click handlers
                bodyEl.querySelectorAll('.cal-cell:not(.cal-cell--other)').forEach(cell => {
                    cell.addEventListener('click', () => openDayDetail(cell.dataset.date));
                });
            }

            async function openDayDetail(date) {
                modalTitle.textContent = 'Cargando...';
                modalBody.innerHTML = '<div class="cal-loading"><i class="fas fa-circle-notch fa-spin"></i></div>';
                overlay.classList.add('active');

                try {
                    const res = await fetch(`{{ route('admin.calendario.day') }}?negocio_id=${negocioEl.value}&date=${date}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();

                    modalTitle.textContent = data.date_human;

                    if (data.total === 0) {
                        modalBody.innerHTML = '<div class="cal-modal-empty">No hay reservas este día</div>';
                        return;
                    }

                    let html = '';
                    data.reservas.forEach(r => {
                        const statusClass = {
                            'Confirmada': 'status-confirmada',
                            'Pendiente': 'status-pendiente',
                            'Completada': 'status-completada'
                        }[r.estado] || 'status-pendiente';

                        const personasText = r.personas ? `${r.personas} pers.` : '';
                        const recursoText = r.recurso || '';
                        const metaParts = [recursoText, personasText, r.telefono].filter(Boolean);

                        html += `<div class="reserva-row">
                            <div class="reserva-time">${r.hora_inicio} - ${r.hora_fin}</div>
                            <div class="reserva-info">
                                <div class="reserva-title">${r.servicio || '—'} · ${r.cliente || 'Sin cliente'}</div>
                                <div class="reserva-meta">${metaParts.join(' · ')}</div>
                                ${r.notas ? `<div class="reserva-meta" style="color:#aa8800;margin-top:2px;">📝 ${r.notas}</div>` : ''}
                            </div>
                            <span class="reserva-status ${statusClass}">${r.estado}</span>
                        </div>`;
                    });

                    modalBody.innerHTML = html;
                } catch {
                    modalBody.innerHTML = '<div class="cal-modal-empty" style="color:#c00;">Error al cargar detalle</div>';
                }
            }

            // Close modal
            document.getElementById('modal-close').addEventListener('click', () => overlay.classList.remove('active'));
            overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.classList.remove('active'); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') overlay.classList.remove('active'); });

            // Navigation
            document.getElementById('btn-prev').addEventListener('click', () => { month--; if (month < 1) { month = 12; year--; } loadCalendar(); });
            document.getElementById('btn-next').addEventListener('click', () => { month++; if (month > 12) { month = 1; year++; } loadCalendar(); });
            document.getElementById('btn-today').addEventListener('click', () => { year = today.getFullYear(); month = today.getMonth() + 1; loadCalendar(); });
            negocioEl.addEventListener('change', loadCalendar);

            // Initial load
            loadCalendar();
        });
    </script>
@endpush
