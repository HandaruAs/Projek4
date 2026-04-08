{{--
  =====================================================
  SIMOPANG — Tentang / AI Chat
  File : resources/views/user/tentang.blade.php
  Desc : Halaman chat interaktif dengan AI SIMOPANG
  =====================================================
--}}
@extends('layouts.layout')

@section('title', 'Tanya AI SIMOPANG')

@section('content')

  {{-- ── BREADCRUMB ─────────────────────────────────── --}}
  <nav class="u-breadcrumb">
    <a href="{{ route('user.home') }}">Beranda</a>
    <span class="u-breadcrumb__sep">/</span>
    <span class="u-breadcrumb__current">Tanya AI</span>
  </nav>

  {{-- ── CHAT LAYOUT ─────────────────────────────────── --}}
  <div class="ai-chat-layout">

    {{-- ── SIDEBAR INFO ─────────────────────────────── --}}
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

      <div class="ai-sidebar-section">
        <div class="ai-sidebar-section__label">Tentang AI Ini</div>
        <p class="ai-sidebar-section__text">
          Saya adalah asisten AI SIMOPANG yang siap menjawab pertanyaan seputar harga komoditas,
          prediksi pasar, dan informasi pangan nasional.
        </p>
      </div>

      <div class="ai-sidebar-section">
        <div class="ai-sidebar-section__label">Coba Tanyakan</div>
        <div class="ai-suggestion-list">
          <button class="ai-suggestion" onclick="sendSuggestion(this)">
            📈 Bagaimana tren harga beras bulan ini?
          </button>
          <button class="ai-suggestion" onclick="sendSuggestion(this)">
            🌶️ Kenapa harga cabai sering naik?
          </button>
          <button class="ai-suggestion" onclick="sendSuggestion(this)">
            🔮 Apa itu model prediksi Prophet AI?
          </button>
          <button class="ai-suggestion" onclick="sendSuggestion(this)">
            🛒 Tips belanja hemat saat harga naik?
          </button>
          <button class="ai-suggestion" onclick="sendSuggestion(this)">
            📊 Komoditas apa yang paling fluktuatif?
          </button>
        </div>
      </div>

      <button class="ai-clear-btn" onclick="clearChat()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="1 4 1 10 7 10"/>
          <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
        </svg>
        Reset Percakapan
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
            <div class="ai-chat-header__name">SIMOPANG AI Assistant</div>
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
      <div class="ai-messages" id="aiMessages">

        {{-- Welcome message --}}
        <div class="ai-msg ai-msg--bot" id="welcomeMsg">
          <div class="ai-msg__avatar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2a4 4 0 014 4v1h1a3 3 0 013 3v7a3 3 0 01-3 3H7a3 3 0 01-3-3V10a3 3 0 013-3h1V6a4 4 0 014-4z"/>
              <circle cx="9" cy="13" r="1" fill="currentColor"/>
              <circle cx="15" cy="13" r="1" fill="currentColor"/>
            </svg>
          </div>
          <div class="ai-msg__content">
            <div class="ai-msg__bubble">
              <p>Halo! Saya <strong>SIMOPANG AI</strong> 👋</p>
              <p>Saya siap membantu Anda dengan informasi seputar:</p>
              <ul>
                <li>📊 Harga & tren komoditas pangan</li>
                <li>🔮 Prediksi harga menggunakan AI Prophet</li>
                <li>💡 Tips perencanaan belanja & stok</li>
                <li>🌾 Informasi ketahanan pangan nasional</li>
              </ul>
              <p>Silakan tanyakan apa saja!</p>
            </div>
            <div class="ai-msg__time">Sekarang</div>
          </div>
        </div>

      </div>

      {{-- Input Area --}}
      <div class="ai-chat-input-wrap">
        <div class="ai-chat-input-box" id="inputBox">
          <textarea
            class="ai-chat-textarea"
            id="chatInput"
            placeholder="Tanyakan sesuatu tentang harga komoditas, prediksi, atau tips belanja..."
            rows="1"
            onkeydown="handleKey(event)"
            oninput="autoResize(this)"
          ></textarea>
          <button class="ai-send-btn" id="sendBtn" onclick="sendMessage()">
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
  const SYSTEM_PROMPT = `Kamu adalah SIMOPANG AI, asisten cerdas untuk Sistem Monitoring Pangan Nasional Indonesia.
Tugasmu adalah membantu pengguna memahami:
- Tren dan fluktuasi harga komoditas pangan (beras, cabai, bawang, telur, dll)
- Prediksi harga menggunakan model AI Prophet
- Tips perencanaan belanja dan manajemen stok rumah tangga
- Informasi ketahanan pangan dan kebijakan pangan nasional
- Faktor-faktor yang mempengaruhi harga pangan (musim, cuaca, distribusi, dll)

Jawab dalam Bahasa Indonesia yang ramah, informatif, dan mudah dipahami.
Gunakan emoji secara wajar untuk membuat jawaban lebih menarik.
Jika ditanya di luar topik pangan, arahkan kembali ke topik yang relevan.
Berikan jawaban yang singkat, padat, dan berguna. Maksimal 3-4 paragraf kecuali diminta lebih detail.`;

  let conversationHistory = [];
  let isLoading = false;

  function getTime() {
    return new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  }

  function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 160) + 'px';
  }

  function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  }

  function sendSuggestion(btn) {
    const text = btn.textContent.trim().replace(/^[^\s]+\s/, '');
    document.getElementById('chatInput').value = text;
    sendMessage();
  }

  function appendMessage(role, text) {
    const container = document.getElementById('aiMessages');
    const isBot = role === 'bot';
    const div = document.createElement('div');
    div.className = `ai-msg ai-msg--${role}`;

    if (isBot) {
      div.innerHTML = `
        <div class="ai-msg__avatar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2a4 4 0 014 4v1h1a3 3 0 013 3v7a3 3 0 01-3 3H7a3 3 0 01-3-3V10a3 3 0 013-3h1V6a4 4 0 014-4z"/>
            <circle cx="9" cy="13" r="1" fill="currentColor"/>
            <circle cx="15" cy="13" r="1" fill="currentColor"/>
          </svg>
        </div>
        <div class="ai-msg__content">
          <div class="ai-msg__bubble">${formatText(text)}</div>
          <div class="ai-msg__time">${getTime()}</div>
        </div>`;
    } else {
      div.innerHTML = `
        <div class="ai-msg__content">
          <div class="ai-msg__bubble">${escapeHtml(text)}</div>
          <div class="ai-msg__time">${getTime()}</div>
        </div>`;
    }

    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
    return div;
  }

  function appendTyping() {
    const container = document.getElementById('aiMessages');
    const div = document.createElement('div');
    div.className = 'ai-msg ai-msg--bot ai-msg--typing';
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
    container.scrollTop = container.scrollHeight;
  }

  function removeTyping() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
  }

  function formatText(text) {
    return text
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.*?)\*/g, '<em>$1</em>')
      .replace(/^- (.+)$/gm, '<li>$1</li>')
      .replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>')
      .replace(/\n\n/g, '</p><p>')
      .replace(/\n/g, '<br>')
      .replace(/^(.+)$/, '<p>$1</p>');
  }

  function escapeHtml(text) {
    return text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
  }

  async function sendMessage() {
    const input = document.getElementById('chatInput');
    const text  = input.value.trim();
    if (!text || isLoading) return;

    isLoading = true;
    input.value = '';
    input.style.height = 'auto';
    document.getElementById('sendBtn').disabled = true;

    appendMessage('user', text);
    conversationHistory.push({ role: 'user', content: text });

    appendTyping();

    try {
      const res = await fetch('https://api.anthropic.com/v1/messages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          model: 'claude-sonnet-4-20250514',
          max_tokens: 1000,
          system: SYSTEM_PROMPT,
          messages: conversationHistory
        })
      });

      const data = await res.json();
      removeTyping();

      const reply = data.content?.[0]?.text ?? 'Maaf, terjadi kesalahan. Coba lagi ya.';
      conversationHistory.push({ role: 'assistant', content: reply });
      appendMessage('bot', reply);

    } catch (err) {
      removeTyping();
      appendMessage('bot', '⚠️ Gagal terhubung ke AI. Periksa koneksi dan coba lagi.');
    }

    isLoading = false;
    document.getElementById('sendBtn').disabled = false;
    input.focus();
  }

  function clearChat() {
    conversationHistory = [];
    const container = document.getElementById('aiMessages');
    container.innerHTML = '';

    const welcome = document.createElement('div');
    welcome.className = 'ai-msg ai-msg--bot';
    welcome.innerHTML = document.getElementById('welcomeMsg')?.innerHTML ?? '';
    container.appendChild(welcome);
  }
</script>
@endpush