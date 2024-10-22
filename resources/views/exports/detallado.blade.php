<table>
    <tr>
        <td>SOFER</td>
    </tr>
    <tr>
        <td>RESUMEN DETALLADO {{$fecha}}</td>
    </tr>
</table>
<table>
    <thead>
    <tr>
        <th>Fecha</th>
        <th>Tipo</th>
        <th>Documento</th>
        <th>Cliente</th>
        <th>RUC/DNI</th>
        <th>Moneda</th>
        <th>SubTotal</th>
        <th>IGV</th>
        <th>Total</th>
        <th>Estado</th>
    </tr>
    </thead>
    <tbody>
    @foreach($facturas as $factura)
        <tr>
            <td>{{ $factura->setFechaEmision }}</td>
            <td>FACTURA</td>
            <td>{{ $factura->setSerie}}-{{ $factura->setCorrelativo}}</td>
            <td>{{ $factura->cliente_setRznSocial }}</td>
            <td>{{ $factura->cliente_setNumDoc }}</td>
            <td>SOLES</td>
            <td>{{ $factura->setMtoOperGravadas }}</td>
            <td>{{ $factura->setMtoIGV }}</td>
            <td>{{ $factura->setMtoImpVenta }}</td>
            <td>{{ $factura->estadoDesc }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>Descripcion</td>
            <td>Precio</td>
            <td>Cantidas</td>
            <td>Total</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        @foreach ($factura->items as $item)
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>{{$item->setDescripcion}}</td>
                <td>{{$item->setMtoPrecioUnitario}}</td>
                <td>{{$item->setCantidad}}</td>
                @if ($item->setTotal != null)
                    <td>{{$item->setTotal}}</td>
                @else
                    <td>{{ $item->setCantidad * $item->setMtoPrecioUnitario}}</td>
                @endif
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
