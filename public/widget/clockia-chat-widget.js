(function(e){Object.defineProperties(e,{__esModule:{value:!0},[Symbol.toStringTag]:{value:`Module`}});function t({apiBase:e,businessId:t,widgetKey:n}){let r=e.replace(/\/$/,``)+`/businesses/${t}/chat`;async function i(e,{method:t=`GET`,body:i=null,query:a=null}={}){let o=r+e;if(a){let e=new URLSearchParams;Object.entries(a).forEach(([t,n])=>{n!=null&&n!==``&&e.append(t,n)});let t=e.toString();t&&(o+=(o.includes(`?`)?`&`:`?`)+t)}let s=await fetch(o,{method:t,headers:{Accept:`application/json`,"Content-Type":`application/json`,"X-Widget-Key":n},body:i?JSON.stringify(i):void 0,mode:`cors`,credentials:`omit`}),c=(s.headers.get(`content-type`)||``).includes(`application/json`)?await s.json().catch(()=>({})):{};if(!s.ok){let e=Error(c.error||c.message||`No se pudo contactar con el chat.`);throw e.status=s.status,e}return c}return{greeting:()=>i(`/greeting`),history:e=>i(`/history`,{query:{conversation_id:e}}),send:(e,t)=>i(`/message`,{method:`POST`,body:{message:e,conversation_id:t||null}})}}function n(e){return`
:host {
    --ck-primary: ${e.primary_color};
    --ck-secondary: ${e.secondary_color};
    --ck-text: ${e.text_color};
    --ck-bg: ${e.background_color};
    --ck-font: ${e.font_family};
    --ck-font-size: ${e.font_size_base};
    --ck-radius: ${e.border_radius};
    --ck-border: #e5e7eb;
    --ck-muted: #6b7280;
    --ck-shadow: 0 10px 30px rgba(0,0,0,0.18);
    --ck-user-bg: var(--ck-primary);
    --ck-user-fg: #ffffff;
    --ck-bot-bg: #f3f4f6;
    --ck-bot-fg: var(--ck-text);
    font-family: var(--ck-font);
    font-size: var(--ck-font-size);
    color: var(--ck-text);
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 2147483000;
}
*, *::before, *::after { box-sizing: border-box; }

.ck-chat-bubble {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--ck-primary);
    color: #fff;
    border: none;
    cursor: pointer;
    box-shadow: var(--ck-shadow);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform .12s ease, box-shadow .12s ease;
    padding: 0;
    font-family: inherit;
}
.ck-chat-bubble:hover { transform: translateY(-2px); box-shadow: 0 14px 34px rgba(0,0,0,0.22); }
.ck-chat-bubble:focus-visible { outline: 3px solid var(--ck-secondary); outline-offset: 3px; }
.ck-chat-bubble svg { width: 28px; height: 28px; }

.ck-chat-panel {
    position: fixed;
    right: 20px;
    bottom: 90px;
    width: 380px;
    max-width: calc(100vw - 24px);
    height: 560px;
    max-height: calc(100vh - 120px);
    background: var(--ck-bg);
    border: 1px solid var(--ck-border);
    border-radius: var(--ck-radius);
    box-shadow: var(--ck-shadow);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    opacity: 0;
    transform: translateY(14px) scale(.98);
    transform-origin: bottom right;
    pointer-events: none;
    transition: opacity .16s ease, transform .16s ease;
}
.ck-chat-panel.ck-open {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}

.ck-chat-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    background: var(--ck-primary);
    color: #fff;
}
.ck-chat-header .ck-chat-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: rgba(255,255,255,0.22);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: calc(var(--ck-font-size) * 1.05);
}
.ck-chat-header .ck-chat-title { font-weight: 600; flex: 1; line-height: 1.15; }
.ck-chat-header .ck-chat-title small { display: block; font-weight: 400; opacity: 0.85; font-size: calc(var(--ck-font-size) * 0.78); }
.ck-chat-header button {
    background: transparent;
    border: 0;
    color: #fff;
    cursor: pointer;
    padding: 6px;
    border-radius: 6px;
    font-family: inherit;
    font-size: calc(var(--ck-font-size) * 1.1);
    line-height: 1;
}
.ck-chat-header button:hover { background: rgba(255,255,255,0.15); }

.ck-chat-body {
    flex: 1;
    padding: 14px;
    overflow-y: auto;
    background: var(--ck-bg);
    display: flex;
    flex-direction: column;
    gap: 8px;
    scroll-behavior: smooth;
}
.ck-chat-body::-webkit-scrollbar { width: 8px; }
.ck-chat-body::-webkit-scrollbar-thumb { background: var(--ck-border); border-radius: 4px; }

.ck-msg {
    max-width: 82%;
    padding: 10px 12px;
    border-radius: calc(var(--ck-radius) * 1.2);
    font-size: var(--ck-font-size);
    line-height: 1.4;
    white-space: pre-wrap;
    word-wrap: break-word;
}
.ck-msg.ck-msg-user {
    align-self: flex-end;
    background: var(--ck-user-bg);
    color: var(--ck-user-fg);
    border-bottom-right-radius: 4px;
}
.ck-msg.ck-msg-bot {
    align-self: flex-start;
    background: var(--ck-bot-bg);
    color: var(--ck-bot-fg);
    border-bottom-left-radius: 4px;
}
.ck-msg.ck-msg-error {
    align-self: center;
    background: #fee2e2;
    color: #b91c1c;
    font-size: calc(var(--ck-font-size) * 0.9);
}

.ck-typing {
    align-self: flex-start;
    background: var(--ck-bot-bg);
    color: var(--ck-muted);
    padding: 10px 14px;
    border-radius: calc(var(--ck-radius) * 1.2);
    border-bottom-left-radius: 4px;
    display: inline-flex;
    gap: 4px;
    align-items: center;
}
.ck-typing span {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--ck-muted);
    animation: ck-bounce 1s infinite ease-in-out;
}
.ck-typing span:nth-child(2) { animation-delay: .15s; }
.ck-typing span:nth-child(3) { animation-delay: .3s; }
@keyframes ck-bounce {
    0%, 80%, 100% { transform: translateY(0); opacity: .4; }
    40% { transform: translateY(-4px); opacity: 1; }
}

.ck-chat-footer {
    border-top: 1px solid var(--ck-border);
    padding: 10px 12px;
    background: var(--ck-bg);
}
.ck-chat-form { display: flex; gap: 8px; align-items: flex-end; }
.ck-chat-form textarea {
    flex: 1;
    min-height: 38px;
    max-height: 120px;
    resize: none;
    border: 1px solid var(--ck-border);
    border-radius: calc(var(--ck-radius) * 0.9);
    padding: 9px 12px;
    font-family: inherit;
    font-size: inherit;
    color: var(--ck-text);
    background: var(--ck-bg);
    line-height: 1.3;
    box-sizing: border-box;
}
.ck-chat-form textarea:focus { outline: 2px solid var(--ck-primary); outline-offset: 1px; border-color: var(--ck-primary); }
.ck-chat-form button {
    background: var(--ck-primary);
    color: #fff;
    border: none;
    border-radius: calc(var(--ck-radius) * 0.9);
    padding: 0 16px;
    height: 38px;
    cursor: pointer;
    font-family: inherit;
    font-weight: 600;
}
.ck-chat-form button:disabled { opacity: 0.55; cursor: not-allowed; }
.ck-chat-form button:hover:not(:disabled) { filter: brightness(0.95); }

.ck-chat-footer .ck-chat-branding {
    text-align: center;
    font-size: calc(var(--ck-font-size) * 0.75);
    color: var(--ck-muted);
    margin-top: 6px;
}

@media (max-width: 480px) {
    .ck-chat-panel {
        right: 8px;
        left: 8px;
        bottom: 78px;
        width: auto;
        max-width: none;
        height: calc(100vh - 100px);
    }
    :host {
        right: 12px;
        bottom: 12px;
    }
    .ck-chat-bubble {
        width: 54px;
        height: 54px;
    }
}
`}var r=`/api/widget`,i=[`primary-color`,`secondary-color`,`text-color`,`background-color`,`font-family`,`font-size-base`,`border-radius`],a={"primary-color":`primary_color`,"secondary-color":`secondary_color`,"text-color":`text_color`,"background-color":`background_color`,"font-family":`font_family`,"font-size-base":`font_size_base`,"border-radius":`border_radius`},o={primary_color:`#7B3F00`,secondary_color:`#EAD7C5`,text_color:`#2B2B2B`,background_color:`#FFFFFF`,font_family:`Inter, system-ui, sans-serif`,font_size_base:`14px`,border_radius:`12px`};function s(e,t={},n=[]){let r=document.createElement(e);return Object.entries(t||{}).forEach(([e,t])=>{t==null||t===!1||(e===`class`?r.className=t:e===`html`?r.innerHTML=t:e.startsWith(`on`)&&typeof t==`function`?r.addEventListener(e.slice(2).toLowerCase(),t):r.setAttribute(e,t))}),(Array.isArray(n)?n:[n]).forEach(e=>{e==null||e===!1||r.appendChild(typeof e==`string`?document.createTextNode(e):e)}),r}var c=class extends HTMLElement{static get observedAttributes(){return[`business-id`,`widget-key`,`api-base`,`title`,...i]}constructor(){super(),this.attachShadow({mode:`open`}),this.state={open:!1,theme:{...o},businessName:null,api:null,messages:[],conversationId:null,sending:!1,error:null,ready:!1,storageKey:null},this.bodyRef=null,this.panelRef=null}connectedCallback(){this.init()}async init(){let e=this.getAttribute(`business-id`),n=this.getAttribute(`widget-key`),i=this.getAttribute(`api-base`)||r;if(!e||!n){console.error(`[Clockia chat] Faltan atributos business-id y/o widget-key.`);return}this.state.theme=this.applyOverrides(o),this.state.api=t({apiBase:i,businessId:e,widgetKey:n}),this.state.storageKey=`clockia_chat_cid_${e}`,this.state.conversationId=this.restoreConversationId(),this.render();try{let e=await this.state.api.greeting();this.state.businessName=e.business?.name||null;let t=this.getAttribute(`title`);if(t&&(this.state.businessName=t),e.greeting&&this.state.messages.length===0&&this.state.messages.push({role:`assistant`,text:e.greeting}),this.state.ready=!0,this.render(),this.state.conversationId)try{let e=await this.state.api.history(this.state.conversationId);Array.isArray(e.history)&&e.history.length>0&&(this.state.messages=e.history.map(e=>({role:e.role===`user`?`user`:`assistant`,text:e.text||``})),this.render())}catch{}}catch(e){this.state.error=e.message||`No se pudo iniciar el chat.`,this.state.ready=!0,this.render()}}applyOverrides(e){let t={...e};return i.forEach(e=>{let n=this.getAttribute(e);n!==null&&n!==``&&(t[a[e]]=n)}),t}restoreConversationId(){try{return localStorage.getItem(this.state.storageKey)||null}catch{return null}}persistConversationId(e){try{e&&localStorage.setItem(this.state.storageKey,e)}catch{}}clearConversation(){this.state.messages=[],this.state.conversationId=null;try{localStorage.removeItem(this.state.storageKey)}catch{}this.state.error=null,this.render()}toggleOpen(){this.state.open=!this.state.open,this.render(),this.state.open&&setTimeout(()=>{let e=this.shadowRoot?.querySelector(`textarea`);e&&e.focus(),this.scrollToBottom()},120)}scrollToBottom(){let e=this.shadowRoot?.querySelector(`.ck-chat-body`);e&&(e.scrollTop=e.scrollHeight)}async sendMessage(e){let t=(e||``).trim();if(!(!t||this.state.sending)){this.state.messages.push({role:`user`,text:t}),this.state.sending=!0,this.state.error=null,this.render(),this.scrollToBottom();try{let e=await this.state.api.send(t,this.state.conversationId);e.conversation_id&&(this.state.conversationId=e.conversation_id,this.persistConversationId(e.conversation_id)),this.state.messages.push({role:`assistant`,text:e.reply||`(Sin respuesta)`})}catch(e){this.state.error=e.message||`Error al enviar el mensaje.`}finally{this.state.sending=!1,this.render(),this.scrollToBottom()}}}render(){let e=this.shadowRoot;for(;e.firstChild;)e.removeChild(e.firstChild);let t=document.createElement(`style`);t.textContent=n(this.state.theme),e.appendChild(t);let r=s(`button`,{class:`ck-chat-bubble`,type:`button`,"aria-label":this.state.open?`Cerrar chat`:`Abrir chat`,onclick:()=>this.toggleOpen()});r.innerHTML=this.state.open?`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`:`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>`,e.appendChild(r);let i=s(`div`,{class:`ck-chat-panel`+(this.state.open?` ck-open`:``)}),a=this.state.businessName||`Chat`,o=s(`div`,{class:`ck-chat-header`},[s(`div`,{class:`ck-chat-avatar`},a.trim().charAt(0).toUpperCase()||`C`),s(`div`,{class:`ck-chat-title`},[document.createTextNode(a),s(`small`,{},`Responde en unos segundos`)]),s(`button`,{type:`button`,title:`Iniciar nueva conversación`,"aria-label":`Iniciar nueva conversación`,onclick:()=>this.clearConversation()},`↻`),s(`button`,{type:`button`,title:`Cerrar`,"aria-label":`Cerrar chat`,onclick:()=>this.toggleOpen()},`×`)]),c=s(`div`,{class:`ck-chat-body`});if(this.state.messages.forEach(e=>{let t=`ck-msg `+(e.role===`user`?`ck-msg-user`:`ck-msg-bot`);c.appendChild(s(`div`,{class:t},e.text))}),this.state.sending){let e=s(`div`,{class:`ck-typing`},[s(`span`),s(`span`),s(`span`)]);c.appendChild(e)}this.state.error&&c.appendChild(s(`div`,{class:`ck-msg ck-msg-error`},this.state.error));let l=s(`div`,{class:`ck-chat-footer`}),u=s(`form`,{class:`ck-chat-form`,onsubmit:e=>{e.preventDefault();let t=u.querySelector(`textarea`),n=t.value;t.value=``,t.style.height=`auto`,this.sendMessage(n)}}),d=s(`textarea`,{rows:`1`,placeholder:`Escribe tu mensaje…`,"aria-label":`Mensaje`,disabled:this.state.sending?`disabled`:null,onkeydown:e=>{e.key===`Enter`&&!e.shiftKey&&(e.preventDefault(),u.requestSubmit())},oninput:e=>{e.target.style.height=`auto`,e.target.style.height=Math.min(120,e.target.scrollHeight)+`px`}}),f=s(`button`,{type:`submit`,disabled:this.state.sending?`disabled`:null,"aria-label":`Enviar`},`Enviar`);u.appendChild(d),u.appendChild(f),l.appendChild(u),i.appendChild(o),i.appendChild(c),i.appendChild(l),e.appendChild(i)}};customElements.get(`clockia-chat-widget`)||customElements.define(`clockia-chat-widget`,c);var l={init(e={}){if(!e.businessId||!e.widgetKey)return console.error(`[ClockiaChat] init() requires businessId and widgetKey.`),null;let t=document.createElement(`clockia-chat-widget`);return t.setAttribute(`business-id`,String(e.businessId)),t.setAttribute(`widget-key`,String(e.widgetKey)),e.apiBase&&t.setAttribute(`api-base`,e.apiBase),e.title&&t.setAttribute(`title`,e.title),Object.entries({primaryColor:`primary-color`,secondaryColor:`secondary-color`,textColor:`text-color`,backgroundColor:`background-color`,fontFamily:`font-family`,fontSizeBase:`font-size-base`,borderRadius:`border-radius`}).forEach(([n,r])=>{e[n]!==void 0&&e[n]!==null&&t.setAttribute(r,String(e[n]))}),(document.body||document.documentElement).appendChild(t),t},Widget:c};if(typeof window<`u`){let e=window.Clockia,t=e&&typeof e==`object`?Object.assign(e,{Chat:l,initChat:l.init}):{Chat:l,initChat:l.init};window.Clockia=t}e.ClockiaChatWidget=c,e.default=l})(this.ClockiaChat=this.ClockiaChat||{});