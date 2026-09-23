<?php

##################################################################################
#    Plugin "Anteprima email" per HOTELDRUID (versione per il sistema di plugin)
#
#    In visualizza_contratto.php, per le email HTML, mostra al posto del codice
#    HTML il testo compilato e formattato in modo leggibile; il sorgente HTML
#    (modificabile) si apre con "Mostra sorgente HTML".
#
#    Distribuito con la stessa licenza di HotelDruid: GNU Affero General Public
#    License versione 3 o successiva.
##################################################################################

if (!defined('HDP_CARICATORE')) return;

if (!function_exists('apeml_testi')) include(dirname(__FILE__)."/lingue.php");
hdp_registra("visualizza_contratto.php","html","apeml_html");



# Chiamata alla fine di visualizza_contratto.php con l'HTML della pagina.
function apeml_html ($html) {
global $tipo_contratto,$mostra_contratto,$dir_salva,$messaggio_di_errore,$contratto,$numero_contratto,$tablecontratti,$lingua_mex,$anno_utente_attivato;

# solo dopo i controlli di sessione e permessi della pagina: C_ID_UTENTE_CONTR
# viene definita da visualizza_contratto.php solo quando mostra il documento
if (!hdp_utente_autenticato() or !defined('C_ID_UTENTE_CONTR') or !isset($anno_utente_attivato) or $anno_utente_attivato != "SI") return $html;
if (!isset($tipo_contratto) or $tipo_contratto != "contreml" or !isset($mostra_contratto) or $mostra_contratto != "SI") return $html;
if (!empty($dir_salva) or !empty($messaggio_di_errore) or !isset($contratto)) return $html;
# si procede solo se nella pagina c'è davvero il modulo di invio email
if (strpos((string) $contratto,"name=\"manda_mail\"") === false) return $html;
# se è ancora installata la versione precedente del plugin (cartella
# plugin_anteprima_email/ agganciata a visualizza_contratto.php) e ha già agito,
# non si fa nulla
if (function_exists('ape_mostra_anteprima') or strpos($html,"id=\"ape-config\"") !== false) return $html;
if (!function_exists('esegui_query')) return $html;

$numero_contratto_int = (int) $numero_contratto;

# formato e copie nascoste: stessi dati (record "opzeml") letti da
# visualizza_contratto.php al momento dell'invio
$opz_eml = esegui_query("select testo from $tablecontratti where numero = '$numero_contratto_int' and tipo = 'opzeml'");
if (numlin_query($opz_eml)) $opz_eml = explode(";",(string) risul_query($opz_eml,0,'testo'));
else $opz_eml = array();
for ($num1 = count($opz_eml) ; $num1 < 4 ; $num1++) $opz_eml[$num1] = "";
$cont_type = "text/plain";
if ($opz_eml[0] == "html") {
if (!empty($opz_eml[3])) $cont_type = "multipart/alternative";
else $cont_type = "text/html";
} # fine if ($opz_eml[0] == "html")
# le email in testo semplice restano come nell'originale
if ($cont_type == "text/plain") return $html;
if ($opz_eml[1] == "SI") $bcc_mittente = "1";
else $bcc_mittente = "";
$bcc_indirizzo = trim($opz_eml[2]);

# immagini incorporate (record "img_inln"), sempre inviate insieme all'email
$nomi_iim = array();
$iimg_email = esegui_query("select testo from $tablecontratti where numero = '$numero_contratto_int' and tipo = 'img_inln'");
if (numlin_query($iimg_email)) {
$iimg_email = explode(",",(string) risul_query($iimg_email,0,'testo'));
for ($num1 = 1 ; $num1 < (count($iimg_email) - 1) ; $num1++) {
$num_iimg = explode(":",$iimg_email[$num1]);
$num_iimg = (int) $num_iimg[0];
$file_iim = esegui_query("select testo from $tablecontratti where numero = '$num_iimg' and tipo = 'file_iim'");
if (numlin_query($file_iim)) {
$file_iim = explode(",",(string) risul_query($file_iim,0,'testo'),2);
if (strcmp($file_iim[0],"")) $nomi_iim[] = $file_iim[0];
} # fine if (numlin_query($file_iim))
} # fine for $num1
} # fine if (numlin_query($iimg_email))

if (!isset($lingua_mex)) $lingua_mex = "ita";
$testi = apeml_testi($lingua_mex);
$cartella = dirname(__FILE__);

$codice = "
<div id=\"ape-config\" style=\"display: none;\" data-cont-type=\"".hdp_h($cont_type)."\" data-bcc-mittente=\"$bcc_mittente\" data-bcc-indirizzo=\"".hdp_h($bcc_indirizzo)."\">";
for ($num1 = 0 ; $num1 < count($nomi_iim) ; $num1++) $codice .= "<span class=\"ape-iim\">".hdp_h($nomi_iim[$num1])."</span>";
foreach ($testi as $chiave => $valore) $codice .= "<span data-ape-txt=\"".hdp_h($chiave)."\">".hdp_h($valore)."</span>";
$codice .= "</div>
<style type=\"text/css\">
".@file_get_contents("$cartella/anteprima_email.css")."
</style>
<script type=\"text/javascript\">
".@file_get_contents("$cartella/anteprima_email.js")."
</script>
";

return hdp_inserisci_prima_di_body($html,$codice);

} # fine function apeml_html

?>
