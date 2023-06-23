<?php
//datos extraidos de https://www.meteogalicia.gal/datosred/infoweb/meteo/docs/rss/JSON_Pred_Concello_es.pdf
//dia (ma�ana y tarde)
$meteogalicia_prediccion_leyenda['-9999'] = 'No disponible';
$meteogalicia_prediccion_leyenda['101'] = 'Despejado';
$meteogalicia_prediccion_leyenda['102'] = 'Nubes altas';
$meteogalicia_prediccion_leyenda['103'] = 'Nubes y claros';
$meteogalicia_prediccion_leyenda['104'] = 'Nublado 75%';
$meteogalicia_prediccion_leyenda['105'] = 'Cubierto';
$meteogalicia_prediccion_leyenda['106'] = 'Nieblas';
$meteogalicia_prediccion_leyenda['107'] = 'Chubasco';
$meteogalicia_prediccion_leyenda['108'] = 'Chubasco (75%) ';
$meteogalicia_prediccion_leyenda['109'] = 'Chubasco nieve';
$meteogalicia_prediccion_leyenda['110'] = 'Llovizna';
$meteogalicia_prediccion_leyenda['111'] = 'Lluvia';
$meteogalicia_prediccion_leyenda['112'] = 'Nieve';
$meteogalicia_prediccion_leyenda['113'] = 'Tormenta';
$meteogalicia_prediccion_leyenda['114'] = 'Bruma';
$meteogalicia_prediccion_leyenda['115'] = 'Bancos de niebla';
$meteogalicia_prediccion_leyenda['116'] = 'Nubes medias';
$meteogalicia_prediccion_leyenda['117'] = 'Lluvia d�bil';
$meteogalicia_prediccion_leyenda['118'] = 'Chubascos d�biles';
$meteogalicia_prediccion_leyenda['119'] = 'Tormenta con pocas nubes';
$meteogalicia_prediccion_leyenda['120'] = 'Agua nieve';
$meteogalicia_prediccion_leyenda['121'] = 'Granizo';
//noche
$meteogalicia_prediccion_leyenda['201'] = 'Despejado';
$meteogalicia_prediccion_leyenda['202'] = 'Nubes altas';
$meteogalicia_prediccion_leyenda['203'] = 'Nubes y claros';
$meteogalicia_prediccion_leyenda['204'] = 'Nublado 75%';
$meteogalicia_prediccion_leyenda['205'] = 'Cubierto';
$meteogalicia_prediccion_leyenda['206'] = 'Nieblas';
$meteogalicia_prediccion_leyenda['207'] = 'Chubasco';
$meteogalicia_prediccion_leyenda['208'] = 'Chubasco (75%) ';
$meteogalicia_prediccion_leyenda['209'] = 'Chubasco nieve';
$meteogalicia_prediccion_leyenda['210'] = 'Llovizna';
$meteogalicia_prediccion_leyenda['211'] = 'Lluvia';
$meteogalicia_prediccion_leyenda['212'] = 'Nieve';
$meteogalicia_prediccion_leyenda['213'] = 'Tormenta';
$meteogalicia_prediccion_leyenda['214'] = 'Bruma';
$meteogalicia_prediccion_leyenda['215'] = 'Bancos de niebla';
$meteogalicia_prediccion_leyenda['216'] = 'Nubes medias';
$meteogalicia_prediccion_leyenda['217'] = 'Lluvia d�bil';
$meteogalicia_prediccion_leyenda['218'] = 'Chubascos d�biles';
$meteogalicia_prediccion_leyenda['219'] = 'Tormenta con pocas nubes';
$meteogalicia_prediccion_leyenda['220'] = 'Agua nieve';
$meteogalicia_prediccion_leyenda['221'] = 'Granizo';

/*
*  Puede obtener este icono en:
https://www.meteogalicia.gal/datosred/infoweb/meteo/imagenes/meteoros/
ceo/<Valor_Numerico>_fondo.png, donde <Valor_Numerico> corresponde con el valor de la
primera columna de la tabla anterior.
*/

$meteogalicia_prediccion_viento['-9999'] = 'No disponible';
$meteogalicia_prediccion_viento['299'] = 'Calma';
$meteogalicia_prediccion_viento['300'] = 'Viento variable';
$meteogalicia_prediccion_viento['301'] = 'Viento flojo del Norte (N)';
$meteogalicia_prediccion_viento['302'] = 'Viento flojo del Nord�s (NE)';
$meteogalicia_prediccion_viento['303'] = 'Viento flojo del Leste (E)';
$meteogalicia_prediccion_viento['304'] = 'Viento flojo del Sueste (SE)';
$meteogalicia_prediccion_viento['305'] = 'Viento flojo del Sur (S)';
$meteogalicia_prediccion_viento['306'] = 'Viento flojo del Sudoeste (SO)';
$meteogalicia_prediccion_viento['307'] = 'Viento flojo del Oeste (O)';
$meteogalicia_prediccion_viento['308'] = 'Viento flojo del Noroeste (NO)';
$meteogalicia_prediccion_viento['309'] = 'Viento moderado del Norte (N)';
$meteogalicia_prediccion_viento['310'] = 'Viento moderado del Nord�s (NE)';
$meteogalicia_prediccion_viento['311'] = 'Viento moderado del Leste (E)';
$meteogalicia_prediccion_viento['312'] = 'Viento moderado del Sueste (SE)';
$meteogalicia_prediccion_viento['313'] = 'Viento moderado del Sur (S)';
$meteogalicia_prediccion_viento['314'] = 'Viento  moderado  del  Sudoeste (SO)';
$meteogalicia_prediccion_viento['315'] = 'Viento moderado del Oeste (O)';
$meteogalicia_prediccion_viento['316'] = 'Viento  moderado  del  Noroeste (NO)';
$meteogalicia_prediccion_viento['317'] = 'Viento fuerte del Norte (N)';
$meteogalicia_prediccion_viento['318'] = 'Viento fuerte del Nord�s (NE)';
$meteogalicia_prediccion_viento['319'] = 'Viento fuerte del Leste (E)';
$meteogalicia_prediccion_viento['320'] = 'Viento fuerte del Sueste (SE)';
$meteogalicia_prediccion_viento['321'] = 'Viento fuerte del Sur (S)';
$meteogalicia_prediccion_viento['322'] = 'Viento fuerte del Sudoeste (SO)';
$meteogalicia_prediccion_viento['323'] = 'Viento fuerte del Oeste (O)';
$meteogalicia_prediccion_viento['324'] = 'Viento fuerte del Noroeste (NO)';
$meteogalicia_prediccion_viento['325'] = 'Viento muy fuerte del Norte (N)';
$meteogalicia_prediccion_viento['326'] = 'Viento muy fuerte del Nord�s (NE)';
$meteogalicia_prediccion_viento['327'] = 'Viento muy fuerte del Leste (E)';
$meteogalicia_prediccion_viento['328'] = 'Viento muy fuerte del Sueste (SE)';
$meteogalicia_prediccion_viento['329'] = 'Viento muy fuerte del Sur (S)';
$meteogalicia_prediccion_viento['330'] =
    'Viento  muy  fuerte  del  Sudoeste (SO)';
$meteogalicia_prediccion_viento['331'] = 'Viento muy fuerte del Oeste (O)';
$meteogalicia_prediccion_viento['332'] =
    'Viento  muy  fuerte  del  Noroeste (NO)';
?>
