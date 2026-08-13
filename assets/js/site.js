/* ==========================================================================
   DH Studio — gedrag
   Progressieve verbetering: zonder dit bestand blijft de pagina volledig
   leesbaar en bruikbaar. Geen dependencies.
   ========================================================================== */

(function () {
  "use strict";

  var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
  var $$ = function (sel, ctx) {
    return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
  };

  /* ----------------------------------------------------------------------
     1. Jaartal
     ---------------------------------------------------------------------- */

  var year = $("#year");
  if (year) year.textContent = String(new Date().getFullYear());

  /* ----------------------------------------------------------------------
     2. Header — vaste staat zodra je scrollt
     ---------------------------------------------------------------------- */

  var header = $("#header");
  var stuck = false;

  function onScrollHeader() {
    var next = window.scrollY > 12;
    if (next !== stuck) {
      stuck = next;
      header.classList.toggle("is-stuck", stuck);
    }
  }

  /* ----------------------------------------------------------------------
     3. Mobiel menu
     ---------------------------------------------------------------------- */

  var burger = $("#burger");
  var menu = $("#menu");
  var lastFocus = null;

  function setMenu(open) {
    if (!menu || !burger) return;
    burger.setAttribute("aria-expanded", String(open));
    burger.setAttribute("aria-label", open ? "Menu sluiten" : "Menu openen");
    document.documentElement.style.overflow = open ? "hidden" : "";

    if (open) {
      lastFocus = document.activeElement;
      menu.hidden = false;
      // Forceer een frame zodat de transitie loopt.
      requestAnimationFrame(function () { menu.classList.add("is-open"); });
    } else {
      menu.classList.remove("is-open");
      var hide = function () { menu.hidden = true; };
      reduced ? hide() : window.setTimeout(hide, 400);
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
  }

  if (burger && menu) {
    burger.addEventListener("click", function () {
      setMenu(burger.getAttribute("aria-expanded") !== "true");
    });

    menu.addEventListener("click", function (e) {
      if (e.target.closest("a")) setMenu(false);
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && burger.getAttribute("aria-expanded") === "true") {
        setMenu(false);
      }
    });

    // Menu is een overlay: sluit hem als het scherm breed genoeg wordt.
    window.matchMedia("(min-width: 64em)").addEventListener("change", function (e) {
      if (e.matches) setMenu(false);
    });
  }

  /* ----------------------------------------------------------------------
     4. Reveal on scroll
     ---------------------------------------------------------------------- */

  var revealables = $$(".reveal");

  if ("IntersectionObserver" in window && revealables.length) {
    var revealObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-in");
          revealObserver.unobserve(entry.target);
        });
      },
      { rootMargin: "0px 0px -12% 0px", threshold: 0.05 }
    );
    revealables.forEach(function (el) { revealObserver.observe(el); });

    // Vangnet: mocht de observer om wat voor reden dan ook niet afgaan, dan
    // mag content nooit onzichtbaar blijven staan.
    window.setTimeout(function () {
      revealables.forEach(function (el) {
        var r = el.getBoundingClientRect();
        if (r.top < window.innerHeight * 1.5) el.classList.add("is-in");
      });
    }, 2500);
  } else {
    revealables.forEach(function (el) { el.classList.add("is-in"); });
  }

  /* ----------------------------------------------------------------------
     5. Outcomes — welke regel is 'aan'
     ---------------------------------------------------------------------- */

  var outcomes = $$("[data-outcome]");

  if ("IntersectionObserver" in window && outcomes.length) {
    // Eenrichtingsverkeer: regels lichten op zodra je ze bereikt en blijven
    // daarna staan. Een 'spotlight' die alles eromheen dimt leest prettig in
    // een demo, maar maakt de regel die je nét las weer onleesbaar.
    var outcomeObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-active");
          outcomeObserver.unobserve(entry.target);
        });
      },
      { rootMargin: "0px 0px -22% 0px", threshold: 0 }
    );
    outcomes.forEach(function (el) { outcomeObserver.observe(el); });
  } else {
    outcomes.forEach(function (el) { el.classList.add("is-active"); });
  }

  /* ----------------------------------------------------------------------
     6. Werkwijze — lijn die meeloopt met de scroll
     ---------------------------------------------------------------------- */

  var process = $("#process");
  var fill = $("#processFill");
  var steps = $$("[data-step]");

  if ("IntersectionObserver" in window && steps.length) {
    var stepObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) entry.target.classList.add("is-in");
        });
      },
      { rootMargin: "0px 0px -45% 0px", threshold: 0 }
    );
    steps.forEach(function (el) { stepObserver.observe(el); });
  } else {
    steps.forEach(function (el) { el.classList.add("is-in"); });
  }

  /* ----------------------------------------------------------------------
     7. Scroll-afhankelijke effecten, gebundeld in één rAF-lus
     ---------------------------------------------------------------------- */

  var glow = $("#heroGlow");
  var ticking = false;

  function frame() {
    ticking = false;

    onScrollHeader();

    if (!reduced) {
      // Parallax op de hero-gloed — alleen zolang de hero in beeld is.
      if (glow && window.scrollY < window.innerHeight * 1.2) {
        glow.style.transform =
          "translate(-50%, " + Math.round(window.scrollY * 0.14) + "px)";
      }

      // Voortgang van de werkwijze-lijn.
      if (process && fill) {
        var rect = process.getBoundingClientRect();
        var anchor = window.innerHeight * 0.62;
        var p = (anchor - rect.top) / rect.height;
        fill.style.setProperty("--p", Math.max(0, Math.min(1, p)).toFixed(3));
      }
    }
  }

  function requestFrame() {
    if (!ticking) {
      ticking = true;
      requestAnimationFrame(frame);
    }
  }

  window.addEventListener("scroll", requestFrame, { passive: true });
  window.addEventListener("resize", requestFrame, { passive: true });
  frame();

  /* ----------------------------------------------------------------------
     8. Actieve sectie in de navigatie
     ---------------------------------------------------------------------- */

  var navLinks = $$(".nav__link");
  var sections = navLinks
    .map(function (a) { return document.getElementById(a.hash.slice(1)); })
    .filter(Boolean);

  if ("IntersectionObserver" in window && sections.length) {
    var visible = new Set();

    var navObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          entry.isIntersecting ? visible.add(entry.target.id) : visible.delete(entry.target.id);
        });

        // De bovenste zichtbare sectie wint.
        var current = sections.filter(function (s) { return visible.has(s.id); })[0];
        navLinks.forEach(function (a) {
          a.classList.toggle("is-active", !!current && a.hash === "#" + current.id);
        });
      },
      { rootMargin: "-45% 0px -50% 0px", threshold: 0 }
    );
    sections.forEach(function (s) { navObserver.observe(s); });
  }

  /* ----------------------------------------------------------------------
     9. Het bewijs — echte cijfers uit de browser van de bezoeker
     ---------------------------------------------------------------------- */

  function animateTo(el, value, suffix) {
    var target = Number(value);
    if (!isFinite(target)) return;

    if (reduced || target <= 0) {
      el.textContent = target + (suffix || "");
      return;
    }

    var start = performance.now();
    var duration = 900;

    (function tick(now) {
      var t = Math.min(1, (now - start) / duration);
      // easeOutExpo
      var eased = t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
      el.textContent = Math.round(target * eased) + (suffix || "");
      if (t < 1) requestAnimationFrame(tick);
    })(start);
  }

  function readMetrics() {
    var nav = (performance.getEntriesByType &&
      performance.getEntriesByType("navigation")[0]) || null;

    // Tijd tot de pagina leesbaar is.
    var ms = nav
      ? Math.round(nav.domContentLoadedEventEnd)
      : Math.round(performance.now());

    // Alles wat de browser voor deze pagina heeft opgehaald.
    var bytes = 0;
    var measurable = true;

    if (nav && typeof nav.transferSize === "number") {
      bytes += nav.transferSize;
    } else {
      measurable = false;
    }

    (performance.getEntriesByType ? performance.getEntriesByType("resource") : [])
      .forEach(function (r) {
        if (typeof r.transferSize === "number") bytes += r.transferSize;
      });

    return {
      ms: ms > 0 ? ms : null,
      kb: measurable && bytes > 0 ? Math.round(bytes / 1024) : null
    };
  }

  function paintMetrics() {
    var m = readMetrics();
    var msEl = $('[data-metric="ms"]');
    var kbEl = $('[data-metric="kb"]');
    var heroWrap = $("#heroTiming");
    var heroMs = $("#heroMs");

    if (msEl && m.ms) animateTo(msEl, m.ms);
    if (kbEl && m.kb) animateTo(kbEl, m.kb);

    // De meting kan door caching of privacy-instellingen ontbreken:
    // dan tonen we hem gewoon niet, in plaats van een verzonnen getal.
    if (heroWrap && heroMs && m.ms) {
      heroMs.textContent = String(m.ms);
      heroWrap.hidden = false;
    }

    if (msEl && !m.ms) msEl.closest(".metric").hidden = true;
    if (kbEl && !m.kb) kbEl.closest(".metric").hidden = true;
  }

  // Wacht tot de laadcijfers definitief zijn.
  if (document.readyState === "complete") {
    window.setTimeout(paintMetrics, 0);
  } else {
    window.addEventListener("load", function () {
      window.setTimeout(paintMetrics, 60);
    });
  }

  /* ----------------------------------------------------------------------
     10. Toast
     ---------------------------------------------------------------------- */

  var toast = $("#toast");
  var toastTimer;

  function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add("is-on");
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(function () {
      toast.classList.remove("is-on");
    }, 2600);
  }

  /* ----------------------------------------------------------------------
     11. E-mailadres kopiëren
     ---------------------------------------------------------------------- */

  var copyBtn = $("#copyMail");

  if (copyBtn) {
    copyBtn.addEventListener("click", function () {
      var mail = copyBtn.dataset.mail;

      var done = function () { showToast("E-mailadres gekopieerd"); };
      var failed = function () { showToast("Kopiëren lukt niet — selecteer het adres handmatig"); };

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(mail).then(done, failed);
      } else {
        failed();
      }
    });
  }

  /* ----------------------------------------------------------------------
     12. Pakket-knoppen vullen het formulier voor
     ---------------------------------------------------------------------- */

  var messageField = $("#f-bericht");

  $$("[data-plan]").forEach(function (link) {
    link.addEventListener("click", function () {
      if (!messageField) return;
      var plan = link.dataset.plan;

      // Alleen invullen als de bezoeker zelf nog niets heeft getypt.
      if (!messageField.value.trim()) {
        messageField.value = "Ik heb interesse in: " + plan + ".\n\n";
      }

      var radio = $$('input[name="onderwerp"]').filter(function (r) {
        return /onderhoud/i.test(plan) ? /Onderhoud/i.test(r.value) : false;
      })[0];
      if (radio) radio.checked = true;

      window.setTimeout(function () {
        messageField.focus({ preventScroll: true });
        messageField.setSelectionRange(messageField.value.length, messageField.value.length);
      }, 700);
    });
  });

  /* ----------------------------------------------------------------------
     13. Contactformulier
     ---------------------------------------------------------------------- */

  var form = $("#contactForm");

  if (form) {
    var status = $("#formStatus");

    var rules = [
      { id: "f-naam", err: "err-naam", msg: "Vul je naam in." },
      { id: "f-email", err: "err-email", msg: "Vul een geldig e-mailadres in." },
      { id: "f-bericht", err: "err-bericht", msg: "Vertel kort waar het over gaat." }
    ];

    function validateField(rule) {
      var field = document.getElementById(rule.id);
      var errEl = document.getElementById(rule.err);
      var value = field.value.trim();
      var ok = field.type === "email"
        ? /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value)
        : value.length > 0;

      field.setAttribute("aria-invalid", ok ? "false" : "true");
      if (errEl) errEl.textContent = ok ? "" : rule.msg;
      return ok;
    }

    // Pas na de eerste poging live meevalideren — anders straft het formulier
    // je terwijl je nog aan het typen bent.
    var attempted = false;

    rules.forEach(function (rule) {
      var field = document.getElementById(rule.id);
      field.addEventListener("input", function () {
        if (attempted) validateField(rule);
      });
      field.addEventListener("blur", function () {
        if (attempted) validateField(rule);
      });
    });

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      attempted = true;

      var results = rules.map(validateField);
      var firstBad = rules[results.indexOf(false)];

      if (firstBad) {
        status.dataset.tone = "error";
        status.textContent = "Nog even: er ontbreekt iets hierboven.";
        document.getElementById(firstBad.id).focus();
        return;
      }

      var data = new FormData(form);
      var get = function (k) { return String(data.get(k) || "").trim(); };

      var lines = [
        "Naam: " + get("naam"),
        "Bedrijf: " + (get("bedrijf") || "—"),
        "E-mail: " + get("email"),
        "Telefoon: " + (get("telefoon") || "—"),
        "Onderwerp: " + (get("onderwerp") || "—"),
        "",
        get("bericht")
      ];

      var href =
        "mailto:info@dh-studio.nl" +
        "?subject=" + encodeURIComponent("Aanvraag via dh-studio.nl — " + get("naam")) +
        "&body=" + encodeURIComponent(lines.join("\n"));

      status.dataset.tone = "ok";
      status.textContent =
        "Je e-mailprogramma opent nu met je bericht. Nog één keer op verzenden en hij is onderweg.";

      window.location.href = href;
    });
  }
})();
