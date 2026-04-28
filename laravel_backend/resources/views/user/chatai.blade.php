{{--
  =====================================================
  SIMOPANG — Chat AI / Budget Wizard
  File : resources/views/user/chatai.blade.php
  Desc : Wizard rekomendasi belanja pangan berbasis AI
  AI   : Groq (Llama 3.3) via Flask ML
  =====================================================
--}}
@extends('layouts.layout')

@section('title', 'Rekomendasi Belanja AI')
@section('page-title', 'Rekomendasi Belanja AI')
@section('page-sub', 'Wizard belanja cerdas berbasis AI untuk rekomendasi personal')

@section('content')

  {{-- Route URLs & Data dirender di sini agar tidak konflik dengan @push('scripts') --}}
  <div id="app-routes"
       data-rekomendasi="{{ route('user.chatai.rekomendasi') }}"
       data-followup="{{ route('user.chatai.followup') }}"
       data-komoditas="{{ route('user.chatai.komoditas') }}"
       style="display:none"></div>

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
          <span>Powered by Groq AI</span>
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

  {{-- ── CSS CAROUSEL KOMODITAS ───────────────────────── --}}
  <style>
    .komoditas-carousel-wrap {
      width: 100%;
      margin-top: 8px;
    }
    .komoditas-carousel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 8px;
      padding: 0 2px;
    }
    .komoditas-carousel-hint {
      font-size: 11px;
      color: #94a3b8;
    }
    .komoditas-selected-count {
      font-size: 11px;
      color: #3b82f6;
      font-weight: 600;
    }
    .komoditas-carousel {
      display: grid;
      grid-template-rows: repeat(3, auto);
      grid-auto-flow: column;
      gap: 6px;
      overflow-x: auto;
      padding-bottom: 8px;
      scroll-behavior: smooth;
      -webkit-overflow-scrolling: touch;
      /* Sembunyikan scrollbar tapi tetap bisa scroll */
      scrollbar-width: thin;
      scrollbar-color: #e2e8f0 transparent;
    }
    .komoditas-carousel::-webkit-scrollbar {
      height: 4px;
    }
    .komoditas-carousel::-webkit-scrollbar-track {
      background: transparent;
    }
    .komoditas-carousel::-webkit-scrollbar-thumb {
      background: #e2e8f0;
      border-radius: 4px;
    }
    .komoditas-carousel::-webkit-scrollbar-thumb:hover {
      background: #cbd5e1;
    }
    .komoditas-btn {
      white-space: nowrap;
      padding: 6px 12px;
      border-radius: 20px;
      border: 1.5px solid #e2e8f0;
      background: #fff;
      color: #475569;
      font-size: 12px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.15s ease;
      user-select: none;
    }
    .komoditas-btn:hover {
      border-color: #94a3b8;
      background: #f8fafc;
    }
    .komoditas-btn.selected {
      border-color: #3b82f6;
      background: #eff6ff;
      color: #1d4ed8;
      font-weight: 600;
    }
    .komoditas-confirm-row {
      margin-top: 10px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .komoditas-confirm-btn {
      padding: 8px 20px;
      border-radius: 20px;
      border: none;
      background: #3b82f6;
      color: #fff;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.15s;
    }
    .komoditas-confirm-btn:hover {
      background: #2563eb;
    }
    .komoditas-confirm-btn:disabled {
      background: #cbd5e1;
      cursor: not-allowed;
    }
    .komoditas-loading {
      color: #94a3b8;
      font-size: 13px;
      padding: 12px 0;
    }
  </style>

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

// Cache daftar komoditas dari DB
let dbKomoditas = [];

// ── STEP DEFINITIONS ─────────────────────────────────
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
    question: 'Baik, untuk periode <strong>{[periode]}</strong>.<br><br>Berapa jumlah anggota keluarga yang perlu dipenuhi kebutuhan pangannya?',
    type: 'choice',
    choices: [
      { label: '👤 1 Orang',      value: '1 orang' },
      { label: '👥 2 Orang',      value: '2 orang' },
      { label: '👨‍👩‍👦 3–4 Orang',  value: '3-4 orang' },
      { label: '👨‍👩‍👧‍👦 5+ Orang',    value: '5 orang atau lebih' },
    ]
  },
  3: {
    key: 'budget',
    question: 'Untuk <strong>{[anggota]}</strong> selama <strong>{[periode]}</strong>.<br><br>Berapa total budget belanja pangan Anda?',
    type: 'choice',
    choices: [
      { label: '💰 < Rp 200rb',         value: 'di bawah Rp 200.000' },
      { label: '💳 Rp 200rb – 500rb',   value: 'Rp 200.000 – 500.000' },
      { label: '💵 Rp 500rb – 1 juta',  value: 'Rp 500.000 – 1.000.000' },
      { label: '💎 Rp 1 juta – 2 juta', value: 'Rp 1.000.000 – 2.000.000' },
      { label: '🏆 > Rp 2 juta',        value: 'lebih dari Rp 2.000.000' },
    ]
  },
  4: {
    key: 'komoditas',
    question: 'Budget <strong>{[budget]}</strong>. Bagus!<br><br>Komoditas pangan apa saja yang biasanya Anda beli? <em>(Pilih satu atau lebih, geser → untuk lihat semua)</em>',
    type: 'komoditas_carousel', // tipe khusus dari DB
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

// ── ROUTE URLs ────────────────────────────────────────
const CSRF_TOKEN      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const ROUTE_REKOMENDASI = document.getElementById('app-routes').dataset.rekomendasi;
const ROUTE_FOLLOWUP    = document.getElementById('app-routes').dataset.followup;
const ROUTE_KOMODITAS   = document.getElementById('app-routes').dataset.komoditas;

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
function fillTemplate(str) {
  return str.replace(/\{\[(\w+)\]\}/g, (match, key) => {
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
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
function stripEmojiPrefix(label) {
  return label.replace(/^[\u{1F000}-\u{1FFFF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}\u{FE00}-\u{FEFF}\u200d\uFE0F\s]+/u, '').trim();
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

// ── SIDEBAR ───────────────────────────────────────────
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
  if (!entries.length) { if (section) section.style.display = 'none'; return; }
  if (section) section.style.display = 'block';
  if (list) {
    list.innerHTML = entries.map(([k, v]) => `
      <div class="ai-summary-item">
        <div class="ai-summary-item__key">${labels[k] || k}</div>
        <div class="ai-summary-item__val">${Array.isArray(v) ? v.join(', ') : v}</div>
      </div>`).join('');
  }
}

// ── RENDER CHOICES ────────────────────────────────────
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

  if (stepDef.type === 'komoditas_carousel') {
    renderKomoditasCarousel(container);
  }
}

// ── CAROUSEL KOMODITAS DARI DB ────────────────────────
async function renderKomoditasCarousel(container) {
  multiSelected.clear();

  // Tampilkan loading dulu
  container.innerHTML = `<div class="komoditas-loading">⏳ Memuat daftar komoditas...</div>`;

  // Fetch dari DB jika belum di-cache
  if (!dbKomoditas.length) {
    try {
      const res  = await fetch(ROUTE_KOMODITAS);
      const data = await res.json();
      dbKomoditas = data.komoditas ?? [];
    } catch (e) {
      container.innerHTML = `<div class="komoditas-loading">⚠️ Gagal memuat komoditas. <button onclick="renderKomoditasCarousel(document.getElementById('quickReplies'))" style="color:#3b82f6;background:none;border:none;cursor:pointer;text-decoration:underline">Coba lagi</button></div>`;
      return;
    }
  }

  container.innerHTML = '';

  // Buat wrapper carousel
  const wrap = document.createElement('div');
  wrap.className = 'komoditas-carousel-wrap';

  // Header: hint geser + counter terpilih
  const header = document.createElement('div');
  header.className = 'komoditas-carousel-header';
  header.innerHTML = `
    <span class="komoditas-carousel-hint">← Geser untuk lihat semua (${dbKomoditas.length} komoditas)</span>
    <span class="komoditas-selected-count" id="komCountLabel">0 dipilih</span>`;
  wrap.appendChild(header);

  // Grid carousel (3 baris, scroll horizontal)
  const grid = document.createElement('div');
  grid.className = 'komoditas-carousel';

  dbKomoditas.forEach(nama => {
    const btn = document.createElement('button');
    btn.className = 'komoditas-btn';
    btn.textContent = nama;
    btn.dataset.value = nama;
    btn.onclick = () => {
      if (multiSelected.has(nama)) {
        multiSelected.delete(nama);
        btn.classList.remove('selected');
      } else {
        multiSelected.add(nama);
        btn.classList.add('selected');
      }
      // Update counter
      const label = document.getElementById('komCountLabel');
      if (label) label.textContent = `${multiSelected.size} dipilih`;
      // Enable/disable confirm
      const confirmBtn = document.getElementById('komConfirmBtn');
      if (confirmBtn) confirmBtn.disabled = multiSelected.size === 0;
    };
    grid.appendChild(btn);
  });

  wrap.appendChild(grid);

  // Baris konfirmasi
  const confirmRow = document.createElement('div');
  confirmRow.className = 'komoditas-confirm-row';
  confirmRow.innerHTML = `
    <button class="komoditas-confirm-btn" id="komConfirmBtn" disabled>
      ✓ Lanjutkan
    </button>
    <span style="font-size:11px;color:#94a3b8">Pilih minimal 1 komoditas</span>`;
  confirmRow.querySelector('#komConfirmBtn').onclick = () => confirmKomoditas();
  wrap.appendChild(confirmRow);

  container.appendChild(wrap);
}

function confirmKomoditas() {
  if (!multiSelected.size) {
    appendBotBubble('⚠️ Pilih minimal satu komoditas dulu ya!');
    return;
  }
  wizard.answers.komoditas = Array.from(multiSelected);
  multiSelected.clear();

  // Tampilkan di bubble: maks 5 nama + "dan X lainnya"
  const list = wizard.answers.komoditas;
  const preview = list.length > 5
    ? list.slice(0, 5).join(', ') + ` dan ${list.length - 5} lainnya`
    : list.join(', ');
  appendUserBubble(preview);

  clearQuickReplies();
  updateSummary();
  nextStep();
}

// ── SINGLE CHOICE ────────────────────────────────────
function handleChoice(choice, key) {
  wizard.answers[key] = choice.value;
  const cleanLabel = stripEmojiPrefix(choice.label);
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
        <span>Sedang menganalisis data harga pangan dan menyusun rekomendasi terbaik untuk Anda...</span>
      </div>`);
    kirimKeGroq(loadingDiv);
  }, 400);
}

async function kirimKeGroq(loadingDiv) {
  try {
    const res = await fetch(ROUTE_REKOMENDASI, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({
        periode:   wizard.answers.periode,
        anggota:   wizard.answers.anggota,
        budget:    wizard.answers.budget,
        komoditas: wizard.answers.komoditas,
        prioritas: wizard.answers.prioritas,
      })
    });
    const data = await res.json();
    const bubble = loadingDiv.querySelector('.ai-msg__bubble');
    if (data.success) {
      if (bubble) bubble.innerHTML = formatReply(data.reply);
    } else {
      if (bubble) bubble.innerHTML = `⚠️ ${data.error ?? 'Terjadi kesalahan, coba lagi.'}`;
    }
    scrollBottom();
    showFollowUpButtons();
  } catch (err) {
    console.error('Error:', err);
    const bubble = loadingDiv.querySelector('.ai-msg__bubble');
    if (bubble) bubble.innerHTML = '⚠️ Gagal terhubung ke server. Pastikan Flask ML berjalan.';
    showFollowUpButtons();
  }
}

// ── FORMAT REPLY ─────────────────────────────────────
function formatReply(text) {
  let formatted = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/^#{1,3} (.+)$/gm, '<strong style="font-size:13.5px;color:#1e293b;display:block;margin:10px 0 4px">$1</strong>')
    .replace(/\n\n/g, '</p><p>')
    .replace(/\n/g, '<br>');

  formatted = formatted.replace(/(\|.+\|\n?)+/g, tableBlock => {
    const rawLines = tableBlock.trim().split('\n').map(l => l.trim()).filter(l => l);
    let headerCells = [], dataRows = [];
    rawLines.forEach((line, idx) => {
      const cells = line.split('|').map(c => c.trim()).filter(c => c !== '');
      if (idx === 0) headerCells = cells;
      else if (cells.every(c => /^[-:\s]+$/.test(c))) { /* skip separator */ }
      else dataRows.push(cells);
    });
    if (!dataRows.length) return tableBlock;
    const cards = dataRows.map(row => {
      const items = headerCells.map((header, i) => {
        const val = row[i] ?? '';
        return `<div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0;border-bottom:1px solid #f1f5f9">
          <span style="color:#64748b;font-size:12px;font-weight:500">${header}</span>
          <span style="color:#1e293b;font-size:13px;font-weight:600;text-align:right;max-width:60%">${val}</span>
        </div>`;
      }).join('');
      return `<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;margin-bottom:8px">${items}</div>`;
    }).join('');
    return `<div style="margin:8px 0">${cards}</div>`;
  });

  formatted = formatted.replace(/^- (.+)$/gm, '<li>$1</li>');
  formatted = formatted.replace(/(<li>[\s\S]*?<\/li>\n?)+/g, lis => `<ul>${lis}</ul>`);
  return `<div>${formatted}</div>`;
}

// ── FOLLOW UP BUTTONS ─────────────────────────────────
function showFollowUpButtons() {
  const container = document.getElementById('quickReplies');
  if (!container) return;
  container.innerHTML = '';
  const followUps = [
    { label: '🔄 Coba Budget Berbeda',      action: 'resetStep3'  },
    { label: '📊 Komoditas Murah Sekarang?', action: 'cheapNow'    },
    { label: '📦 Tips Menyimpan Stok?',      action: 'storageTips' },
    { label: '🔁 Mulai Ulang Wizard',        action: 'reset'       },
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
  appendTyping();
  try {
    const res = await fetch(ROUTE_FOLLOWUP, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({ action, komoditas: wizard.answers.komoditas ?? [] })
    });
    const data = await res.json();
    removeTyping();
    if (data.success) appendBotBubble(formatReply(data.reply));
    else appendBotBubble(`⚠️ ${data.error ?? 'Terjadi kesalahan.'}`);
    showFollowUpButtons();
  } catch (err) {
    removeTyping();
    appendBotBubble('⚠️ Gagal terhubung. Coba lagi.');
    showFollowUpButtons();
  }
}

// ── RESET ─────────────────────────────────────────────
function resetWizard() {
  wizard.step              = 1;
  wizard.answers.periode   = null;
  wizard.answers.anggota   = null;
  wizard.answers.budget    = null;
  wizard.answers.komoditas = [];
  wizard.answers.prioritas = null;
  multiSelected.clear();
  document.getElementById('aiMessages').innerHTML    = '';
  document.getElementById('quickReplies').innerHTML  = '';
  document.getElementById('inputWrap').style.display = 'none';
  document.getElementById('summarySection').style.display = 'none';
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

// ── INIT ──────────────────────────────────────────────
function startWizard() {
  updateSidebarSteps(1);
  appendBotBubble(`<p>${steps[1].question}</p>`);
  renderChoices(steps[1]);
}

document.addEventListener('DOMContentLoaded', () => startWizard());
</script>
@endpush
