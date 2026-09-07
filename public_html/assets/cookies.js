/* ---------------------------------------------------------------------------
   Cookiemelding van DH Studio.

   Uitgangspunt: niets meten voordat de bezoeker ja zegt. Zolang er geen keuze
   is gemaakt laden we geen enkel meetscript, en staat Google Consent Mode op
   "denied" (dat zetten we al in de <head> van iedere pagina). Pas na
   "Alles accepteren" laden we Google Ads en zetten we de toestemming om.

   Kiest iemand voor alleen noodzakelijk, dan blijft de site helemaal schoon:
   er wordt geen script geladen en er komt geen cookie van derden binnen.

   De keuze staat een jaar in de browser van de bezoeker; daarna vragen we het
   opnieuw. Wijzigen kan altijd via /cookies of via de link in de footer.
   --------------------------------------------------------------------------- */
(function () {
	"use strict";

	var SLEUTEL = "dh-cookiekeuze";
	var GELDIG = 365 * 24 * 60 * 60 * 1000; // een jaar
	var VERSIE = 2;                          // omhoog = opnieuw vragen

	// Het enige script dat toestemming nodig heeft.
	var GOOGLE_ADS = "AW-18237624823";

	/* ------------------------------------------------------------ keuze bewaren */

	function leesKeuze() {
		try {
			var rij = JSON.parse(localStorage.getItem(SLEUTEL) || "null");
			if (!rij || rij.versie !== VERSIE) return null;
			if (Date.now() - rij.tijd > GELDIG) return null;
			return { marketing: rij.marketing === true, tijd: rij.tijd };
		} catch (e) {
			return null; // privémodus of storage vol: dan vragen we het opnieuw
		}
	}

	function bewaarKeuze(marketing) {
		try {
			localStorage.setItem(SLEUTEL, JSON.stringify({
				marketing: marketing === true, tijd: Date.now(), versie: VERSIE
			}));
		} catch (e) { /* niets te doen: de volgende keer vragen we het weer */ }
	}

	function wisKeuze() {
		try { localStorage.removeItem(SLEUTEL); } catch (e) {}
	}

	/* ------------------------------------------------------------ meetscripts */

	// gtag staat al in de <head> van elke pagina, met alles op "denied".
	// Valt dat om welke reden dan ook weg, dan maken we het hier alsnog aan.
	function gtag() {
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push(arguments);
	}

	var geladen = false;
	function laadMarketing() {
		gtag("consent", "update", {
			ad_storage: "granted",
			ad_user_data: "granted",
			ad_personalization: "granted",
			analytics_storage: "granted"
		});

		if (geladen) return;
		geladen = true;

		var s = document.createElement("script");
		s.async = true;
		s.src = "https://www.googletagmanager.com/gtag/js?id=" + GOOGLE_ADS;
		document.head.appendChild(s);

		gtag("js", new Date());
		gtag("config", GOOGLE_ADS);
	}

	function weigerMarketing() {
		gtag("consent", "update", {
			ad_storage: "denied",
			ad_user_data: "denied",
			ad_personalization: "denied",
			analytics_storage: "denied"
		});
	}

	/* ------------------------------------------------------------ opmaak

	   De opmaak zit in dit bestand en niet in de pagina-CSS: zo werkt de
	   melding op elke pagina hetzelfde, ook op pagina's die later bijkomen. */

	function zetStijl() {
		if (document.getElementById("dhCookieStijl")) return;
		var s = document.createElement("style");
		s.id = "dhCookieStijl";
		s.textContent = [
			".dh-cookie{position:fixed;left:1rem;right:1rem;bottom:1rem;z-index:80;",
			"max-width:25.5rem;margin-inline:auto;background:#fff;color:#0d1b3e;",
			"border:1px solid #e7e9f0;border-radius:18px;padding:1.05rem 1.1rem 1.15rem;",
			"box-shadow:0 14px 36px rgba(13,27,62,.14);",
			"font:400 14px/1.5 'Plus Jakarta Sans',ui-sans-serif,system-ui,-apple-system,'Segoe UI',sans-serif;",
			"opacity:0;transform:translateY(14px);transition:opacity .3s ease,transform .3s cubic-bezier(.22,1,.36,1)}",
			".dh-cookie[data-open='true']{opacity:1;transform:none}",
			".dh-cookie h2{margin:0;font-size:.95rem;font-weight:700;letter-spacing:-.02em;color:#0d1b3e}",
			".dh-cookie p{margin:.4rem 0 0;font-size:.845rem;line-height:1.5;color:#5c6577}",
			".dh-cookie a{color:#1d4ed8;font-weight:600;text-underline-offset:2px}",

			/* knoppen: accepteren en weigeren zijn even groot en even bereikbaar,
			   dat is niet alleen netjes maar ook wat de AVG verlangt */
			".dh-cookie-knoppen{margin-top:.85rem;display:flex;flex-wrap:wrap;gap:.45rem}",
			".dh-cookie button{flex:1 1 9.5rem;min-height:40px;cursor:pointer;",
			"padding:.5rem .9rem;border-radius:999px;font:inherit;font-weight:700;font-size:.845rem;",
			"transition:background .18s ease,color .18s ease,border-color .18s ease}",
			".dh-cookie .ja{border:1px solid #2f6bff;background:#2f6bff;color:#fff}",
			".dh-cookie .ja:hover{background:#1d4ed8;border-color:#1d4ed8}",
			".dh-cookie .nee{border:1px solid #d8dce7;background:#fff;color:#0d1b3e}",
			".dh-cookie .nee:hover{background:#f2f4f9}",
			".dh-cookie .meer{flex:1 1 100%;margin-top:0;border:0;background:none;color:#5c6577;",
			"font-weight:600;font-size:.8rem;text-decoration:underline;text-underline-offset:3px;min-height:32px;padding:0}",
			".dh-cookie .meer:hover{color:#0d1b3e}",
			".dh-cookie button:focus-visible{outline:2px solid #2f6bff;outline-offset:2px}",

			/* het uitklapbare deel met de losse keuzes */
			".dh-cookie-keuzes{margin-top:.8rem;border-top:1px solid #e7e9f0;padding-top:.2rem}",
			".dh-cookie-keuzes[hidden]{display:none}",
			".dh-cookie-rij{display:flex;gap:.7rem;align-items:flex-start;padding:.65rem 0;border-bottom:1px solid #f0f2f7}",
			".dh-cookie-rij:last-child{border-bottom:0}",
			".dh-cookie-rij b{display:block;font-size:.845rem;font-weight:700}",
			".dh-cookie-rij span{display:block;margin-top:.1rem;font-size:.78rem;color:#5c6577;line-height:1.4}",
			".dh-cookie-rij input{flex:0 0 auto;width:18px;height:18px;margin:.1rem 0 0;accent-color:#2f6bff;cursor:pointer}",
			".dh-cookie-rij input:disabled{cursor:default;opacity:.55}",
			".dh-cookie-rij label{cursor:pointer}",
			".dh-cookie-rij input:disabled+label{cursor:default}",

			/* zolang de melding staat, schuift de vaste mobiele knop weg */
			"body[data-cookie-open='true'] .cta-bar{display:none}",
			"@media (min-width:640px){.dh-cookie{right:1.25rem;left:auto;bottom:1.25rem;margin-inline:0}}",
			"@media (prefers-reduced-motion:reduce){.dh-cookie{transition:none}}"
		].join("");
		document.head.appendChild(s);
	}

	/* ------------------------------------------------------------ de melding */

	var kaart = null;

	function sluit() {
		if (!kaart) return;
		var weg = kaart;
		kaart = null;
		weg.setAttribute("data-open", "false");
		document.body.removeAttribute("data-cookie-open");
		window.setTimeout(function () {
			if (weg.parentNode) weg.parentNode.removeChild(weg);
		}, 320);
	}

	function kies(marketing) {
		bewaarKeuze(marketing);
		if (marketing) laadMarketing();
		else weigerMarketing();
		sluit();
		meld();
	}

	// Seintje voor de cookiepagina, zodat die de status kan bijwerken
	// zonder er steeds naar te hoeven vragen.
	function meld() {
		var e;
		try {
			e = new CustomEvent("dh-cookiekeuze");
		} catch (err) {
			e = document.createEvent("CustomEvent");
			e.initCustomEvent("dh-cookiekeuze", false, false, null);
		}
		document.dispatchEvent(e);
	}

	function toon() {
		if (kaart) return;
		zetStijl();

		var vorige = leesKeuze();

		kaart = document.createElement("div");
		kaart.className = "dh-cookie";
		kaart.setAttribute("role", "dialog");
		kaart.setAttribute("aria-modal", "false");
		kaart.setAttribute("aria-labelledby", "dhCookieKop");
		kaart.setAttribute("data-open", "false");
		kaart.innerHTML =
			'<h2 id="dhCookieKop">Even over cookies</h2>' +
			"<p>Noodzakelijke cookies staan altijd aan. Met jouw toestemming meten we " +
			"via Google Ads welke advertentie iets oplevert. " +
			'<a href="/cookies">Cookiebeleid</a>.</p>' +

			'<div class="dh-cookie-keuzes" id="dhCookieKeuzes" hidden>' +
				'<div class="dh-cookie-rij">' +
					'<input type="checkbox" id="dhCookieNodig" checked disabled />' +
					'<label for="dhCookieNodig"><b>Noodzakelijk</b>' +
					"<span>Zorgt dat de site werkt en onthoudt deze keuze. " +
					"Staat altijd aan en is niet uit te zetten.</span></label>" +
				"</div>" +
				'<div class="dh-cookie-rij">' +
					'<input type="checkbox" id="dhCookieMarketing"' + (vorige && vorige.marketing ? " checked" : "") + " />" +
					'<label for="dhCookieMarketing"><b>Marketing</b>' +
					"<span>Google Ads meet welke advertentie tot een aanvraag leidt. " +
					"Zonder deze keuze laden we dat script niet.</span></label>" +
				"</div>" +
			"</div>" +

			'<div class="dh-cookie-knoppen">' +
				'<button type="button" class="ja">Alles accepteren</button>' +
				'<button type="button" class="nee">Alleen noodzakelijk</button>' +
				'<button type="button" class="meer" aria-expanded="false" aria-controls="dhCookieKeuzes">Zelf instellen</button>' +
			"</div>";

		document.body.appendChild(kaart);
		document.body.setAttribute("data-cookie-open", "true");

		var jaKnop = kaart.querySelector(".ja");
		var neeKnop = kaart.querySelector(".nee");
		var meerKnop = kaart.querySelector(".meer");
		var keuzes = kaart.querySelector("#dhCookieKeuzes");
		var marketingVink = kaart.querySelector("#dhCookieMarketing");

		jaKnop.addEventListener("click", function () { kies(true); });

		// "Zelf instellen" openen; daarna bewaart dezelfde knop de eigen keuze,
		// zodat niemand zijn vinkjes zet en vervolgens niets kan opslaan.
		meerKnop.addEventListener("click", function () {
			if (keuzes.hidden) {
				keuzes.hidden = false;
				meerKnop.setAttribute("aria-expanded", "true");
				meerKnop.textContent = "Mijn keuze bewaren";
				neeKnop.textContent = "Alles weigeren";
				marketingVink.focus();
			} else {
				kies(marketingVink.checked);
			}
		});

		neeKnop.addEventListener("click", function () { kies(false); });

		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(function () {
				if (kaart) kaart.setAttribute("data-open", "true");
			});
		});
	}

	/* ------------------------------------------------------------ start */

	function start() {
		var keuze = leesKeuze();
		if (keuze && keuze.marketing) laadMarketing();
		else if (keuze) weigerMarketing();
		else toon();
	}

	if (document.body) start();
	else document.addEventListener("DOMContentLoaded", start);

	/* Voor de cookiepagina en de link in de footer: keuze opvragen, de melding
	   opnieuw openen, of de keuze intrekken. */
	window.dhCookies = {
		keuze: function () { return leesKeuze(); },
		open: function () {
			if (document.body) toon();
			else document.addEventListener("DOMContentLoaded", toon);
		},
		intrekken: function () {
			wisKeuze();
			weigerMarketing();
			meld();
			toon();
		}
	};
})();
