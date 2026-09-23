# Modifiche ai file di HotelDruid (sistema di plugin)

Per attivare il sistema di plugin della cartella `plugins/` serve modificare **un solo file** e aggiungere **una sola riga**. Nessuna riga originale viene cambiata o rimossa. `visualizza_contratto.php` resta quello originale.

| File | Riga | Tipo | Modifica |
|------|------|------|----------|
| `includes/funzioni.php` | 90 (nuova, dopo la 89 dell'originale) | aggiunta | `if (file_exists("./plugins/caricatore.php")) include("./plugins/caricatore.php"); # sistema di plugin (facoltativo)` |

Il diff unificato rispetto a `sorgente/` si trova in `modifiche.patch`. Si applica dalla cartella di HotelDruid con `patch -p1 < modifiche.patch`.

## Perché `includes/funzioni.php`

Delle 37 pagine PHP nella cartella principale di HotelDruid, 36 includono `./includes/funzioni.php`. L'unica eccezione è `costanti.php`, che contiene solo configurazione. È quindi l'unico punto da cui si raggiungono tutte le pagine con una sola riga.

## Perché proprio lì

La riga è inserita subito dopo:

```php
define('C_PHPR_VERSIONE_TXT',"3.0.8");
```

In quel punto:
- le variabili della richiesta sono già state lette (`$var_pag`, righe 45–54), e `$pag` è impostata dalla pagina;
- le costanti di versione sono definite, quindi i plugin possono controllare la versione di HotelDruid;
- la pagina non ha ancora prodotto output, quindi il caricatore può aprire il buffer prima di qualsiasi HTML;
- i controlli di sessione (`controlla_login()`) **non sono ancora stati fatti**. Per questo le funzioni `"html"` dei plugin, eseguite a fine pagina, controllano sempre `hdp_utente_autenticato()` e i privilegi della pagina. Le funzioni `"inizio"` non devono dare per scontato che l'utente sia autenticato.

## Perché con `file_exists()`

Se la cartella `plugins/` non c'è, la condizione è falsa e HotelDruid si comporta esattamente come l'originale. L'ho verificato confrontando byte per byte la pagina ottenuta senza `plugins/`, e quella con il plugin disattivato, con la pagina prodotta dal `funzioni.php` originale.

## Prove eseguite

Le prove sono state fatte su un'installazione reale di prova: HotelDruid 3.0.8 dalla cartella `sorgente/`, database SQLite, server integrato di PHP 8.2 e Chrome headless. I risultati sono nella tabella.

| Prova | Risultato |
|-------|-----------|
| `php -l` su `caricatore.php`, `plugin.php`, `lingue.php`, `funzioni.php` modificato | nessun errore |
| Email HTML (modello 8 reso HTML) con variabili da prenotazione | un solo riquadro con testo formattato e variabili sostituite; riga Ccn |
| "Mostra sorgente HTML", modifica dell'HTML, ritorno al testo | vista aggiornata; il modulo invia il testo modificato e gli stessi campi dell'originale |
| Email in testo semplice (modelli 8, 9, 10 originali) | nessun intervento del plugin |
| Invio (`manda_mail=SI`) | flusso originale fino a `mail()`, nessun intervento del plugin |
| Download RTF (modello 6), TXT (16), CSV (17) | `Content-Type` e contenuto originali, nessun intervento del plugin |
| Altre pagine (`inizio.php`, `tabella.php`, documenti HTML) | nessun intervento, nessun errore nel log |
| Accesso diretto a `plugins/caricatore.php`, `plugin.php`, `lingue.php` | risposta vuota |
| Senza `plugins/` oppure con `plugins/anteprima_email/DISATTIVATO` | pagina identica byte per byte all'originale |
