<?php

##################################################################################
#    Plugin "Anteprima email" per HOTELDRUID - testi dell'interfaccia
#    Lingue: italiano (predefinita), inglese, spagnolo.
#    Le altre lingue di HotelDruid usano i testi in inglese.
##################################################################################

if (!defined('HDP_CARICATORE')) return;



function apeml_testi ($lingua) {

$testi = array(
'ccn' => "Ccn",
'immagini' => "Immagini incorporate",
'corpo_vuoto' => "(testo vuoto)",
'mostra_sorgente' => "Mostra sorgente HTML",
'nascondi_sorgente' => "Nascondi sorgente HTML"
);

if ($lingua == "ita") return $testi;

if ($lingua == "es") {
$testi['ccn'] = "Cco";
$testi['immagini'] = "Imágenes incrustadas";
$testi['corpo_vuoto'] = "(texto vacío)";
$testi['mostra_sorgente'] = "Mostrar código HTML";
$testi['nascondi_sorgente'] = "Ocultar código HTML";
return $testi;
} # fine if ($lingua == "es")

$testi['ccn'] = "Bcc";
$testi['immagini'] = "Embedded images";
$testi['corpo_vuoto'] = "(empty text)";
$testi['mostra_sorgente'] = "Show HTML source";
$testi['nascondi_sorgente'] = "Hide HTML source";
return $testi;

} # fine function apeml_testi

?>
