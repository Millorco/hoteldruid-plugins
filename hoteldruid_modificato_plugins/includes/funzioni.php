<?php

##################################################################################
#    HOTELDRUID
#    Copyright (C) 2001-2025 by Marco Maria Francesco De Santis (marco@digitaldruid.net)
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU Affero General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    any later version accepted by Marco Maria Francesco De Santis, which
#    shall act as a proxy as defined in Section 14 of version 3 of the
#    license.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU Affero General Public License for more details.
#
#    You should have received a copy of the GNU Affero General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
##################################################################################


# "on", "off" or "param" to log the parameters the page is called with
define('C_DEBUG_HD',"off");

#if (function_exists('version_compare') and version_compare(PHP_VERSION, '8.0.0', '>=')) error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
#else error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
if (C_DEBUG_HD == "on") {
error_reporting(E_ALL);
#if (function_exists('ini_set')) @ini_set('display_errors','On');
} # fine if (C_DEBUG_HD == "on")
else {
error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
#if (function_exists('ini_set')) @ini_set('display_errors','Off');
} # fine else if (C_DEBUG_HD == "on")
if (function_exists('mb_internal_encoding')) mb_internal_encoding('UTF-8');

#$pag = explode("/", $SCRIPT_NAME);
#$pag = end($pag);
if (isset($PHPR_TAB_PRE)) $tablepersonalizza = $PHPR_TAB_PRE.'personalizza';
else $tablepersonalizza = 'personalizza';


$var_pag[$n_var_pag++] = 'id_sessione';
$var_pag[$n_var_pag++] = 'anno';
$var_pag[$n_var_pag++] = 'nome_utente_phpr';
$var_pag[$n_var_pag++] = 'password_phpr';
$var_pag[$n_var_pag++] = 'vers_hinc';
$var_pag[$n_var_pag++] = 'test_cookie';
for ($num1 = 0 ; $num1 < $n_var_pag; $num1++) {
if (isset($_REQUEST[$var_pag[$num1]]) and !isset(${$var_pag[$num1]})) ${$var_pag[$num1]} = $_REQUEST[$var_pag[$num1]];
} # fine for $num1
unset($var_pag);

if (C_DEBUG_HD == "on" or C_DEBUG_HD == "param") {
$log_param = "";
if (@is_array($_REQUEST)) reset($_REQUEST);
for ($num1 = 0 ; $num1 < count($_REQUEST); $num1++) {
$var_REQUEST = key($_REQUEST);
if (is_array($_REQUEST[$var_REQUEST])) $log_param .= "&$var_REQUEST=arr:".substr(implode(",",$_REQUEST[$var_REQUEST]),0,30);
else $log_param .= "&$var_REQUEST=".substr($_REQUEST[$var_REQUEST],0,30);
if (!isset($$var_REQUEST)) {
# nel caso fosse settato register_globals = Off
#$$var_REQUEST = $_REQUEST[$var_REQUEST];
if (C_DEBUG_HD == "on") {
error_log("PHP not registered variable in $pag: \$var_pag[".($n_var_pag++ - 6)."] = '$var_REQUEST';");
echo "\$var_pag[".($n_var_pag - 7)."] = '".htmlspecialchars($var_REQUEST)."';<br>";
} # fine if (C_DEBUG_HD == "on")
} # fine (!isset($$var_REQUEST))
next($_REQUEST);
} # fine for $num1
if (C_DEBUG_HD == "param" and $log_param) error_log("$pag called with: $log_param");
unset($log_param);
} # fine if (C_DEBUG_HD == "on" or C_DEBUG_HD == "param")
unset($n_var_pag);


if (defined('C_DATI_PATH') and @is_file(C_DATI_PATH."/tema.php")) include(C_DATI_PATH."/tema.php");
else {
$tema = array(1 => 'base');
$trad_ui = 0;
$parole_sost = 0;
} # fine else if (@is_file(C_DATI_PATH."/tema.php"))

#@include("./includes/costanti.php");
#@include(C_DATI_PATH."/costanti.php");
define('C_PHPR_VERSIONE_NUM',3.08);
define('C_PHPR_VERSIONE_TXT',"3.0.8");
if (file_exists("./plugins/caricatore.php")) include("./plugins/caricatore.php"); # sistema di plugin (facoltativo)

if (!isset($nome_utente_phpr)) $nome_utente_phpr = "";
if (!isset($password_phpr)) $password_phpr = "";
if (!isset($id_sessione)) $id_sessione = "";

/*if (!function_exists('utf8_encode')) {
function utf8_encode ($testo) { return ($testo); }
function utf8_decode ($testo) { return ($testo); }
} # fine if (!function_exists('utf8_encode'))*/
if (!function_exists('get_magic_quotes_gpc')) {
function get_magic_quotes_gpc () { return 0; }
} # fine if (!function_exists('get_magic_quotes_gpc'))

$id_sessione = htmlspecialchars($id_sessione);
unset($cookie_sess);

# Se non si specifica nessun anno uso l'attuale
if (!defined('C_DIFF_ORE')) {
if (@is_file(C_DATI_PATH."/versione.php")) include(C_DATI_PATH."/versione.php");
else define('C_DIFF_ORE',0);
} # fine if (!defined('C_DIFF_ORE'))
if (function_exists('date_default_timezone_set')) {
if (defined('C_DIFF_TZ') and C_DIFF_TZ != "" and C_DIFF_ORE == 0) date_default_timezone_set(C_DIFF_TZ);
elseif (!ini_get('date.timezone')) date_default_timezone_set('UTC');
} # fine if (function_exists('date_default_timezone_set'))
$anno_corrente = date("Y",(time() + (C_DIFF_ORE * 3600)));
if (empty($anno)) {
if ($id_sessione) $anno = substr($id_sessione,0,4);
else {
if (!defined('C_GIORNI_NUOVO_ANNO')) include("./costanti.php");
$anno = date("Y",(time() + (C_DIFF_ORE * 3600) - (C_GIORNI_NUOVO_ANNO * 86400)));
# Se ci troviamo nel periodo di C_GIORNI_NUOVO_ANNO ma il nuovo anno è già stato creato o non c'è nessuno dei 2
if ($anno_corrente != $anno) {
if (@is_file(C_DATI_PATH."/selectperiodi$anno_corrente.1.php") or !@is_file(C_DATI_PATH."/selectperiodi$anno.1.php")) $anno = $anno_corrente;
} # fine if ($anno_corrente != $anno)
} # fine else if ($id_sessione)
} # fine if (empty($anno))
if ($id_sessione and $pag != "inizio.php" and $pag != "api.php" and $pag != "aggiorna.php" and !is_file(C_DATI_PATH."/selectperiodi$anno.1.php")) $id_sessione = "";


if (isset($vers_hinc)) $vers_hinc = "?v=".C_PHPR_VERSIONE_NUM;

if ($_SERVER['SERVER_NAME']) $HOSTNAME = $_SERVER['SERVER_NAME'];
elseif ($SERVER_NAME) $HOSTNAME = $SERVER_NAME;
$HOSTNAME = htmlspecialchars($HOSTNAME,ENT_COMPAT);

if (isset($_SERVER['HTTP_USER_AGENT']) and (strstr($_SERVER['HTTP_USER_AGENT'],'(iP') or strstr($_SERVER['HTTP_USER_AGENT'],'Android') or strstr($_SERVER['HTTP_USER_AGENT'],'webOS') or strstr($_SERVER['HTTP_USER_AGENT'],'BlackBerry') or strstr($_SERVER['HTTP_USER_AGENT'],'Mobile') or strstr($_SERVER['HTTP_USER_AGENT'],'Opera Mini'))) $mobile_device = 1;
else $mobile_device = 0;


if (function_exists('aggslashdb')) {
$anno = aggslashdb($anno);
$id_sessione = aggslashdb($id_sessione);
$HOSTNAME = aggslashdb($HOSTNAME);
} # fine if (function_exists('aggslashdb'))
else {
$anno = addslashes($anno);
$id_sessione = addslashes($id_sessione);
$HOSTNAME = addslashes($HOSTNAME);
} # fine else if (function_exists('aggslashdb'))



if (!$parole_sost) {
function mex ($messaggio,$pagina) {

global $lingua_mex;
if ($lingua_mex != "ita") {
include("./includes/lang/$lingua_mex/$pagina");
} # fine if ($lingua_mex != "ita")
elseif ($pagina == "unit.php") include("./includes/unit.php");

return $messaggio;

} # fine function mex
} # fine if (!$parole_sost)


else {
function mex ($messaggio,$pagina) {

global $lingua_mex;
if ($lingua_mex != "ita") {
include("./includes/lang/$lingua_mex/$pagina");
} # fine if ($lingua_mex != "ita")
elseif ($pagina == "unit.php") include("./includes/unit.php");
if (substr($messaggio,0,4) != 'var_') @include(C_DATI_PATH."/parole_sost.php");

return $messaggio;

} # fine function mex
} # fine else if (!$parole_sost)



function fixset (&$var) {

if (isset($var)) return $var;
else return null;

} # fine function fixset



function fix_set (&$var) {

if (!isset($var)) $var = null;
return $var;

} # fine function fix_set



function fixstr (&$var) {

if (isset($var)) return (string) $var;
else return (string) "";

} # fine function fixstr



function controlla_anno (&$anno) {

$verificato = "SI";
if (!isset($anno)) $verificato = "NO";
else {
$verifica_num = preg_replace("/[0-9]/","",$anno);
if (strcmp((string) $verifica_num,"")) $verificato = "NO";
if (strlen($anno) != 4) $verificato = "NO";
$prime_cifre = substr($anno,0,2);
if ($prime_cifre != 18 and $prime_cifre != 19 and $prime_cifre != 20 and $prime_cifre != 21) $verificato = "NO";
} # fine else (!isset($anno)) 

return $verificato;

} # fine function controlla_anno

# controllo che l'anno passato a tutte le pagine non sia falso
if (controlla_anno($anno) != "SI") $anno = "";



# parametro deve essere positivo o 0
function controlla_num_pos ($num) {

$verificato = "SI";
if (is_array($num)) $num = "";
else $num = (string) $num;
if (!strcmp($num,"")) $verificato = "NO";
$verifica_num = preg_replace("/[0-9]/","",$num);
if (strcmp($verifica_num,"")) $verificato = "NO";

return $verificato;

} # fine function controlla_num_pos



function controlla_num ($num) {

$verificato = "SI";
$uno = substr($num,0,1);
if ($uno == "-") $num = substr($num,1);
$verifica_num = preg_replace("/[0-9]/","",$num);
if (strcmp((string) $verifica_num,"")) $verificato = "NO";

return $verificato;

} # fine function controlla_num



function nome_valuta ($id_utente_valuta = "") {

global $tablepersonalizza;
if ($id_utente_valuta) $id_utente = $id_utente_valuta;
else global $id_utente;
$nome_valuta = esegui_query("select * from $tablepersonalizza where idpersonalizza = 'valuta' and idutente = '$id_utente'");
$nome_valuta = risul_query($nome_valuta,0,'valpersonalizza');

return $nome_valuta;

} # fine function nome_valuta



function altre_valute ($id_utente_valuta = "") {

$altre_valute = array();
global $tablepersonalizza;
if ($id_utente_valuta) $id_utente = $id_utente_valuta;
else global $id_utente;
$valute = esegui_query("select valpersonalizza from $tablepersonalizza where idpersonalizza = 'valuta2' and idutente = '$id_utente' order by valpersonalizza ");
$altre_valute['num'] = numlin_query($valute);
for ($num1 = 0 ; $num1 < $altre_valute['num'] ; $num1++) {
$nome = explode(">",risul_query($valute,$num1,'valpersonalizza'));
$altre_valute[$num1]['nome'] = $nome[0];
$altre_valute[$num1]['cambio'] = $nome[1];
$altre_valute[$num1]['arrotond'] = $nome[2];
$altre_valute['id'][$nome[0]] = $num1;
} # fine for $num1

return $altre_valute;

} # fine function altre_valute



function stile_data ($id_utente_st = "") {

global $tablepersonalizza;
if ($id_utente_st) $id_utente = $id_utente_st;
else global $id_utente;
$stile_data = esegui_query("select * from $tablepersonalizza where idpersonalizza = 'stile_data' and idutente = '$id_utente'");
$stile_data = risul_query($stile_data,0,'valpersonalizza');

return $stile_data;

} # fine function stile_data



function formatta_data ($data,$stile_data="europa") {

$data = (string) $data;
$giorno = htmlspecialchars(substr($data,8,2));
$mese = htmlspecialchars(substr($data,5,2));
$anno = htmlspecialchars(substr($data,0,4));
#$formato = "d-m-Y";
#if ($stile_data == 'usa') $formato = "m-d-Y";
#$data_formattata = date ($formato, mktime(0,0,0,$mese,$giorno,$anno));
switch ($stile_data) {
case "usa": $data_formattata = $mese."-".$giorno."-".$anno; break;
default: $data_formattata = $giorno."-".$mese."-".$anno;
} # fine switch ($stile_data)
$data_formattata .= htmlspecialchars(substr($data,10));

return $data_formattata;

} # fine function formatta_data



function stile_soldi ($id_utente_st = "") {

global $tablepersonalizza;
if ($id_utente_st) $id_utente = $id_utente_st;
else global $id_utente;
$stile_soldi = esegui_query("select * from $tablepersonalizza where idpersonalizza = 'stile_soldi' and idutente = '$id_utente'");
$stile_soldi = risul_query($stile_soldi,0,'valpersonalizza');

return $stile_soldi;

} # fine function stile_soldi



function punti_in_num ($num,$stile_soldi="europa",$decimali="") {

#$uno = substr ($num,0,1);
#if ($uno == "-") { $num = substr ($num,1); }
#$num = strrev($num);
#$num = chunk_split($num,3,".");
#$num = substr ($num,0,-1);
#$num = strrev($num);
#if ($uno == "-") { $num = "-".$num; }

if (!strcmp((string) $decimali,"")) {
if (!strstr((string) $num,".") or substr(strstr((string) $num,"."),1) == 0) $decimali = 0;
else $decimali = 2;
} # fine (!strcmp((string) $decimali,""))
if ($stile_soldi == "usa") $num = number_format((float) $num,$decimali);
else $num = number_format((float) $num,$decimali,",",".");

return $num;

} # fine function punti_in_num



function virgola_in_num ($num,$stile_soldi="europa") {

if ($stile_soldi == "europa") $num = str_replace(".",",",(string) $num);

return $num;

} # fine function virgola_in_num



function controlla_soldi ($num,$pos="NO") {

$verificato = "SI";
$num = (string) $num;
$uno = substr($num,0,1);
if ($uno == "-" and $pos == "NO") $num = substr($num,1);
#$num = str_replace(",",".",$num);
$parti = explode(".",$num);
if (count($parti) > 2) $verificato = "NO";
$num = str_replace(".","",$num);
$verifica_num = preg_replace("/[0-9]/","",$num);
if (strcmp((string) $verifica_num,"")) $verificato = "NO";

return $verificato;

} # fine function controlla_soldi



function formatta_soldi ($num) {

if (strcmp((string) $num,"")) {
$num = str_replace(",",".",$num);
if (strstr($num,".")) {
$parti = explode(".",$num);
if (!empty($parti[1])) {
if (strlen($parti[1]) > 2) {
$parti[1] = substr($parti[1],0,2).".".substr($parti[1],2);
$parti[1] = round((float) $parti[1]);
if (strlen($parti[1]) == 1) $parti[1] = "0".$parti[1];
elseif ((int) $parti[1] == 100) {
$parti[0] = (int) $parti[0] + 1;
$parti[1] = "00";
} # fine elseif ((int) $parti[1] == 100)
} # fine if (strlen($parti[1]) > 2)
$num = $parti[0].".".$parti[1];
settype($num,'float');
} # fine if (!empty($parti[1]))
else $num = (int) $parti[0];
} # fine if (strstr($num,"."))
else $num = (int) $num;
} # fine if (strcmp((string) $num,""))

return $num;

} # fine function formatta_soldi



function togli_acapo($stringa) {

$stringa = str_replace("\r\n","",(string) $stringa);
$stringa = str_replace("\r","",$stringa);
$stringa = str_replace("\n","",$stringa);
$stringa = str_replace("
","",$stringa);

return $stringa;

} # fine function togli_acapo



function aggiungi_slash($stringa) {

$stringa = str_replace("\\","\\\\",$stringa);
$stringa = str_replace("\$","\\\$",$stringa);
$stringa = str_replace("\"","\\\"",$stringa);

return $stringa;

} # fine function aggiungi_slash



function strip_magic_slashs ($val) {
if (@get_magic_quotes_gpc()) $val = stripslashes($val);
return $val;
} # fine function strip_magic_slashs



function prendi_numero_versione ($tableversioni,$idversioni="idversioni",$num_versione="num_versione") {

$versione = esegui_query("select * from $tableversioni where $idversioni = '2'");
if (numlin_query($versione)) {
$versione = risul_query($versione,0,$num_versione);
$versione = $versione + 1;
# mariadb per tipo float non scrive numeri maggiori di 1000000
if ($versione >= 1000000) $versione = 100;
esegui_query("update $tableversioni set $num_versione = '$versione' where $idversioni = '2'");
} # fine if (numlin_query($versione))
else {
$versione = 100;
esegui_query("insert into $tableversioni ($idversioni,$num_versione) values ('2','100') ");
} # fine else if (numlin_query($versione))

return $versione;

} # fine function prendi_numero_versione



function crea_val_casuale ($lung=16) {

$car = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$val_casuale = "";
if (function_exists('random_int')) {
for ($num1 = 0 ; $num1 < $lung ; $num1++) $val_casuale .= substr($car,random_int(0,61),1);
} # fine if (function_exists('random_int')) 
else {
list($usec, $sec) = explode(' ', microtime());
mt_srand($sec + (int) ((float) $usec * 1000000.0));
for ($num1 = 0 ; $num1 < $lung ; $num1++) $val_casuale .= substr($car,mt_rand(0,61),1);
} # fine else if (function_exists('random_int')) 

return $val_casuale;

} # fine function crea_val_casuale



function crea_id_sessione ($anno,$tableversioni,$lung=16,$diff_ore=1,$idversioni="idversioni",$num_versione="num_versione") {

if ($anno) $adesso = $anno.date("mdHis",(time() + (C_DIFF_ORE * 3600)));
else {
if ($diff_ore) $adesso = date("YmdHis",(time() + (C_DIFF_ORE * 3600)));
else $adesso = date("YmdHis");
} # fine else if ($anno)
if (is_numeric($tableversioni)) $versione_unica = $tableversioni;
else $versione_unica = prendi_numero_versione($tableversioni,$idversioni,$num_versione);
$val_casuale = crea_val_casuale($lung);
$id_sessione = $adesso.$val_casuale.$versione_unica;

return $id_sessione;

} # fine function crea_id_sessione



#Funzione per controllare le sessioni

$id_utente = "";


function controlla_login (&$numconnessione,&$PHPR_TAB_PRE,&$id_sessione,$nome_utente_phpr,$password_phpr,$anno) {

$id_utente = "";
$messaggio_errore = "";
$avviso_giorni_scadenza = 0;
if (defined('C_FILE_SCADENZA_ACCOUNT') and C_FILE_SCADENZA_ACCOUNT != "") {
$scadenza = trim(@implode("",@file("./".C_FILE_SCADENZA_ACCOUNT)));
#$adesso = date("YmdHis");
$adesso = date("Ymd")."000000";
if (!$scadenza or $scadenza < $adesso) {
$disattivato = 1;
$pag = "";
$titolo = "HotelDruid expired account";
$lingua_mex = "en";
include("./includes/head.php");
if (defined("C_HTML_PRE_LOGIN") and C_HTML_PRE_LOGIN != "") echo C_HTML_PRE_LOGIN;
echo "Expired account.<br>";
if (defined("C_HTML_POST_LOGIN") and C_HTML_POST_LOGIN != "") echo C_HTML_POST_LOGIN;
include("./includes/foot.php");
} # fine (!$scadenza or $scadenza < $adesso)
elseif (defined('C_HTML_POST_LOGIN') and C_HTML_POST_LOGIN != "") {
$giorni_avviso = 3;
$limite_giorni_avviso = date("YmdHis",(time() + ($giorni_avviso * 86400)));
if ($scadenza < $limite_giorni_avviso) $avviso_giorni_scadenza = 1;
} # fine elseif (defined('C_HTML_POST_LOGIN') and C_HTML_POST_LOGIN != "")
} # fine (defined("C_FILE_SCADENZA_ACCOUNT") and C_FILE_SCADENZA_ACCOUNT != "")
if (!isset($disattivato)) {

global $lingua_mex,$locale_mex,$tema,$pag,$ILIKE,$LIKE,$DATETIME,$nome_utente_login,$PHPR_LOG,$cookie_sess;
@include(C_DATI_PATH."/lingua.php");
#@include_once(C_DATI_PATH."/versione.php");
$locale_mex = "";
if (!empty($lingua[1])) {
$locale_mex = substr(strstr($lingua[1],"-"),1);
$lingua_mex = str_replace("-$locale_mex","",$lingua[1]);
if (!$lingua_mex or !@is_dir("./includes/lang/$lingua_mex")) $lingua_mex = "ita";
elseif ($locale_mex and !@is_dir("./includes/lang/$lingua_mex/locales/$locale_mex")) $locale_mex = "";
} # fine if (!empty($lingua[1]))
else $lingua_mex = "ita";
$nome_utente_login = "";


if (!is_file(C_DATI_PATH."/abilita_login")) {
if ($pag == "inizio.php" or $pag == "creadb.php" or $pag == "aggiorna.php" or @is_file(C_DATI_PATH."/selectperiodi$anno.1.php")) {
$id_utente = 1;
if (isset($_SERVER['HTTP_REFERER']) and isset($_SERVER['SERVER_NAME'])) {
if ((!empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'off') or (isset($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] == "443")) $url_dom = "https://".$_SERVER['SERVER_NAME'];
else $url_dom = "http://".$_SERVER['SERVER_NAME'];
$url_ref = substr($_SERVER['HTTP_REFERER'],0,(strlen($url_dom) + 1));
if ($url_ref != $url_dom."/" and $url_ref != $url_dom.":") $id_utente = "";
} # fine if (isset($_SERVER['HTTP_REFERER']) and isset($_SERVER['SERVER_NAME']))
} # fine if ($pag == "inizio.php" or $pag == "creadb.php" or $pag == "aggiorna.php" or...
else echo "<br>The requested year does not exist.<br><br><a href=\"./inizio.php\">Go back</a><br>";
} # fine if (!is_file(C_DATI_PATH."/abilita_login"))

else {
if (C_VERSIONE_ATTUALE < 3.04) {
define('C_MIN_SESSIONE',90);
define('C_USA_COOKIES',0);
} # fine if (C_VERSIONE_ATTUALE < 3.04)

if (!$id_sessione) {
if ($nome_utente_phpr and $password_phpr) {
if (!$numconnessione) {
include(C_DATI_PATH."/dati_connessione.php");
include("./includes/funzioni_$PHPR_DB_TYPE.php");
$numconnessione = connetti_db($PHPR_DB_NAME,$PHPR_DB_HOST,$PHPR_DB_PORT,$PHPR_DB_USER,$PHPR_DB_PASS,$PHPR_LOAD_EXT);
if (!$numconnessione) exit('No database cnnection');
} # fine if (!$numconnessione)
$tableutenti = $PHPR_TAB_PRE."utenti";
$tablesessioni = $PHPR_TAB_PRE."sessioni";
$tableversioni = $PHPR_TAB_PRE."versioni";
$tabletransazioni = $PHPR_TAB_PRE."transazioni";
$tabelle_lock = array($tableversioni,$tablesessioni,$tabletransazioni);
$altre_tab_lock = array($tableutenti);
$tabelle_lock = lock_tabelle($tabelle_lock,$altre_tab_lock);
$nome_utente_phpr = trim($nome_utente_phpr);

$sec_log_sbagliati = 5;
$limite_login_sbagliati = date("Y-m-d H:i:s",(time() - $sec_log_sbagliati + (C_DIFF_ORE * 3600)));
$ultimo_login_sbagliato = esegui_query("select ultimo_accesso from $tabletransazioni where tipo_transazione = 'err_l' and dati_transazione1 = '".aggslashdb($nome_utente_phpr)."' and ultimo_accesso >= '$limite_login_sbagliati'");
if (numlin_query($ultimo_login_sbagliato) > 0) {
$mostra_form_login = 1;
$messaggio_errore .= mex("Dopo un login <div style=\"display: inline; color: red;\">errato</div> si devono attendere","funzioni.php")." $sec_log_sbagliati ".mex("secondi","funzioni.php").".<br><br>";
} # fine if (numlin_query($ultimo_login_sbagliato) > 0)
else {
$max_log_sbagliati = 7;
$num_log_avviso = 3;
$limite_transazioni_vecchie = date("Y-m-d H:i:s",(time() - (C_MIN_SESSIONE * 60) + (C_DIFF_ORE * 3600)));
esegui_query("delete from $tabletransazioni where ultimo_accesso <= '$limite_transazioni_vecchie'");
$login_sbagliati = esegui_query("select tipo_transazione from $tabletransazioni where tipo_transazione = 'err_l' and dati_transazione1 = '".aggslashdb($nome_utente_phpr)."' ");
$login_sbagliati = numlin_query($login_sbagliati);
if ($login_sbagliati >= $max_log_sbagliati) {
$messaggio_errore .= mex("Numero eccesivo di login <div style=\"display: inline; color: red;\">errati</div> negli ultimi","funzioni.php")." ".C_MIN_SESSIONE." ".mex("minuti","funzioni.php").".<br><br>";
if (defined("C_HTML_POST_LOGIN") and C_HTML_POST_LOGIN != "") $messaggio_errore .= mex("È possibile reimpostare la password dal proprio account di hosting","funzioni.php").".".C_HTML_POST_LOGIN;
} # fine if ($login_sbagliati >= $max_log_sbagliati)
else {
if ($pag != "inizio.php" and !@is_file(C_DATI_PATH."/selectperiodi$anno.1.php")) {
if ($_SERVER['REQUEST_METHOD'] == "GET") {
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\"
        \"http://www.w3.org/TR/html4/strict.dtd\">
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\" >
<meta http-equiv=\"refresh\" content=\"0; url=inizio.php?nome_utente_phpr=".urlencode($nome_utente_phpr)."&password_phpr=".urlencode($password_phpr)."\">
<title> Hoteldruid </title>
</head>
<body style=\"background-color: #ffffff;\">
<div>&nbsp;</div>
</body>
</html>
";
exit();
} # fine if ($_SERVER['REQUEST_METHOD'] == "GET")
else {
$mostra_form_login = 1;
$pag = "inizio.php";
} # fine else if ($_SERVER['REQUEST_METHOD'] == "GET")
} # fine if ($pag != "inizio.php" and !@is_file(C_DATI_PATH."/selectperiodi$anno.1.php"))
else {

$nome_utente_phpr = aggslashdb($nome_utente_phpr);
$password_phpr = aggslashdb($password_phpr);
if (trim($password_phpr) != $password_phpr) $password_phpr_orig = $password_phpr;
$utente = esegui_query("select * from $tableutenti where nome_utente = '$nome_utente_phpr'");
if (numlin_query($utente) == 1) {
$nome_utente_login = risul_query($utente,0,'nome_utente');
$tipo_pass = risul_query($utente,0,'tipo_pass');
$password = risul_query($utente,0,'password');
if ($tipo_pass == "5") {
if (C_VERSIONE_ATTUALE > 1.32) {
$salt = (string) risul_query($utente,0,'salt');
for ($num1 = 0 ; $num1 < 15 ; $num1++) $password_phpr = md5($password_phpr.substr($salt,0,(20 - $num1)));
} # fine if (C_VERSIONE_ATTUALE > 1.32)
else $password_phpr = md5($password_phpr);
} # fine if ($tipo_pass == "5")
if ($password != $password_phpr and isset($password_phpr_orig)) {
$password_phpr = trim($password_phpr_orig);
if ($tipo_pass == "5" and C_VERSIONE_ATTUALE > 1.32) for ($num1 = 0 ; $num1 < 15 ; $num1++) $password_phpr = md5($password_phpr.substr($salt,0,(20 - $num1)));
} # fine if ($password != $password_phpr and isset($password_phpr_orig))

# login effettuato con successo
if ($password == $password_phpr and $tipo_pass != "n") {
$id_utente = risul_query($utente,0,'idutenti');
$id_sessione = crea_id_sessione($anno,$tableversioni);
$ultimo_accesso = date("Y-m-d H:i:s",(time() + (C_DIFF_ORE * 3600)));
global $_SERVER,$REMOTE_ADDR,$REMOTE_PORT,$HTTP_USER_AGENT,$test_cookie;
if (!empty($_SERVER['REMOTE_ADDR'])) $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
$REMOTE_ADDR = aggslashdb(fixstr($REMOTE_ADDR));
if (!empty($_SERVER['REMOTE_PORT'])) $REMOTE_PORT = $_SERVER['REMOTE_PORT'];
$REMOTE_PORT = aggslashdb(fixstr($REMOTE_PORT));
if (!empty($_SERVER['HTTP_USER_AGENT'])) $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
$HTTP_USER_AGENT = aggslashdb(fixstr($HTTP_USER_AGENT));
if ((isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == "on") or (isset($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] == "443")) $tipo_conn = "HTTPS";
else $tipo_conn = "HTTP";
if (C_USA_COOKIES) {
$id_cliente = crea_val_casuale();
if (function_exists('version_compare') and version_compare(PHP_VERSION, '5.2.0', '>=')) setcookie('HD_session_'.substr($id_sessione,30),$id_cliente,0,'','',1,1);
else setcookie('HD_session_'.substr($id_sessione,30),$id_cliente,0,'','',1);
if (empty($test_cookie) or fixset($_COOKIE['HD_test']) != $test_cookie) $id_cliente = "-$id_cliente";
else $cookie_sess = 1;
esegui_query("insert into $tablesessioni (idsessioni,idcliente,idutente,indirizzo_ip,tipo_conn,user_agent,ultimo_accesso) values ('$id_sessione','$id_cliente','$id_utente','$REMOTE_ADDR','$tipo_conn','$HTTP_USER_AGENT','$ultimo_accesso')","",$id_utente);
} # fine if (C_USA_COOKIES)
else {
if (C_VERSIONE_ATTUALE < 3.04) $id_sessione = substr($id_sessione,0,20).substr($id_sessione,30);
if (C_VERSIONE_ATTUALE >= 2.1) {
esegui_query("insert into $tablesessioni (idsessioni,idutente,indirizzo_ip,tipo_conn,user_agent,ultimo_accesso) values ('$id_sessione','$id_utente','$REMOTE_ADDR','$tipo_conn','$HTTP_USER_AGENT','$ultimo_accesso')","",$id_utente);
} # fine if (C_VERSIONE_ATTUALE >= 2.1)
else esegui_query("insert into $tablesessioni (idsessioni,idutente,indirizzo_ip,user_agent,ultimo_accesso) values ('$id_sessione','$id_utente','$REMOTE_ADDR','$HTTP_USER_AGENT','$ultimo_accesso')","",$id_utente);
} # fine else if (C_USA_COOKIES)
} # fine if ($password == $password_phpr and $tipo_pass != "n")

else $login_fallito = 1;
} # fine if (numlin_query($utente) == 1)
else $login_fallito = 1;
if (isset($login_fallito)) {
$mostra_form_login = 1;
$messaggio_errore .= mex("Nome utente o password <div style=\"display: inline; color: red;\">errati</div>","funzioni.php").".<br>";
if ($login_sbagliati >= ($max_log_sbagliati - $num_log_avviso - 1)) {
if (($max_log_sbagliati - $login_sbagliati - 1) > 1) $messaggio_errore .= mex("Mancano solo","funzioni.php")." <span class=\"colred\"><b>".($max_log_sbagliati - $login_sbagliati - 1)."</b></span> ".mex("tentativi prima del blocco dei login","funzioni.php").".<br>";
if (($max_log_sbagliati - $login_sbagliati - 1) == 1) $messaggio_errore .= mex("Manca solo","funzioni.php")." <span class=\"colred\"><b>1</b></span> ".mex("tentativo prima del blocco dei login","funzioni.php").".<br>";
} # fine if (numlin_query($utente) == 1)
$messaggio_errore .= "<br>";
$ultimo_accesso = date("Y-m-d H:i:s",(time() + (C_DIFF_ORE * 3600)));
global $_SERVER,$REMOTE_ADDR;
if ($_SERVER['REMOTE_ADDR']) $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
$REMOTE_ADDR = aggslashdb($REMOTE_ADDR);
$id_transazione = crea_id_sessione("",$tableversioni,8);
esegui_query("insert into $tabletransazioni (idtransazioni,tipo_transazione,dati_transazione1,dati_transazione2,ultimo_accesso) values ('$id_transazione','err_l','$nome_utente_phpr','$REMOTE_ADDR','$ultimo_accesso')");
} # fine if (isset($login_fallito))

} # fine else if ($pag != "inizio.php" and !@is_file(C_DATI_PATH."/selectperiodi$anno.1.php")
} # fine else if ($login_sbagliati >= $max_log_sbagliati)
} # fine else if (numlin_query($ultimo_login_sbagliato) > 0)
unlock_tabelle($tabelle_lock);
} # fine if ($nome_utente and $password)
else $mostra_form_login = 1;
} # fine if (!$id_sessione)

else {
if (!$numconnessione) {
include(C_DATI_PATH."/dati_connessione.php");
include("./includes/funzioni_$PHPR_DB_TYPE.php");
$numconnessione = connetti_db($PHPR_DB_NAME,$PHPR_DB_HOST,$PHPR_DB_PORT,$PHPR_DB_USER,$PHPR_DB_PASS,$PHPR_LOAD_EXT);
} # fine if ($numconnessione)
$tableutenti = $PHPR_TAB_PRE."utenti";
$tablesessioni = $PHPR_TAB_PRE."sessioni";
$tabelle_lock = array($tableutenti,$tablesessioni);
$tabelle_lock = lock_tabelle($tabelle_lock);
$limite_sessioni_vecchie = date("Y-m-d H:i:s",(time() - (C_MIN_SESSIONE * 60) + (C_DIFF_ORE * 3600)));
esegui_query("delete from $tablesessioni where ultimo_accesso <= '$limite_sessioni_vecchie'");
global $_SERVER,$REMOTE_ADDR,$REMOTE_PORT,$HTTP_USER_AGENT;
if (!empty($_SERVER['REMOTE_ADDR'])) $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
if (isset($REMOTE_ADDR)) $REMOTE_ADDR = aggslashdb($REMOTE_ADDR);
else $REMOTE_ADDR = "";
if (!empty($_SERVER['REMOTE_PORT'])) $REMOTE_PORT = $_SERVER['REMOTE_PORT'];
if (isset($REMOTE_PORT)) $REMOTE_PORT = aggslashdb($REMOTE_PORT);
else $REMOTE_PORT = "";
if (!empty($_SERVER['HTTP_USER_AGENT'])) $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
if (isset($HTTP_USER_AGENT)) $HTTP_USER_AGENT = aggslashdb($HTTP_USER_AGENT);
else $HTTP_USER_AGENT = "";
$ultimo_accesso = date("Y-m-d H:i:s",(time() + (C_DIFF_ORE * 3600)));
$sessione = esegui_query("select * from $tablesessioni where idsessioni = '$id_sessione'");
if (numlin_query($sessione) == 1) {
$indirizzo_ip = risul_query($sessione,0,'indirizzo_ip');
$tipo_conn = @risul_query($sessione,0,'tipo_conn');
$user_agent = risul_query($sessione,0,'user_agent');
if (($indirizzo_ip == $REMOTE_ADDR and $user_agent == $HTTP_USER_AGENT) or ($tipo_conn == "HTTPS" and ($_SERVER['HTTPS'] == "on" or $_SERVER['SERVER_PORT'] == "443"))) {
if (C_USA_COOKIES) {
$id_cliente = (string) risul_query($sessione,0,'idcliente');
if ($id_cliente) {
if (substr($id_cliente,0,1) == "-") {
if (fixset($_COOKIE['HD_session_'.substr($id_sessione,30)]) == substr($id_cliente,1)) {
esegui_query("update $tablesessioni set idcliente = '".substr($id_cliente,1)."' where idsessioni = '$id_sessione'");
$cookie_sess = 1;
} # fine if (fixset($_COOKIE['HD_session_'.substr($id_sessione,30)]) == substr($id_cliente,1))
else esegui_query("update $tablesessioni set idcliente = '' where idsessioni = '$id_sessione'");
} # fine if (substr($id_cliente,0,1) == "-")
else {
if (fixset($_COOKIE['HD_session_'.substr($id_sessione,30)]) != $id_cliente) $sessione_scaduta = 1;
else $cookie_sess = 1;
} # fine else if (substr($id_cliente,0,1) == "-")
} # fine if ($id_cliente)
} # fine if (C_USA_COOKIES)
if (!isset($sessione_scaduta)) {
$id_utente_presunto = risul_query($sessione,0,'idutente');
$utente_presente = esegui_query("select nome_utente from $tableutenti where idutenti = '$id_utente_presunto'");
if (numlin_query($utente_presente) == 1) {
$nome_utente_login = risul_query($utente_presente,0,'nome_utente');
$id_utente = $id_utente_presunto;
esegui_query("update $tablesessioni set ultimo_accesso = '$ultimo_accesso' where idsessioni = '$id_sessione'");
} # fine if (numlin_query($utente_presente) == 1)
else $sessione_scaduta = 1;
} # fine if (!isset($sessione_scaduta))
} # fine if (($indirizzo_ip == $REMOTE_ADDR and $user_agent == $HTTP_USER_AGENT) or...
else $sessione_scaduta = 1;
} # fine if (numlin_query($sessione) == 1)
else $sessione_scaduta = 1;
unlock_tabelle($tabelle_lock);

if (isset($sessione_scaduta)) {
$mostra_form_login = 1;
$tableversioni = $PHPR_TAB_PRE."versioni";
$tabletransazioni = $PHPR_TAB_PRE."transazioni";
$tabelle_lock = array($tableversioni,$tablesessioni,$tabletransazioni);
$tabelle_lock = lock_tabelle($tabelle_lock);
esegui_query("delete from $tabletransazioni where ultimo_accesso <= '$limite_sessioni_vecchie' ");
$max_sessioni_sbagliate = 300;
$sessioni_sbagliate = esegui_query("select tipo_transazione from $tabletransazioni where tipo_transazione = 'err_s' and dati_transazione1 = '$REMOTE_ADDR' ");
$sessioni_sbagliate = numlin_query($sessioni_sbagliate);
if ($sessioni_sbagliate >= $max_sessioni_sbagliate) {
# Cancellando tutte le sessioni chi volesse scoprirne una dovrà ripartire da zero e anche a eventuali altri utenti verrà mostrato questo errore
esegui_query("delete from $tablesessioni where indirizzo_ip = '$REMOTE_ADDR'");
$messaggio_errore .= mex("Numero eccesivo di sessioni <span style=\"color: red;\">errate</span> originate dal tuo indirizzo IP negli ultimi","funzioni.php")." ".C_MIN_SESSIONE." ".mex("minuti","funzioni.php").".
 ".mex("È possibile che qualcuno dalla tua stessa rete stia tentando di rubare il tuo accesso","funzioni.php").".<br><br>";
} # fine if ($sessioni_sbagliate >= $max_sessioni_sbagliate)
else {
$messaggio_errore .= "".mex("Sessione <div style=\"display: inline; color: red;\">scaduta</div>","funzioni.php").".<br><br>";
$id_transazione = crea_id_sessione("",$tableversioni,8);
esegui_query("insert into $tabletransazioni (idtransazioni,tipo_transazione,dati_transazione1,ultimo_accesso) values ('$id_transazione','err_s','$REMOTE_ADDR','$ultimo_accesso')");
} # fine else if ($sessioni_sbagliate >= $max_sessioni_sbagliate)
unlock_tabelle($tabelle_lock);
} # fine if (isset($sessione_scaduta))
} # fine else if (!$id_sessione)

if ($messaggio_errore or isset($mostra_form_login)) {
$input_var_passate = "";
if ($pag == "aggiorna.php") $action = "aggiorna.php";
else {
if (!isset($_SERVER['HTTP_REFERER']) or !isset($_SERVER['SERVER_NAME'])) $pag = "";
else {
if ((!empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'off') or (isset($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] == "443")) $url_dom = "https://".$_SERVER['SERVER_NAME']."/";
else $url_dom = "http://".$_SERVER['SERVER_NAME']."/";
if (substr($_SERVER['HTTP_REFERER'],0,strlen($url_dom)) != $url_dom) $pag = "";
} # fine else if (!isset($_SERVER['HTTP_REFERER']) or !isset($_SERVER['SERVER_NAME']))
if (isset($_REQUEST) and is_array($_REQUEST) and $pag) {
$action = $pag;
reset($_REQUEST);
for ($num1 = 0 ; $num1 < count($_REQUEST); $num1++) {
$key = key($_REQUEST);
if (is_string($key) and $key != "id_sessione" and $key != "nome_utente_phpr" and $key != "password_phpr" and $key != "pass_cc" and $key != "logout" and $key != "test_cookie") $input_var_passate .= "<input type=\"hidden\" name=\"$key\" value=\"".htmlspecialchars(strip_magic_slashs(current($_REQUEST)))."\">";
next($_REQUEST);
} # fine for $num1
} # fine if (isset($_REQUEST) and is_array($_REQUEST) and $pag)
else $action = "inizio.php";
} # fine else if ($pag == "aggiorna.php")
if (C_USA_COOKIES) {
$test_cookie = crea_val_casuale();
if (function_exists('version_compare') and version_compare(PHP_VERSION, '5.2.0', '>=')) setcookie('HD_test',$test_cookie,0,'','',1,1);
else setcookie('HD_test',$test_cookie,0,'','',1);
$input_var_passate = "<input type=\"hidden\" name=\"test_cookie\" value=\"$test_cookie\">".$input_var_passate;
} # fine if (C_USA_COOKIES)
$pag = "login";
if (defined('C_NASCONDI_MARCA') and C_NASCONDI_MARCA == "SI") $titolo = "Login";
else $titolo = mex("Login per HotelDruid","funzioni.php");
$show_bar = "NO";
$tema_corr = $tema[1];
if ($tema[1] and $tema[1] != "base" and @is_dir("./themes/".$tema[1]."/php")) include("./themes/".$tema[1]."/php/head.php");
else include("./includes/head.php");
if (!defined('C_URL_LOGO') or C_URL_LOGO == "") echo "<div id=\"flogin\">";
else echo "<div style=\"background: url(".C_URL_LOGO.") no-repeat right top; padding: 5px; line-height: 1.6;\">";
echo $messaggio_errore;
} # fine if ($messaggio_errore or isset($mostra_form_login))
if (isset($mostra_form_login)) {
if (@is_file(C_DATI_PATH."/dati_subordinazione.php")) include(C_DATI_PATH."/dati_subordinazione.php");
if (defined("C_HTML_PRE_LOGIN") and C_HTML_PRE_LOGIN != "") echo C_HTML_PRE_LOGIN;
$mess = $titolo;
if (isset($commento_subordinazione)) $mess .= " ($commento_subordinazione)";
echo "<big><big>$mess:</big></big><br><div style=\"height: 6px\"></div>
<form accept-charset=\"utf-8\" method=\"post\" action=\"$action\"><div>
<input type=\"hidden\" name=\"vers_hinc\" value=\"1\">
$input_var_passate
".mex("Nome utente","funzioni.php").": <input type=\"text\" name=\"nome_utente_phpr\" size=\"12\"><br>
".mex("Password","funzioni.php").": <input type=\"password\" name=\"password_phpr\" size=\"12\" autocorrect=\"off\" autocapitalize=\"off\"><br>
<table><tr><td style=\"height: 2px;\"></td></tr></table>
<button class=\"login\" type=\"submit\"><div>".mex("Entra","funzioni.php")."</div></button>
</div></form>";
if (defined("C_HTML_POST_LOGIN") and C_HTML_POST_LOGIN != "") {
echo C_HTML_POST_LOGIN;
if ($avviso_giorni_scadenza) {
$scadenza_vedi = "<span class=\"colred\">".substr($scadenza,0,4)."-".substr($scadenza,4,2)."-".substr($scadenza,6,2)." ".substr($scadenza,8,2).":".substr($scadenza,10,2)."</span> ".mex("orario del server","funzioni.php");
echo "<br><span class=\"colred\">".mex("Avviso","funzioni.php")."</span>:".mex("Mancano meno di","funzioni.php")." $giorni_avviso ".mex("giorni alla scadenza dell'account","funzioni.php")." ($scadenza_vedi).<br>";
} # fine if ($avviso_giorni_scadenza)
} # fine if (defined("C_HTML_POST_LOGIN") and C_HTML_POST_LOGIN != "")
} # fine if (isset($mostra_form_login))
if ($messaggio_errore or isset($mostra_form_login)) {

# You are not authorized to remove the following copyright notice. Ask for permission info@digitaldruid.net
if (!defined('C_MOSTRA_COPYRIGHT') or C_MOSTRA_COPYRIGHT != "NO") {
echo "<div style=\"position: absolute; bottom: 3%; right: 1%; background: #ffffff; color: #000000; font-size: 11px;\">
&nbsp;Website <a style=\"color: #000000;\" href=\"./mostra_sorgente.php\">engine code</a> is copyright © by DigitalDruid.Net.
<a style=\"color: #000000;\" href=\"http://www.hoteldruid.com\">HotelDruid</a> is a free software released under the GNU/AGPL.</div>";
} # fine if (!defined('C_MOSTRA_COPYRIGHT') or C_MOSTRA_COPYRIGHT != "NO")
echo "<div>";

if ($tema[1] and $tema[1] != "base" and @is_dir("./themes/".$tema[1]."/php")) include("./themes/".$tema[1]."/php/foot.php");
else include("./includes/foot.php");
} # fine if ($messaggio_errore or isset($mostra_form_login))

if ($id_utente and (isset($lingua[$id_utente]) and ($lingua[$id_utente] == "ita" or @is_dir("./includes/lang/".$lingua[$id_utente])))) $lingua_mex = $lingua[$id_utente];
} # fine else if (!is_file(C_DATI_PATH."/abilita_login"))


if ($id_utente and (!isset($tema[$id_utente]) or $tema[$id_utente] != "base") and (empty($tema[$id_utente]) or !@is_dir("./themes/".$tema[$id_utente]."/php"))) $tema[$id_utente] = "base";

if (defined('C_VERSIONE_ATTUALE') and C_VERSIONE_ATTUALE < C_PHPR_VERSIONE_NUM and $id_utente and $pag != "aggiorna.php") {
if (@is_file(C_DATI_PATH."/dati_connessione.php") or @is_file("./dati/connessione_db.php") or @is_file("./datipermanenti/connessione_db.inc")) {
$id_utente_azione_ic = null;
if ($pag == "interconnessioni.php" and $id_utente != 1) {
$id_utente_az = esegui_query("select idlocale from $PHPR_TAB_PRE"."interconnessioni where tipoid = 'id_utente_az' ");
if (numlin_query($id_utente_az) == 1) $id_utente_azione_ic = risul_query($id_utente_az,0,'idlocale');
} # fine if ($pag == "interconnessioni.php" and $id_utente != 1)
if ($id_utente == $id_utente_azione_ic) {
include("./includes/funzioni_aggiorna.php");
aggiorna_versione_phpr($numconnessione,$id_utente,$id_sessione,$nome_utente_phpr,$password_phpr,$anno);
} # fine if ($id_utente == $id_utente_azione_ic) 
else {
$show_bar = "NO";
$titolo = "HotelDruid";
if ($tema[$id_utente] and $tema[$id_utente] != "base" and @is_dir("./themes/".$tema[$id_utente]."/php")) include("./themes/".$tema[$id_utente]."/php/head.php");
else include("./includes/head.php");
echo mex("Il database deve essere aggiornato","funzioni.php").".<br>";
echo "<form method=\"post\" action=\"aggiorna.php\"><div>
<input type=\"hidden\" name=\"anno\" value=\"$anno\">
<input type=\"hidden\" name=\"id_sessione\" value=\"$id_sessione\">
<input class=\"sbutton\" type=\"submit\" value=\"".mex("Aggiorna","funzioni.php")."\">
</div></form>";
if ($tema[$id_utente] and $tema[$id_utente] != "base" and @is_dir("./themes/".$tema[$id_utente]."/php")) include("./themes/".$tema[$id_utente]."/php/foot.php");
else include("./includes/foot.php");
} # fine else if ($id_utente == $id_utente_azione_ic) 
$id_utente = "";
} # fine if (@is_file(C_DATI_PATH."/dati_connessione.php") or...
} # fine if (defined('C_VERSIONE_ATTUALE') and C_VERSIONE_ATTUALE < C_PHPR_VERSIONE_NUM and...


return $id_utente;

} # fine if (!isset($disattivato))

} # fine function controlla_login



function scrivi_file ($linee,$nome_file) {

if (function_exists('ini_set')) @ini_set('opcache.enable',0);
$scrittura_corretta = "SI";
if ($filelock = fopen("$nome_file.tmp.lock","w+")) {
flock($filelock,2);

if ($fileaperto = fopen("$nome_file.tmp","w+")) {
flock($fileaperto,2);
if (!@is_array($linee)) {
$linee2 = $linee;
unset($linee);
$linee[0] = $linee2;
unset($linee2);
} # fine if (!@is_array($linee))
for ($num1 = 0 ; $num1 < count($linee) ; $num1++) fwrite($fileaperto,$linee[$num1]);
flock($fileaperto,3);
fclose($fileaperto);
$linee2 = @file("$nome_file.tmp");
$num_linee2 = 0;
$ultima_linea = "";
for ($num1 = 0 ; $num1 < count($linee) ; $num1++) {
$linee[$num1] = str_replace("\r\n","\n",$linee[$num1]);
$linee[$num1] = str_replace("
","\n",$linee[$num1]);
$sub_linee = explode("\n",$linee[$num1]);
for ($num2 = 0 ; $num2 < count($sub_linee) ; $num2++) {
if ($num2 == 0)  $sub_linee[$num2] = $ultima_linea.$sub_linee[$num2];
if ($num2 != (count($sub_linee)-1)) {
if (togli_acapo($linee2[$num_linee2]) != $sub_linee[$num2]) $scrittura_corretta = "NO";
$num_linee2++;
} # fine if ($num2 != (count($sub_linee)-1))
else $ultima_linea = $sub_linee[$num2];
} # fine for $num2
} # fine for $num1
if (togli_acapo(fixset($linee2[$num_linee2])) != $ultima_linea) $scrittura_corretta = "NO";
if ($scrittura_corretta != "NO") {
if (@is_file("$nome_file.tmp")) {
@unlink("$nome_file");
if (!rename("$nome_file.tmp","$nome_file")) $scrittura_corretta = "NO";
} # fine if (@is_file("$nome_file.tmp"))
else $scrittura_corretta = "NO";
} # fine if ($scrittura_corretta != "NO")
} # fine if ($fileaperto = fopen("$nome_file.tmp","w+"))
else $scrittura_corretta = "NO";

flock($filelock,3);
fclose($filelock);
@unlink("$nome_file.tmp.lock");
} # fine if ($filelock = fopen("$nome_file.tmp.lock","w+"))
else $scrittura_corretta = "NO";

if ($scrittura_corretta == "NO") {
@unlink("$nome_file.tmp");
echo mex("<div style=\"display: inline; color: red;\">ERRORE</div> di scrittura del file","funzioni.php")." $nome_file.<br>";
} # fine if ($scrittura_corretta == "NO")

return $scrittura_corretta;

} # fine function scrivi_file



function crea_lock_file ($nome_file) {

$filelock = fopen("$nome_file.lock","w+");
flock($filelock,2);

return $filelock;

} # fine function crea_lock_file



function distruggi_lock_file ($filelock,$nome_file) {

flock($filelock,3);
fclose($filelock);
@unlink("$nome_file.lock");

} # fine function distruggi_lock_file



function elimina_caratteri_slash (&$var) {

if (isset($var)) {
$vr = str_replace ("'","",(string) $var);
$vr = str_replace ("\\","",$vr);
$vr = str_replace ("\"","",$vr);
} # fine if (isset($var))
else $vr = "";

return $vr;

} # fine function elimina_caratteri_slash



function recupera_dati_transazione (&$id_transazione,$id_sessione,$anno,$lock_tabletransazioni,&$tipo_transazione,$minuti_durata_sessione="",$cond_sessione="SI",$cond_tipo="NO",$transazioni="transazioni") {

global $PHPR_TAB_PRE;
$tabletransazioni = $PHPR_TAB_PRE.$transazioni;
$tablepersonalizza = $PHPR_TAB_PRE."personalizza";

$tipo_transazione = "";
$dati_transazione = "";
if (!empty($id_transazione)) {
$tabelle_lock = array($tabletransazioni);
if (!$minuti_durata_sessione) $altre_tab_lock = array($tablepersonalizza);
else $altre_tab_lock = array();
if ($lock_tabletransazioni == "SI") $tabelle_lock = lock_tabelle($tabelle_lock,$altre_tab_lock);
if (!$minuti_durata_sessione) {
$minuti_durata_sessione = esegui_query("select valpersonalizza_num from $tablepersonalizza where idpersonalizza = 'minuti_durata_sessione' and idutente = '1'");
$minuti_durata_sessione = risul_query($minuti_durata_sessione,0,'valpersonalizza_num');
} # fine if (!$minuti_durata_sessione)
$limite_transazioni_vecchie = date("Y-m-d H:i:s",(time() - ($minuti_durata_sessione * 60) + (C_DIFF_ORE * 3600)));
if ($cond_tipo != "NO") $cond_tipo = " and tipo_transazione = '$cond_tipo'";
else $cond_tipo = "";
esegui_query("delete from $tabletransazioni where ultimo_accesso <= '$limite_transazioni_vecchie' $cond_tipo");
$id_transazione = aggslashdb($id_transazione);
if ($cond_sessione == "SI") $cond_sessione = " and idsessione = '$id_sessione'";
else $cond_sessione = "";
$dati_transazione = esegui_query("select * from $tabletransazioni where idtransazioni = '$id_transazione' $cond_sessione");
if (numlin_query($dati_transazione) == 1) {
if ($anno == risul_query($dati_transazione,0,'anno')) {
$tipo_transazione = risul_query($dati_transazione,0,'tipo_transazione');
$ultimo_accesso = date("Y-m-d H:i:s",(time() + (C_DIFF_ORE * 3600)));
esegui_query("update $tabletransazioni set ultimo_accesso = '$ultimo_accesso' where idtransazioni = '$id_transazione' $cond_sessione");
} # fine if ($anno == risul_query($dati_transazione,0,'anno'))
} # fine if (numlin_query($dati_transazione) == 1)
if ($lock_tabletransazioni == "SI") unlock_tabelle($tabelle_lock);
} # fine if (!empty($id_transazione))

return $dati_transazione;

} # fine function recupera_dati_transazione



# Function che calcola l'id del periodo corrente per le prenotazioni già iniziate ==> fisse
function calcola_id_periodo_corrente ($anno,$anticipare="SI",$data_oggi="") {

global $id_utente;
global $PHPR_TAB_PRE;
$tableperiodi = $PHPR_TAB_PRE."periodi".$anno;
if ($anticipare == "SI") {
$tablepersonalizza = $PHPR_TAB_PRE."personalizza";
if ($id_utente == "") $id_utente_anticipa_ore = 1;
else $id_utente_anticipa_ore = $id_utente;
$ore_anticipa_periodo_corrente = esegui_query("select valpersonalizza_num from $tablepersonalizza where idpersonalizza = 'ore_anticipa_periodo_corrente' and idutente = '$id_utente_anticipa_ore'");
$ore_anticipa_periodo_corrente = risul_query($ore_anticipa_periodo_corrente,0,'valpersonalizza_num');
} # fine if ($anticipare == "SI")
else $ore_anticipa_periodo_corrente = 0;
if ($data_oggi) $oggi = aggslashdb($data_oggi);
else $oggi = date("Y-m-d",(time() + ($ore_anticipa_periodo_corrente * 3600) + (C_DIFF_ORE * 3600)));
$idperiodocorrente = esegui_query("select idperiodi from $tableperiodi where datainizio <= '$oggi' and datafine >= '$oggi' ");
$numidperiodocorrente = numlin_query($idperiodocorrente);
if ($numidperiodocorrente >= 1) {
$num_risposta = 0;
if ($numidperiodocorrente == 2) $num_risposta = 1;
$idperiodocorrente = risul_query($idperiodocorrente,$num_risposta,'idperiodi');
} # fine if ($numidperiodocorrente >= 1)
else {
$annocorrente = date("Y",(time() + (C_DIFF_ORE * 3600)));
if ($anno < $annocorrente) $idperiodocorrente = 100000;
if ($anno >= $annocorrente) $idperiodocorrente = -1;
} # fine else if ($numidperiodocorrente >= 1)

return $idperiodocorrente;

} # fine function calcola_id_periodo_corrente



function mostra_menu_date ($file,$name_date_var,$date_selected,$show_blank_option,$blank_selected,$id_utente,$tema,$standalone_dates_menu="",$second_date_selected="",$js="") {

global $last_dates_menu,$hide_default_dates,$y_ini_menu,$m_ini_menu,$d_ini_menu,$n_dates_menu,$d_increment,$d_names,$m_names,$dates_options_list,$current_dates_menu,$mos_tut_dat,$modifica_pers,$partial_dates;

if ($last_dates_menu != $file) {
$y_ini_menu = "";
$m_ini_menu = "";
$d_ini_menu = "";
$n_dates_menu = "";
$d_increment = "";
$partial_dates = 0;
if (!$standalone_dates_menu) $current_dates_menu = 1;
else $current_dates_menu = 0;
if (substr($file,0,(strlen(C_DATI_PATH) + 15)) == C_DATI_PATH."/selperiodimenu" and $mos_tut_dat == "SI" and $modifica_pers != "NO") include(C_DATI_PATH."/selectperiodi".substr($file,(strlen(C_DATI_PATH) + 15)));
else include($file);
} # fine if ($last_dates_menu != $file)
else if (!$standalone_dates_menu) $current_dates_menu++;

if ($blank_selected) $date_selected = "";
$num_periodi_date = count($d_increment);
if (($num_periodi_date > 1 or (isset($d_increment[0]) and $d_increment[0] > 1)) and $date_selected) {
for ($num1 = 0 ; $num1 < $num_periodi_date ; $num1++) {
$data_prec = "";
for ($num2 = 0 ; $num2 < $n_dates_menu[$num1] ; $num2++) {
$data_corr = date("Y-m-d",mktime(0,0,0,($m_ini_menu[$num1] + 1),($d_ini_menu[$num1] + ($num2 * $d_increment[$num1])),$y_ini_menu[$num1]));
if ($data_corr == $date_selected) break;
if ((int) str_replace("-","",$data_corr) > (int) str_replace("-","",$date_selected)) {
if (floor($current_dates_menu / 2) == (($current_dates_menu - 1) / 2)) $date_selected = $data_prec;
else $date_selected = $data_corr;
break;
} # fine if ((int) str_replace("-","",$data_corr) > (int) str_replace("-","",$date_selected))
$data_prec = $data_corr;
} # fine for $num2
if ($data_corr == $date_selected or $data_prec == $date_selected) break;
} # fine for $num1
} # fine if (($num_periodi_date > 1 or (isset($d_increment[0]) and...

if ($tema[$id_utente] != "base") include("./themes/".$tema[$id_utente]."/php/selectperiod.php");

if (!$hide_default_dates) {
if (!$js) $out = "<select name=\"$name_date_var\">";
else $out = "<select name=\"'+$name_date_var+'\">";
if ($show_blank_option) $out .= "<option value=\"\"$blank_selected>----</option>";
$out .= str_replace("value=\"$date_selected\">","value=\"$date_selected\" selected>",$dates_options_list);
$out .= "</select>";
if (!$js) echo $out;
else echo "$js += '".str_replace("\n","\\\n",$out)."';
";
} # fine if (!$hide_default_dates)
else unset($dates_options_list);

if (substr($file,0,(strlen(C_DATI_PATH) + 15)) == C_DATI_PATH."/selperiodimenu" and floor($current_dates_menu / 2) != (($current_dates_menu - 1) / 2) and $mos_tut_dat != "SI" and $modifica_pers != "NO" and $partial_dates) {
$lista_var = "";
if (isset($_REQUEST) and is_array($_REQUEST)) {
reset($_REQUEST);
for($num1 = 0 ; $num1 < count($_REQUEST); $num1++) {
if (key($_REQUEST) != "mos_tut_dat" and substr(key($_REQUEST),0,8) != "modifica") $lista_var .= "&amp;".urlencode(key($_REQUEST))."=".urlencode(strip_magic_slashs($_REQUEST[key($_REQUEST)]));
next($_REQUEST);
} # fine for $num1
} # fine if (isset($_REQUEST) and is_array($_REQUEST))
if ($lista_var) {
global $pag;
$lista_var = "?mos_tut_dat=SI".$lista_var;
$out = "<a href=\"$pag$lista_var\" style=\"text-decoration: none;\" title=\"".mex("mostra tutte le date","inizio.php")."\"><b style=\"font-weight: normal; color: #666666;\">&prime;</b></a>";
if (!$js) echo $out;
else echo "$js += '".str_replace("\n","\\\n",$out)."';
";
} # fine if ($lista_var)
} # fine if (substr($file,0,(strlen(C_DATI_PATH) + 15)) == C_DATI_PATH."/selperiodimenu" and floor($current_dates_menu / 2) != (($current_dates_menu - 1) / 2) and...

$last_dates_menu = $file;

} # fine function mostra_menu_date
if (isset($mos_tut_dat)) $mos_tut_dat = "SI";



function allunga_tempo_limite ($n_lim="",$agg_lim="") {

if (function_exists('set_time_limit')) {
if (!strcmp((string) $n_lim,"")) $n_lim = 420;
$lim_att = 30;
if (function_exists('ini_get')) {
$lim_att = ini_get('max_execution_time');
if (strcmp((string) $agg_lim,"")) $n_lim = $lim_att + $agg_lim;
} # fine if (function_exists('ini_get'))
if ($lim_att < $n_lim) set_time_limit($n_lim);
} # fine if (function_exists('set_time_limit')

} # fine function allunga_tempo_limite



function http_keep_alive ($car=" ") {

echo $car;
flush();
if (function_exists('ob_flush')) @ob_flush();

} # fine function http_keep_alive



function controlla_pag_origine (&$origine_passa) {

if (isset($origine_passa)) {
$origine = trim((string) $origine_passa);
if (substr($origine,0,2) == "./") $origine = substr($origine,2);
if (strstr($origine,"http") or strstr($origine,"..") or strstr($origine,"/") or strstr($origine,"\\")) $origine = "inizio.php";
} # fine if (isset($origine_passa))
else $origine = "";

return str_replace("\"","",str_replace(">","",str_replace("<","",$origine)));

} # fine function controlla_pag_origine



if (function_exists('mb_strcut')) {
# Taglia dopo un certo numero di byte (quindi può avere meno lettere di $fine - $ini). Per tagliare dopo numero di lettere usare tronca_testo() in funzioni_testo.php
function par_taglia ($str,$ini,$fine) { return mb_strcut($str,$ini,$fine); }
} # fine if (function_exists('mb_strcut'))
else {
function par_taglia ($str,$ini,$fine) { return substr($str,$ini,$fine); }
} # fine else if (function_exists('mb_strcut'))



if (function_exists('htmlspecialchars_decode')) {
function html_decod ($str) { return htmlspecialchars_decode((string) $str,ENT_QUOTES); }
} # fine if (function_exists('htmlspecialchars_decode'))
else {
function html_decod ($str) { return str_replace("&amp;","&",str_replace("&quot;","\"",str_replace("&apos;","'",str_replace("&#039;","'",str_replace("&lt;","<",str_replace("&gt;",">",(string) $str)))))); }
} # fine else if (function_exists('htmlspecialchars_decode'))




?>
