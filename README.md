# HotelDruid plugins

Sistema di plugin per [HotelDruid](https://www.hoteldruid.com/) 3.0.8, con il plugin **email_wysiwyg**.

Il sistema si attiva con **una sola riga** aggiunta a `includes/funzioni.php`. Tutti i plugin stanno in sottocartelle di `plugins/`, e senza la cartella `plugins/` HotelDruid funziona esattamente come l'originale.

## Contenuto

| Cartella | Contenuto |
|----------|-----------|
| [`plugins/`](plugins/) | Il caricatore (`caricatore.php`) e i plugin. Da copiare nella cartella principale di HotelDruid. Istruzioni e guida per scrivere plugin in [`plugins/README.md`](plugins/README.md). |
| [`plugins/email_wysiwyg/`](plugins/email_wysiwyg/) | In `visualizza_contratto.php`, per le email HTML, mostra al posto del codice HTML il testo compilato e formattato in modo leggibile, con il pulsante `< >` per vedere e modificare il sorgente HTML. |
| [`hoteldruid_modificato_plugins/`](hoteldruid_modificato_plugins/) | `includes/funzioni.php` con la riga di aggancio, `modifiche.patch` (diff rispetto a HotelDruid 3.0.8) e `MODIFICHE.md` con i dettagli e le prove eseguite. |

## Installazione rapida

1. Copiare `plugins/` nella cartella principale di HotelDruid, accanto a `inizio.php`.
2. Dalla cartella di HotelDruid applicare la patch:
   ```
   patch -p1 < /percorso/hoteldruid_modificato_plugins/modifiche.patch
   ```
   In alternativa, aggiungere a mano in `includes/funzioni.php`, subito dopo `define('C_PHPR_VERSIONE_TXT',"3.0.8");`, la riga:
   ```php
   if (file_exists("./plugins/caricatore.php")) include("./plugins/caricatore.php"); # sistema di plugin (facoltativo)
   ```

Un aggiornamento di HotelDruid sovrascrive `includes/funzioni.php`: dopo ogni aggiornamento va rimessa la riga.

## Licenza

GNU Affero General Public License versione 3 o successiva, come HotelDruid (vedi [`LICENSE`](LICENSE)).
