/*
 * Plugin "Anteprima email" per HotelDruid
 * Nel modulo di invio di visualizza_contratto.php, per le email HTML, mostra al
 * posto del codice HTML il testo formattato in modo leggibile; il sorgente HTML
 * (la textarea originale, modificabile) si apre con "Mostra sorgente HTML".
 * Il testo viene ricavato dal valore attuale della textarea (quello che verrà
 * inviato) e si aggiorna a ogni modifica. Non aggiunge campi al modulo e non ne
 * cambia i valori: l'invio resta identico a quello di HotelDruid.
 * Licenza: GNU AGPL v3 o successiva (come HotelDruid).
 */
(function () {
"use strict";

var conf = document.getElementById("ape-config");
if (!conf) return;

var SPAZIO = "\u0001"; // spazio "protetto" usato per rientri e prefissi durante la conversione
var IGNORA = { SCRIPT: 1, STYLE: 1, HEAD: 1, TITLE: 1, META: 1, LINK: 1, NOSCRIPT: 1, TEMPLATE: 1, OBJECT: 1, IFRAME: 1, SVG: 1 };
var BLOCCHI = { ADDRESS: 1, ARTICLE: 1, ASIDE: 1, CENTER: 1, DIV: 1, DL: 1, DT: 1, DD: 1, FIELDSET: 1, FIGURE: 1,
  FIGCAPTION: 1, FOOTER: 1, FORM: 1, HEADER: 1, MAIN: 1, NAV: 1, SECTION: 1, TR: 1, TD: 1, TH: 1, TBODY: 1, THEAD: 1, TFOOT: 1, CAPTION: 1 };

function testoDi(el) {
  return (el.textContent !== undefined) ? el.textContent : el.innerText;
}

function pulisci(s) {
  return String(s).replace(/^[\s ]+|[\s ]+$/g, "");
}

function ripeti(s, n) {
  var r = "";
  while (n-- > 0) r += s;
  return r;
}

// testi dell'interfaccia e dati passati dal PHP
var testi = {}, immagini = [], nodi = conf.getElementsByTagName("span"), i, k;
for (i = 0; i < nodi.length; i++) {
  k = nodi[i].getAttribute("data-ape-txt");
  if (k) testi[k] = testoDi(nodi[i]);
  else if (nodi[i].className === "ape-iim") immagini.push(testoDi(nodi[i]));
}
function t(chiave) {
  return testi.hasOwnProperty(chiave) ? testi[chiave] : chiave;
}
var contType = conf.getAttribute("data-cont-type") || "text/plain";
var eHtml = (contType !== "text/plain");
var bccMittente = (conf.getAttribute("data-bcc-mittente") === "1");
var bccIndirizzo = pulisci(conf.getAttribute("data-bcc-indirizzo") || "");


/* ---------- conversione HTML -> testo semplice ---------- */

function finalizza(s) {
  var righe = s.replace(/\r\n?/g, "\n").split("\n"), j;
  for (j = 0; j < righe.length; j++) righe[j] = righe[j].replace(/[ \t\f\v]+/g, " ").replace(/^ +| +$/g, "");
  return righe.join("\n").replace(/\n{3,}/g, "\n\n").replace(/^\n+|\n+$/g, "");
}

function proteggi(s) {
  return s.replace(/ /g, SPAZIO);
}

function converti(nodo, ctx) {
  var out = "", figli = nodo.childNodes, j;
  for (j = 0; j < figli.length; j++) out += convertiNodo(figli[j], ctx);
  return out;
}

function convertiLista(lista, ctx, ordinata) {
  var out = "", num = parseInt(lista.getAttribute("start"), 10), figli = lista.childNodes, j, r, c, segno, righe, rientro;
  if (isNaN(num)) num = 1;
  for (j = 0; j < figli.length; j++) {
    if (figli[j].nodeType === 1 && figli[j].nodeName.toUpperCase() === "LI") {
      c = finalizza(converti(figli[j], { pre: false, lista: ctx.lista + 1 }));
      segno = ordinata ? (num++) + "." : "-";
      rientro = ripeti(SPAZIO, segno.length + 1);
      righe = c.split("\n");
      out += "\n" + segno + SPAZIO + righe[0];
      for (r = 1; r < righe.length; r++) out += "\n" + (righe[r] ? rientro + righe[r] : "");
    }
    else out += convertiNodo(figli[j], ctx);
  }
  return out;
}

function convertiTabella(tab, ctx) {
  var parti = [], out = "", r, c, riga, celle, multiriga, intestazione, testo, j;
  if (tab.caption) {
    testo = finalizza(converti(tab.caption, ctx));
    if (testo) parti.push({ testo: testo, blocco: true });
  }
  for (r = 0; r < tab.rows.length; r++) {
    riga = tab.rows[r];
    celle = [];
    multiriga = false;
    intestazione = true;
    for (c = 0; c < riga.cells.length; c++) {
      testo = finalizza(converti(riga.cells[c], ctx));
      if (riga.cells[c].nodeName.toUpperCase() !== "TH") intestazione = false;
      if (!testo) continue;
      if (testo.indexOf("\n") >= 0) multiriga = true;
      celle.push(testo);
    }
    if (!celle.length) continue;
    // tabelle di impaginazione (una cella per riga o celle con più righe): contenuto a blocchi
    if (multiriga || celle.length === 1) parti.push({ testo: celle.join("\n\n"), blocco: true });
    else {
      testo = celle.join(" | ");
      parti.push({ testo: testo, blocco: false });
      if (intestazione) parti.push({ testo: ripeti("-", testo.length), blocco: false });
    }
  }
  for (j = 0; j < parti.length; j++) {
    if (j) out += (parti[j].blocco || parti[j - 1].blocco) ? "\n\n" : "\n";
    out += parti[j].testo;
  }
  return out;
}

function convertiNodo(n, ctx) {
  var tag, c, href, visibile, righe, j, alt;
  if (n.nodeType === 3) {
    if (ctx.pre) return proteggi(n.nodeValue.replace(/\r\n?/g, "\n").replace(/\t/g, "    "));
    return n.nodeValue.replace(/[\s ]+/g, " ");
  }
  if (n.nodeType !== 1) return "";
  tag = n.nodeName.toUpperCase();
  if (IGNORA[tag]) return "";
  switch (tag) {
    case "BR":
      return "\n";
    case "HR":
      return "\n\n" + ripeti("-", 40) + "\n\n";
    case "P":
      return "\n\n" + converti(n, ctx) + "\n\n";
    case "H1": case "H2": case "H3": case "H4": case "H5": case "H6":
      c = finalizza(converti(n, ctx)).replace(/\n+/g, " ");
      if (!c) return "";
      if (tag === "H1") c += "\n" + ripeti("=", c.length);
      else if (tag === "H2") c += "\n" + ripeti("-", c.length);
      return "\n\n" + c + "\n\n";
    case "UL": case "OL":
      c = convertiLista(n, ctx, tag === "OL");
      return ctx.lista ? c + "\n" : "\n\n" + c + "\n\n";
    case "LI":
      return "\n-" + SPAZIO + converti(n, ctx) + "\n";
    case "PRE":
      return "\n\n" + converti(n, { pre: true, lista: ctx.lista }) + "\n\n";
    case "BLOCKQUOTE":
      righe = finalizza(converti(n, ctx)).split("\n");
      for (j = 0; j < righe.length; j++) righe[j] = ">" + SPAZIO + righe[j];
      return "\n\n" + righe.join("\n") + "\n\n";
    case "TABLE":
      return "\n\n" + convertiTabella(n, ctx) + "\n\n";
    case "IMG":
      alt = pulisci(n.getAttribute("alt") || "");
      return alt ? "[" + alt + "]" : "";
    case "A":
      c = converti(n, ctx);
      visibile = finalizza(c);
      href = pulisci(n.getAttribute("href") || "");
      if (!href || /^(#|javascript:)/i.test(href)) return c;
      href = href.replace(/^(mailto|tel):/i, "");
      if (!visibile) return href;
      if (visibile.indexOf(href) >= 0) return c;
      return c + " (" + href + ")";
  }
  if (BLOCCHI[tag]) return "\n" + converti(n, ctx) + "\n";
  return converti(n, ctx);
}

// il documento creato da DOMParser è inerte: script non eseguiti, immagini non caricate
function analizzaHtml(html) {
  var doc = null;
  try {
    if (window.DOMParser) doc = new window.DOMParser().parseFromString(html, "text/html");
  } catch (e) { doc = null; }
  if (!doc || !doc.body) {
    try {
      doc = document.implementation.createHTMLDocument("");
      doc.documentElement.innerHTML = html;
    } catch (e2) { doc = null; }
  }
  return doc;
}

function htmlInTesto(html) {
  var doc = analizzaHtml(html), radice;
  if (!doc) return finalizza(html.replace(/<(style|script)[\s\S]*?<\/\1\s*>/gi, "").replace(/<br\s*\/?>/gi, "\n").replace(/<[^>]*>/g, ""));
  radice = doc.body || doc.documentElement;
  return finalizza(converti(radice, { pre: false, lista: 0 })).replace(/\u0001/g, " ");
}


/* ---------- lettura del modulo di invio ---------- */

function campo(form, nome) {
  var el = form.elements[nome];
  return (el && el.nodeName) ? el : null;
}

// stessa estrazione dell'indirizzo fatta da manda_email() in includes/funzioni_email.php
function soloIndirizzo(mittente) {
  if (mittente.indexOf("<") >= 0) return pulisci(mittente.split("<")[1].split(">")[0]);
  return pulisci(mittente);
}

function leggiMittente(form, n) {
  var el = campo(form, "mittente_email" + n), dest, tab, cella;
  if (el) return el.value;
  // mittente non modificabile: HotelDruid lo mostra come testo nella prima riga della tabella
  dest = campo(form, "destinatario_email" + n);
  tab = dest;
  while (tab && tab.nodeName.toUpperCase() !== "TABLE") tab = tab.parentNode;
  if (tab && tab.rows.length && tab.rows[0].cells.length > 1) {
    cella = tab.rows[0].cells[1];
    return pulisci(testoDi(cella));
  }
  return "";
}


/* ---------- riquadro formattato al posto della textarea ---------- */

function crea(tag, classe, testo) {
  var el = document.createElement(tag);
  if (classe) el.className = classe;
  if (testo !== undefined) el.appendChild(document.createTextNode(testo));
  return el;
}

function imposta(el, testo) {
  while (el.firstChild) el.removeChild(el.firstChild);
  el.appendChild(document.createTextNode(testo));
}

function mostra(el, si) {
  el.style.display = si ? "" : "none";
}

function creaPulsante(testo) {
  var b = document.createElement("button"), d = document.createElement("div");
  b.setAttribute("type", "button"); // non deve inviare il modulo
  b.className = "ape-pulsante";
  d.appendChild(document.createTextNode(testo));
  b.appendChild(d);
  return b;
}

// La textarea di HotelDruid (quella che viene inviata) viene spostata dentro il
// riquadro e nascosta: al suo posto si vede il testo formattato. Con "Mostra
// sorgente HTML" ricompare la textarea, modificabile come nell'originale.
function creaRiquadro(area, n) {
  var form = area.form, riquadro, barra, bSorgente, vista, info, attesa = null,
    larghezza = area.offsetWidth, altezza = area.offsetHeight;

  riquadro = crea("div", "ape-riquadro");
  barra = crea("div", "ape-pulsanti");
  bSorgente = creaPulsante(t("mostra_sorgente"));
  barra.appendChild(bSorgente);
  vista = crea("div", "ape-corpo");
  if (larghezza) vista.style.width = larghezza + "px";
  if (altezza) vista.style.height = altezza + "px";
  info = crea("div", "ape-nota");

  area.parentNode.insertBefore(riquadro, area);
  riquadro.appendChild(barra);
  riquadro.appendChild(vista);
  riquadro.appendChild(area);
  riquadro.appendChild(info);
  mostra(area, false);

  bSorgente.onclick = function () {
    var sorgenteVisibile = (area.style.display === "none");
    mostra(area, sorgenteVisibile);
    mostra(vista, !sorgenteVisibile);
    imposta(bSorgente.firstChild, sorgenteVisibile ? t("nascondi_sorgente") : t("mostra_sorgente"));
    if (!sorgenteVisibile) aggiorna();
  };

  function aggiorna() {
    var indirizzoMittente = soloIndirizzo(leggiMittente(form, n)), ccn = [], parti = [], testo;

    attesa = null;
    testo = htmlInTesto(area.value);
    if (pulisci(testo)) {
      imposta(vista, testo);
      vista.className = "ape-corpo";
    }
    else {
      imposta(vista, t("corpo_vuoto"));
      vista.className = "ape-corpo ape-vuoto";
    }

    // dati inviati che non compaiono nel modulo di HotelDruid
    if (bccMittente && indirizzoMittente) ccn.push(indirizzoMittente);
    if (bccIndirizzo) ccn.push(bccIndirizzo);
    if (ccn.length) parti.push(t("ccn") + ": " + ccn.join(", "));
    if (immagini.length) parti.push(t("immagini") + ": " + immagini.join(", "));
    imposta(info, parti.join("  ·  "));
    mostra(info, parti.length > 0);
  }

  function programma() {
    if (attesa) window.clearTimeout(attesa);
    attesa = window.setTimeout(aggiorna, 200);
  }

  if (form.addEventListener) {
    form.addEventListener("input", programma, false);
    form.addEventListener("change", programma, false);
    form.addEventListener("keyup", programma, false);
  }
  else if (form.attachEvent) {
    form.attachEvent("onkeyup", programma);
    form.attachEvent("onclick", programma);
  }
  aggiorna();
}


/* ---------- avvio ---------- */

// solo le email HTML: quelle in testo semplice sono già leggibili nella textarea
if (!eHtml) return;
var aree = document.getElementsByTagName("textarea"), trovate = [], m;
for (i = 0; i < aree.length; i++) {
  m = /^testo_email(\d+)$/.exec(aree[i].name || "");
  if (m && aree[i].form && campo(aree[i].form, "manda_mail")) trovate.push({ area: aree[i], n: m[1] });
}
for (i = 0; i < trovate.length; i++) creaRiquadro(trovate[i].area, trovate[i].n);

})();
