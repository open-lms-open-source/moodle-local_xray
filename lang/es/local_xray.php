<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Local xray lang file Spanish
 *
 * @package   local_xray
 * @author    Pablo Pagnone
 * @author    German Vitale
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die();

/** @var string[] $string */
$string['navigation_xray'] = 'X-Ray Learning Analytics ';
$string['navitationcourse_xray'] = 'X-Ray Learning Analytics';
$string['pluginname'] = 'X-Ray Learning Analytics';
$string['reports'] = 'Reportes';
$string['analytics'] = 'Análisis de curso';
$string['xraydisplayheading'] = 'Integración de curso';
$string['xraydisplayheading_desc'] = 'Controla la visualización de información y links a los reportes en la página principal.';
$string['xraydisplaysystemheading'] = 'Reportes del sistema';
$string['xraydisplaysystemheading_desc'] = 'Controla si se muestran los reportes del sistema.';
$string['displaymenu'] = 'Mostrar menu de reportes.';
$string['displaymenu_desc'] = 'Controlar la visualización del menu de reportes en página principal del curso.';
$string['displaysystemreports'] = 'Mostrar reportes del sistema.';
$string['displaysystemreports_desc'] = 'Por defecto, los reportes del sistema no aparecen en el menú de reportes.'.
                                       ' Selecciona esta opción para mostrarlos.';
$string['displayheaderdata'] = 'Mostrar Análisis';
$string['displayheaderdata_desc'] = 'Controla la visualización de Análisis del curso en la página principal del curso.';
$string['debuginfo'] = 'Información:';
$string['cachedef_request'] = 'X-Ray caché de los pedidos';
$string['reportviewed'] = 'Reporte Visualizado';

/* Capabilities */
$string['xray:activityreportindividual_view'] = 'Ver reporte de métricas de actividad individuales';
$string['xray:activityreport_view'] = 'Ver reporte de actividad';
$string['xray:adminrecommendations_view'] = 'Ver las recomendaciones para el administrador';
$string['xray:dashboard_view'] = 'Ver interfaz de reportes';
$string['xray:discussionreport_view'] = 'Ver reporte de discusiones';
$string['xray:discussionreportindividual_view'] = 'Ver discusión - Reporte individual';
$string['xray:discussionreportindividualforum_view'] = 'Ver reporte individual de discusiones en foros';
$string['xray:discussionendogenicplagiarism_view'] = 'Ver reporte de superposición de palabras';
$string['xray:discussiongrading_view'] = 'Ver reporte de calificaciones de discusión';
$string['xray:gradebookreport_view'] = 'Ver reporte de calificaciones';
$string['xray:gradebookreportindividualquiz_view'] = 'Ver reporte individual de calificaciones para quiz';
$string['xray:risk_view'] = 'Ver reporte de estado de riesgo';
$string['xray:subscription_view'] = 'Suscribirse al reporte de correo electrónico';
$string['xray:systemreports_view'] = 'Ver los reportes del sistema';
$string['xray:teacherrecommendations_view'] = 'Ver las recomendaciones para el instructor';
$string['xray:view'] = 'Ver X-Ray Learning Analytics';

/* Categories for numbers values */
$string['high'] = 'Alto';
$string['low'] = 'Bajo';
$string['medium'] = 'Medio';

$string['highlyregular'] = 'Alta regularidad';
$string['irregular'] = 'Irregular';
$string['regular'] = 'Regular';

/* Report Activity Report*/
$string['activityreport'] = 'Actividad';
/* Report Activity Report Individual*/
$string['activityreportindividual'] = 'Métricas de actividad individuales';
/* Discussion report*/
$string['discussionreport'] = 'Discusiones';
/* Discussion report individual*/
$string['discussionreportindividual'] = 'Discusión - Reporte individual';
/* Discussion report individual forum*/
$string['discussionreportindividualforum'] = 'Reporte individual de discusiones en foros';
/* Discussion report Endogenic Plagiarism*/
$string['discussionendogenicplagiarism'] = 'Superposición de palabras';
/* Risk report*/
$string['risk'] = 'Estado de riesgo';
/* Discussiongrading report*/
$string['discussiongrading'] = 'Calificaciones de discusión';
/* Gradebook report*/
$string['gradebookreport'] = 'Calificaciones';

/* Columns reports */
$string['reportdate'] = 'Fecha de reporte';
$string['weeks'] = 'Semanas';
$string['week'] = 'Semana';

/* Error to load tables and images */
$string['error_loadimg'] = 'Error al cargar imagen, por favor intenta nuevamente recargando la página. Si el '.
                           'error persiste, por favor contacta con el administrador del sitio.';

/* Error Webservice */
$string['error_xray'] = 'Error al conectar con X-Ray Learning Analytics, por favor intenta nuevamente recargando'.
                        ' la página. Si el error '.
                        'persiste, por favor contacta con el administrador del sitio.';
$string['error_compress'] = 'No fue possible crear archivo comprimido. Por favor contacta con el administrador del sitio.';
$string['error_generic'] = '{$a}';
$string['error_fexists'] = '¡El archivo "{$a}" ya existe!';
$string['error_fnocreate'] = '¡No puedo crear el archivo "{$a}"!';
$string['error_systemreports_nourl'] = 'La url para obtener reportes de sistemas no esta configurada correctamente.';
$string['error_systemreports_gettoken'] = 'Error al obtener token para acceder a reportes de sistema.';
$string['error_systemreports_disabled'] = 'Los reportes del sistema no se están mostrando.'.
                                          ' Están inhabilitados en la página de configuración de X-Ray.';

/* Settings */
$string['xrayclientid'] = 'Identificador de cliente';
$string['xrayclientid_desc'] = 'Identificador de cliente para X-Ray Learning Analytics';
$string['xraypassword'] = 'Password para X-Ray Learning Analytics ';
$string['xraypassword_desc'] = 'Password utilizado para loguearse en X-Ray Learning Analytics';
$string['xrayurl'] = 'Url de X-Ray Learning Analytics';
$string['xrayurl_desc'] = 'Url de servidor X-Ray Learning Analytics.';
$string['xrayusername'] = 'Usuario de X-Ray Learning Analytics';
$string['xrayusername_desc'] = 'Usuario utilizado para loguearse en X-Ray Learning Analytics.';
$string['xrayawsheading'] = 'Sincronización de datos';
$string['xrayawsheading_desc'] = 'En esta sección tu puedes configurar la sincronización de datos con X-Ray Learning Analytics.';
$string['enablesync'] = 'Sincronizar datos';
$string['enablesync_desc'] = 'Activar sincronización automática de datos con X-Ray Learning Analytics.';
$string['awskey'] = 'Clave AWS';
$string['awskey_desc'] = 'Clave de acceso para AWS web services';
$string['awssecret'] = 'Secret para AWS';
$string['awssecret_desc'] = 'Clave de acceso para AWS web services';
$string['s3bucket'] = 'S3 bucket';
$string['s3bucket_desc'] = 'Nombre del bucket a usar para guardar datos subidos.';
$string['s3bucketregion'] = 'S3 region';
$string['s3bucketregion_desc'] = 'Región de destino del bucket.';
$string['enablepacker'] = 'Compresión nativa';
$string['enablepacker_desc'] = 'Una vez habilitida permite uso de compresores de sistema operativo.';
$string['packertar'] = 'Ejecutable GNU tar';
$string['packertar_desc'] = 'Configurar locación de <a href="http://www.gnu.org/software/tar/" '.
                            'target="_blank" title="GNU tar">GNU tar</a> ejecutable en tu servidor. Asegurate de '.
                            'instalar <a href="http://www.gnu.org/software/gzip/" target="_blank" title="GNU Gzip">GNU Gzip</a>.';
$string['exportlocation'] = 'Locacion de export';
$string['exportlocation_desc'] = 'Configura directorio local para almacenamiento temporario de data exportada. '.
                                 'Si lo dejas vacio (o ruta es invalida), Moodle tempdir es usado.';
$string['exporttime'] = 'Tiempo de exportacion';
$string['exporttime_desc'] = 'Configure tiempo permitido para exportacion de los datos.'.
                             'Si valor configurado es 0 exportacion es sin limite.';
$string['export_progress'] = 'Reiniciar informacion del progreso';
$string['export_progress_desc'] = 'Durante exportacion de los datos, informacion del progreso se guarda en '.
                                  'la base de datos. Markando esa opccion borraria esa informacion.';
$string['curlcache'] = 'Expiracion de Web service caché';
$string['curlcache_desc'] = 'Determina por cuanto tiempo guardar respuestas de Web service.'.
                            'Puesto en cero caché seria apagado. Para aclarar - solo respuestas exitosas se guardaran.';

$string['xrayadminserver'] = 'Servidor de administración X-Ray Learning Analytics';
$string['xrayadminserver_desc'] = 'Locación del servidor.';
$string['xrayadmin'] = 'Usuario administrador';
$string['xrayadmin_desc'] = 'Usuario para loguear en servidor de administración.';
$string['xrayadminkey'] = 'Clave de Administrador';
$string['xrayadminkey_desc'] = 'Clave de acceso para loguearse dentro de servidor de administración.';
$string['s3protocol'] = 'Protocolo de subida';
$string['s3protocol_desc'] = 'Determina protocolo que se debe usar para subir informacion extraida.';
$string['http'] = 'Protocolo HTTP';
$string['https'] = 'Protocolo seguro HTTP';
$string['s3uploadretry'] = 'Intentar de subir de nuevo';
$string['s3uploadretry_desc'] = 'Define cuantas veces sistema debe intentar de subir datos en el caso de falla.';

$string['useast1'] = 'US Standard (N. Virginia)';
$string['uswest2'] = 'US West (Oregon)';
$string['uswest1'] = 'US West (N. California)';
$string['euwest1'] = 'EU (Irlanda)';
$string['eucentral1'] = 'EU (Frankfurt)';
$string['apsoutheast1'] = 'Asia Pacific (Singapur)';
$string['apsoutheast2'] = 'Asia Pacific (Sidney)';
$string['apnortheast1'] = 'Asia Pacific (Tokio)';
$string['saeast1'] = 'South America (San Pablo)';

/* webservice api */
$string['xrayws_error_nocurl'   ] = 'Módulo cURL debe estar presente y activado!';
$string['xrayws_error_nourl'    ] = 'Debes espeficificar una URL!';
$string['xrayws_error_nomethod' ] = 'Debes especificar método requerido!';

/* Web service errors returned from X-Ray Learning Analytics*/
$string['xrayws_error_server'] = '{$a}';
$string['xrayws_error_curl'] = '{$a}';
$string['xrayws_error_graphs'] = 'Error al traer imagen ({$a->url}) para "{$a->graphelement}": {$a->error}.';
$string['xrayws_error_graphs_incorrect_contentype'] = 'Incorrecto content-type recibido desde xray webservice: {$a}.';

/* Scheduled task */
$string['datasync'] = 'Sincronización de datos';
$string['syncfailed'] = 'Sincronización de datos con X-Ray Learning Analytics ha fallado';
$string['unexperror'] = 'Error inesperado: ';
$string['syncfailedexplanation'] = 'Fallo al sincronizar datos con X-Ray Learning Analytics.';
$string['synclog'] = 'Mensaje de información de sincronización de datos con X-Ray Learning Analytics';
$string['synclogexplanation'] = 'Entrada de registro regular para sincronizar datos.';
$string['dataprune'] = 'Limpieza de datos obsoletos';

// Course Header.
$string['atrisk'] = 'En riesgo';
$string['dashboard'] = 'Interfaz';
$string['headline_lastweekwas_discussion'] = 'Semana anterior fueron {$a}.';
$string['averageofweek_integer'] = 'Promedio de la semana anterior fue {$a->previous} de {$a->total}.';
$string['averageofweek_gradebook'] = 'Promedio de la semana anterior fue {$a}%.';
$string['headline_lastweekwasof_activity'] = 'Semana anterior fueron {$a->current} de {$a->total}.';
$string['headline_studentatrisk'] = 'Estudiantes en <b>Riesgo</b> ayer.';
$string['headline_loggedstudents'] = 'Estudiantes <b>Logueados</b> en los últimos 7 dias.';
$string['headline_posts'] = '<b>Posts</b> en los últimos 7 dias.';
$string['headline_average'] = '<b>Promedio de calificaciones en curso</b> ayer.';
$string['link_gotoreport'] = 'Ir al reporte';
$string['arrow_increase'] = 'Esto es un incremento.';
$string['arrow_decrease'] = 'Esto es una disminucion.';
$string['arrow_same'] = 'Esto esta igual.';
$string['headline_number_of'] = '{$a->first} de {$a->second}';
$string['headline_number_percentage'] = '{$a}%';

// Jquery Tables (with plugin datatables).
$string['error_datatables'] = 'Error al traer datos para esta tabla. Por favor intenta nuevamente recargando la '.
                              'página. Si el error persiste, por favor contacta con el administrador del sitio.';
$string['sProcessingMessage'] = 'Trayendo datos, Por favor espera...';
$string['sFirst'] = 'Primero';
$string['sLast'] = 'Último';
$string['sNext'] = 'Siguiente';
$string['sPrevious'] = 'Anterior';
$string['sProcessing'] = 'Procesando...';
$string['sLengthMenu'] = 'Mostrar _MENU_';
$string['sZeroRecords'] = 'No se encontraron registros';
$string['sEmptyTable'] = 'No hay datos disponibles para esta tabla';
$string['sInfo'] = 'Mostrando _START_';
$string['sInfoEmpty'] = 'Mostrando 0';
$string['sLoadingRecords'] = 'Cargando...';
$string['sSortAscending'] = ': activar para ordenar columna ascendentemente';
$string['sSortDescending'] = ': activar para ordenar columna descendentemente';

/* Close modal */
$string['close'] = 'Cerrar';
/* Close Report Tables */
$string['closetable'] = 'Cerrar';

/*Accessible data */
$string['accessibledata'] = 'Datos accesibles';
$string['accessible_view_data'] = 'Ver datos';
$string['accessible_view_data_for'] = 'Datos accesibles para {$a} (nueva ventana)';
$string['accessible_emptydata'] = 'No hay datos disponibles para version accesible.';
$string['accessible_error'] = 'Versión accesible para este gráfico no fue encontrada en X-Ray Learning Analytics.';
$string['reports_help'] = 'Ayuda';
$string['accessibledata_of'] = 'Datos accesibles de {$a}';

// Tables names all report.
$string['activityreport_nonStarters'] = 'Estudiantes inactivos';// Activity and risk report.
$string['activityreport_studentList'] = 'Métricas de actividad'; // Activity report.
$string['risk_nonStarters'] = 'Estudiantes inactivos'; // Risk report.
$string['risk_riskMeasures'] = 'Métricas de riesgo'; // Risk report.
$string['gradebookreport_courseGradeTable'] = 'Calificaciones de los estudiantes'; // Gradebook report.
$string['gradebookreport_gradableItemsTable'] = 'Resumen de items calificables'; // Gradebook report.
$string['discussionreport_discussionMetrics'] = 'Métricas de participación'; // Discussion report.
$string['discussionreport_discussionActivityByWeek'] = 'Actividad semanal'; // Discussion report.
$string['discussionreport_studentDiscussionGrades'] = 'Calificaciones recomendadas'; // Discussion report.
$string['discussionreportindividual_discussionMetrics'] = 'Métricas de participación'; // Discussion report individual.
$string['discussionreportindividual_discussionActivityByWeek'] = 'Actividad semanal'; // Discussion report individual.

// Help tables all reports.
// Activity and risk report.
$string['activityreport_nonStarters_help'] = 'Los siguientes estudiantes aún no muestran actividad en el curso.';
$string['activityreport_studentList_help'] = 'Observando la actividad de los estudiantes en un curso, se obtiene'.
    ' una idea de su compromiso y sus prácticas. Esta tabla muestra la actividad del estudiante y su regularidad.'.
    ' Cuanto menor sea el número de la columna Regularidad de Visitas, más regular será el estudiante. También'.
    ' se pueden ver los reportes individuales de cada alumno haciendo clic en ícono al inicio de cada fila.'; // Activity report.
$string['risk_nonStarters_help'] = 'Los siguientes estudiantes aún no han participado del curso. '; // Risk report.
$string['risk_riskMeasures_help'] = 'Esta tabla ayuda a identificar a los estudiantes que se encuentran en riesgo'.
    ' de dejar el curso, retirarse de la clase o abandonar la escuela. Los números más altos indican mayor riesgo.'.
    ' El riesgo total está basado en las calificaciones de los estudiantes (Riesgo Académico), así como también en'.
    ' su participación en los foros de discusión (Riesgo Social).'; // Risk report.
$string['gradebookreport_courseGradeTable_help'] = 'Esta tabla muestra el desempeño de los estudiantes en los'.
    ' ítems calificables dentro de los curso. Se observa el puntaje porcentual para la calificación del curso'.
    ' y el puntaje promedio en los diferentes tipos de ítems para cada estudiante.'; // Gradebook report.
$string['gradebookreport_gradableItemsTable_help'] = 'Esta tabla muestra el desempeño de los estudiantes con un'.
    ' porcentaje para cada item. Además nos muestra la relación entre ese item con el desempeño general del'.
    ' estudiante hasta el momento.'; // Gradebook report.
$string['discussionreport_discussionMetrics_help'] = 'Esta tabla muestra cuánto participa un estudiante en las'.
    ' discusiones. Los puntajes para la contribución original y el pensamiento crítico, están determinados por'.
    ' el análisis de las palabras utilizadas por los estudiantes. La contribución original se basa en el conteo'.
    ' de palabras y conectores tales como pronombres y preposiciones, filtradas para determinar un radio de'.
    ' palabras únicas utilizadas. El pensamiento crítico se establece a partir del número de comentarios o posteos'.
    ' reflexivos como por ejemplo, "Estoy de acuerdo" o "Yo también". Haga click en el ícono al lado del nombre del'.
    ' estudiante para ver su performance individual.'; // Discussion report.
$string['discussionreport_discussionActivityByWeek_help'] = ''; // No mostraremos help.
$string['discussionreport_studentDiscussionGrades_help'] = 'Las recomendaciones de calificación se basan en la'.
    ' frecuencia de los posteos, el aporte original y la evidencia de pensamiento crítico. Cada uno tiene'.
    ' el mismo valor por defecto.'; // Discussion report.
$string['discussionreportindividual_discussionMetrics_help'] = 'Esta tabla muestra cuanto participa un estudiante'.
    ' en las discusiones. Los puntajes para la contribución original y el pensamiento crítico, están determinados'.
    ' por el análisis de las palabras utilizadas por los estudiantes. La contribución original se basa en el conteo'.
    ' de palabras y conectores tales como pronombres y preposiciones, filtradas para determinar un radio de palabras'.
    ' únicas utilizadas. El pensamiento crítico se establece a partir del numero de comentarios o posteos reflexivos'.
    ' como por ejemplo, "Estoy de acuerdo" o "Yo también". Haga click en el ícono al lado del nombre del estudiante'.
    ' para ver su performance individual.'; // Discussion report individual.
$string['discussionreportindividual_discussionActivityByWeek_help'] = ''; // No mostraremos help.

/* Graphs Activity report*/
$string['activityreport_activityLevelTimeline'] = 'Actividad del curso por fecha';
$string['activityreport_compassTimeDiagram'] = 'Actividad según la hora del día';
$string['activityreport_barplotOfActivityByWeekday'] = 'Actividad de las últimas dos semanas por día de la semana';
$string['activityreport_barplotOfActivityWholeWeek'] = 'Actividad durante las últimas semanas';
$string['activityreport_activityByWeekAsFractionOfTotal'] = 'Actividad relativa en comparación con otros estudiantes de la clase';
$string['activityreport_activityByWeekAsFractionOfOwn'] = 'Actividad relativa en comparación consigo mismo';
$string['activityreport_firstloginPiechartAdjusted'] = 'Diagrama de torta de distribución de primer acceso';

/* Help Graphs Activity report*/
$string['activityreport_activityLevelTimeline_help'] = 'Este gráfico muestra un estimado del tiempo de permanencia'.
    ' en el curso (línea azul) y una previsión (línea de puntos) para las próximas dos semanas. La línea gris'.
    ' oscura muestra el promedio de horas estimadas durante un período activo de tiempo. El área sombreada muestra'.
    ' la cercanía de la media estimada con la verdadera media de la clase. La actividad prevista para las próximas'.
    ' dos semanas se indica con una línea de puntos. Los picos de actividad fuera de lo esperado, se ven resaltados.';
$string['activityreport_compassTimeDiagram_help'] = 'Este diagrama muestra las 24 horas de un día. Se basa en el'.
    ' horario  establecido en el servidor de la institución. Una línea muestra el el tiempo que los estudiantes'.
    ' pasan en el curso. El curso es más concurrido cuando las líneas se acercan a los bordes exteriores del'.
    ' círculo. Esta información puede ayudarle a diseñar actividades que requieran la plena participación.';
$string['activityreport_barplotOfActivityByWeekday_help'] = 'Este gráfico muestra el tiempo estimado invertido en'.
    ' el curso,  desglosado por día de la semana. Las barras azules representan la actividad de los últimos siete días.';
$string['activityreport_barplotOfActivityWholeWeek_help'] = 'Este gráfico muestra el nivel de actividad (tiempo'.
    ' estimado) a lo largo de la semana. Las barras azules representan la actividad de los últimos siete'.
    ' días. Las barras amarillas muestran  la actividad de los siete días anteriores a esta semana.';
$string['activityreport_activityByWeekAsFractionOfTotal_help'] = 'Cada punto en este gráfico representa el tiempo que'.
    ' el estudiante pasa en el curso durante una semana en comparación con otros estudiantes. Los puntos más altos'.
    ' indican mayor actividad. Los nombres entre paréntesis refieren a personas que no están inscriptos como'.
    ' estudiantes en este curso.';
$string['activityreport_activityByWeekAsFractionOfOwn_help'] = 'Cada punto en este gráfico representa el tiempo que el'.
    ' estudiante pasa en el curso durante una semana en comparación con otras semanas. Los puntos más altos indican'.
    ' mayor actividad. Los nombres entre paréntesis refieren a personas que no están inscriptos como estudiantes en'.
    ' este curso.';
$string['activityreport_firstloginPiechartAdjusted_help'] = 'El diagrama muestra cuántos estudiantes ingresaron al'.
    ' curso y cuántos aún no. El primer día del curso no se establece en un momento programado, este comienza el'.
    ' día en el que el primer participante acceda al mismo. El patrón que se vea aquí puede dar un indicio del'.
    ' compromiso futuro.';

/* Graphs Activity Individual report*/
$string['activityreportindividual_activityLevelTimeline'] = 'Actividad por fecha';
$string['activityreportindividual_barplotOfActivityByWeekday'] = 'Actividad de las últimas dos semanas por día de la semana';
$string['activityreportindividual_barplotOfActivityWholeWeek'] = 'Actividad durante las últimas semanas';

/* Help Graphs Activity Individual report*/
$string['activityreportindividual_activityLevelTimeline_help'] = 'El gráfico muestra un estimado de tiempo dedicado'.
    ' al curso (línea azul) y una previsión (línea de puntos) para las próximas dos semanas. La línea gris oscuro'.
    ' muestra el promedio de horas estimadas durante un período activo de tiempo. El área sombreada muestra la'.
    ' cercanía entre el tiempo previsto y el promedio real. La actividad prevista para las próximas dos semanas'.
    ' se indica con una línea de puntos. Los picos de actividad más altos y fuera del rango esperado, se encuentran'.
    ' resaltados.';
$string['activityreportindividual_barplotOfActivityByWeekday_help'] = 'El gráfico muestra el tiempo estimado'.
    ' invertido en el curso desglosado por día de la semana. Las barras azules representan la actividad de los'.
    ' últimos siete días. Las barras amarillas, los siete días previos a esa semana.';
$string['activityreportindividual_barplotOfActivityWholeWeek_help'] = 'El gráfico muestra el tiempo estimado'.
    ' dedicado al curso en la ultima semana. Las barras azules representan la actividad de la última semana. Las'.
    ' barras amarillas muestran los siete días previos a esa semana.';

/* Graphs Risk report*/
$string['risk_riskDensity'] = 'Perfil total de riesgo';
$string['risk_balloonPlotRiskHistory'] = 'Historial de riesgo';

/* Help Graphs Risk report*/
$string['risk_riskDensity_help'] = 'Este gráfico muestra la distribución del riesgo estimado en el curso. El color'.
    ' verde representa a los estudiantes fuera de riesgo. En rojo se observan aquellos en alto riesgo y en amarillo'.
    ' los de riesgo medio.';
$string['risk_balloonPlotRiskHistory_help'] = 'Se muestra el desarrollo de riesgo a través del tiempo para cada'.
    ' estudiante. Los cambios de color en los puntos indican cambios en las categorías de riesgo. El color verde'.
    ' representa riesgo bajo, el amarillo riesgo medio y el rojo riesgo alto. El tamaño de los puntos muestra el'.
    ' riesgo estimado. Los puntos pequeños representan riesgo bajo y los puntos grandes representan riesgo alto.';

/* Graphs Risk report*/
$string['gradebookreport_studentScoreDistribution'] = 'Distribución de calificaciones';
$string['gradebookreport_scoreDistributionByItem'] = 'Distribución de puntajes';
$string['gradebookreport_scatterPlot'] = 'Calificación automática de los foros de discusión versus calificación del curso';
$string['gradebookreport_itemsHeatmap'] = 'Comparación de puntajes';

/* Help Graphs Gradebook report*/
$string['gradebookreport_studentScoreDistribution_help'] = 'Este gráfico muestra cómo se distribuyen los puntajes'.
    ' entre los estudiantes. Los picos representan la calificación general que obtienen la mayoría de los estudiantes,'.
    ' separados por líneas para cada tipo de ítem calificable. Una distribución representada por un patrón'.
    ' en forma de campana, podría indicar que no hubo sesgos o inconsistencias. Otros patrones de distribución'.
    ' pueden indicar diferencias en el nivel de dificultad de los ítems calificables.';

$string['gradebookreport_scoreDistributionByItem_help'] = 'Este diagrama de caja muestra la distribución de las'.
    ' puntuaciones de los estudiantes en los ítems o categorías calificables, los cuales está representados por una'.
    ' caja y por las líneas verticales por encima y por debajo de ella. Los diamantes son los puntajes obtenidos'.
    ' por los estudiantes. La línea horizontal gruesa muestra la puntuación promedio para ese ítem. Existen cuatro'.
    ' rangos de calificación. El 25% superior (línea vertical en la parte superior de la caja), el 25% por arriba del'.
    ' promedio (área de la caja por encima de la puntuación media), el 25% por debajo del promedio (el área de la caja'.
    ' debajo de la puntuación media), y el 25% inferior (línea vertical inferior por debajo de la caja). Cuanto más'.
    ' bajo o más alto sea el rango, más hacia los márgenes se verán los resultados.';
$string['gradebookreport_scatterPlot_help'] = 'Este gráfico compara la calificación automática propuesta por X-Ray'.
    ' basada en la calidad de los posteos en las discusiones; con las calificaciones de todos los ítems calificables.'.
    ' Cada punto en este diagrama representa los valores para un estudiante. La línea negra muestra la relación'.
    ' estimada entre los dos métodos de calificación. El área sombreada (intervalo de confianza) ofrece el rango'.
    ' estimado de la relación. Si todos los puntos están cerca de la línea negra, ambos métodos de calificación son'.
    ' coherentes.';
$string['gradebookreport_itemsHeatmap_help'] = 'Este mapa de temperatura muestra el desempeño de cada estudiante en'.
    ' cada ítem calificable, comparado con el resto de la clase. Los colores más oscuros indican mejores'.
    ' calificaciones. Si se observan muchos estudiantes dentro de la zona sombreada, se podría decir que el ítem o'.
    ' ejercicio es demasiado sencillo o demasiado complejo. Los nombres entre paréntesis indican personas que han'.
    ' participado en los ítems calificables pero no están inscriptos en este curso.';

/* Graphs Discussion report*/
$string['discussionreport_wordcloud'] = 'Palabras más utilizadas';
$string['discussionreport_avgWordPerPost'] = 'Promedio semanal de palabras por posteo';
$string['discussionreport_socialStructure'] = 'Análisis de interacción';
$string['discussionreport_socialStructureWordCount'] = 'Análisis de interacción con conteo de palabras';
$string['discussionreport_socialStructureWordContribution'] = 'Análisis de interacción con contribuciones originales';
$string['discussionreport_socialStructureWordCTC'] = 'Análisis de interacción con pensamiento crítico';
$string['discussionreport_endogenicPlagiarismStudentsHeatmap'] = 'Superposición de palabras entre posts (Sin instructor)';
$string['discussionreport_endogenicPlagiarismHeatmap'] = 'Superposición de palabras incluyendo al instructor';
$string['discussionreport_discussionSuggestedGrades'] = 'Distribución de calificaciones recomendadas';

/* Help Graphs Discussion report*/
$string['discussionreport_wordcloud_help'] = 'Esta nube de palabras muestra las palabras que se usan con mayor'.
    ' frecuencia en las discusiones. Se basa en una única cuenta de las palabras más utilizadas. Las palabras'.
    ' más grandes indican mayor uso.';
$string['discussionreport_avgWordPerPost_help'] = 'Este gráfico muestra el promedio del conteo de palabras en los'.
    ' posteos de discusión a lo largo de una semana. La línea azul representa los valores observados y la línea'.
    ' de puntos representa el promedio esperado. ';
$string['discussionreport_socialStructure_help'] = 'Este diagrama muestra a quiénes están respondiendo sus'.
    ' estudiantes. El color demuestra cuán conectados están sus estudiantes con el resto de la clase. El'.
    ' azul muestra mucha interacción con otros estudiantes, el amarillo por debajo del promedio de interacción'.
    ' y el rojo es de nula interacción. "inst" entre paréntesis es el instructor(es) del curso, y los nombres entre'.
    ' paréntesis son individuos que participaron en los foros, pero no están enrolados como estudiantes del curso.';
$string['discussionreport_socialStructureWordCount_help'] = 'Este diagrama muestra a quién habla cada estudiante'.
    ' y lo mucho que está diciendo. Estos valores se obtienen del conteo de palabras intercambiadas entre los'.
    ' estudiantes. Las líneas más gruesas indican mayor utilización de palabras en el intercambio de'.
    ' posteos. "inst" entre paréntesis es el instructor(es) del curso, y los nombres entre paréntesis son individuos'.
    ' que participaron en los foros, pero no están enrolados como estudiantes del curso.';
$string['discussionreport_socialStructureWordContribution_help'] = 'Este diagrama muestra quién habla a quién entre'.
    ' los estudiantes, junto con la calidad de su contribución. La contribución original se obtiene del conteo'.
    ' de palabras filtradas como únicas en cuanto a su nivel de utilización. Las líneas más gruesas indican'.
    ' la cantidad de palabras únicas utilizadas. "inst" entre paréntesis es el instructor(es) del curso, y los'.
    ' nombres entre paréntesis son individuos que participaron en los foros, pero no están enrolados como'.
    ' estudiantes del curso.';
$string['discussionreport_socialStructureWordCTC_help'] = 'Este diagrama muestra en cuáles de las respuestas de los'.
    ' estudiantes se observa un pensamiento crítico. Esto está basado en el número de posteos reflexivos. "inst"'.
    ' entre paréntesis es el instructor(es) del curso, y los nombres entre paréntesis son individuos que participaron'.
    ' en los foros, pero no están enrolados como estudiantes del curso.';
$string['discussionreport_endogenicPlagiarismStudentsHeatmap_help'] = 'Los mapas de temperatura muestran cuán'.
    ' similares son los posteos entre los estudiantes de la clase. Este esquema demuestra quién fue la fuente'.
    ' de un profundo conocimiento y quién está copiando los posteos de otros. Los posteos originales están'.
    ' determinadas por las marcas de tiempo. Los valores más bajos indican menos similitud. Es importante revisar'.
    ' los posteos de estudiantes con valores más altos ya que pueden estar citando o plagiando a otros estudiantes.'.
    ' Los nombres entre paréntesis son individuos que participaron en los foros, pero no están enrolados como'.
    ' estudiantes del curso.';
$string['discussionreport_endogenicPlagiarismHeatmap_help'] = 'Los mapas de temperatura muestran cuán similares'.
    ' son los posteos entre los estudiantes de la clase, incluyendo al instructor. Este esquema demuestra quién'.
    ' fue la fuente de un profundo conocimiento y quién está copiando los posteos de otros. Los posteos originales'.
    ' están determinadas por las marcas de tiempo. Los valores más bajos indican menos similitud. Es importante'.
    ' revisar los posteos de estudiantes con valores más altos ya que pueden estar citando o plagiando a otros'.
    ' estudiantes y al instructor. Los nombres entre paréntesis son individuos que participaron en los foros, pero'.
    ' no están enrolados como estudiantes del curso.';
$string['discussionreport_discussionSuggestedGrades_help'] = 'Este diagrama de barras muestra la distribución'.
    ' de las calificaciones sugeridas para la participación grupos de discusión. La línea continua muestra la'.
    ' distribución esperada de las calificaciones, con una calificación promedio de C. La línea de puntos muestra'.
    ' la distribución con un promedio de B para la clase. Las calificaciones reales se representan con la barra azul.';

/* Graphs Discussion report individual*/
$string['discussionreportindividual_wordcloud'] = 'Palabras más utilizadas';
$string['discussionreportindividual_socialStructure'] = 'Análisis de interacción';
$string['discussionreportindividual_wordHistogram'] = 'Frecuencia de palabras más utilizadas';

/* Help Graphs Discussion report individual*/
$string['discussionreportindividual_wordcloud_help'] = 'Esta nube de palabras representa las palabras utilizadas'.
    ' con más frecuencia a lo largo de las discusiones. Se basa en el conteo de palabras únicas utilizadas. Las'.
    ' de mayor tamaño indican mayor uso.';
$string['discussionreportindividual_socialStructure_help'] = 'Este diagrama representa a quiénes responden sus'.
    ' estudiantes. El color muestra la conexión de un estudiante con el resto de la clase. El azul muestra a los'.
    ' estudiantes que están por arriba o dentro del promedio de interacción. En amarillo vemos los que se encuentran'.
    ' un poco por debajo de ese promedio y en rojo observamos a aquellos estudiantes con un nivel de interacción muy'.
    ' bajo, o aún no han interactuado con el resto de la clase. "inst" entre paréntesis indica "instructor(es)", y'.
    ' los nombres en paréntesis indican personas que participaron en el foro de discusión pero no se han enrolado'.
    ' en el curso.';
$string['discussionreportindividual_wordHistogram_help'] = 'Este histograma representa la frecuencia de palabras más'.
    ' utilizadas en el foro. Las palabras con una frecuencia menor a 10 han sido excluidas.';

/* Graphs Discussion report individual forum*/
$string['discussionreportindividualforum_wordcloud'] = 'Palabras más utilizadas';
$string['discussionreportindividualforum_socialStructure'] = 'Análisis de interacción';
$string['discussionreportindividualforum_wordHistogram'] = 'Frecuencia de palabras más utilizadas';

/* Help Graphs Discussion report individual forum*/
$string['discussionreportindividualforum_wordcloud_help'] = 'Esta nube de palabras representa las palabras utilizadas'.
    ' con más frecuencia a lo largo de las discusiones. Se basa en el conteo de palabras únicas utilizadas. Las'.
    ' de mayor tamaño indican mayor uso.';
$string['discussionreportindividualforum_socialStructure_help'] = 'Este diagrama representa a quiénes responden sus'.
    ' estudiantes. El color muestra la conexión de un estudiante con el resto de la clase. El azul muestra a los'.
    ' estudiantes que están por arriba o dentro del promedio de interacción. En amarillo vemos los que se encuentran'.
    ' un poco por debajo de ese promedio y en rojo observamos a aquellos estudiantes con un nivel de interacción muy'.
    ' bajo, o aún no han interactuado con el resto de la clase. "inst" entre paréntesis indica "instructor(es)", y'.
    ' los nombres en paréntesis indican personas que participaron en el foro de discusión pero no se han enrolado'.
    ' en el curso.';
$string['discussionreportindividualforum_wordHistogram_help'] = 'Este histograma representa la frecuencia de palabras más'.
    ' utilizadas en el foro. Las palabras con una frecuencia menor a 10 han sido excluidas.';

/* Behat test */
$string['error_behat_getjson'] = 'Error al traer archivo "{$a}" de carpeta local/xray/tests/fixtures para simular '.
                                 'llamada a X-Ray Learning Analytics webservice cuando se esta corriendo behat test.';
$string['error_behat_instancefail'] = 'Esta es una instancia configurada para fallar al correr behat tests.';

/* Format for time range value */
$string['strftimehoursminutes'] = '%H:%M';

/* Empties reports */
$string['xray_course_report_empty'] = 'No hay datos suficientes para este reporte. Por favor, inténtelo de nuevo'.
    ' cuando haya más actividad de los usuarios en su curso';

// Email.
$string['changesubscription'] = 'Cambie sus preferencias de suscripción';
$string['coursesubscribe'] = 'Suscribirse a los reportes por correo electrónico de {$a}';
$string['coursesubscribedesc'] = 'Usted recibirá un correo electrónico con el resumen de los datos de X-Ray';
$string['email_log_desc'] = 'Correo electrónico enviado. ID de curso {$a->courseid} ID de usuario {$a->to}';
$string['emailsubject'] = 'Resumen de informe de X-Ray para {$a}';
$string['erroremailheadline'] = 'Error en el panel de datos destacados.'.
                                ' No fue enviado el correo electrónico para el ID de curso {$a}';
$string['profilelink'] = 'Suscripción global a X-Ray';
$string['subscribeall'] = 'Suscribirse a reportes por correo electrónico para todos los cursos';
$string['subscribetothiscourse'] = 'Suscribirse al reporte por correo electrónico';
$string['subscriptiontitle'] = 'Suscripción al correo electrónico de X-Ray';
$string['unsubscribeemail'] = 'Cancelar suscripción';
$string['unsubscribetothiscourse'] = 'Cancelar suscripción a los reportes por correo electrónico';
$string['globalsubtitle'] = 'Suscripción global a X-Ray';
$string['globalsubcourse'] = 'Utilizar la configuración de suscripción a nivel curso';
$string['globalsubon'] = 'Suscribirse a todos los cursos';
$string['globalsuboff'] = 'Cancelar todas las suscripciones';
$string['globalsubdesctitle'] = 'Seleccione la configuración para la suscripción global al resumen de X-Ray. '.
    'Puede decidir suscribirse para un curso o para todos los cursos.';

$string['globalsubdescfirst'] = 'Si elige <b>Utilizar la configuración de suscripción a nivel de curso</b>, recibirá '.
    'el reporte resumen de X-Ray sólo para los cursos a los que está suscrito. Esta opción está seleccionada de '.
    'forma predeterminada.';
$string['globalsubdescsecond'] = 'Las opciones <b>Suscribirse a todos los cursos</b> y <b>Cancelar todas las suscripciones</b> '.
    'sobre-escriben los ajustes de suscripción realizados a nivel del curso. Usted recibirá o no el resumen de X-Ray '.
    'para todos los cursos.';
$string['subscriptiondisabled'] = 'Habilite esta configuración en la página de suscripción global de X-Ray. '.
    'Puede acceder a esta página utilice el enlace Suscripción global de X-Ray en su perfil. ';

// Frequency control for emails.
$string['daily'] = 'Diaria';
$string['emailfrequency'] = 'Frecuencia para las alertas de correo electrónico';
$string['emailfrequency_desc'] = 'Si elige enviar alertas por correo electrónico diariamente, los mensajes de '.
    'correo electrónico comenzarán mañana. Si decide enviar alertas por correo electrónico cada semana, comenzarán '.
    'el próximo domingo. Si elige no enviar alertas por correo electrónico, los usuarios aún podrán suscribirse '.
    'pero no se enviarán correos electrónicos.';
$string['emailsdisabled'] = 'No recibirá alertas en este momento. Las notificaciones por correo electrónico de '.
    'X-Ray están desactivadas. Aún se puede suscribir. Los correos electrónicos serán enviados cuando las alertas '.
    'estén activadas. Contáctese con su administrador de sistema para más información.';
$string['frequencyheading'] = 'Frecuencia de alertas de correo electrónico';
$string['frequencyheading_desc'] = 'Elija la frecuencia con la que desea que las alertas de X-Ray se envíen por '.
    'correo electrónico a los suscriptores. De forma predeterminada, las alertas se envían por correo electrónico '.
    'cada semana. Esto ocurre todos los domingos. Puede elegir enviar alertas por correo electrónico diariamente o nunca.';
$string['never'] = 'Nunca';
$string['weekly'] = 'Semanal';

// Recommended Actions.
$string['countaction'] = '{$a} recomendación';
$string['countactions'] = '{$a} recomendaciones';
$string['recommendedactions'] = 'Recomendaciones';
$string['youhave'] = 'Usted tiene ';
$string['youdonthave'] = 'Usted no tiene recomendaciones';
$string['recommendedactions_button'] = 'Mostrar/Ocultar recomendaciones';
$string['recommendedaction_button'] = 'Mostrar/Ocultar recomendación';

// Config validation errors for wsapi.
$string['error_wsapi_config_params_empty'] = 'Los parámetros X-Ray están vacíos';
$string['error_wsapi_config_xrayusername'] = 'El campo username de X-Ray Learning Analytics está vacío';
$string['error_wsapi_config_xraypassword'] = 'El campo password de X-Ray Learning Analytics está vacío';
$string['error_wsapi_config_xrayurl'] = 'El campo url de X-Ray Learning Analytics está vacío';
$string['error_wsapi_config_xrayclientid'] = 'El campo clientid de X-Ray Learning Analytics está vacío';
$string['error_wsapi_exception'] = 'Hubo un error al comunicarse con el servidor X-Ray:<br/>{$a}';
$string['error_wsapi_domaininfo_incomplete'] = 'La información del dominio está incompleta: ${a}';

// Config validation errors for aws.
$string['error_awssync_config_enablesync'] = 'El campo Data Sync está vacío';
$string['error_awssync_config_awskey'] = 'El campo AWS Key está vacío';
$string['error_awssync_config_awssecret'] = 'El campo AWS Secret está vacío';
$string['error_awssync_config_s3bucket'] = 'El campo S3 bucket está vacío';
$string['error_awssync_config_s3bucketregion'] = 'El campo S3 region está vacío';
$string['error_awssync_config_s3protocol'] = 'El campo Upload protocol está vacío';
$string['error_awssync_exception'] = 'Hubo un error al comunicarse con el servidor AWS:<br />{$a}';

// Config validation error reasons.
$string['error_wsapi_reason_login'] = 'Ingresar';
$string['error_wsapi_reason_accesstoken'] = 'Acceder al token';
$string['error_wsapi_reason_domaininfo'] = 'Obtener información del dominio';
$string['error_wsapi_reason_courses'] = 'Obtener cursos';
$string['error_aws_reason_client_create'] = 'Crear el cliente AWS';
$string['error_aws_reason_object_list'] = 'Listar los Objectos del Bucket';
$string['error_aws_reason_upload_file'] = 'Cargar un arhchivo';
$string['error_aws_reason_download_file'] = 'Descargar un archivo';
$string['error_aws_reason_erase_file'] = 'Borrar un archivo';

// Config validation errors for compression.
$string['error_compress_config_enablepacker'] = 'El capo de uso de compresión nativa está vacío';
$string['error_compress_config_packertar'] = 'El campo del ejecutable GNU tar está vacío';
$string['error_compress_config_exportlocation'] = 'El campo Locación de export está vacío';
$string['error_compress_exception'] = 'Error al comprimir:<br />{$a}';
$string['error_compress_files'] = 'Se encontraron archivos incorrectos en el/los archivo(s) comprimido(s)';

// Temporary for API check.
$string['connectionfailed'] = 'Conexión fallida - verificar parámetros';
$string['connectionverified'] = 'Parámetros verificados';
$string['connectionstatusunknown'] = 'Estado de conexión no verificado';
$string['verifyingapi'] = '<div class="xray_validate_loader"></div> Revisando el estado de conexión, por favor espere.';
$string['test_api_action'] = 'Validar parámetros';
$string['validate_check_fields'] = 'Por favor, revise los siguientes campos';
$string['validate_service_response'] = 'Verifique la respuesta del servicio dando click acá';

// API titles.
$string['test_api_ws_connect'] = 'Servidor X-Ray';
$string['test_api_s3_bucket'] = 'Bucket S3 de AWS';
$string['test_api_compress'] = 'Compresión';
$string['test_api_label'] = 'Validación';
$string['test_api_description'] = 'Validar <strong>parámetros guardados</strong> '.
                                  'para encontrar problemas de conexión o del sistema.';
$string['validate_when'] = 'Al';

// X-Ray Reports.
$string['xraydashboardurl'] = 'URL de la interfaz de X-Ray';
$string['xraydashboardurl_desc'] = 'URL para conectar con la interfaz de X-Ray';
$string['xrayreportsurl'] = 'URL de los reportes de X-Ray';
$string['xrayreportsurl_desc'] = 'URL para conectar con los reportes de X-Ray';
$string['xrayreports'] = 'Reportes de X-Ray';
$string['noaccessxrayreports'] = 'Los reportes de X-Ray no están disponibles. Por favor, contacta con el '.
    'administrador del sitio para solicitar su habilitación.';
$string['noaccessoldxrayreports'] = 'Esta versión de los reportes ya no está disponible. Por favor, contacta con el '.
    'administrador del sitio para solicitar su habilitación.';
$string['error_xrayreports_nourl'] = 'La url para obtener reportes de X-Ray no esta configurada correctamente.';
$string['error_xrayreports_gettoken'] = 'Error al obtener token para acceder a los reportes de X-Ray.';
$string['dashboard_button'] = 'Mostrar/Ocultar interfaz';