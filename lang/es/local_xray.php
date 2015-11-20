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

/* @var string[] $string */
$string['navigation_xray'] = 'X-Ray Learning Analytics ';
$string['navitationcourse_xray'] = 'X-Ray Learning Analytics';
$string['pluginname'] = 'X-Ray Learning Analytics';
$string['reports'] = 'Reportes';
$string['analytics'] = 'Análisis de curso';
$string['xraydisplayheading'] = 'Integración de curso';
$string['xraydisplayheading_desc'] = 'Controla la visualización de información y links a los reportes en la página principal.';
$string['displaymenu'] = 'Mostrar menu de reportes.';
$string['displaymenu_desc'] = 'Controlar la visualización del menu de reportes en página principal del curso.';
$string['displayheaderdata'] = 'Mostrar Análisis';
$string['displayheaderdata_desc'] = 'Controla la visualización de Análisis del curso en la página principal del curso.';
$string['debuginfo'] = 'Información';

/* Capabilities */
$string['xray:activityreportindividual_view'] = 'Ver reporte individual de actividad';
$string['xray:activityreport_view'] = 'Ver reporte de actividad';
$string['xray:dashboard_view'] = 'Ver interfaz de reportes';
$string['xray:discussionreport_view'] = 'Ver reporte de discusiones';
$string['xray:discussionreportindividual_view'] = 'Ver reporte individual de discusiones';
$string['xray:discussionreportindividualforum_view'] = 'Ver reporte individual de discusiones en foros';
$string['xray:discussionendogenicplagiarism_view'] = 'Ver reporte de plagio en foros de discusión';
$string['xray:discussiongrading_view'] = 'Ver calificaciones en foros de discusión';
$string['xray:gradebookreport_view'] = 'Ver reporte de calificaciones';
$string['xray:gradebookreportindividualquiz_view'] = 'Ver reporte individual de calificaciones para quiz';
$string['xray:risk_view'] = 'Ver reporte de riesgo';
$string['xray:view'] = 'Ver X-Ray Learning Analytics';

/* Categories for numbers values */
$string['high'] = 'Alto';
$string['low'] = 'Bajo';
$string['medium'] = 'Medio';

$string['highlyregularity'] = 'Alta regularidad';
$string['irregular'] = 'Irregular';
$string['somewhatregularity'] = 'Regularidad media';

/* Report Activity Report*/
$string['activityreport'] = 'Reporte de actividad';
/* Report Activity Report Individual*/
$string['activityreportindividual'] = 'Reporte individual de actividad';
/* Discussion report*/
$string['discussionreport'] = 'Reporte de discusiones';
/* Discussion report individual*/
$string['discussionreportindividual'] = 'Reporte individual de discusiones';
/* Discussion report individual forum*/
$string['discussionreportindividualforum'] = 'Reporte individual de discusiones en foros';
/* Discussion report Endogenic Plagiarism*/
$string['discussionendogenicplagiarism'] = 'Reporte de plagio en foros de discusión';
/* Risk report*/
$string['risk'] = 'Reporte de riesgo';
/* Discussiongrading report*/
$string['discussiongrading'] = 'Calificaciones en foros de discusión';
/* Gradebook report*/
$string['gradebookreport'] = 'Reporte de calificaciones';

/* Columns reports */
$string['reportdate'] = 'Fecha de reporte';
$string['weeks'] = 'Semanas';
$string['week'] = 'Semana';

/* Error to load tables and images */
$string['error_loadimg'] = 'Error al cargar imagen, por favor intenta nuevamente recargando la página. Si el '.
                           'error persiste, por favor contacta con el administrador del sitio.';

/* Error Webservice */
$string['error_xray'] = 'Error al conectar con X-Ray Learning Analytics, por favor intenta nuevamente recargando la página. Si el error '.
                        'persiste, por favor contacta con el administrador del sitio.';

$string['error_compress'] = 'No fue possible crear archivo comprimido. Por favor contacta con el administrador del sitio.';

$string['error_generic'] = '{$a}';

/* Settings */
$string['xrayclientid'] = 'Identificador de cliente';
$string['xrayclientid_desc'] = 'Identificador de client para X-Ray Learning Analytics';
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

$string['xrayadminserver'] = 'Servidor de administración X-Ray Learning Analytics';
$string['xrayadminserver_desc'] = 'Locación del servidor.';
$string['xrayadmin'] = 'Usuario administrador';
$string['xrayadmin_desc'] = 'Usuario para loguear en servidor de administración.';
$string['xrayadminkey'] = 'Clave de Administrador';
$string['xrayadminkey_desc'] = 'Clave de acceso para loguearse dentro de servidor de administración.';

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

/* Scheduled task */
$string['datasync'] = 'Sincronización de datos';
$string['syncfailed'] = 'Sincronización de datos con X-Ray Learning Analytics ha fallado';
$string['unexperror'] = 'Error inesperado';
$string['syncfailedexplanation'] = 'Fallo al sincronizar datos con X-Ray Learning Analytics.';
$string['synclog'] = 'Mensaje de información de sincronización de datos con X-Ray Learning Analytics';
$string['synclogexplanation'] = 'Entrada de registro regular para sincronizar datos.';

// Course Header.
$string['atrisk'] = 'En riesgo';
$string['dashboard'] = 'Interfaz';
$string['fromlastweek'] = '{$a}% de cambio desde la ultima semana';
$string['of'] = ' de ';
$string['studentatrisk'] = 'estudiantes en riesgo';
$string['studentvisitslastdays'] = 'visitas de estudiantes en los últimos 7 dias';
$string['visitors'] = 'Visitantes';

// Jquery Tables (with plugin datatables).
$string['error_datatables'] = 'Error al traer datos para esta tabla. Por favor intenta nuevamente recargando la '.
                              'página. Si el error persiste, por favor contacta con el administrador del sitio.';
$string['sProcessingMessage'] = 'Trayendo datos, Por favor espera...';
$string['sFirst'] = 'Primero';
$string['sLast'] = 'Último';
$string['sNext'] = 'Siguiente';
$string['sPrevious'] = 'Anterior';
$string['sProcessing'] = 'Procesando...';
$string['sLengthMenu'] = 'Mostrar _MENU_ entradas';
$string['sZeroRecords'] = 'No se encontraron registros';
$string['sEmptyTable'] = 'No hay datos disponibles para esta tabla';
$string['sInfo'] = 'Mostrando _START_ a _END_ de _TOTAL_ entradas';
$string['sInfoEmpty'] = 'Mostrando 0 a 0 de 0 entradas';
$string['sLoadingRecords'] = 'Cargando...';
$string['sSortAscending'] = ': activar para ordenar columna ascendentemente';
$string['sSortDescending'] = ': activar para ordenar columna descendentemente';

/*Accessible data */
$string['accessibledata'] = 'Datos accesibles';
$string['accessible_view_data'] = 'Ver datos';
$string['accessible_emptydata'] = 'No hay datos disponibles para version accesible.';
$string['accessible_error'] = 'Versión accesible para este gráfico no fue encontrada en X-Ray Learning Analytics.';