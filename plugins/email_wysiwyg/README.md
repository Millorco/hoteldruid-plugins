# Plugin email_wysiwyg

Plugin per il [sistema di plugin](../../README.md) di HotelDruid. Non richiede modifiche a `visualizza_contratto.php`: basta l'aggancio unico in `includes/funzioni.php`.

## Cosa fa

In `visualizza_contratto.php`, per le email in formato **HTML**, mostra al posto del codice HTML il **testo già compilato e formattato in modo leggibile**, con le variabili già sostituite.

- Il pulsante **`< >`** apre il codice HTML, modificabile come nell'originale, e poi riporta al testo formattato, aggiornato con le modifiche. Quando il sorgente è aperto il pulsante appare premuto. Passandoci sopra con il mouse compare la descrizione ("Mostra sorgente HTML" / "Nascondi sorgente HTML"), nella lingua dell'utente.
- Se il modello prevede copie nascoste (Ccn) o immagini incorporate, che non compaiono nel modulo, una riga sotto il riquadro le riporta.
- Le email in testo semplice restano come nell'originale.

L'email inviata non cambia: la textarea di HotelDruid resta nel modulo, solo nascosta, e il plugin non aggiunge campi.

## Installazione e disattivazione

- **Installazione:** copiare la cartella `email_wysiwyg/` in `plugins/`, con il sistema di plugin già installato.
- **Disattivazione:** creare nella cartella un file vuoto `DISATTIVATO`, oppure rinominare la cartella con un `_` iniziale, oppure cancellarla.

## File

| File | Contenuto |
|------|-----------|
| `plugin.php` | Registra la funzione `apeml_html()` sull'aggancio `"html"` di `visualizza_contratto.php`. Dopo i controlli su sessione, permessi e tipo di documento, legge formato e copie nascoste del modello e inserisce CSS e JS prima di `</body>`. |
| `email_wysiwyg.js` | Mostra il testo formattato al posto della textarea, lo aggiorna a ogni modifica e gestisce il pulsante `< >`. JavaScript semplice, senza librerie. |
| `email_wysiwyg.css` | Stile, con classi prefissate `ape-`. |
| `lingue.php` | Testi in italiano, inglese e spagnolo (funzione `apeml_testi()`). Le altre lingue usano l'inglese. |

## Come funziona

- **Fedeltà.** HotelDruid compila il testo dell'email (`crea_contratto()` in `includes/funzioni_contratti.php`) e lo inserisce nella `<textarea name="testo_emailN">`. Quello che c'è nella textarea al momento dell'invio è esattamente ciò che viene spedito.
  - Il plugin sposta la textarea dentro il proprio riquadro, sempre all'interno del modulo, la nasconde e al suo posto mostra la versione leggibile, ricalcolata a ogni modifica.
  - Una textarea nascosta viene comunque inviata con il modulo.
  - Il plugin non ricompila né duplica la sostituzione delle variabili.
  - Se il JavaScript non parte, la textarea resta visibile come nell'originale.
- **Dati non presenti nel modulo.** Formato (HTML o testo) e copie nascoste vengono dal record `opzeml` del modello, lo stesso letto da `visualizza_contratto.php` al momento dell'invio. Per la copia al mittente si usa solo l'indirizzo, come fa `manda_email()`. Le immagini incorporate vengono dal record `img_inln`.
- **Conversione HTML → testo.** `DOMParser` crea un documento inerte: gli script non vengono eseguiti e le immagini non vengono caricate. La conversione:
  - trasforma in a capo `<br>` e i blocchi; paragrafi, titoli, liste e tabelle sono separati da righe vuote;
  - sottolinea i titoli `h1`/`h2`;
  - rende le liste puntate con trattini e quelle numerate con `1.`, `2.`;
  - mostra i link come `testo (url)`, le immagini come `[alt]`, le citazioni con `> `;
  - rende le tabelle di dati come `cella | cella` e quelle di impaginazione come blocchi di testo;
  - rimuove `<style>` e `<script>`, decodifica le entità, compatta spazi e righe vuote.
- **Formato `multipart/alternative`.** La parte in testo semplice che HotelDruid allega da solo è più grezza: tiene solo `<br>`/`<p>`. Il plugin mostra la versione leggibile del contenuto HTML, cioè la parte principale del messaggio.
- **Sicurezza.**
  - Il plugin agisce solo dopo i controlli di HotelDruid: sessione valida, `$anno_utente_attivato == "SI"`, costante `C_ID_UTENTE_CONTR` definita da `visualizza_contratto.php`.
  - Lato PHP, tutti i valori scritti nella pagina passano da `hdp_h()`.
  - Lato JS, il testo formattato viene inserito come testo (`createTextNode`), mai come HTML.
  - Il pulsante è `type="button"`, quindi non invia il modulo.
