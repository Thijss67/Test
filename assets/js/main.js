/* =========================================================================
   DH Studio — interactie
   Geen dependencies. Alles transform/opacity, dus geen layout shift.
   Elk effect degradeert netjes zonder JS of bij prefers-reduced-motion.
   ========================================================================= */

(function () {
  "use strict";

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ---------------------------------------------------------------------
     1. Jaartal
     --------------------------------------------------------------------- */
  var yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = String(new Date().getFullYear());

  /* ---------------------------------------------------------------------
     2. Scroll reveal
     --------------------------------------------------------------------- */
  var revealables = document.querySelectorAll(".reveal");

  if (reduceMotion || !("IntersectionObserver" in window)) {
    revealables.forEach(function (el) { el.classList.add("is-visible"); });
  } else {
    var revealObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-visible");
          revealObserver.unobserve(entry.target);
        });
      },
      { rootMargin: "0px 0px -12% 0px", threshold: 0.1 }
    );

    revealables.forEach(function (el) {
      var rect = el.getBoundingClientRect();
      // Staat het al in beeld bij het laden? Dan meteen tonen. De rootMargin
      // hierboven sluit de onderste rand van het scherm uit, waardoor zulke
      // elementen anders pas bij scrollen zouden verschijnen.
      if (rect.top < window.innerHeight && rect.bottom > 0) {
        el.classList.add("is-visible");
      } else {
        revealObserver.observe(el);
      }
    });
  }

  /* ---------------------------------------------------------------------
     3. Header: vastzetten, voortgang, en meekleuren met de sectie eronder
     --------------------------------------------------------------------- */
  var header = document.getElementById("siteHeader");
  var progress = document.getElementById("headerProgress");
  var darkSections = Array.prototype.slice.call(
    document.querySelectorAll(".contact, .site-footer")
  );

  // Vaste actiebalk: verschijnt voorbij de hero, verdwijnt bij het
  // contactformulier — daar staat de actie zelf al op het scherm.
  var stickyCta = document.getElementById("stickyCta");
  var contactSection = document.querySelector(".contact");

  function updateStickyCta(y) {
    if (!stickyCta) return;
    var heroEl = document.querySelector(".hero");
    var past = heroEl ? y > heroEl.offsetHeight * 0.6 : y > 600;
    var atContact = contactSection
      ? y + window.innerHeight > contactSection.offsetTop + 120
      : false;
    stickyCta.classList.toggle("is-visible", past && !atContact);
  }

  function updateHeader() {
    var y = window.scrollY || window.pageYOffset;

    header.classList.toggle("is-stuck", y > 12);
    updateStickyCta(y);

    // Voortgangsbalk
    if (progress) {
      var doc = document.documentElement;
      var max = doc.scrollHeight - window.innerHeight;
      var ratio = max > 0 ? Math.min(y / max, 1) : 0;
      progress.style.transform = "scaleX(" + ratio + ")";
    }

    // Welke sectie zit er onder de header? Bepaalt tekstkleur van de header.
    var probe = y + 36;
    var overDark = darkSections.some(function (section) {
      var top = section.offsetTop;
      return probe >= top && probe < top + section.offsetHeight;
    });
    header.setAttribute("data-theme", overDark ? "dark" : "light");
  }

  /* ---------------------------------------------------------------------
     4. Parallax — subtiel, alleen via de --py custom property
     --------------------------------------------------------------------- */
  var parallaxEls = Array.prototype.slice.call(document.querySelectorAll("[data-parallax]"));

  function updateParallax() {
    if (reduceMotion) return;
    var viewportH = window.innerHeight;

    parallaxEls.forEach(function (el) {
      var rect = el.getBoundingClientRect();
      if (rect.bottom < -200 || rect.top > viewportH + 200) return;
      var factor = parseFloat(el.getAttribute("data-parallax")) || 0;
      // Afstand vanaf het midden van de viewport
      var offset = rect.top + rect.height / 2 - viewportH / 2;
      el.style.setProperty("--py", (offset * factor).toFixed(2) + "px");
    });
  }

  /* ---------------------------------------------------------------------
     5. Statement: woorden lichten op tijdens het scrollen
     --------------------------------------------------------------------- */
  var wordHost = document.querySelector(".word-reveal");
  var words = [];

  if (wordHost) {
    var source = wordHost.textContent.trim().split(/\s+/);
    wordHost.textContent = "";
    source.forEach(function (word, i) {
      var span = document.createElement("span");
      span.textContent = word;
      wordHost.appendChild(span);
      if (i < source.length - 1) wordHost.appendChild(document.createTextNode(" "));
      words.push(span);
    });
    if (reduceMotion) words.forEach(function (w) { w.classList.add("is-lit"); });
  }

  function updateWords() {
    if (!words.length || reduceMotion) return;
    var section = document.querySelector(".statement");
    if (!section) return;

    var rect = section.getBoundingClientRect();
    var vh = window.innerHeight;
    // 0 wanneer de sectie onderin komt, 1 wanneer hij ruim in beeld staat
    var raw = (vh * 0.85 - rect.top) / (vh * 0.55);
    var ratio = Math.max(0, Math.min(1, raw));
    var lit = Math.round(ratio * words.length);

    words.forEach(function (w, i) {
      w.classList.toggle("is-lit", i < lit);
    });
  }

  /* ---------------------------------------------------------------------
     5b. Ons werk: de screenshot scrollt mee met de bezoeker
         De afbeelding schuift binnen een vast venster, dus er verandert
         niets aan de layout. Ontbreekt de afbeelding, dan verschijnt een
         nette terugvaloptie in plaats van een gebroken beeld.
     --------------------------------------------------------------------- */
  var caseFrame = document.getElementById("caseFrame");
  var caseShot = document.getElementById("caseShot");
  var caseFallback = document.getElementById("caseFallback");

  function showCaseFallback() {
    if (caseShot) caseShot.hidden = true;
    if (caseFallback) caseFallback.hidden = false;
  }

  if (caseShot) {
    caseShot.addEventListener("error", showCaseFallback);
    // Al mislukt voordat de listener bestond?
    if (caseShot.complete && caseShot.naturalWidth === 0) showCaseFallback();
  }

  function updateCase() {
    if (!caseFrame || !caseShot || caseShot.hidden) return;

    var view = caseFrame.querySelector(".case-view");
    if (!view) return;

    var travel = caseShot.offsetHeight - view.clientHeight;
    if (travel <= 0) return; // screenshot past al in het venster

    var rect = caseFrame.getBoundingClientRect();
    var vh = window.innerHeight;

    // 0 zodra het frame onderin verschijnt, 1 wanneer het bovenlangs vertrekt
    var raw = (vh - rect.top) / (vh + rect.height);
    var progress = Math.max(0, Math.min(1, raw));

    caseShot.style.setProperty("--shot-y", (-travel * progress).toFixed(1) + "px");
  }

  /* ---------------------------------------------------------------------
     6. Eén rAF-lus voor alle scroll-afhankelijke effecten
     --------------------------------------------------------------------- */
  var ticking = false;

  function onScroll() {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(function () {
      updateHeader();
      updateParallax();
      updateWords();
      updateCase();
      ticking = false;
    });
  }

  window.addEventListener("scroll", onScroll, { passive: true });
  window.addEventListener("resize", onScroll, { passive: true });
  onScroll();

  /* ---------------------------------------------------------------------
     7. Mobiel menu
     --------------------------------------------------------------------- */
  var menuToggle = document.getElementById("menuToggle");
  var mobileMenu = document.getElementById("mobileMenu");

  function setMenu(open) {
    if (!menuToggle || !mobileMenu) return;
    menuToggle.setAttribute("aria-expanded", open ? "true" : "false");
    menuToggle.querySelector(".sr-only").textContent = open ? "Menu sluiten" : "Menu openen";
    document.body.style.overflow = open ? "hidden" : "";

    if (open) {
      mobileMenu.hidden = false;
      // Forceer een reflow zodat de transitie draait
      void mobileMenu.offsetWidth;
      mobileMenu.classList.add("is-open");
    } else {
      mobileMenu.classList.remove("is-open");
      var done = function () {
        mobileMenu.hidden = true;
        mobileMenu.removeEventListener("transitionend", done);
      };
      if (reduceMotion) done();
      else mobileMenu.addEventListener("transitionend", done);
    }
  }

  if (menuToggle && mobileMenu) {
    menuToggle.addEventListener("click", function () {
      setMenu(menuToggle.getAttribute("aria-expanded") !== "true");
    });
    mobileMenu.addEventListener("click", function (e) {
      if (e.target.closest("a")) setMenu(false);
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && menuToggle.getAttribute("aria-expanded") === "true") {
        setMenu(false);
        menuToggle.focus();
      }
    });
  }

  /* ---------------------------------------------------------------------
     8. FAQ — soepel open- en dichtklappen (details blijft de bron van waarheid)
     --------------------------------------------------------------------- */
  var faqItems = Array.prototype.slice.call(document.querySelectorAll(".faq-item"));

  faqItems.forEach(function (item) {
    var summary = item.querySelector("summary");
    var answer = item.querySelector(".faq-answer");
    if (!summary || !answer) return;

    var animation = null;

    function expand() {
      item.open = true;
      var end = answer.scrollHeight;
      if (animation) animation.cancel();
      animation = answer.animate(
        { height: ["0px", end + "px"], opacity: [0, 1] },
        { duration: reduceMotion ? 0 : 380, easing: "cubic-bezier(0.16, 1, 0.3, 1)" }
      );
      animation.onfinish = function () { answer.style.height = ""; animation = null; };
    }

    function collapse() {
      var start = answer.scrollHeight;
      if (animation) animation.cancel();
      animation = answer.animate(
        { height: [start + "px", "0px"], opacity: [1, 0] },
        { duration: reduceMotion ? 0 : 300, easing: "cubic-bezier(0.16, 1, 0.3, 1)" }
      );
      animation.onfinish = function () {
        item.open = false;
        answer.style.height = "";
        animation = null;
      };
    }

    summary.addEventListener("click", function (e) {
      e.preventDefault();

      if (item.open) {
        collapse();
        return;
      }

      // Exclusief gedrag: sluit een eventueel ander open item netjes af
      faqItems.forEach(function (other) {
        if (other !== item && other.open) {
          var otherSummary = other.querySelector("summary");
          if (otherSummary) otherSummary.click();
        }
      });

      expand();
    });
  });

  /* ---------------------------------------------------------------------
     9. Contactformulier
        Geen backend op een statische site. Standaard opent het formulier
        een vooringevulde mail. Zodra er een endpoint is, zet je op het
        <form> een data-endpoint="https://..." en wordt er gepost.
     --------------------------------------------------------------------- */
  var form = document.getElementById("contactForm");
  var status = document.getElementById("formStatus");
  var MAIL_TO = "info@dh-studio.nl";

  function setStatus(message, state) {
    if (!status) return;
    status.textContent = message;
    if (state) status.setAttribute("data-state", state);
    else status.removeAttribute("data-state");
  }

  function markInvalid(field, invalid) {
    if (invalid) field.setAttribute("aria-invalid", "true");
    else field.removeAttribute("aria-invalid");
  }

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      var name = form.elements.name;
      var email = form.elements.email;
      var message = form.elements.message;

      var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.value.trim());
      var problems = [];

      markInvalid(name, !name.value.trim());
      markInvalid(email, !emailOk);
      markInvalid(message, !message.value.trim());

      if (!name.value.trim()) problems.push("je naam");
      if (!emailOk) problems.push("een geldig e-mailadres");
      if (!message.value.trim()) problems.push("je vraag");

      if (problems.length) {
        // Nette Nederlandse opsomming: "a, b en c"
        var list = problems.length > 1
          ? problems.slice(0, -1).join(", ") + " en " + problems[problems.length - 1]
          : problems[0];
        setStatus("Vul nog even " + list + " in.", "error");
        var firstBad = form.querySelector('[aria-invalid="true"]');
        if (firstBad) firstBad.focus();
        return;
      }

      var endpoint = form.getAttribute("data-endpoint");

      if (endpoint) {
        setStatus("Versturen…");
        fetch(endpoint, {
          method: "POST",
          headers: { "Content-Type": "application/json", Accept: "application/json" },
          body: JSON.stringify({
            name: name.value.trim(),
            email: email.value.trim(),
            company: form.elements.company.value.trim(),
            website: form.elements.website.value.trim(),
            message: message.value.trim()
          })
        })
          .then(function (res) {
            if (!res.ok) throw new Error("Request failed");
            setStatus("Bedankt " + name.value.trim() + ". Je hoort snel van ons.");
            form.reset();
          })
          .catch(function () {
            setStatus(
              "Versturen lukte niet. Mail ons gerust direct op " + MAIL_TO + ".",
              "error"
            );
          });
        return;
      }

      // Fallback: vooringevulde mail
      var body =
        "Naam: " + name.value.trim() + "\n" +
        "E-mail: " + email.value.trim() + "\n" +
        "Bedrijf: " + (form.elements.company.value.trim() || "—") + "\n" +
        "Huidige website: " + (form.elements.website.value.trim() || "—") + "\n\n" +
        message.value.trim();

      setStatus("Je mailprogramma opent met de aanvraag erin.");
      window.location.href =
        "mailto:" + MAIL_TO +
        "?subject=" + encodeURIComponent("Aanvraag via dh-studio.nl — " + name.value.trim()) +
        "&body=" + encodeURIComponent(body);
    });

    form.addEventListener("input", function (e) {
      if (e.target.getAttribute("aria-invalid")) markInvalid(e.target, false);
    });
  }
})();
