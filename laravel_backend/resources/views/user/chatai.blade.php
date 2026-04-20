{{--
  =====================================================
  SIMOPANG — Chat AI / Budget Wizard
  File : resources/views/user/chatai.blade.php
  Desc : Wizard rekomendasi belanja pangan berbasis AI
  =====================================================
--}}
@extends('layouts.layout')

@section('title', 'Rekomendasi Belanja AI')
@section('page-title', 'Rekomendasi Belanja AI')
@section('page-sub', 'Wizard belanja cerdas berbasis AI untuk rekomendasi personal')

@section('content')

  {{-- ── LAYOUT UTAMA ────────────────────────────────── --}}
  <div class="ai-chat-layout">

    {{-- ── SIDEBAR ──────────────────────────────────── --}}
    <aside class="ai-chat-sidebar">
      <div class="ai-sidebar-hero">
        <div class="ai-sidebar-orb">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2a4 4 0 014 4v1h1a3 3 0 013 3v7a3 3 0 01-3 3H7a3 3 0 01-3-3V10a3 3 0 013-3h1V6a4 4 0 014-4z"/>
            <circle cx="9" cy="13" r="1" fill="currentColor"/>
            <circle cx="15" cy="13" r="1" fill="currentColor"/>
          </svg>
        </div>
        <div class="ai-sidebar-hero__title">SIMOPANG AI</div>
        <div class="ai-sidebar-hero__sub">Asisten Cerdas Pangan Nasional</div>
        <div class="ai-online-dot">
          <span class="ai-online-dot__ring"></span>
          <span class="ai-online-dot__core"></span>
          Online
        </div>
      </div>

      {{-- Progress Steps --}}
      <div class="ai-sidebar-section">
        <div class="ai-sidebar-section__label">Langkah Wizard</div>
        <div class="ai-wizard-steps" id="wizardSteps">
          <div class="ai-wizard-step active" data-step="1">
            <div class="ai-wizard-step__num">1</div>
            <div class="ai-wizard-step__label">Jangka Waktu</div>
          </div>
          <div class="ai-wizard-step" data-step="2">
            <div class="ai-wizard-step__num">2</div>
            <div class="ai-wizard-step__label">Jumlah Anggota</div>
          </div>
          <div class="ai-wizard-step" data-step="3">
            <div class="ai-wizard-step__num">3</div>
            <div class="ai-wizard-step__label">Budget Belanja</div>
          </div>
          <div class="ai-wizard-step" data-step="4">
            <div class="ai-wizard-step__num">4</div>
            <div class="ai-wizard-step__label">Komoditas Utama</div>
          </div>
          <div class="ai-wizard-step" data-step="5">
            <div class="ai-wizard-step__num">5</div>
            <div class="ai-wizard-step__label">Prioritas Belanja</div>
          </div>
          <div class="ai-wizard-step" data-step="6">
            <div class="ai-wizard-step__num">6</div>
            <div class="ai-wizard-step__label">Rekomendasi AI</div>
          </div>
        </div>
      </div>

      {{-- Ringkasan Pilihan --}}
      <div class="ai-sidebar-section" id="summarySection" style="display:none">
        <div class="ai-sidebar-section__label">Ringkasan Pilihan</div>
        <div class="ai-summary-list" id="summaryList"></div>
      </div>

      <button class="ai-clear-btn" onclick="resetWizard()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="1 4 1 10 7 10"/>
          <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
        </svg>
        Mulai Ulang
      </button>
    </aside>

    {{-- ── CHAT MAIN ─────────────────────────────────── --}}
    <div class="ai-chat-main">

      {{-- Header --}}
      <div class="ai-chat-header">
        <div class="ai-chat-header__left">
          <div class="ai-chat-header__avatar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2a4 4 0 014 4v1h1a3 3 0 013 3v7a3 3 0 01-3 3H7a3 3 0 01-3-3V10a3 3 0 013-3h1V6a4 4 0 014-4z"/>
              <circle cx="9" cy="13" r="1" fill="currentColor"/>
              <circle cx="15" cy="13" r="1" fill="currentColor"/>
            </svg>
          </div>
          <div>
            <div class="ai-chat-header__name">SIMOPANG AI — Rekomendasi Belanja</div>
            <div class="ai-chat-header__status">
              <span class="ai-status-dot"></span>
              Aktif & siap membantu
            </div>
          </div>
        </div>
        <div class="ai-chat-header__model">
          <span>Powered by Claude</span>
        </div>
      </div>

      {{-- Messages --}}
      <div class="ai-messages" id="aiMessages"></div>

      {{-- Quick Reply / Pilihan --}}
      <div class="ai-quick-replies" id="quickReplies"></div>

      {{-- Input Area (hanya muncul jika step butuh free text) --}}
      <div class="ai-chat-input-wrap" id="inputWrap" style="display:none">
        <div class="ai-chat-input-box">
          <textarea
            class="ai-chat-textarea"
            id="chatInput"
            placeholder="Ketik jawaban Anda..."
            rows="1"
            onkeydown="handleKey(event)"
            oninput="autoResize(this)"
          ></textarea>
          <button class="ai-send-btn" id="sendBtn" onclick="sendFreeText()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="22" y1="2" x2="11" y2="13"/>
              <polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
          </button>
        </div>
        <div class="ai-chat-input-hint">
          Tekan <kbd>Enter</kbd> untuk kirim · <kbd>Shift+Enter</kbd> untuk baris baru
        </div>
      </div>

    </div>
    {{-- / chat main --}}

  </div>

@endsection

@push('scripts')
<script>
// ── STATE ────────────────────────────────────────────
const wizard = {
  step: 1,
  answers: {
    periode:   null,
    anggota:   null,
    budget:    null,
    komoditas: [],
    prioritas: null,
  }
};

// ── STEP DEFINITIONS (Gunakan @{{variable}} untuk menghindari Blade parsing) ──
const steps = {
  1: {
    key: 'periode',
    question: 'Halo! Saya <strong>SIMOPANG AI</strong> 👋<br><br>Saya akan membantu merekomendasikan belanja pangan yang cerdas sesuai budget Anda.<br><br>Pertama, untuk jangka waktu berapa Anda ingin merencanakan belanja?',
    type: 'choice',
    choices: [
      { label: '📅 1 Minggu',  value: '1 minggu' },
      { label: '📆 2 Minggu',  value: '2 minggu' },
      { label: '🗓️ 1 Bulan',   value: '1 bulan'  },
    ]
  },
  2: {
    key: 'anggota',
    question: 'Baik, untuk periode <strong>@{{periode}}</strong>.<br><br>Berapa jumlah anggota keluarga yang perlu dipenuhi kebutuhan pangannya?',
    type: 'choice',
    choices: [
      { label: '👤 1 Orang',         value: '1 orang' },
      { label: '👥 2 Orang',         value: '2 orang' },
      { label: '👨‍👩‍👦 3–4 Orang',     value: '3-4 orang' },
      { label: '👨‍👩‍👧‍👦 5+ Orang',       value: '5 orang atau lebih' },
    ]
  },
  3: {
    key: 'budget',
    question: 'Untuk <strong>@{{anggota}}</strong> selama <strong>@{{periode}}</strong>.<br><br>Berapa total budget belanja pangan Anda?',
    type: 'choice',
    choices: [
      { label: '💰 < Rp 200rb',          value: 'di bawah Rp 200.000' },
      { label: '💳 Rp 200rb – 500rb',    value: 'Rp 200.000 – 500.000' },
      { label: '💵 Rp 500rb – 1 juta',   value: 'Rp 500.000 – 1.000.000' },
      { label: '💎 Rp 1 juta – 2 juta',  value: 'Rp 1.000.000 – 2.000.000' },
      { label: '🏆 > Rp 2 juta',         value: 'lebih dari Rp 2.000.000' },
    ]
  },
  4: {
    key: 'komoditas',
    question: 'Budget <strong>@{{budget}}</strong>. Bagus!<br><br>Komoditas pangan apa saja yang biasanya Anda beli? <em>(Pilih satu atau lebih)</em>',
    type: 'multi',
    choices: [
      { label: '🌾 Beras',         value: 'beras' },
      { label: '🥚 Telur',         value: 'telur' },
      { label: '🌶️ Cabai',         value: 'cabai' },
      { label: '🧅 Bawang Merah',  value: 'bawang merah' },
      { label: '🧄 Bawang Putih',  value: 'bawang putih' },
      { label: '🥩 Daging Sapi',   value: 'daging sapi' },
      { label: '🍗 Daging Ayam',   value: 'daging ayam' },
      { label: '🐟 Ikan',          value: 'ikan' },
      { label: '🥬 Sayuran',       value: 'sayuran' },
      { label: '🛢️ Minyak Goreng', value: 'minyak goreng' },
    ],
  },
  5: {
    key: 'prioritas',
    question: 'Pilihan komoditas sudah dicatat ✅<br><br>Terakhir, apa prioritas utama Anda dalam belanja pangan?',
    type: 'choice',
    choices: [
      { label: '💰 Sehemat mungkin',    value: 'penghematan maksimal' },
      { label: '⚖️ Hemat tapi bergizi', value: 'keseimbangan harga dan gizi' },
      { label: '🥦 Utamakan gizi',      value: 'kualitas dan kandungan gizi' },
      { label: '📦 Beli stok banyak',   value: 'pembelian stok jangka panjang' },
    ]
  },
};

// ── HELPERS ──────────────────────────────────────────
function getTime() {
  return new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function autoResize(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 160) + 'px';
}

function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendFreeText(); }
}

// Fungsi untuk mengganti placeholder @{{variabel}} dengan nilai dari wizard.answers
function fillTemplate(str) {
  return str.replace(/@\{\{(\w+)\}\}/g, (match, key) => {
    const value = wizard.answers[key];
    if (value === null || value === undefined) return '';
    if (Array.isArray(value)) return value.join(', ');
    return value;
  });
}

function scrollBottom() {
  const el = document.getElementById('aiMessages');
  if (el) el.scrollTop = el.scrollHeight;
}

// ── DOM HELPERS ──────────────────────────────────────
function appendBotBubble(html) {
  const container = document.getElementById('aiMessages');
  const div = document.createElement('div');
  div.className = 'ai-msg ai-msg--bot';
  div.innerHTML = `
    <div class="ai-msg__avatar">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 2a4 4 0 014 4v1h1a3 3 0 013 3v7a3 3 0 01-3 3H7a3 3 0 01-3-3V10a3 3 0 013-3h1V6a4 4 0 014-4z"/>
        <circle cx="9" cy="13" r="1" fill="currentColor"/>
        <circle cx="15" cy="13" r="1" fill="currentColor"/>
      </svg>
    </div>
    <div class="ai-msg__content">
      <div class="ai-msg__bubble">${html}</div>
      <div class="ai-msg__time">${getTime()}</div>
    </div>`;
  container.appendChild(div);
  scrollBottom();
  return div;
}

function appendUserBubble(text) {
  const container = document.getElementById('aiMessages');
  const div = document.createElement('div');
  div.className = 'ai-msg ai-msg--user';
  div.innerHTML = `
    <div class="ai-msg__content">
      <div class="ai-msg__bubble">${escapeHtml(text)}</div>
      <div class="ai-msg__time">${getTime()}</div>
    </div>`;
  container.appendChild(div);
  scrollBottom();
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function appendTyping() {
  removeTyping();
  const container = document.getElementById('aiMessages');
  const div = document.createElement('div');
  div.className = 'ai-msg ai-msg--bot';
  div.id = 'typingIndicator';
  div.innerHTML = `
    <div class="ai-msg__avatar">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 2a4 4 0 014 4v1h1a3 3 0 013 3v7a3 3 0 01-3 3H7a3 3 0 01-3-3V10a3 3 0 013-3h1V6a4 4 0 014-4z"/>
        <circle cx="9" cy="13" r="1" fill="currentColor"/>
        <circle cx="15" cy="13" r="1" fill="currentColor"/>
      </svg>
    </div>
    <div class="ai-msg__content">
      <div class="ai-msg__bubble ai-msg__bubble--typing">
        <span></span><span></span><span></span>
      </div>
    </div>`;
  container.appendChild(div);
  scrollBottom();
}

function removeTyping() {
  const el = document.getElementById('typingIndicator');
  if (el) el.remove();
}

function clearQuickReplies() {
  const container = document.getElementById('quickReplies');
  if (container) container.innerHTML = '';
}

// ── SIDEBAR STEP UPDATE ───────────────────────────────
function updateSidebarSteps(currentStep) {
  document.querySelectorAll('.ai-wizard-step').forEach(el => {
    const s = parseInt(el.dataset.step);
    const num = el.querySelector('.ai-wizard-step__num');
    el.classList.remove('active', 'done');
    if (s < currentStep) {
      el.classList.add('done');
      if (num) num.textContent = '✓';
    } else if (s === currentStep) {
      el.classList.add('active');
      if (num) num.textContent = s;
    } else {
      if (num) num.textContent = s;
    }
  });
}

// ── SIDEBAR SUMMARY UPDATE ────────────────────────────
function updateSummary() {
  const labels = {
    periode:   'Jangka Waktu',
    anggota:   'Anggota Keluarga',
    budget:    'Budget',
    komoditas: 'Komoditas',
    prioritas: 'Prioritas',
  };
  const section = document.getElementById('summarySection');
  const list    = document.getElementById('summaryList');
  const entries = Object.entries(wizard.answers)
    .filter(([k, v]) => v && (!Array.isArray(v) || v.length > 0));

  if (!entries.length) { 
    if (section) section.style.display = 'none'; 
    return; 
  }
  if (section) section.style.display = 'block';
  if (list) {
    list.innerHTML = entries.map(([k, v]) => `
      <div class="ai-summary-item">
        <div class="ai-summary-item__key">${labels[k] || k}</div>
        <div class="ai-summary-item__val">${Array.isArray(v) ? v.join(', ') : v}</div>
      </div>
    `).join('');
  }
}

// ── RENDER QUICK REPLIES ──────────────────────────────
const multiSelected = new Set();

function renderChoices(stepDef) {
  const container = document.getElementById('quickReplies');
  if (!container) return;
  container.innerHTML = '';
  const inputWrap = document.getElementById('inputWrap');
  if (inputWrap) inputWrap.style.display = 'none';

  if (stepDef.type === 'choice') {
    stepDef.choices.forEach(c => {
      const btn = document.createElement('button');
      btn.className = 'ai-qr-btn';
      btn.textContent = c.label;
      btn.onclick = () => handleChoice(c, stepDef.key);
      container.appendChild(btn);
    });
  }

  if (stepDef.type === 'multi') {
    stepDef.choices.forEach(c => {
      const btn = document.createElement('button');
      btn.className = 'ai-qr-btn';
      btn.dataset.value = c.value;
      btn.textContent = c.label;
      btn.onclick = () => toggleMulti(btn, c.value);
      container.appendChild(btn);
    });

    const confirmBtn = document.createElement('button');
    confirmBtn.className = 'ai-qr-btn ai-qr-btn--confirm';
    confirmBtn.textContent = '✓ Lanjutkan';
    confirmBtn.onclick = () => confirmMulti(stepDef.key);
    container.appendChild(confirmBtn);
  }
}

// ── MULTI SELECT ─────────────────────────────────────
function toggleMulti(btn, value) {
  if (multiSelected.has(value)) {
    multiSelected.delete(value);
    btn.classList.remove('selected');
  } else {
    multiSelected.add(value);
    btn.classList.add('selected');
  }
}

function confirmMulti(key) {
  if (!multiSelected.size) {
    appendBotBubble('⚠️ Pilih minimal satu komoditas dulu ya!');
    return;
  }
  wizard.answers[key] = Array.from(multiSelected);
  multiSelected.clear();
  appendUserBubble(wizard.answers[key].join(', '));
  clearQuickReplies();
  updateSummary();
  nextStep();
}

// ── SINGLE CHOICE ────────────────────────────────────
function handleChoice(choice, key) {
  wizard.answers[key] = choice.value;
  const cleanLabel = choice.label.replace(/^[^\w]+/, '').replace(/^\S+\s/, '');
  appendUserBubble(cleanLabel);
  clearQuickReplies();
  updateSummary();
  nextStep();
}

// ── FREE TEXT ────────────────────────────────────────
function sendFreeText() {
  const input = document.getElementById('chatInput');
  if (!input) return;
  const text = input.value.trim();
  if (!text) return;
  input.value = '';
  input.style.height = 'auto';
  const inputWrap = document.getElementById('inputWrap');
  if (inputWrap) inputWrap.style.display = 'none';
  appendUserBubble(text);
  nextStep(text);
}

// ── NEXT STEP ────────────────────────────────────────
function nextStep(freeVal = null) {
  wizard.step++;
  updateSidebarSteps(wizard.step);

  if (wizard.step > 5) {
    generateRekomendasi();
    return;
  }

  const stepDef = steps[wizard.step];
  if (!stepDef) return;
  
  const question = fillTemplate(stepDef.question);

  setTimeout(() => {
    appendBotBubble(`<p>${question}</p>`);
    renderChoices(stepDef);
  }, 400);
}

// ── GENERATE REKOMENDASI ──────────────────────────────
function generateRekomendasi() {
  updateSidebarSteps(6);

  setTimeout(() => {
    const loadingDiv = appendBotBubble(`
      <div class="ai-rekom-loading">
        <div class="ai-rekom-spinner"></div>
        <span>Sedang menganalisis dan menyusun rekomendasi belanja terbaik untuk Anda...</span>
      </div>
    `);
    callClaudeAPI(buildPrompt(), loadingDiv);
  }, 400);
}

function buildPrompt() {
  const a = wizard.answers;
  return `Buatkan rekomendasi belanja pangan untuk kondisi berikut:
- Jangka waktu: ${a.periode}
- Jumlah anggota keluarga: ${a.anggota}
- Budget total: ${a.budget}
- Komoditas yang dibutuhkan: ${Array.isArray(a.komoditas) ? a.komoditas.join(', ') : a.komoditas}
- Prioritas: ${a.prioritas}

Berikan rekomendasi dalam format berikut (Bahasa Indonesia, ramah dan informatif):

1. **Ringkasan Alokasi Budget** — tabel alokasi per komoditas dengan estimasi harga dan jumlah yang disarankan (format: | Komoditas | Jumlah | Estimasi Harga |). Sesuaikan dengan anggota keluarga dan periode waktu.

2. **Tips Belanja Cerdas** — 3 tips spesifik sesuai kondisi mereka.

3. **Peringatan Harga** — komoditas yang sedang fluktuatif dan perlu diperhatikan.

4. **Saran Substitusi** — alternatif lebih hemat jika ada komoditas mahal.

Gunakan emoji secukupnya. Jawaban praktis dan langsung bisa diterapkan. Maksimal 400 kata.`;
}

async function callClaudeAPI(prompt, loadingDiv) {
  try {
    const res = await fetch('https://api.anthropic.com/v1/messages', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'x-api-key': 'YOUR_API_KEY_HERE'
      },
      body: JSON.stringify({
        model: 'claude-3-sonnet-20240229',
        max_tokens: 1000,
        system: 'Kamu adalah SIMOPANG AI, asisten cerdas Sistem Monitoring Pangan Nasional Indonesia. Tugasmu memberikan rekomendasi belanja pangan yang praktis, hemat, dan bergizi berdasarkan budget dan kebutuhan pengguna. Jawab dalam Bahasa Indonesia yang hangat dan mudah dipahami. Gunakan data harga pangan yang realistis di Indonesia.',
        messages: [{ role: 'user', content: prompt }]
      })
    });

    const data = await res.json();
    const reply = data.content?.[0]?.text ?? 'Maaf, terjadi kesalahan. Coba lagi ya.';
    const bubble = loadingDiv.querySelector('.ai-msg__bubble');
    if (bubble) bubble.innerHTML = formatReply(reply);
    scrollBottom();
    showFollowUpButtons();

  } catch (err) {
    console.error('API Error:', err);
    const bubble = loadingDiv.querySelector('.ai-msg__bubble');
    if (bubble) {
      bubble.innerHTML = '⚠️ Gagal terhubung ke AI. Periksa koneksi internet Anda dan coba lagi.';
    }
    showFollowUpButtons();
  }
}

function formatReply(text) {
  let formatted = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/^#{1,3} (.+)$/gm, '<strong style="font-size:13.5px;color:#1e293b">$1</strong>')
    .replace(/\n\n/g, '</p><p>')
    .replace(/\n/g, '<br>');
  
  formatted = formatted.replace(/\|(.+)\|/g, match => {
    const cells = match.split('|').filter(c => c.trim() && !c.match(/^[-\s:]+$/));
    if (!cells.length) return '';
    return `<tr>${cells.map(c => `<td>${c.trim()}</td>`).join('')}</tr>`;
  });
  
  formatted = formatted.replace(/(<td>[\s\S]*?<\/tr>\n?)+/g, rows =>
    `<table class="ai-rekom-table"><tbody>${rows}</tbody></table>`);
  
  formatted = formatted.replace(/^- (.+)$/gm, '<li>$1</li>');
  formatted = formatted.replace(/(<li>[\s\S]*?<\/li>\n?)+/g, lis => `<ul>${lis}</ul>`);
  
  return `<div>${formatted}</div>`;
}

// ── FOLLOW UP BUTTONS ────────────────────────────────
function showFollowUpButtons() {
  const container = document.getElementById('quickReplies');
  if (!container) return;
  container.innerHTML = '';

  const followUps = [
    { label: '🔄 Coba Budget Berbeda',            action: 'resetStep3'   },
    { label: '📊 Komoditas Murah Sekarang?',       action: 'cheapNow'     },
    { label: '📦 Tips Menyimpan Stok?',            action: 'storageTips'  },
    { label: '🔁 Mulai Ulang Wizard',              action: 'reset'        },
  ];

  followUps.forEach(f => {
    const btn = document.createElement('button');
    btn.className = 'ai-qr-btn';
    btn.textContent = f.label;
    btn.onclick = () => handleFollowUp(f.action, btn);
    container.appendChild(btn);
  });
}

async function handleFollowUp(action, btn) {
  btn.classList.add('selected');
  clearQuickReplies();
  appendUserBubble(btn.textContent);

  if (action === 'reset')      { resetWizard(); return; }
  if (action === 'resetStep3') { resetToStep(3); return; }

  const prompts = {
    cheapNow:    'Berdasarkan kondisi pasar pangan Indonesia saat ini, komoditas pangan apa yang sedang harganya terjangkau atau turun? Info singkat per komoditas (beras, telur, cabai, bawang, daging ayam, dll) dan tips memanfaatkannya. Bahasa Indonesia, ringkas, dengan emoji.',
    storageTips: `Berikan tips menyimpan stok bahan pangan seperti ${wizard.answers.komoditas?.join(', ') || 'bahan pangan'} agar lebih tahan lama dan tidak cepat rusak. Bahasa Indonesia, praktis, dengan emoji.`,
  };

  if (prompts[action]) {
    appendTyping();
    try {
      const res = await fetch('https://api.anthropic.com/v1/messages', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'x-api-key': 'YOUR_API_KEY_HERE'
        },
        body: JSON.stringify({
          model: 'claude-3-sonnet-20240229',
          max_tokens: 600,
          system: 'Kamu adalah SIMOPANG AI, asisten pangan nasional Indonesia. Jawab dalam Bahasa Indonesia yang ramah dan praktis.',
          messages: [{ role: 'user', content: prompts[action] }]
        })
      });
      const data = await res.json();
      removeTyping();
      appendBotBubble(formatReply(data.content?.[0]?.text ?? 'Maaf, terjadi kesalahan.'));
      showFollowUpButtons();
    } catch (err) {
      console.error('Follow-up Error:', err);
      removeTyping();
      appendBotBubble('⚠️ Gagal terhubung. Coba lagi.');
      showFollowUpButtons();
    }
  }
}

// ── RESET ────────────────────────────────────────────
function resetWizard() {
  wizard.step              = 1;
  wizard.answers.periode   = null;
  wizard.answers.anggota   = null;
  wizard.answers.budget    = null;
  wizard.answers.komoditas = [];
  wizard.answers.prioritas = null;
  multiSelected.clear();

  const messagesDiv = document.getElementById('aiMessages');
  const quickRepliesDiv = document.getElementById('quickReplies');
  const inputWrap = document.getElementById('inputWrap');
  const summarySection = document.getElementById('summarySection');
  
  if (messagesDiv) messagesDiv.innerHTML = '';
  if (quickRepliesDiv) quickRepliesDiv.innerHTML = '';
  if (inputWrap) inputWrap.style.display = 'none';
  if (summarySection) summarySection.style.display = 'none';

  updateSidebarSteps(1);
  startWizard();
}

function resetToStep(stepNum) {
  wizard.step = stepNum - 1;
  const keys = ['periode', 'anggota', 'budget', 'komoditas', 'prioritas'];
  for (let i = stepNum - 1; i < keys.length; i++) {
    wizard.answers[keys[i]] = keys[i] === 'komoditas' ? [] : null;
  }
  updateSummary();
  nextStep();
}

// ── INIT ─────────────────────────────────────────────
function startWizard() {
  updateSidebarSteps(1);
  const question = steps[1].question;
  appendBotBubble(`<p>${question}</p>`);
  renderChoices(steps[1]);
}

document.addEventListener('DOMContentLoaded', () => startWizard());
</script>
@endpush