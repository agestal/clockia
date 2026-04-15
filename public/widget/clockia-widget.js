(function(e){Object.defineProperties(e,{__esModule:{value:!0},[Symbol.toStringTag]:{value:`Module`}});function t({apiBase:e,businessId:t,widgetKey:n}){let r=e.replace(/\/$/,``)+`/businesses/${t}`;async function i(e,{method:t=`GET`,body:i=null,query:a=null}={}){let o=r+e;if(a){let e=new URLSearchParams;Object.entries(a).forEach(([t,n])=>{n!=null&&n!==``&&e.append(t,n)});let t=e.toString();t&&(o+=(o.includes(`?`)?`&`:`?`)+t)}let s=await fetch(o,{method:t,headers:{Accept:`application/json`,"Content-Type":`application/json`,"X-Widget-Key":n},body:i?JSON.stringify(i):void 0,mode:`cors`,credentials:`omit`}),c=(s.headers.get(`content-type`)||``).includes(`application/json`)?await s.json().catch(()=>({})):{};if(!s.ok){let e=Error(c.error||c.message||`Error al contactar con el servidor.`);throw e.status=s.status,e.payload=c,e}return c}return{config:()=>i(`/config`),calendar:(e,t,n={})=>i(`/availability/calendar`,{query:{year:e,month:t,...n}}),date:(e,t=null)=>i(`/availability/date`,{query:{date:e,participants:t}}),check:e=>i(`/availability/check`,{method:`POST`,body:e}),book:e=>i(`/bookings`,{method:`POST`,body:e})}}function n(e){return`
:host {
    --ck-primary: ${e.primary_color};
    --ck-secondary: ${e.secondary_color};
    --ck-text: ${e.text_color};
    --ck-bg: ${e.background_color};
    --ck-font: ${e.font_family};
    --ck-font-size: ${e.font_size_base};
    --ck-radius: ${e.border_radius};
    --ck-border: #e5e5e5;
    --ck-muted: #6b7280;
    --ck-danger: #b91c1c;
    --ck-success: #15803d;
    display: block;
    font-family: var(--ck-font);
    font-size: var(--ck-font-size);
    color: var(--ck-text);
    box-sizing: border-box;
}
*, *::before, *::after { box-sizing: border-box; }

.ck-root {
    background: var(--ck-bg);
    border: 1px solid var(--ck-border);
    border-radius: var(--ck-radius);
    padding: 20px;
    max-width: 720px;
    margin: 0 auto;
}
.ck-header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 16px;
    gap: 12px;
}
.ck-header h2 {
    margin: 0;
    font-size: calc(var(--ck-font-size) * 1.35);
    font-weight: 600;
}
.ck-header .ck-business {
    color: var(--ck-muted);
    font-size: calc(var(--ck-font-size) * 0.9);
}

button.ck-btn, button.ck-btn-primary, button.ck-btn-ghost {
    font-family: inherit;
    font-size: inherit;
    border-radius: var(--ck-radius);
    padding: 10px 16px;
    border: 1px solid var(--ck-border);
    background: var(--ck-bg);
    color: var(--ck-text);
    cursor: pointer;
    transition: transform .05s ease, background .15s ease, border-color .15s ease;
}
button.ck-btn-primary {
    background: var(--ck-primary);
    color: #fff;
    border-color: var(--ck-primary);
    font-weight: 600;
}
button.ck-btn-primary:hover { filter: brightness(0.95); }
button.ck-btn-primary:disabled { opacity: 0.55; cursor: not-allowed; filter: none; }
button.ck-btn-ghost { background: transparent; border-color: transparent; color: var(--ck-muted); }
button.ck-btn-ghost:hover { color: var(--ck-text); }
button.ck-btn:hover { border-color: var(--ck-primary); }

.ck-calendar-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.ck-calendar-nav .ck-month-label {
    font-weight: 600;
    text-transform: capitalize;
}
.ck-calendar {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
}
.ck-calendar .ck-dow {
    text-align: center;
    font-size: calc(var(--ck-font-size) * 0.8);
    color: var(--ck-muted);
    padding: 4px 0;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.ck-day {
    aspect-ratio: 1 / 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: calc(var(--ck-radius) * 0.8);
    border: 1px solid var(--ck-border);
    background: var(--ck-bg);
    cursor: pointer;
    color: var(--ck-text);
    font-weight: 500;
    transition: all .12s ease;
    user-select: none;
}
.ck-day.ck-empty { visibility: hidden; }
.ck-day.ck-unavailable { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; border-color: transparent; }
.ck-day.ck-available:hover { border-color: var(--ck-primary); background: var(--ck-secondary); }
.ck-day.ck-selected { background: var(--ck-primary); color: #fff; border-color: var(--ck-primary); }
.ck-day.ck-today { outline: 2px solid var(--ck-primary); outline-offset: -2px; }

.ck-section { margin-top: 24px; }
.ck-section h3 { margin: 0 0 10px 0; font-size: calc(var(--ck-font-size) * 1.1); }

.ck-experience-list { display: flex; flex-direction: column; gap: 10px; }
.ck-experience {
    border: 1px solid var(--ck-border);
    border-radius: var(--ck-radius);
    padding: 14px;
    cursor: pointer;
    transition: border-color .15s ease, background .15s ease;
}
.ck-experience:hover { border-color: var(--ck-primary); background: var(--ck-secondary); }
.ck-experience.ck-selected { border-color: var(--ck-primary); background: var(--ck-secondary); }
.ck-experience .ck-exp-head { display: flex; justify-content: space-between; gap: 12px; align-items: baseline; }
.ck-experience .ck-exp-name { font-weight: 600; font-size: calc(var(--ck-font-size) * 1.05); }
.ck-experience .ck-exp-price { color: var(--ck-primary); font-weight: 600; }
.ck-experience .ck-exp-meta { color: var(--ck-muted); font-size: calc(var(--ck-font-size) * 0.9); margin-top: 4px; }
.ck-experience .ck-exp-desc { color: var(--ck-text); font-size: calc(var(--ck-font-size) * 0.95); margin-top: 8px; }

.ck-timeslots { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.ck-timeslot {
    padding: 8px 14px;
    border-radius: calc(var(--ck-radius) * 0.8);
    border: 1px solid var(--ck-border);
    background: var(--ck-bg);
    cursor: pointer;
    font-weight: 500;
}
.ck-timeslot:hover { border-color: var(--ck-primary); }
.ck-timeslot.ck-selected { background: var(--ck-primary); color: #fff; border-color: var(--ck-primary); }

.ck-form { display: grid; gap: 12px; margin-top: 12px; }
.ck-form .ck-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
@media (max-width: 520px) { .ck-form .ck-row { grid-template-columns: 1fr; } }
.ck-form label { display: block; font-size: calc(var(--ck-font-size) * 0.9); font-weight: 500; margin-bottom: 4px; color: var(--ck-text); }
.ck-form input, .ck-form textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--ck-border);
    border-radius: calc(var(--ck-radius) * 0.8);
    background: var(--ck-bg);
    color: var(--ck-text);
    font-family: inherit;
    font-size: inherit;
}
.ck-form input:focus, .ck-form textarea:focus { outline: 2px solid var(--ck-primary); outline-offset: 1px; border-color: var(--ck-primary); }
.ck-form textarea { min-height: 80px; resize: vertical; }
.ck-form .ck-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 4px; }

.ck-summary {
    background: var(--ck-secondary);
    border-radius: var(--ck-radius);
    padding: 12px 14px;
    margin: 12px 0;
    font-size: calc(var(--ck-font-size) * 0.95);
}
.ck-summary .ck-summary-row { display: flex; justify-content: space-between; gap: 12px; padding: 2px 0; }
.ck-summary .ck-summary-total { font-weight: 700; margin-top: 6px; padding-top: 6px; border-top: 1px solid rgba(0,0,0,0.1); }

.ck-error { color: var(--ck-danger); background: #fee2e2; padding: 10px 12px; border-radius: calc(var(--ck-radius) * 0.8); font-size: calc(var(--ck-font-size) * 0.95); }
.ck-success { color: var(--ck-success); background: #dcfce7; padding: 10px 12px; border-radius: calc(var(--ck-radius) * 0.8); font-size: calc(var(--ck-font-size) * 0.95); }
.ck-muted { color: var(--ck-muted); font-size: calc(var(--ck-font-size) * 0.9); }

.ck-loader {
    display: inline-block;
    width: 16px; height: 16px;
    border: 2px solid var(--ck-border);
    border-top-color: var(--ck-primary);
    border-radius: 50%;
    animation: ck-spin 0.8s linear infinite;
    vertical-align: -3px;
    margin-right: 8px;
}
@keyframes ck-spin { to { transform: rotate(360deg); } }

.ck-back { margin-right: auto; }
`}var r=[`Lun`,`Mar`,`Mié`,`Jue`,`Vie`,`Sáb`,`Dom`],i=[`Enero`,`Febrero`,`Marzo`,`Abril`,`Mayo`,`Junio`,`Julio`,`Agosto`,`Septiembre`,`Octubre`,`Noviembre`,`Diciembre`];function a(e){return e<10?`0`+e:String(e)}function o(e,t,n){return`${e}-${a(t)}-${a(n)}`}function s(e){let[t,n,r]=e.split(`-`).map(Number);return`${r} de ${i[n-1].toLowerCase()} de ${t}`}function c(e,t=`EUR`){let n=typeof e==`number`?e:parseFloat(e);if(!isFinite(n))return``;try{return new Intl.NumberFormat(`es-ES`,{style:`currency`,currency:t}).format(n)}catch{return n.toFixed(2)+` `+t}}function l(e,t={},n=[]){let r=document.createElement(e);return Object.entries(t||{}).forEach(([e,t])=>{t==null||t===!1||(e===`class`?r.className=t:e===`style`&&typeof t==`object`?Object.assign(r.style,t):e.startsWith(`on`)&&typeof t==`function`?r.addEventListener(e.slice(2).toLowerCase(),t):e===`html`?r.innerHTML=t:r.setAttribute(e,t))}),(Array.isArray(n)?n:[n]).forEach(e=>{e==null||e===!1||r.appendChild(typeof e==`string`?document.createTextNode(e):e)}),r}function u(e){for(;e.firstChild;)e.removeChild(e.firstChild)}function d({container:e,year:t,month:n,days:a,selectedDate:s,onPrev:c,onNext:d,onSelect:f,todayIso:p}){u(e);let m=l(`div`,{class:`ck-calendar-nav`},[l(`button`,{class:`ck-btn`,type:`button`,onclick:c,"aria-label":`Mes anterior`},`‹`),l(`div`,{class:`ck-month-label`},`${i[n-1]} ${t}`),l(`button`,{class:`ck-btn`,type:`button`,onclick:d,"aria-label":`Mes siguiente`},`›`)]);e.appendChild(m);let h=l(`div`,{class:`ck-calendar`,role:`grid`});r.forEach(e=>h.appendChild(l(`div`,{class:`ck-dow`},e)));let g=(new Date(t,n-1,1).getDay()+6)%7,_=new Date(t,n,0).getDate();for(let e=0;e<g;e++)h.appendChild(l(`div`,{class:`ck-day ck-empty`}));let v=Object.fromEntries(a.map(e=>[e.date,e]));for(let e=1;e<=_;e++){let r=o(t,n,e),i=v[r]||{available:!1,is_past:!1},a=[`ck-day`];i.is_past||!i.available?a.push(`ck-unavailable`):a.push(`ck-available`),r===s&&a.push(`ck-selected`),r===p&&a.push(`ck-today`);let c={class:a.join(` `),"data-date":r,role:`gridcell`};i.available&&!i.is_past&&(c.onclick=()=>f(r),c.tabindex=`0`),h.appendChild(l(`div`,c,String(e)))}e.appendChild(h)}function f({container:e,services:t,selectedServiceId:n,selectedTime:r,onSelectService:i,onSelectTime:a}){if(u(e),!t.length){e.appendChild(l(`div`,{class:`ck-muted`},`No hay experiencias disponibles para este día.`));return}let o=l(`div`,{class:`ck-experience-list`});t.forEach(e=>{let t=l(`div`,{class:`ck-experience`+(e.id===n?` ck-selected`:``),onclick:t=>{t.target.closest(`.ck-timeslot`)||i(e.id)}}),s=l(`div`,{class:`ck-exp-head`},[l(`div`,{class:`ck-exp-name`},e.name||`Experiencia`),l(`div`,{class:`ck-exp-price`},e.price?c(e.price,e.currency):``)]);t.appendChild(s);let u=[];if(e.duration_minutes&&u.push(`${e.duration_minutes} min`),e.min_participants&&u.push(`Mín ${e.min_participants} pers.`),e.max_participants&&u.push(`Máx ${e.max_participants} pers.`),u.length&&t.appendChild(l(`div`,{class:`ck-exp-meta`},u.join(` · `))),e.description&&t.appendChild(l(`div`,{class:`ck-exp-desc`},e.description)),e.id===n&&e.requires_timeslot&&e.timeslots&&e.timeslots.length){let n=l(`div`,{class:`ck-timeslots`});e.timeslots.forEach(e=>{let t=l(`button`,{type:`button`,class:`ck-timeslot`+(r===e.time?` ck-selected`:``),onclick:t=>{t.stopPropagation(),a(e)}},e.time);n.appendChild(t)}),t.appendChild(n)}else e.id===n&&!e.requires_timeslot&&t.appendChild(l(`div`,{class:`ck-muted`,style:{marginTop:`8px`}},`Este servicio no requiere hora concreta.`));o.appendChild(t)}),e.appendChild(o)}function p({container:e,service:t,date:n,time:r,participants:i,pricing:a,onBack:o,onSubmit:d,submitting:f,errorMessage:p}){u(e);let m=l(`div`);m.appendChild(l(`div`,{class:`ck-summary`},[l(`div`,{class:`ck-summary-row`},[l(`span`,{},`Experiencia`),l(`strong`,{},t?.name||``)]),l(`div`,{class:`ck-summary-row`},[l(`span`,{},`Fecha`),l(`strong`,{},s(n))]),r?l(`div`,{class:`ck-summary-row`},[l(`span`,{},`Hora`),l(`strong`,{},r)]):null,l(`div`,{class:`ck-summary-row`},[l(`span`,{},`Participantes`),l(`strong`,{},String(i))]),a?l(`div`,{class:`ck-summary-row ck-summary-total`},[l(`span`,{},`Total estimado`),l(`strong`,{},c(a.total_price,a.currency||`EUR`))]):null]));let h=l(`form`,{class:`ck-form`,onsubmit:e=>{e.preventDefault();let t=new FormData(h);d({name:t.get(`name`)?.toString().trim()||``,last_name:t.get(`last_name`)?.toString().trim()||``,email:t.get(`email`)?.toString().trim()||``,phone:t.get(`phone`)?.toString().trim()||``,notes:t.get(`notes`)?.toString().trim()||``})}}),g=l(`div`,{class:`ck-row`},[l(`div`,{},[l(`label`,{for:`ck-name`},`Nombre *`),l(`input`,{id:`ck-name`,name:`name`,type:`text`,required:`required`,autocomplete:`given-name`})]),l(`div`,{},[l(`label`,{for:`ck-lastname`},`Apellidos`),l(`input`,{id:`ck-lastname`,name:`last_name`,type:`text`,autocomplete:`family-name`})])]),_=l(`div`,{class:`ck-row`},[l(`div`,{},[l(`label`,{for:`ck-email`},`Email`),l(`input`,{id:`ck-email`,name:`email`,type:`email`,autocomplete:`email`})]),l(`div`,{},[l(`label`,{for:`ck-phone`},`Teléfono *`),l(`input`,{id:`ck-phone`,name:`phone`,type:`tel`,required:`required`,autocomplete:`tel`})])]),v=l(`div`,{},[l(`label`,{for:`ck-notes`},`Observaciones`),l(`textarea`,{id:`ck-notes`,name:`notes`,rows:`3`,placeholder:`Alergias, niños, necesidades especiales...`})]);h.appendChild(g),h.appendChild(_),h.appendChild(v),p&&h.appendChild(l(`div`,{class:`ck-error`},p));let y=l(`div`,{class:`ck-actions`},[l(`button`,{type:`button`,class:`ck-btn ck-back`,onclick:o},`‹ Atrás`),l(`button`,{type:`submit`,class:`ck-btn-primary`,disabled:f?`disabled`:null},f?`Reservando…`:`Confirmar reserva`)]);h.appendChild(y),m.appendChild(h),e.appendChild(m)}var m=`/api/widget`,h=[`primary-color`,`secondary-color`,`text-color`,`background-color`,`font-family`,`font-size-base`,`border-radius`,`locale`],g=class extends HTMLElement{static get observedAttributes(){return[`business-id`,`widget-key`,`api-base`,...h]}constructor(){super(),this.attachShadow({mode:`open`}),this.state=this.initialState()}initialState(){let e=new Date;return{loading:!0,error:null,theme:null,business:null,api:null,year:e.getFullYear(),month:e.getMonth()+1,days:[],selectedDate:null,services:[],loadingDate:!1,selectedServiceId:null,selectedTime:null,selectedSlotKey:null,participants:2,pricing:null,submitting:!1,bookingError:null,confirmation:null,view:`calendar`}}connectedCallback(){this.init()}attributeChangedCallback(){this.shadowRoot.querySelector(`.ck-root`)&&this.init()}async init(){let e=this.getAttribute(`business-id`),n=this.getAttribute(`widget-key`),r=this.getAttribute(`api-base`)||m;if(!e||!n){this.renderFatal(`Faltan atributos business-id y/o widget-key en <clockia-widget>.`);return}let i=t({apiBase:r,businessId:e,widgetKey:n});this.state.api=i,this.state.loading=!0,this.state.error=null,this.renderRoot();try{let e=await i.config(),t=this.applyAttributeOverrides(e.widget);this.state.theme=t,this.state.business=e.business,this.state.loading=!1,this.renderRoot(),await this.loadMonth(this.state.year,this.state.month)}catch(e){this.state.loading=!1,this.state.error=e.message||`No se pudo cargar el widget.`,this.renderRoot()}}applyAttributeOverrides(e){let t={"primary-color":`primary_color`,"secondary-color":`secondary_color`,"text-color":`text_color`,"background-color":`background_color`,"font-family":`font_family`,"font-size-base":`font_size_base`,"border-radius":`border_radius`,locale:`locale`},n={...e};return h.forEach(e=>{let r=this.getAttribute(e);r!==null&&r!==``&&(n[t[e]]=r)}),n}async loadMonth(e,t){this.state.loading=!0,this.state.error=null,this.renderRoot();try{let n=await this.state.api.calendar(e,t);this.state.year=n.year,this.state.month=n.month,this.state.days=n.days,this.state.loading=!1,this.renderRoot()}catch(e){this.state.loading=!1,this.state.error=e.message,this.renderRoot()}}async loadDate(e){this.state.selectedDate=e,this.state.services=[],this.state.selectedServiceId=null,this.state.selectedTime=null,this.state.selectedSlotKey=null,this.state.pricing=null,this.state.loadingDate=!0,this.state.view=`date`,this.renderRoot();try{let t=await this.state.api.date(e,this.state.participants);this.state.services=t.services||[],this.state.loadingDate=!1,this.renderRoot()}catch(e){this.state.loadingDate=!1,this.state.error=e.message,this.renderRoot()}}selectService(e){this.state.selectedServiceId=e,this.state.selectedTime=null,this.state.selectedSlotKey=null,this.state.pricing=null;let t=this.state.services.find(t=>t.id===e);t&&!t.requires_timeslot&&(this.state.selectedTime=null,this.state.selectedSlotKey=null),this.renderRoot()}async selectTime(e){this.state.selectedTime=e.time,this.state.selectedSlotKey=e.slot_key||null,this.renderRoot()}async goToForm(){let e=this.state.services.find(e=>e.id===this.state.selectedServiceId);if(e){if(e.min_participants&&this.state.participants<e.min_participants){this.state.error=`Este servicio requiere al menos ${e.min_participants} participantes.`,this.renderRoot();return}if(e.max_participants&&this.state.participants>e.max_participants){this.state.error=`Este servicio admite como máximo ${e.max_participants} participantes.`,this.renderRoot();return}if(e.requires_timeslot&&!this.state.selectedTime){this.state.error=`Selecciona una hora disponible.`,this.renderRoot();return}this.state.error=null,this.state.bookingError=null;try{let t=await this.state.api.check({service_id:e.id,date:this.state.selectedDate,time:this.state.selectedTime,participants:this.state.participants});if(!t.available){this.state.bookingError=t.error||`El hueco elegido ya no está disponible.`,this.renderRoot();return}this.state.pricing={total_price:t.summary?.total_price,currency:t.currency||`EUR`},!this.state.selectedSlotKey&&t.slot?.slot_key&&(this.state.selectedSlotKey=t.slot.slot_key),this.state.view=`form`,this.renderRoot()}catch(e){this.state.bookingError=e.message,this.renderRoot()}}}async submitBooking(e){this.state.submitting=!0,this.state.bookingError=null,this.renderRoot();try{let t=await this.state.api.book({service_id:this.state.selectedServiceId,date:this.state.selectedDate,time:this.state.selectedTime,slot_key:this.state.selectedSlotKey,participants:this.state.participants,customer:{name:e.name,last_name:e.last_name,email:e.email,phone:e.phone},notes:e.notes});this.state.submitting=!1,this.state.confirmation=t.booking,this.state.view=`done`,this.renderRoot()}catch(e){this.state.submitting=!1,this.state.bookingError=e.message||`No se pudo crear la reserva.`,this.renderRoot()}}reset(){let e=this.state.theme,t=this.state.business,n=this.state.api;this.state=this.initialState(),this.state.theme=e,this.state.business=t,this.state.api=n,this.loadMonth(this.state.year,this.state.month)}renderFatal(e){let t=document.createElement(`style`);t.textContent=`:host { display:block; font-family: system-ui; color:#b91c1c; padding:12px; border:1px solid #fecaca; border-radius:8px; background:#fef2f2; }`,u(this.shadowRoot),this.shadowRoot.appendChild(t);let n=document.createElement(`div`);n.textContent=e,this.shadowRoot.appendChild(n)}renderRoot(){if(!this.state.theme&&!this.state.loading&&!this.state.error)return;u(this.shadowRoot);let e=this.state.theme||{primary_color:`#7B3F00`,secondary_color:`#EAD7C5`,text_color:`#2B2B2B`,background_color:`#FFFFFF`,font_family:`Inter, system-ui, sans-serif`,font_size_base:`14px`,border_radius:`10px`},t=document.createElement(`style`);t.textContent=n(e),this.shadowRoot.appendChild(t);let r=l(`div`,{class:`ck-root`}),i=l(`div`,{class:`ck-header`},[l(`div`,{},[l(`h2`,{},`Reserva tu experiencia`),this.state.business?l(`div`,{class:`ck-business`},this.state.business.name):null])]);if(r.appendChild(i),this.state.loading){r.appendChild(l(`div`,{},[l(`span`,{class:`ck-loader`}),`Cargando…`])),this.shadowRoot.appendChild(r);return}if(this.state.error){r.appendChild(l(`div`,{class:`ck-error`},this.state.error));let e=l(`div`,{style:{marginTop:`12px`}},[l(`button`,{class:`ck-btn-primary`,type:`button`,onclick:()=>this.init()},`Reintentar`)]);r.appendChild(e),this.shadowRoot.appendChild(r);return}if(this.state.view===`done`&&this.state.confirmation){this.renderDone(r),this.shadowRoot.appendChild(r);return}let o=l(`div`),c=new Date,m=`${c.getFullYear()}-${a(c.getMonth()+1)}-${a(c.getDate())}`;if(d({container:o,year:this.state.year,month:this.state.month,days:this.state.days,selectedDate:this.state.selectedDate,todayIso:m,onPrev:()=>{let e=this.state.year,t=this.state.month-1;t<1&&(t=12,--e),this.loadMonth(e,t)},onNext:()=>{let e=this.state.year,t=this.state.month+1;t>12&&(t=1,e+=1),this.loadMonth(e,t)},onSelect:e=>this.loadDate(e)}),r.appendChild(o),this.state.view===`date`&&this.state.selectedDate){let e=l(`div`,{class:`ck-section`},[l(`h3`,{},`Experiencias disponibles – ${s(this.state.selectedDate)}`)]),t=l(`div`,{class:`ck-row`,style:{marginTop:`8px`,marginBottom:`8px`}},[l(`div`,{},[l(`label`,{for:`ck-parts`},`Participantes`),l(`input`,{id:`ck-parts`,type:`number`,min:`1`,value:String(this.state.participants),onchange:e=>{let t=parseInt(e.target.value,10);!isNaN(t)&&t>0&&(this.state.participants=t,this.loadDate(this.state.selectedDate))}})])]);if(e.appendChild(t),this.state.loadingDate)e.appendChild(l(`div`,{},[l(`span`,{class:`ck-loader`}),`Cargando experiencias…`]));else{let t=l(`div`);if(f({container:t,services:this.state.services,selectedServiceId:this.state.selectedServiceId,selectedTime:this.state.selectedTime,onSelectService:e=>this.selectService(e),onSelectTime:e=>this.selectTime(e)}),e.appendChild(t),this.state.bookingError&&e.appendChild(l(`div`,{class:`ck-error`,style:{marginTop:`12px`}},this.state.bookingError)),this.state.selectedServiceId){let t=this.state.services.find(e=>e.id===this.state.selectedServiceId),n=t&&(!t.requires_timeslot||this.state.selectedTime);e.appendChild(l(`div`,{class:`ck-actions`,style:{marginTop:`14px`}},[l(`button`,{class:`ck-btn-primary`,type:`button`,disabled:n?null:`disabled`,onclick:()=>this.goToForm()},`Continuar →`)]))}}r.appendChild(e)}if(this.state.view===`form`&&this.state.selectedServiceId){let e=this.state.services.find(e=>e.id===this.state.selectedServiceId),t=l(`div`,{class:`ck-section`});t.appendChild(l(`h3`,{},`Confirma tu reserva`)),p({container:t,service:e,date:this.state.selectedDate,time:this.state.selectedTime,participants:this.state.participants,pricing:this.state.pricing,submitting:this.state.submitting,errorMessage:this.state.bookingError,onBack:()=>{this.state.view=`date`,this.state.bookingError=null,this.renderRoot()},onSubmit:e=>this.submitBooking(e)}),r.appendChild(t)}this.shadowRoot.appendChild(r)}renderDone(e){let t=this.state.confirmation;e.appendChild(l(`div`,{class:`ck-success`},`¡Reserva confirmada!`));let n=l(`div`,{class:`ck-summary`},[l(`div`,{class:`ck-summary-row`},[l(`span`,{},`Localizador`),l(`strong`,{},t.reference||`-`)]),l(`div`,{class:`ck-summary-row`},[l(`span`,{},`Experiencia`),l(`strong`,{},t.service_name||`-`)]),l(`div`,{class:`ck-summary-row`},[l(`span`,{},`Fecha`),l(`strong`,{},s(t.date))]),t.time?l(`div`,{class:`ck-summary-row`},[l(`span`,{},`Hora`),l(`strong`,{},t.time)]):null,l(`div`,{class:`ck-summary-row`},[l(`span`,{},`Participantes`),l(`strong`,{},String(t.participants))]),t.total_price?l(`div`,{class:`ck-summary-row ck-summary-total`},[l(`span`,{},`Total estimado`),l(`strong`,{},c(t.total_price,t.currency||`EUR`))]):null]);e.appendChild(n);let r=l(`div`,{class:`ck-actions`},[l(`button`,{class:`ck-btn`,type:`button`,onclick:()=>this.reset()},`Hacer otra reserva`)]);e.appendChild(r)}};customElements.get(`clockia-widget`)||customElements.define(`clockia-widget`,g);var _={init(e={}){if(!e.businessId||!e.widgetKey)return console.error(`[Clockia] init() requires businessId and widgetKey.`),null;let t=typeof e.container==`string`?document.querySelector(e.container):e.container;if(!t)return console.error(`[Clockia] container not found:`,e.container),null;let n=document.createElement(`clockia-widget`);return n.setAttribute(`business-id`,String(e.businessId)),n.setAttribute(`widget-key`,String(e.widgetKey)),e.apiBase&&n.setAttribute(`api-base`,e.apiBase),Object.entries({primaryColor:`primary-color`,secondaryColor:`secondary-color`,textColor:`text-color`,backgroundColor:`background-color`,fontFamily:`font-family`,fontSizeBase:`font-size-base`,borderRadius:`border-radius`,locale:`locale`}).forEach(([t,r])=>{e[t]!==void 0&&e[t]!==null&&n.setAttribute(r,String(e[t]))}),t.innerHTML=``,t.appendChild(n),n},Widget:g};if(typeof window<`u`){let e=window.Clockia;window.Clockia=e&&typeof e==`object`?Object.assign(e,_):_}e.ClockiaWidget=g,e.default=_})(this.Clockia=this.Clockia||{});