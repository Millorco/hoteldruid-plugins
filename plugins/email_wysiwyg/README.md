# Plugin "email_wysiwyg" (sistema di plugin)

Plugin per il sistema di plugin di `plugins/`. Non serve nessuna modifica a `visualizza_contratto.php`: basta l'aggancio unico in `includes/funzioni.php` (vedi `../README.md`).

## Cosa fa

In `visualizza_contratto.php`, per le email in formato **HTML**, mostra al posto del codice HTML il **testo già compilato e formattato in modo leggibile**, con le variabili già sostituite.
- Il pulsante **"Mostra sorgente HTML" / "Nascondi sorgente HTML"** passa al codice HTML, che si può modificare come nell'originale, e poi di nuovo al testo formattato, aggiornato con le modifiche.
- Se il modello prevede copie nascoste (Ccn) o immagini incorporate, che non compaiono nel modulo, una riga sotto il riquadro le riporta.
- Le email in testo semplice restano come nell'originale.

L'email inviata non cambia. La textarea di HotelDruid resta nel modulo, solo nascosta, e il plugin non aggiunge campi.

## File

| File | Contenuto |
|------|-----------|
| `plugin.php` | Registra la funzione `apeml_html()` sull'aggancio `"html"` di `visualizza_contratto.php`. Dopo i controlli su sessione, permessi e tipo di documento, legge formato e copie nascoste del modello e inserisce CSS e JS prima di `</body>`. |
| `email_wysiwyg.js` | Mostra il testo formattato al posto della textarea e gestisce il pulsante. |
| `email_wysiwyg.css` | Stile, con classi prefissate `ape-`. |
| `lingue.php` | Testi in italiano, inglese e spagnolo (funzione `apeml_testi()`). |

## Disattivazione

Creare nella cartella un file vuoto `DISATTIVATO`, oppure rinominare la cartella con un `_` iniziale, oppure cancellarla.
