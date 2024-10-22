<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Events\ActualizarCocina;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//usuario autenticado
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/generar_factura/{factura}','Api\web\ventas\VentasController@reenviar_factura');
Route::post('/getCotizacion','Api\web\CotizacionesController@getCotizacion');
Route::get('/generar_guia_remision/{movimiento}','Api\web\inventarios\SalidasController@generar_guia_remision');
Route::post('login', 'Api\UserController@login');
Route::get('users', 'Api\UserController@getUsers');
Route::group(['middleware' => 'auth:api'], function(){
    Route::post('addUser', 'Api\UserController@addUser');

    //facturacion
    //Route::get('/emitirComp/{contrato_id}','facturacion\FacturadorController@emitirComp');
    //Route::get('generar_boleta/{codigo}','facturacion\FacturadorController@generar_boleta');
    //Route::get('/generar_pdf/{contrato_id}','facturacion\FacturadorController@generar_pdf');
    //Route::get('/prueba','Api\facturacion\FacturadorController@prueba');

    //DASHBOARD
      Route::get('dashboardEstados','Api\web\DashboardController@dashboardEstados');
      Route::get('dataAuxiliar','Api\web\DashboardController@dataAuxiliar');

    //CONTABILIDAD
        //Route::get('/generarPDF/{contrato_id}','Api\web\contabilidad\FacturadorController@generar_pdf');*/

    //PRODUCTOS
        Route::apiResource('productos', 'Api\web\productos\ProductosController');
        Route::get('getDatosAuxProductos','Api\web\productos\ProductosController@datosAuxialires');
        Route::get('selectCategorias','Api\web\productos\ProductosController@selectCategorias');
        

    //CLIENTES
        Route::apiResource('clientes', 'Api\web\clientes\ClientesController');
        Route::get('consultarDocumento/{documento}/{tipo}','Api\web\clientes\ClientesController@consultarDocumento');

    //VENTAS
        Route::apiResource('ventas', 'Api\web\ventas\VentasController');
        Route::get('/datosProductosVenta','Api\web\ventas\VentasController@datosProductosVenta');
        Route::get('/datosClientesVenta','Api\web\ventas\VentasController@datosClientesVenta');
        
        Route::get('/anular_boleta/{factura}','Api\web\ventas\VentasController@anularBoleta');
        
        //enviar comprobante email
        Route::post('/enviarComprobante','Api\web\ventas\VentasController@enviarComprobante');
        //descargas
        Route::get('/downloadPDF/{nombrePDF}','Api\web\ventas\VentasController@downloadPDF');
        Route::get('/downloadXML/{nombreXML}','Api\web\ventas\VentasController@downloadXML');
        Route::get('/downloadCRD/{nombreXML}','Api\web\ventas\VentasController@downloadCRD');
      //VENTAS CON GUIAS
        Route::get('/buscarGuias/{cliente_id}','Api\web\ventas\VentasController@buscarGuias');
        Route::get('/datosGuia/{movimiento_id}','Api\web\ventas\VentasController@datosGuia');
        Route::get('/emitirFacturaDeGuias/{codigos}','Api\web\ventas\VentasController@emitirFacturaDeGuias');

    //FACTURAS
        Route::get('/boletas','Api\web\ventas\FacturacionController@boletas');
        Route::get('/facturas','Api\web\ventas\FacturacionController@facturas');
        //SERIES
        Route::apiResource('series', 'Api\web\ventas\SeriesController');
        //resumenes
        Route::apiResource('resumenes', 'Api\web\ventas\ResumenesController');
        Route::get('/conseguirBoletas/{fecha}','Api\web\ventas\ResumenesController@conseguirBoletas');
        Route::get('/conseguirBoletasResumen/{factura_id}','Api\web\ventas\ResumenesController@conseguirBoletasResumen');
        Route::get('/generarResumen/{fecha_resumen}','Api\web\ventas\ResumenesController@generarResumen');
        Route::get('/conseguirBoletas/{fecha}','Api\web\ventas\ResumenesController@conseguirBoletas');
        //notas
        Route::apiResource('notas', 'Api\web\ventas\NotasController');
        Route::get('generar_pdf_nota/{factura_id}','Api\web\ventas\NotasController@generar_pdf_nota');
        Route::get('downloadNotaPDF/{nombrePdf}','Api\web\ventas\NotasController@downloadNotaPDF');

    //INVENTARIOS
        //INGRESOS
            Route::apiResource('ingresos', 'Api\web\inventarios\IngresosController');
            Route::get('dataAuxiliarIngreso','Api\web\inventarios\IngresosController@dataAuxiliarIngreso');
            Route::get('dataAuxiliarCotizacion','Api\web\inventarios\IngresosController@dataAuxiliarCotizacion');
            
            Route::get('recalcularSaldo','Api\web\inventarios\IngresosController@recalcularSaldo');
            Route::get('inUpdateProductos','Api\web\inventarios\IngresosController@updateProductos');
            Route::get('obtenerTablaDetalle/{movimiento_id}','Api\web\inventarios\IngresosController@obtenerTablaDetalle');
        //SALIDAS
            Route::apiResource('salidas', 'Api\web\inventarios\SalidasController');
            Route::get('dataAuxiliarSalida','Api\web\inventarios\SalidasController@dataAuxiliarSalida');
        //CATEGORIAS
            Route::apiResource('categorias', 'Api\web\inventarios\CategoriasController');
        //UNIDADES
            Route::apiResource('unidades', 'Api\web\inventarios\UnidadesController');
        //FACTURACION DESDE LA SALIDA
        Route::get('facturarSalidas/{movimiento_id}/{detraccion}/{cod_bien_detraccion}/{forma_pago}/{dias}','Api\web\inventarios\SalidasController@facturarSalidas');


    //REPORTERIA
        //AREA DE INVENTARIOS
            Route::post('/inventarios.kardexGeneral','Api\web\reportes\InventariosController@kardexGeneral');
            Route::post('/inventarios.kardexGeneralProducto','Api\web\reportes\InventariosController@kardexGeneralProducto');
            Route::get('/inventarios.stockProducto','Api\web\reportes\InventariosController@stockProducto');
            Route::post('/inventarios.ingresosFecha','Api\web\reportes\InventariosController@ingresosFecha');
            Route::post('/inventarios.ingresoAgrupadoProducto','Api\web\reportes\InventariosController@ingresoAgrupadoProducto');
            Route::post('/inventarios.salidasFecha','Api\web\reportes\InventariosController@salidasFecha');
            Route::post('/inventarios.salidasAgrupadoProducto','Api\web\reportes\InventariosController@salidasAgrupadoProducto');
            //EXCEL
            Route::get('/inventarios.reporte/{reporte}/{producto}/{fecha_desde}/{fecha_hasta}','Api\web\reportes\InventariosController@reportes');

    //MESAS
        //mesas
        Route::apiResource('mesas', 'Api\web\ventas\MesasController');
        //escenarios
        Route::post('/crearEscenario','Api\web\ventas\MesasController@crearEscenario');
        //actualizar escenario
        Route::post('/updateEscenario/{escenario}','Api\web\ventas\MesasController@updateEscenario');
        //obtener escenario 1
        Route::get('/getEscenarios1/{escenario}','Api\web\ventas\MesasController@getEscenarios1');
        //guardar venta
        Route::post('/guardarVenta','Api\web\ventas\VentasController@guardarVenta');
        //obtener venta y sus item
        Route::get('/getVenta/{idMesa}','Api\web\ventas\VentasController@getVenta');
        //cerrar mesa
        Route::get('/cerrarMesa/{idMesa}','Api\web\ventas\MesasController@cerrarMesa');
        //obtener mesas del escenario y relacionados
        Route::get('/getMesasEscenario/{idMesa}/{idEscenario}','Api\web\ventas\MesasController@mesasEscenario');

    //CAJA CHICA
        //caja
        Route::apiResource('cajas', 'Api\web\caja\CajaController');
        Route::post('/cerrarCaja','Api\web\caja\CajaController@cerrarCaja');
        Route::post('/aperturarCaja','Api\web\caja\CajaController@aperturarCaja');
        Route::get('/getCierresCaja','Api\web\caja\CajaController@getCierresCaja');
        Route::get('/getDataCierre/{fecha}','Api\web\caja\CajaController@getDataCierre');
        Route::get('/cajaAbierta','Api\web\caja\CajaController@cajaAbierta');
        Route::post('/busquedaCierre','Api\web\caja\CajaController@busquedaCierre');//busqeuda por fecha
    //GASTOS
        //gstos
        Route::apiResource('gastos', 'Api\web\caja\GastosController');

    //USUARIOS
        Route::apiResource('usuarios', 'Api\web\usuarios\UsuariosController');

    //DASHBOARD
        //RESUMEN
            //productos mas vendidos
            Route::get('/getProductos','Api\web\dashboard\ResumenController@getProductos');
            Route::get('/getDatosGenerales','Api\web\dashboard\ResumenController@getDatosGenerales');
            Route::post('/datosGenerales.fecha','Api\web\dashboard\ResumenController@getDatosGeneralesFecha');
        //reporte generales
            Route::post('/reporte.ventaFecha','Api\web\dashboard\ReportesGenerales@ventasFecha');
            Route::post('/reporte.gastosFecha','Api\web\dashboard\ReportesGenerales@gastosFecha');
            Route::post('/reporte.cajasFecha','Api\web\dashboard\ReportesGenerales@cajasFecha');
            Route::post('/reporte.boletas','Api\web\dashboard\ReportesGenerales@boletas');
            Route::post('/reporte.facturas','Api\web\dashboard\ReportesGenerales@facturas');
            Route::post('/reporte.notas','Api\web\dashboard\ReportesGenerales@notas');
            //EXCEL
            Route::get('/reportes.general/{reporte}/{fecha_desde}/{fecha_hasta}','Api\web\dashboard\ReportesGenerales@reportes');
        //REPORTES CONTABLES
            Route::get('/contable.resumen/{mes}/{anio}/{nombre_mes}','Api\web\dashboard\ReportesContables@resumenTotales');
            Route::get('/contable.detallado/{mes}/{anio}/{nombre_mes}','Api\web\dashboard\ReportesContables@resumenDetallado');
            Route::get('/contable.cBoletas/{mes}/{anio}/{nombre_mes}','Api\web\dashboard\ReportesContables@conpendioBoletas');
            Route::get('/contable.cFacturas/{mes}/{anio}/{nombre_mes}','Api\web\dashboard\ReportesContables@conpendioFacturas');

    //EMPRESA
        Route::apiResource('empresas', 'Api\web\configuracion\EmpresasController')->except([
            'index', 'update', 'destroy'
        ]);

    //COCINA
        Route::get('/getMesasCocina','Api\web\cocina\CocinaController@getMesasCocina');
        Route::get('/getMesaDetalle/{id_venta}','Api\web\cocina\CocinaController@getMesaDetalle');
        Route::post('/getMesaDetalleListo','Api\web\cocina\CocinaController@getMesaDetalleListo');

});

Route::get('generar_pdf_nota/{factura_id}','Api\web\ventas\NotasController@generar_pdf_nota');
Route::get('convertir_cert','Api\facturacion\FacturadorController@convertir_cert');
Route::get('/reenviar_nota/{factura_id}','Api\web\ventas\VentasController@reenviar_nota');

//pusher
Route::get('/test_pusher', function(){

    $data = "ejemplo1";
    //test event
    //event(new ActualizarCocina($data));

});

Route::get('/anular_factura/{factura}','Api\web\ventas\VentasController@anularFactura');


Route::get('/reenviar_nota/{factura_id}','Api\web\ventas\VentasController@reenviar_nota');

Route::get('/testPusher/{ejemplo}','Api\web\cocina\CocinaController@testPusher');
Route::get('/enviarComprobanteTest/{correo}/{factura}','Api\web\ventas\VentasController@enviarComprobanteTest');
