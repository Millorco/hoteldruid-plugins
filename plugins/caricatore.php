<?php

##################################################################################
#    Sistema di plugin per HOTELDRUID - caricatore
#
#    Viene incluso da includes/funzioni.php (una sola riga aggiunta), quindi
#    all'inizio di ogni pagina di HotelDruid, prima della logica della pagina e
#    prima dei controlli di sessione.
#
#    Ogni sottocartella di plugins/ che contiene un file plugin.php è un plugin.
#    Un plugin è disattivato se il nome della cartella inizia con "_" o "." oppure
#    se nella cartella c'è un file chiamato DISATTIVATO.
#
#    Punti di aggancio (vedi hdp_registra()):
#    - "inizio": funzione chiamata subito, prima della logica della pagina e prima
#      di controlla_login(); non deve scrivere nulla nella pagina.
#    - "html": funzione chiamata alla fine della pagina, quando le variabili
#      globali della pagina sono ancora disponibili; riceve l'HTML prodotto dalla
#      pagina e restituisce l'HTML (eventualmente modificato) da inviare.
#      Chiamata solo se la pagina produce HTML (non per download RTF, TXT, ecc.).
#
#    Distribuito con la stessa licenza di HotelDruid: GNU Affero General Public
#    License versione 3 o successiva.
##################################################################################

# Si procede solo se incluso da una pagina di HotelDruid nella cartella
# principale (quella che contiene plugins/), mai se richiamato direttamente.
if (defined('HDP_CARICATORE') or !isset($pag) or !is_string($pag) or !isset($_SERVER['SCRIPT_FILENAME'])) return;
if (realpath(dirname($_SERVER['SCRIPT_FILENAME'])) != realpath(dirname(dirname(__FILE__)))) return;

define('HDP_CARICATORE',1);
define('HDP_VERSIONE',"0.1");
define('HDP_CARTELLA',dirname(__FILE__));

global $hdp_agganci,$hdp_livello_ob;
$hdp_agganci = array('inizio' => array(), 'html' => array());
$hdp_livello_ob = -1;



# Registra una funzione su un punto di aggancio.
# $pagine: nome di una pagina ("visualizza_contratto.php"), array di pagine o "*"
# $tipo: "inizio" oppure "html"
# $funzione: nome (stringa) della funzione da chiamare
function hdp_registra ($pagine,$tipo,$funzione) {
global $hdp_agganci,$pag;

if (!isset($hdp_agganci[$tipo]) or !is_string($funzione)) return false;
if (!is_array($pagine)) $pagine = array($pagine);
for ($num1 = 0 ; $num1 < count($pagine) ; $num1++) {
if ($pagine[$num1] == "*" or $pagine[$num1] == $pag) {
$hdp_agganci[$tipo][] = $funzione;
return true;
} # fine if ($pagine[$num1] == "*" or $pagine[$num1] == $pag)
} # fine for $num1
return false;

} # fine function hdp_registra



# Vero se la pagina ha già verificato la sessione con controlla_login().
# Da usare nelle funzioni "html"; i controlli sui privilegi specifici della
# pagina restano a carico del plugin.
function hdp_utente_autenticato () {

if (!empty($GLOBALS['id_utente'])) return true;
return false;

} # fine function hdp_utente_autenticato



# Inserisce $codice prima di </body> (o in fondo se </body> non c'è).
function hdp_inserisci_prima_di_body ($html,$codice) {

$pos = strripos($html,"</body>");
if ($pos === false) return $html.$codice;
return substr($html,0,$pos).$codice.substr($html,$pos);

} # fine function hdp_inserisci_prima_di_body



# htmlspecialchars con i parametri usati da tutti i plugin.
function hdp_h ($testo) {

return htmlspecialchars((string) $testo,ENT_QUOTES,'UTF-8');

} # fine function hdp_h



# Vero se la pagina sta inviando HTML (nessun Content-Type diverso da text/html).
function hdp_output_html () {

if (!function_exists('headers_list')) return true;
$intestazioni = headers_list();
for ($num1 = 0 ; $num1 < count($intestazioni) ; $num1++) {
if (strtolower(substr($intestazioni[$num1],0,13)) == "content-type:") {
if (strpos(strtolower($intestazioni[$num1]),"text/html") === false) return false;
} # fine if (strtolower(substr($intestazioni[$num1],0,13)) == "content-type:")
} # fine for $num1
return true;

} # fine function hdp_output_html



# Alla fine della pagina: recupera l'HTML dal buffer, lo passa alle funzioni
# "html" registrate e lo invia.
function hdp_fine_pagina () {
global $hdp_agganci,$hdp_livello_ob;

if ($hdp_livello_ob < 1) return;
# chiude eventuali buffer aperti dalla pagina sopra al nostro
while (ob_get_level() > $hdp_livello_ob) @ob_end_flush();
# il nostro buffer è stato chiuso dalla pagina: nulla da fare
if (ob_get_level() != $hdp_livello_ob) return;
$html = ob_get_contents();
@ob_end_clean();
$hdp_livello_ob = -1;
if ($html === false) return;
if (hdp_output_html()) {
for ($num1 = 0 ; $num1 < count($hdp_agganci['html']) ; $num1++) {
if (function_exists($hdp_agganci['html'][$num1])) {
$risultato = call_user_func($hdp_agganci['html'][$num1],$html);
if (is_string($risultato)) $html = $risultato;
} # fine if (function_exists($hdp_agganci['html'][$num1]))
} # fine for $num1
} # fine if (hdp_output_html())
echo $html;

} # fine function hdp_fine_pagina



# Include plugin.php di un plugin dentro una funzione, così le sue variabili
# non si mescolano con quelle della pagina.
function hdp_carica_plugin ($file_plugin) {

include($file_plugin);

} # fine function hdp_carica_plugin



# caricamento dei plugin attivi, in ordine alfabetico di cartella
$hdp_cartelle = @scandir(HDP_CARTELLA);
if (is_array($hdp_cartelle)) {
sort($hdp_cartelle);
for ($hdp_num = 0 ; $hdp_num < count($hdp_cartelle) ; $hdp_num++) {
$hdp_nome = $hdp_cartelle[$hdp_num];
if (substr($hdp_nome,0,1) == "." or substr($hdp_nome,0,1) == "_") continue;
if (!preg_match("/^[A-Za-z0-9_\-]+$/",$hdp_nome)) continue;
if (!is_file(HDP_CARTELLA."/$hdp_nome/plugin.php") or file_exists(HDP_CARTELLA."/$hdp_nome/DISATTIVATO")) continue;
hdp_carica_plugin(HDP_CARTELLA."/$hdp_nome/plugin.php");
} # fine for $hdp_num
} # fine if (is_array($hdp_cartelle))
unset($hdp_cartelle);
unset($hdp_num);
unset($hdp_nome);

# aggancio "inizio"
for ($hdp_num = 0 ; $hdp_num < count($hdp_agganci['inizio']) ; $hdp_num++) {
if (function_exists($hdp_agganci['inizio'][$hdp_num])) call_user_func($hdp_agganci['inizio'][$hdp_num]);
} # fine for $hdp_num
unset($hdp_num);

# aggancio "html": il buffer si apre solo se almeno un plugin agisce su questa pagina
if (count($hdp_agganci['html']) and function_exists('ob_start') and function_exists('register_shutdown_function')) {
if (ob_start()) {
$hdp_livello_ob = ob_get_level();
register_shutdown_function('hdp_fine_pagina');
} # fine if (ob_start())
} # fine if (count($hdp_agganci['html']) and...

?>
