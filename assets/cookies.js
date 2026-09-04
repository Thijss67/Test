/* ---------------------------------------------------------------------------
   Cookiemelding van DH Studio.

   Werkt zo: zolang iemand geen keuze heeft gemaakt, laden we geen enkel
   meetscript. Pas na "Alles accepteren" komen Google Ads en Leadinfo binnen.
   Kiest iemand voor alleen noodzakelijk, dan blijft de site helemaal schoon.

   De keuze staat een jaar in de browser van de bezoeker; daarna vragen we
   het opnieuw. Wijzigen kan altijd via /cookies.
   --------------------------------------------------------------------------- */
(function () {
	"use strict";

	var SLEUTEL = "dh-cookiekeuze";
	var GELDIG = 365 * 24 * 60 * 60 * 1000; // een jaar
	var VERSIE = 1;

	// De scripts die pas na toestemming mogen laden.
	var GOOGLE_ADS = "AW-18237624823";
	var LEADINFO = "LI-6A43A923F158A";

	function leesKeuze() {
		try {
			var rij = JSON.parse(localStorage.getItem(SLEUTEL) || "null");
			if (!rij || rij.versie !== VERSIE) return null;
			if (Date.now() - rij.tijd > GELDIG) return null;
			return rij.keuze;
		} catch (e) {
			return null;
		}
	}

	function bewaarKeuze(keuze) {
		try {
			localStorage.setItem(SLEUTEL, JSON.stringify({
				keuze: keuze, tijd: Date.now(), versie: VERSIE
			}));
		} catch (e) { /* privémodus: dan vragen we het de volgende keer weer */ }
	}

	/* ------------------------------------------------------------ meetscripts */

	var geladen = false;
	function laadMeetscripts() {
		if (geladen) return;
		geladen = true;

		// Google Ads
		window.dataLayer = window.dataLayer || [];
		window.gtag = function () { window.dataLayer.push(arguments); };
		var g = document.createElement("script");
		g.async = true;
		g.src = "https://www.googletagmanager.com/gtag/js?id=" + GOOGLE_ADS;
		document.head.appendChild(g);
		window.gtag("js", new Date());
		window.gtag("config", GOOGLE_ADS);

		// Leadinfo
		(function (l, e, a, d, i, n, f, o) {
			if (!l[i]) {
				l.GlobalLeadinfoNamespace = l.GlobalLeadinfoNamespace || [];
				l.GlobalLeadinfoNamespace.push(i);
				l[i] = function () { (l[i].q = l[i].q || []).push(arguments); };
				l[i].t = l[i].t || n; l[i].q = l[i].q || [];
				o = e.createElement(a); f = e.getElementsByTagName(a)[0];
				o.async = 1; o.src = d; f.parentNode.insertBefore(o, f);
			}
		})(window, document, "script", "https://cdn.leadinfo.net/ping.js", "leadinfo", LEADINFO);
	}

	/* ------------------------------------------------------------ opmaak */

	function zetStijl() {
		if (document.getElementById("dhCookieStijl")) return;
		var s = document.createElement("style");
		s.id = "dhCookieStijl";
		s.textContent = [
			".dh-cookie{position:fixed;left:1rem;right:1rem;bottom:1rem;z-index:80;",
			"max-width:30rem;margin-inline:auto;background:#fff;color:#0d1b3e;",
			"border:1px solid #e7e9f0;border-radius:22px;padding:1.35rem 1.4rem 1.45rem;",
			"box-shadow:0 18px 45px rgba(13,27,62,.16);",
			"font:400 15px/1.55 'Plus Jakarta Sans',system-ui,-apple-system,'Segoe UI',sans-serif;",
			"opacity:0;transform:translateY(14px);transition:opacity .3s ease,transform .3s cubic-bezier(.22,1,.36,1)}",
			".dh-cookie[data-open='true']{opacity:1;transform:none}",
			".dh-cookie h2{margin:0;font-size:1.05rem;font-weight:700;letter-spacing:-.02em}",
			".dh-cookie p{margin:.55rem 0 0;font-size:.92rem;color:#5c6577}",
			".dh-cookie a{color:#1d4ed8;font-weight:600}",
			".dh-cookie-knoppen{margin-top:1.1rem;display:flex;flex-wrap:wrap;gap:.6rem}",
			".dh-cookie button{flex:1 1 auto;min-width:9rem;min-height:44px;cursor:pointer;",
			"padding:.7rem 1.1rem;border-radius:999px;font:inherit;font-weight:700;font-size:.92rem;",
			"transition:background .18s ease,color .18s ease,border-color .18s ease}",
			".dh-cookie .ja{border:1px solid #2f6bff;background:#2f6bff;color:#fff}",
			".dh-cookie .ja:hover{background:#1d4ed8;border-color:#1d4ed8}",
			".dh-cookie .nee{border:1px solid #d8dce7;background:#fff;color:#0d1b3e}",
			".dh-cookie .nee:hover{background:#f2f4f9}",
			".dh-cookie button:focus-visible{outline:2px solid #2f6bff;outline-offset:2px}",
			// zolang de melding staat, schuift de vaste knop op mobiel weg
			"body[data-cookie-open='true'] .cta-bar{display:none}",
			"@media (min-width:640px){.dh-cookie{left:1.5rem;right:auto;bottom:1.5rem;margin-inline:0}}",
			"@media (prefers-reduced-motion:reduce){.dh-cookie{transition:none}}"
		].join("");
		document.head.appendChild(s);
	}

	/* ------------------------------------------------------------ de melding */

	var kaart = null;

	function sluit() {
		if (!kaart) return;
		kaart.setAttribute("data-open", "false");
		document.body.removeAttribute("data-cookie-open");
		window.setTimeout(function () {
			if (kaart && kaart.parentNode) kaart.parentNode.removeChild(kaart);
			kaart = null;
		}, 320);
	}

	function kies(keuze) {
		bewaarKeuze(keuze);
		if (keuze === "alles") laadMeetscripts();
		sluit();
	}

	function toon() {
		if (kaart) return;
		zetStijl();

		kaart = document.createElement("div");
		kaart.className = "dh-cookie";
		kaart.setAttribute("role", "dialog");
		kaart.setAttribute("aria-live", "polite");
		kaart.setAttribute("aria-label", "Cookies");
		kaart.setAttribute("data-open", "false");
		kaart.innerHTML =
			"<h2>Even over cookies</h2>" +
			"<p>Wij gebruiken cookies om te zien hoe de site gebruikt wordt en welke " +
			"advertenties werken. Liever niet? Dan zetten we alleen wat nodig is om de " +
			"site te laten werken. Meer weten? <a href=\"/cookies\">Lees ons cookiebeleid</a>.</p>" +
			"<div class=\"dh-cookie-knoppen\">" +
			"<button type=\"button\" class=\"ja\">Alles accepteren</button>" +
			"<button type=\"button\" class=\"nee\">Alleen noodzakelijk</button>" +
			"</div>";

		document.body.appendChild(kaart);
		document.body.setAttribute("data-cookie-open", "true");

		kaart.querySelector(".ja").addEventListener("click", function () { kies("alles"); });
		kaart.querySelector(".nee").addEventListener("click", function () { kies("noodzakelijk"); });

		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(function () {
				if (kaart) kaart.setAttribute("data-open", "true");
			});
		});
	}

	/* ------------------------------------------------------------ start */

	var keuze = leesKeuze();
	if (keuze === "alles") {
		laadMeetscripts();
	} else if (keuze === null) {
		if (document.body) toon();
		else document.addEventListener("DOMContentLoaded", toon);
	}

	// Zodat de cookiepagina de keuze kan tonen en opnieuw kan laten kiezen.
	window.dhCookies = {
		keuze: function () { return leesKeuze(); },
		open: function () {
			try { localStorage.removeItem(SLEUTEL); } catch (e) {}
			toon();
		}
	};
})();
