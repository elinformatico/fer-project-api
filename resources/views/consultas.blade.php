<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Resultados de {{ $data['type'] }}</title>
     {!! HTML::style('assets/css/pdf/style.css') !!}
  </head>
  <body>
    <header class="clearfix">
      <div id="logo">
        <!-- <img src="logo.png"> -->
      </div>
      <div id="company">
        <h2 class="name">{{ $data['description'] }} </h2>
        <div>Documento generado de manera Automatica</div>
        <div>Cual error favor de reportarlo al siguiente correo:</div>
        <div><a href="mailto:company@example.com">donoe1985@gmail.com</a></div>
      </div>
      </div>
    </header>
    <main>
      <div id="details" class="clearfix">
        <!-- <div id="client">
          <div class="to">INVOICE TO:</div>
          <h2 class="name">John Doe</h2>
          <div class="address">796 Silver Harbour, TX 79273, US</div>
          <div class="email"><a href="mailto:john@example.com">john@example.com</a></div>
        </div> -->
        <div id="invoice">
          <h1>{{ ucfirst($data['type']) }}</h1>
          <div class="date">Documento generado: {{ $data['fechaCreacion'] }}</div>
          <!-- <div class="date">Due Date: 30/06/2014</div> -->
        </div>
      </div>
      
      @if($data['type'] === "memos" || $data['type'] === "oficios")
        
            				
         <table border="0" cellspacing="0" cellpadding="0">
            <thead>
              <tr>
                <th class="total">Folio</th>
                <th class="total">Tipo</th>
                <th class="total">Creado por</th>
                <th class="total">Turnado A</th>
                <th class="total">Año</th>
                <th class="total">Asunto</th>
                <th class="total">Observaciones</th>
                <th class="total">Fecha Creacion</th>
              </tr>
            </thead>
            <tbody>
              @foreach($data['results'] as $row)
                  <tr>
                    <td class="desc">{{ $row->folio }}</td>
                    <td class="desc">{{ $row->tabla }}</td>
                    <td class="desc">{{ $row->creador }}</td>
                    <td class="desc">{{ $row->turnado_a }}</td>
                    <td class="desc">{{ $row->anio }}</td>
                    <td class="desc">{{ $row->asunto }}</td>
                    <td class="desc">{{ $row->observaciones }}</td>
                    <td class="desc">{{ $row->fecha_creacion }}</td>
                  </tr>
              @endforeach
            </tbody>
          </table>
      @elseif ($data['type'] === "correspondencia")
        <table border="0" cellspacing="0" cellpadding="0">
            <thead>
              <tr>
                <th class="total">Folio</th>
                <th class="total">Tipo</th>
                <th class="total">Fecha Creado</th>
                <th class="total">Solicitante</th>
                <th class="total">Dirigido A</th>
                <th class="total">Depto Dirigido</th>
                <th class="total">Usuario Dirigido</th>
                <th class="total">Referencia</th>
                <th class="total">Fecha Limite</th>
                <th class="total">Estatus</th>
              </tr>
            </thead>
            <tbody>
              @foreach($data['results'] as $row)
                  <tr>
                    <td class="desc">{{ $row->folio }}</td>
                    <td class="desc">{{ $data['type'] }}</td>
                    <td class="desc">{{ $row->fecha_creacion }}</td>
                    <td class="desc">{{ $row->creador }}</td>
                    <td class="desc">{{ $row->dirigido_a }}</td>
                    <td class="desc">{{ $row->depto_dirigido }}</td>
                    <td class="desc">{{ $row->persona_dirigida }}</td>
                    <td class="desc">{{ $row->referencia }}</td>
                    <th class="desc">{{ $row->fecha_limite }}</th>
                    <th class="{{ $row->color_status }}">{{ $row->estatus_limite }}</th>
                  </tr>
              @endforeach
            </tbody>
          </table>
      @endif
        
      <!-- <div id="thanks">Fin del documento</div> -->
      <!-- <div id="notices">
        <<div>NOTICE:</div>
        <div class="notice">A finance charge of 1.5% will be made on unpaid balances after 30 days.</div>
      </div> -->
    </main>
    <footer>
      Este documento fue generado de manera automatica utilzando los criterios de busqueda establecidos.
    </footer>
  </body>
</html>