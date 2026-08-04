/* ============================================================
 * Centric Ecosystem — Lightweight client-side i18n (ID default / EN)
 * ============================================================ */
(function () {
  'use strict';

  const STORAGE_KEY = 'cc-lang';
  const DEFAULT_LANG = 'id';
  const SUPPORTED = ['id', 'en', 'ko'];

  const dicts = {};
  let currentLang = null;
  let readyResolve;

  const readyPromise = new Promise(function (resolve) { readyResolve = resolve; });

  function detectLang() {
    try {
      const params = new URLSearchParams(window.location.search);
      const urlLang = params.get('lang');
      if (urlLang && SUPPORTED.indexOf(urlLang) !== -1) return urlLang;
    } catch (e) {}
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved && SUPPORTED.indexOf(saved) !== -1) return saved;
    } catch (e) {}
    return DEFAULT_LANG;
  }

  function currentPage() {
    return document.body.getAttribute('data-page') || 'index';
  }

  function t(key) {
    const dict = dicts[currentLang] || {};
    if (dict[key] != null) return dict[key];
    const fallback = dicts[DEFAULT_LANG] || {};
    if (fallback[key] != null) return fallback[key];
    return key;
  }

  function applyTranslations() {
    const dict = dicts[currentLang] || {};

    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      const key = el.getAttribute('data-i18n');
      if (dict[key] != null) el.textContent = dict[key];
    });
    document.querySelectorAll('[data-i18n-html]').forEach(function (el) {
      const key = el.getAttribute('data-i18n-html');
      if (dict[key] != null) el.innerHTML = dict[key];
    });
    document.querySelectorAll('[data-i18n-aria]').forEach(function (el) {
      const key = el.getAttribute('data-i18n-aria');
      if (dict[key] != null) el.setAttribute('aria-label', dict[key]);
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {
      const key = el.getAttribute('data-i18n-placeholder');
      if (dict[key] != null) el.setAttribute('placeholder', dict[key]);
    });
  }

  function setMeta(selector, attr, value) {
    if (value == null) return;
    const el = document.querySelector(selector);
    if (el) el.setAttribute(attr, value);
  }

  function updateMeta() {
    const page = currentPage();
    const dict = dicts[currentLang] || {};
    const title = dict[page + '.meta.title'];
    const desc = dict[page + '.meta.description'];

    document.documentElement.setAttribute('lang', currentLang);

    if (title) document.title = title;
    setMeta('meta[name="description"]', 'content', desc);
    setMeta('meta[property="og:title"]', 'content', title);
    setMeta('meta[property="og:description"]', 'content', desc);
    setMeta('meta[name="twitter:title"]', 'content', title);
    setMeta('meta[name="twitter:description"]', 'content', desc);
    const localeMap = { en: 'en_US', ko: 'ko_KR', id: 'id_ID' };
    setMeta('meta[property="og:locale"]', 'content', localeMap[currentLang] || 'id_ID');

    document.querySelectorAll('script[type="application/ld+json"]').forEach(function (script) {
      try {
        const obj = JSON.parse(script.textContent);
        const roots = obj['@graph'] || [obj];
        let changed = false;

        function walk(node) {
          if (!node || typeof node !== 'object') return;
          if (Array.isArray(node)) { node.forEach(walk); return; }
          if (node.inLanguage !== undefined && !Array.isArray(node.inLanguage)) {
            node.inLanguage = currentLang;
            changed = true;
          }
          if (node['@type'] === 'WebPage') {
            if (title && node.name) { node.name = title; changed = true; }
            if (desc && node.description) { node.description = desc; changed = true; }
          }
          Object.keys(node).forEach(function (k) { walk(node[k]); });
        }

        roots.forEach(walk);
        if (changed) script.textContent = JSON.stringify(obj);
      } catch (e) {}
    });

    let canonical = document.querySelector('link[rel="canonical"]');
    const base = canonical ? canonical.href : window.location.origin + window.location.pathname;
    document.querySelectorAll('link[rel="alternate"][hreflang]').forEach(function (l) {
      if (SUPPORTED.indexOf(l.hreflang) !== -1) l.remove();
    });
    SUPPORTED.forEach(function (lg) {
      const link = document.createElement('link');
      link.rel = 'alternate';
      link.hreflang = lg;
      link.href = lg === DEFAULT_LANG ? base : base + '?lang=' + lg;
      document.head.appendChild(link);
    });

    document.querySelectorAll('.lang-btn').forEach(function (btn) {
      const active = btn.getAttribute('data-lang') === currentLang;
      btn.classList.toggle('active', active);
      btn.setAttribute('aria-pressed', String(active));
    });
  }

  async function setLang(lang, opts) {
    opts = opts || {};
    if (SUPPORTED.indexOf(lang) === -1) lang = DEFAULT_LANG;
    if (opts.persist !== false) {
      try { localStorage.setItem(STORAGE_KEY, lang); } catch (e) {}
    }

    if (!dicts[lang]) {
      try {
        const res = await fetch('data/i18n/' + lang + '.json', { cache: 'no-store' });
        if (!res.ok) throw new Error('load failed');
        dicts[lang] = await res.json();
      } catch (e) {
        if (!dicts[DEFAULT_LANG]) {
          try {
            const res = await fetch('data/i18n/' + DEFAULT_LANG + '.json', { cache: 'no-store' });
            if (res.ok) dicts[DEFAULT_LANG] = await res.json();
          } catch (e2) {}
        }
        if (lang !== DEFAULT_LANG) {
          currentLang = DEFAULT_LANG;
          updateMeta();
          applyTranslations();
          document.dispatchEvent(new CustomEvent('cc:langchange', { detail: { lang: DEFAULT_LANG } }));
          readyResolve();
          return;
        }
      }
    }

    currentLang = lang;
    updateMeta();
    applyTranslations();
    document.dispatchEvent(new CustomEvent('cc:langchange', { detail: { lang: lang } }));
    readyResolve();
  }

  function initSwitcher() {
    document.querySelectorAll('.lang-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        setLang(btn.getAttribute('data-lang'));
      });
    });
  }

  function init() {
    initSwitcher();
    const lang = detectLang();
    currentLang = lang;
    setLang(lang, { persist: false });
  }

  window.ccI18n = {
    setLang: setLang,
    getLang: function () { return currentLang || detectLang(); },
    t: t,
    readyPromise: readyPromise,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
