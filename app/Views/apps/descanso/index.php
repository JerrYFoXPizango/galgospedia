<?php require APP_PATH . '/Views/layout/header.php'; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');

.descanso-app *{box-sizing:border-box;-webkit-tap-highlight-color:transparent;}
.descanso-app{
  min-height:100vh;
  background:#080e1c;
  font-family:'Inter',system-ui,-apple-system,sans-serif;
  color:#f1f5f9;
  user-select:none;
}
.d-wrap{max-width:390px;margin:0 auto;padding:20px 16px 40px;display:flex;flex-direction:column;gap:14px;}

/* Header */
.d-header{display:flex;align-items:center;justify-content:space-between;}
.d-back{
  width:40px;height:40px;background:#111827;border:1px solid #1f2937;
  border-radius:12px;display:flex;align-items:center;justify-content:center;
  color:#6b7280;text-decoration:none;flex-shrink:0;
}
.d-title{text-align:right;line-height:1.3;}
.d-title strong{display:block;font-size:13px;font-weight:800;letter-spacing:.06em;color:#f9fafb;}
.d-title span{display:block;font-size:9px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#374151;}

/* Tabs */
.d-tabs{display:grid;grid-template-columns:repeat(3,1fr);gap:4px;background:#0d1526;border:1px solid #1f2937;border-radius:16px;padding:4px;}
.d-tab{
  padding:11px 4px;font-size:11px;font-weight:700;letter-spacing:.06em;
  border:none;border-radius:12px;cursor:pointer;transition:all .2s;
  background:transparent;color:#4b5563;
}
.d-tab.active{background:#f59e0b;color:#111;box-shadow:0 2px 12px rgba(245,158,11,.3);}

/* Display Card */
.d-display{
  background:#0d1526;border:1px solid #1f2937;border-radius:24px;
  padding:28px 20px 24px;text-align:center;
}
.d-display-label{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.3em;color:#374151;margin-bottom:10px;}
.d-time{
  font-size:80px;font-weight:800;line-height:1;letter-spacing:-.03em;
  font-variant-numeric:tabular-nums;color:#f9fafb;
}
.d-time .sep{color:#f59e0b;margin:0 4px;}
.d-time .unit{font-size:28px;font-weight:700;color:#374151;margin-left:4px;}

/* Ring */
.d-ring-wrap{display:flex;justify-content:center;padding:10px 0 6px;}

/* Info */
.d-info{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.d-card{background:#0d1526;border:1px solid #1f2937;border-radius:20px;padding:16px 18px;}
.d-card-label{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.14em;color:#374151;margin-bottom:8px;}
.d-card-val{font-size:34px;font-weight:800;line-height:1;color:#f9fafb;font-variant-numeric:tabular-nums;}
.d-card-unit{font-size:12px;font-weight:600;color:#374151;margin-left:3px;}
.d-apto-pill{
  display:inline-block;background:#f59e0b;border-radius:10px;
  padding:7px 14px;font-size:22px;font-weight:800;color:#111;
  font-variant-numeric:tabular-nums;line-height:1;margin-top:4px;
}

/* Teclado */
.d-keys{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;}
.d-key{
  height:60px;border:none;border-radius:16px;font-size:24px;font-weight:700;
  cursor:pointer;transition:transform .1s,background .15s;
  background:#111827;color:#e5e7eb;border:1px solid #1f2937;
  display:flex;align-items:center;justify-content:center;
}
.d-key:active{transform:scale(.9);}
.d-key-c{background:#ef4444!important;color:#fff!important;border-color:transparent!important;box-shadow:0 0 16px rgba(239,68,68,.25);}
.d-key-del{background:#1f2937!important;color:#6b7280!important;}

/* Botones acción */
.d-btn-start{
  width:100%;height:58px;border:none;border-radius:18px;
  font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.16em;
  cursor:pointer;transition:all .2s;
}
.d-btn-start.on{background:#f59e0b;color:#111;box-shadow:0 4px 24px rgba(245,158,11,.3);}
.d-btn-start.off{background:#111827;color:#1f2937;border:1px solid #1f2937;cursor:not-allowed;}
.d-btn-stop{
  width:100%;height:58px;border:none;border-radius:18px;
  background:#ef4444;color:#fff;font-size:13px;font-weight:800;
  text-transform:uppercase;letter-spacing:.16em;cursor:pointer;
  box-shadow:0 4px 24px rgba(239,68,68,.3);transition:background .2s;
}
.d-btn-stop:hover{background:#dc2626;}

[x-cloak]{display:none!important;}
</style>

<div class="descanso-app" x-data="fegAssistant()">
<div class="d-wrap">

  <!-- Header -->
  <div class="d-header">
    <a href="/apps" class="d-back">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <div class="d-title">
      <strong>Art. 55 FEG</strong>
      <span>Galgospedia</span>
    </div>
  </div>

  <!-- Tabs -->
  <div class="d-tabs">
    <button class="d-tab" :class="mode==='valida'?'active':''" @click="setMode('valida')">Válida</button>
    <button class="d-tab" :class="mode==='nula'?'active':''"   @click="setMode('nula')">Nula</button>
    <button class="d-tab" :class="mode==='trailla'?'active':''" @click="setMode('trailla')">Traílla</button>
  </div>

  <!-- Display: input o ring -->
  <div class="d-display">
    <!-- Input (reposo) -->
    <div x-show="!timerStarted">
      <div class="d-display-label" x-text="mode==='trailla'?'Minutos en collar':'Tiempo de carrera'"></div>
      <div class="d-time">
        <template x-if="mode!=='trailla'">
          <span>
            <span x-text="displayMin()"></span><span class="sep">:</span><span x-text="displaySec()"></span>
          </span>
        </template>
        <template x-if="mode==='trailla'">
          <span>
            <span x-text="rawInput||'0'"></span><span class="unit">min</span>
          </span>
        </template>
      </div>
    </div>

    <!-- Ring countdown -->
    <div x-show="timerStarted" class="d-ring-wrap">
      <div style="position:relative;width:190px;height:190px;">
        <svg viewBox="0 0 190 190" style="transform:rotate(-90deg);width:100%;height:100%;">
          <circle cx="95" cy="95" r="80" stroke="#1f2937" stroke-width="10" fill="transparent"/>
          <circle cx="95" cy="95" r="80" stroke="#f59e0b" stroke-width="10" fill="transparent"
                  stroke-linecap="round" stroke-dasharray="503"
                  :stroke-dashoffset="503-(503*(timeLeft/totalSeconds))"
                  style="transition:stroke-dashoffset 1s linear;"/>
        </svg>
        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;">
          <span style="font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.3em;color:#f59e0b;">Restante</span>
          <span style="font-size:50px;font-weight:800;line-height:1;font-variant-numeric:tabular-nums;letter-spacing:-.02em;"
                x-text="formatTimeLeft()"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Info cards -->
  <div class="d-info">
    <div class="d-card">
      <div class="d-card-label">Descanso</div>
      <span class="d-card-val" x-text="calc().rest"></span><span class="d-card-unit">min</span>
    </div>
    <div class="d-card">
      <div class="d-card-label">Hora apto</div>
      <div class="d-apto-pill" x-text="calc().ready"></div>
    </div>
  </div>

  <!-- Keypad -->
  <div class="d-keys" x-show="!timerStarted">
    <button class="d-key" @click="handleKey('1')">1</button>
    <button class="d-key" @click="handleKey('2')">2</button>
    <button class="d-key" @click="handleKey('3')">3</button>
    <button class="d-key" @click="handleKey('4')">4</button>
    <button class="d-key" @click="handleKey('5')">5</button>
    <button class="d-key" @click="handleKey('6')">6</button>
    <button class="d-key" @click="handleKey('7')">7</button>
    <button class="d-key" @click="handleKey('8')">8</button>
    <button class="d-key" @click="handleKey('9')">9</button>
    <button class="d-key d-key-c" @click="handleKey('C')">C</button>
    <button class="d-key" @click="handleKey('0')">0</button>
    <button class="d-key d-key-del" @click="handleKey('⌫')">
      <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 6H8l-5 6 5 6h13V6zM15 10l-4 4M19 10l-4 4"/>
      </svg>
    </button>
  </div>

  <!-- Botón acción -->
  <button x-show="!timerStarted"
          @click="start()"
          :disabled="calc().rest===0"
          class="d-btn-start"
          :class="calc().rest>0?'on':'off'">
    Iniciar Descanso
  </button>

  <button x-show="timerStarted" x-cloak
          @click="stopTimer()"
          class="d-btn-stop">
    Detener Descanso
  </button>

</div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('fegAssistant', () => ({
    mode: 'valida',
    rawInput: '',
    timerStarted: false,
    timeLeft: 0,
    totalSeconds: 0,
    timerInterval: null,

    setMode(m){ this.mode=m; this.rawInput=''; },

    handleKey(k){
      if(k==='C'){ this.rawInput=''; return; }
      if(k==='⌫'){ this.rawInput=this.rawInput.slice(0,-1); return; }
      if(this.rawInput.length<4 && /\d/.test(k)) this.rawInput+=k;
    },

    displayMin(){ return this.rawInput.length<=2?'00':this.rawInput.slice(0,-2).padStart(2,'0'); },
    displaySec(){
      if(!this.rawInput) return '00';
      return (this.rawInput.length===1?'0'+this.rawInput:this.rawInput.slice(-2));
    },

    calc(){
      let rest=0;
      const s=(parseInt(this.displayMin())||0)*60+(parseInt(this.displaySec())||0);
      if(this.mode==='valida'   && s>0) rest=s<=150?30:(s<=180?45:60);
      if(this.mode==='nula'     && s>0) rest=Math.ceil(s/2);
      if(this.mode==='trailla'  && parseInt(this.rawInput)>0) rest=20;
      const ready=rest>0
        ? new Date(Date.now()+rest*60000).toLocaleTimeString('es-ES',{hour:'2-digit',minute:'2-digit'})
        : '--:--';
      return {rest,ready};
    },

    start(){
      const r=this.calc();
      if(!r.rest) return;
      this.totalSeconds=this.timeLeft=r.rest*60;
      this.timerStarted=true;
      this.timerInterval=setInterval(()=>{
        if(this.timeLeft>0) this.timeLeft--;
        else{ clearInterval(this.timerInterval); this.playAlert(); }
      },1000);
    },

    formatTimeLeft(){
      return Math.floor(this.timeLeft/60)+':'+String(this.timeLeft%60).padStart(2,'0');
    },

    stopTimer(){
      this.timerStarted=false;
      clearInterval(this.timerInterval);
      this.timeLeft=0;
    },

    playAlert(){
      if('vibrate' in navigator) navigator.vibrate([500,150,500,150,500]);
      alert('¡Descanso finalizado!\nEl galgo ya puede correr.');
    }
  }));
});
</script>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
