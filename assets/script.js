/* DH-studio — gedeeld script voor alle pagina's.
   Elk blok controleert eerst of de betreffende elementen op deze pagina
   staan, zodat hetzelfde bestand op de homepage én op losse pagina's werkt. */
(function(){
  "use strict";
  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* --- Jaartal in de footer --- */
  var jaar = document.getElementById("jaar");
  if(jaar) jaar.textContent = new Date().getFullYear();

  /* --- Header krijgt een lijn zodra je scrollt --- */
  var hdr = document.getElementById("hdr");
  if(hdr){
    var ticking = false;
    var onScroll = function(){
      if(ticking) return;
      ticking = true;
      requestAnimationFrame(function(){
        hdr.classList.toggle("stuck", window.scrollY > 8);
        ticking = false;
      });
    };
    window.addEventListener("scroll", onScroll, {passive:true});
    onScroll();
  }

  /* --- Mobiel menu --- */
  var burger = document.getElementById("burger"), drawer = document.getElementById("drawer");
  if(burger && drawer){
    var setDrawer = function(open){
      burger.setAttribute("aria-expanded", String(open));
      burger.setAttribute("aria-label", open ? "Menu sluiten" : "Menu openen");
      drawer.classList.toggle("open", open);
      document.body.classList.toggle("locked", open);
    };
    burger.addEventListener("click", function(){
      setDrawer(burger.getAttribute("aria-expanded") !== "true");
    });
    drawer.addEventListener("click", function(e){
      if(e.target.closest("a")) setDrawer(false);
    });
    document.addEventListener("keydown", function(e){
      if(e.key === "Escape" && burger.getAttribute("aria-expanded") === "true"){
        setDrawer(false);
        burger.focus();
      }
    });
    window.addEventListener("resize", function(){
      if(window.innerWidth > 920 && burger.getAttribute("aria-expanded") === "true") setDrawer(false);
    });
  }

  /* --- Website-previews schalen mee met hun container --- */
  var previews = document.querySelectorAll(".preview");
  if(previews.length){
    var fit = function(){
      previews.forEach(function(box){
        box.style.setProperty("--s", box.clientWidth / 1440);
      });
    };
    fit();
    if("ResizeObserver" in window){
      var ro = new ResizeObserver(fit);
      previews.forEach(function(box){ ro.observe(box); });
    } else {
      window.addEventListener("resize", fit, {passive:true});
    }
  }

  /* --- Anker-navigatie binnen één pagina: markeer het actieve menu-item.
         Op pagina's waar het menu naar andere bestanden wijst, staat
         aria-current al in de HTML en doet dit blok niets. --- */
  var anchorLinks = document.querySelectorAll('.menu a[href^="#"]');
  if(anchorLinks.length && "IntersectionObserver" in window){
    var links = {}, targets = [];
    anchorLinks.forEach(function(a){
      var el = document.getElementById(a.getAttribute("href").slice(1));
      if(el){ links[el.id] = a; targets.push(el); }
    });
    var spy = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(!entry.isIntersecting) return;
        Object.keys(links).forEach(function(id){ links[id].removeAttribute("aria-current"); });
        links[entry.target.id].setAttribute("aria-current", "true");
      });
    }, {rootMargin:"-45% 0px -50% 0px"});
    targets.forEach(function(el){ spy.observe(el); });
  }

  /* --- FAQ-accordeon --- */
  document.querySelectorAll(".q button").forEach(function(btn){
    btn.addEventListener("click", function(){
      var item = btn.closest(".q"), isOpen = item.dataset.open === "true";
      document.querySelectorAll(".q").forEach(function(other){
        other.dataset.open = "false";
        other.querySelector("button").setAttribute("aria-expanded", "false");
      });
      if(!isOpen){
        item.dataset.open = "true";
        btn.setAttribute("aria-expanded", "true");
      }
    });
  });

  /* --- Contactformulier ---
         Opent een vooringevulde e-mail. Vervang dit door een eigen endpoint
         (Formspree, Netlify Forms of PHP) zodra dat beschikbaar is. */
  var form = document.getElementById("quote");
  if(form){
    var note   = document.getElementById("formnote"),
        select = document.getElementById("pakket");

    /* Kwam de bezoeker via een knop als "Kies Professional" of
       "Vraag je websitecheck aan"? Dan staat het onderwerp in de URL:
       contact.html?pakket=Professional */
    if(select){
      var wanted = new URLSearchParams(window.location.search).get("pakket");
      if(wanted){
        var match = [].slice.call(select.options).filter(function(o){
          return o.value.toLowerCase() === wanted.toLowerCase();
        })[0];
        if(match){
          select.value = match.value;
          window.setTimeout(function(){
            var naam = document.getElementById("naam");
            if(naam) naam.focus({preventScroll:true});
          }, reduce ? 0 : 400);
        }
      }
    }

    form.addEventListener("submit", function(e){
      e.preventDefault();
      var naam = form.naam.value.trim(), email = form.email.value.trim();
      note.className = "formnote";

      if(!naam || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
        note.className = "formnote err";
        note.textContent = "Vul je naam en een geldig e-mailadres in, dan kunnen we je antwoorden.";
        (!naam ? form.naam : form.email).focus();
        return;
      }

      var body = "Naam: " + naam +
                 "\nE-mail: " + email +
                 "\nHuidige website: " + (form.site.value.trim() || "—") +
                 "\nNodig: " + form.pakket.value +
                 "\n\n" + form.bericht.value.trim();

      window.location.href = "mailto:info.dhstudionl@gmail.com" +
        "?subject=" + encodeURIComponent("Aanvraag via dh-studio.nl — " + naam) +
        "&body=" + encodeURIComponent(body);

      note.className = "formnote ok";
      note.textContent = "Je e-mailprogramma opent met de aanvraag erin. Versturen en je hoort binnen 1 werkdag van ons.";
    });
  }
})();
