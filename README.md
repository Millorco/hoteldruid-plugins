# HotelDruid plugins

Sistema di plugin per [HotelDruid](https://www.hoteldruid.com/) 3.0.8. Si attiva con **una sola riga** aggiunta a `includes/funzioni.php`; tutti i plugin stanno in sottocartelle di `plugins/`. Senza la cartella `plugins/`, HotelDruid funziona esattamente come l'originale.

Plugin inclusi:
- [`email_wysiwyg`](plugins/email_wysiwyg/README.md): nelle email HTML di `visualizza_contratto.php` mostra il testo formattato al posto del codice HTML.

## Contenuto del repository

```
plugins/                          ← da copiare nella cartella principale di HotelDruid
├── caricatore.php                ← il sistema di plugin
└── email_wysiwyg/                ← un plugin per cartella
    ├── plugin.php                ← obbligatorio
    └── ...                       ← altri file del plugin
hoteldruid_modificato_plugins/
└── includes/funzioni.php         ← funzioni.php di HotelDruid 3.0.8 con la riga di aggancio
```

## Installazione

1. Copiare la cartella `plugins/` nella cartella principale di HotelDruid, accanto a `inizio.php`, `visualizza_contratto.php`, ecc.
2. Aggiungere l'aggancio in `includes/funzioni.php`, in uno di questi modi:
   - aggiungere a mano, subito dopo la riga `define('C_PHPR_VERSIONE_TXT',"3.0.8");` (riga 89), la riga:
     ```php
     if (file_exists("./plugins/caricatore.php")) include("./plugins/caricatore.php"); # sistema di plugin (facoltativo)
     ```
   - oppure, se la versione di HotelDruid è la 3.0.8, sostituire il file con `hoteldruid_modificato_plugins/includes/funzioni.php`.

È l'unica modifica al codice di HotelDruid: nessuna riga originale viene cambiata o rimossa.

**Attenzione:** un aggiornamento di HotelDruid sovrascrive `includes/funzioni.php`. Dopo ogni aggiornamento va rimessa la riga.

### Perché in quel punto

- Delle 37 pagine PHP nella cartella principale di HotelDruid, 36 includono `./includes/funzioni.php`. L'unica eccezione è `costanti.php`, che contiene solo configurazione. Con una sola riga si raggiungono quindi tutte le pagine.
- In quel punto le variabili della richiesta sono già state lette e `$pag` è impostata dalla pagina. Sono definite anche le costanti di versione di HotelDruid.
- La pagina non ha ancora prodotto output, quindi il caricatore può raccoglierlo dall'inizio.
- I controlli di sessione (`controlla_login()`) **non sono ancora stati fatti**: vedi le regole di sicurezza più sotto.
- Grazie a `file_exists()`, se la cartella `plugins/` manca la riga non fa nulla.

## Attivare e disattivare i plugin

- Un plugin è **attivo** se la sua cartella contiene `plugin.php`.
- Per **disattivarlo** senza cancellarlo:
  - creare nella sua cartella un file vuoto chiamato `DISATTIVATO`;
  - oppure rinominare la cartella con un `_` iniziale (es. `_email_wysiwyg`).
- Per disattivare **tutto il sistema**: cancellare o rinominare la cartella `plugins/`.

Senza plugin attivi su una pagina, la pagina è identica byte per byte a quella di HotelDruid originale. I nomi delle cartelle possono contenere solo lettere, cifre, `_` e `-`. I plugin vengono caricati in ordine alfabetico di cartella.

## Scrivere un plugin

`plugin.php` viene incluso all'inizio di ogni pagina. Deve controllare di essere caricato dal sistema e registrare le proprie funzioni con `hdp_registra()`:

```php
<?php
if (!defined('HDP_CARICATORE')) return;

hdp_registra("inizio.php","html","mioplugin_html");

function mioplugin_html ($html) {
global $anno;
if (!hdp_utente_autenticato()) return $html;
return hdp_inserisci_prima_di_body($html,"<p>Anno: ".hdp_h($anno)."</p>");
} # fine function mioplugin_html
?>
```

### Punti di aggancio

`hdp_registra($pagine, $tipo, $funzione)`:
- `$pagine`: il nome di una pagina (il valore di `$pag`, es. `"visualizza_contratto.php"`), un array di pagine, oppure `"*"` per tutte;
- `$funzione`: il **nome** della funzione, come stringa. Non si usano closure, per compatibilità con le vecchie versioni di PHP supportate da HotelDruid;
- `$tipo`: `"inizio"` oppure `"html"`, come descritto sotto.

| Tipo | Quando | Parametri | Note |
|------|--------|-----------|------|
| `"inizio"` | Subito, dentro `funzioni.php`, prima della logica della pagina e **prima di `controlla_login()`**. | nessuno | Non deve scrivere nulla nella pagina. Le variabili della richiesta sono già lette; a queste si accede con `global`. |
| `"html"` | Alla fine della pagina, dopo tutta la sua esecuzione. Le variabili globali della pagina sono ancora disponibili. | l'HTML prodotto dalla pagina | Deve **restituire** l'HTML, modificato o no. Viene chiamata solo se la pagina produce HTML: download RTF, TXT, CSV ecc. vengono lasciati intatti. |

L'aggancio `"html"` funziona così: il caricatore raccoglie in memoria (`ob_start()`) l'output della pagina, e la funzione registrata con `register_shutdown_function()` lo passa ai plugin prima di inviarlo. Questo succede **solo sulle pagine per cui almeno un plugin ha registrato una funzione `"html"`**; le altre pagine vengono inviate come sempre, man mano che sono prodotte.

### Funzioni disponibili

| Funzione | Uso |
|----------|-----|
| `hdp_utente_autenticato()` | Vero se la pagina ha verificato la sessione (`$id_utente` impostato da `controlla_login()`). |
| `hdp_inserisci_prima_di_body($html, $codice)` | Inserisce `$codice` prima di `</body>`. |
| `hdp_h($testo)` | `htmlspecialchars()` con `ENT_QUOTES` e UTF-8. |
| `HDP_CARTELLA` | Percorso della cartella `plugins/`. Nel plugin, per i propri file usare `dirname(__FILE__)`. |
| `HDP_VERSIONE` | Versione del sistema di plugin. |

### Regole di sicurezza

- Il caricatore si ferma subito se non è incluso da una pagina che si trova nella cartella principale di HotelDruid. Aprendo direttamente `plugins/caricatore.php` o un `plugin.php` non succede nulla: la risposta è vuota.
- I plugin **non devono dare per scontato** di essere eseguiti dopo il login:
  - nelle funzioni `"html"` controllare sempre `hdp_utente_autenticato()`, più gli eventuali privilegi specifici della pagina (per esempio `$anno_utente_attivato == "SI"`, o le variabili `$priv_...` che la pagina imposta);
  - le funzioni `"inizio"` vengono eseguite anche per utenti non autenticati.
- Tutto ciò che si scrive nella pagina va passato da `hdp_h()`.
- Usare nomi di funzioni con un prefisso proprio del plugin, perché tutte le funzioni sono globali.
- Non aprire nuovi indirizzi accessibili dall'esterno senza gli stessi controlli di HotelDruid.

## Limiti

- I plugin possono agire solo **all'inizio** e **alla fine** di una pagina. Per intervenire nel mezzo della logica di HotelDruid (per esempio su come `manda_email()` costruisce le intestazioni, o sul calcolo delle tariffe) servirebbero altre modifiche al codice originale.
- I plugin che modificano l'HTML dipendono dal markup di HotelDruid, che può cambiare tra una versione e l'altra.
- Sulle pagine con funzioni `"html"`, l'output arriva al browser tutto insieme alla fine, invece che un po' alla volta. Per questo è meglio non registrare `"html"` su `"*"` e limitarsi alle pagine necessarie.
- In `creaanno.php` la variabile `$pag` vale `"inizio.php"`: per HotelDruid quella pagina si chiama "inizio.php", e così anche per i plugin.

## Prove eseguite

Le prove sono state fatte su un'installazione reale di prova: HotelDruid 3.0.8, database SQLite, server integrato di PHP 8.2 e Chrome headless. Le prove specifiche del plugin sono in [`plugins/email_wysiwyg/README.md`](plugins/email_wysiwyg/README.md).

| Prova | Risultato |
|-------|-----------|
| `php -l` su `caricatore.php` e `funzioni.php` modificato | nessun errore |
| Pagine senza plugin registrati (`inizio.php`, `tabella.php`, documenti HTML) | nessun intervento, nessun errore nel log |
| Download RTF, TXT, CSV da `visualizza_contratto.php` | `Content-Type` e contenuto originali |
| Accesso diretto a `plugins/caricatore.php` e ai file PHP del plugin | risposta vuota |
| Senza `plugins/`, oppure con il plugin disattivato (`DISATTIVATO`) | pagina identica byte per byte all'originale |

## Licenza

GNU Affero General Public License versione 3 o successiva, come HotelDruid (vedi [`LICENSE`](LICENSE)).
