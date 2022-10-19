@php
    use App\CoreFacturalo\Helpers\Number\NumberLetter;
    $establishment = $document->establishment;
    $customer = $document->customer;
    $invoice = $document->invoice;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
    $document_base = ($document->note) ? $document->note : null;
    $payments = $document->payments;

    if($document_base) {
        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {
        $affected_document_number = null;
    }
    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');


    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body class="ticket">
    @php
    $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);

    @endphp
@if($company->logo)
    <div class="text-center company_logo_box pt-5 border-xy border-top">
        <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" class="company_logo_ticket contain">
    </div>
{{--@else--}}
    {{--<div class="text-center company_logo_box pt-5">--}}
        {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo_ticket contain">--}}
    {{--</div>--}}
@endif

@if($document->state_type->id == '11')
    <div class="company_logo_box border-xy" style="position: absolute; text-align: center; top:500px">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif
<table class="full-width border-xy">
    <tr>
        <td class="text-center"><h4>{{ $company->name }}</h4></td>
    </tr>
    {{--<tr>
        <td class="text-center"><h5>{{ $company->trade_name }}</h5></td>
    </tr>--}}
    <tr>
        <td class="text-center"><h5>{{ 'RUC '.$company->number }}</h5></td>
    </tr>
    <tr>
        <td class="text-center" style="text-transform: uppercase;">
            {{ ($establishment->address !== '-')? $establishment->address : '' }}
            {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
            {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
            {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
        </td>
    </tr>

    @isset($establishment->trade_address)
    <tr>
        <td class="text-center ">{{  ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : ''  }}</td>
    </tr>
    @endisset
    <tr>
        <td class="text-center">{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</td>
    </tr>

    <tr>
        <td class="text-center pt-3 border-top"><h4>{{ $document->document_type->description }}</h4></td>
    </tr>
    <tr>
        <td class="text-center pb-3 border-bottom"><h3>{{ $document_number }}</h3></td>
    </tr>
</table>
<table class="full-width border-xy">
    <tr >
        <td class="align-top desc" id="width-20"><p class="desc">F. Emisión:</p></td>
        <td class="text-right desc pr-2" id="width-25"><p class="desc">{{ $document->date_of_issue->format('Y-m-d') }}</p></td>
        <td class="align-top desc pl-2"><p class="desc">H. Emisión:</p></td>
        <td class="text-right desc"><p class="desc">{{ $document->time_of_issue }}</p></td>
    </tr>
</table>

<table class="full-width border-xy">
    <tr >
        <td class="align-top">
            <p class="desc">
            <strong>Estado:</strong>
            </p>
        </td>
        <td class="text-right desc">
            <p class=" desc">
            {{ $paymentCondition }}
            </p>
        </td>
    </tr>

    <tr>
        <td class="align-top desc">
            <strong>Asesor Financiero:</strong>
        </td>
        @if ($document->seller)
            <td class="text-right desc">{{ $document->seller->name }}</td>
        @else
            <td class="text-right desc">{{ $document->user->name }}</td>
        @endif
    </tr>

    <tr>
        <td class="align-top desc"><p class="desc">Cliente:</p></td>
        <td class="text-right desc"><p class="">{{ $customer->name }}</p></td>
    </tr>
    <tr>
        <td class="desc"><p class="desc">{{ $customer->identity_document_type->description }}:</p></td>
        <td><p class="">{{ $customer->number }}</p></td>
    </tr>
    
    @if ($customer->address !== '')
        <tr>
            <td class="align-top"><p class="desc">Dirección:</p></td>
            <td>
                <p class="desc">
                    {{ $customer->address }}
                    {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                    {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                    {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
                </p>
            </td>
        </tr>
    @endif
    
</table>

<table class="full-width border-xy">
    <thead class="">
    <tr>
        <th class="border-top-bottom desc-9 text-left">COD.</th>
        <th class="border-top-bottom desc-9 text-left">CANTIDAD</th>
        <th class="border-top-bottom desc-9 text-left">DESCRIPCIÓN</th>
        <th class="border-top-bottom desc-9 text-left">P.UNIT</th>
        <th class="border-top-bottom desc-9 text-left">TOTAL</th>
    </tr>
    </thead>
    <tbody>
        @php
            $mora_total=0;
        @endphp
    @foreach($document->items as $row)
        @if (isset($row->credit_int))
            <tr>
                <td class="text-left align-top">{{ $row->relation_item->internal_id }}</td>
                <td class="text-center desc-9 align-top font-bold">
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="text-left desc-9 align-top font-bold">
                    INTERESES
                </td>
                <td class="text-right desc-9 align-top">{{ number_format($row->unit_price, 2) }}</td>
                <td class="text-right desc-9 align-top font-bold">{{ number_format($row->total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="border-bottom"></td>
            </tr>
        @endif
        @if (isset($row->credit_mor))
            <tr>
                <td class="text-left align-top">{{ $row->relation_item->internal_id }}</td>
                <td class="text-center desc-9 align-top font-bold">
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="text-left desc-9 align-top font-bold">
                    MORA
                </td>
                @php
                    $mora_total=$row->credit_mor;
                @endphp
                <td class="text-right desc-9 align-top">{{ number_format($row->credit_mor, 2) }}</td>
                <td class="text-right desc-9 align-top font-bold">{{ number_format($row->credit_mor, 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="border-bottom"></td>
            </tr>
        @endif
        
    @endforeach
        @if($document->total_exportation > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif
        @if($document->total_free > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">OP. GRATUITAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_free, 2) }}</td>
            </tr>
        @endif
        @if($document->total_unaffected > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">OP. INAFECTAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
        @endif
        @if($document->total_exonerated > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">OP. EXONERADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
        @endif

        @if ($document->document_type_id === '07')
            @if($document->total_taxed >= 0)
            @php
                $total_credit=$document->total_taxed+$mora_total;
            @endphp
            <tr>
                <td colspan="4" class="text-right font-bold desc">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($total_credit, 2) }}</td>
            </tr>
            @endif
        @elseif($document->total_taxed > 0)
            @php
                $total_credit=$document->total_taxed+$mora_total;
            @endphp
            <tr>
                <td colspan="4" class="text-right font-bold desc">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($total_credit, 2) }}</td>
            </tr>
        @endif

        @if($document->total_plastic_bag_taxes > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">ICBPER: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="4" class="text-right font-bold desc">IGV: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total_igv, 2) }}</td>
        </tr>

        @if($document->total_isc > 0)
        <tr>
            <td colspan="4" class="text-right font-bold desc">ISC: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total_isc, 2) }}</td>
        </tr>
        @endif

        @if($document->total_discount > 0 && $document->subtotal > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">SUBTOTAL: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->subtotal, 2) }}</td>
            </tr>
        @endif

        @if($document->total_discount > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">DESCUENTO TOTAL: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif

        @if($document->total_charge > 0)
            @if($document->charges)
                @php
                    $total_factor = 0;
                    foreach($document->charges as $charge) {
                        $total_factor = ($total_factor + $charge->factor) * 100;
                    }
                @endphp
                <tr>
                    <td colspan="4" class="text-right font-bold desc">CARGOS ({{$total_factor}}%): {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold desc">{{ number_format($document->total_charge, 2) }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="4" class="text-right font-bold desc">CARGOS: {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold desc">{{ number_format($document->total_charge, 2) }}</td>
                </tr>
            @endif
        @endif

        <tr>
            <td colspan="4" class="text-right font-bold desc">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            @php
                $total_paid=$document->total+$mora_total;
            @endphp
            <td class="text-right font-bold desc">{{ number_format($total_paid, 2) }}</td>
        </tr>

        @if(($document->retention || $document->detraction) && $document->total_pending_payment > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">M. PENDIENTE: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_pending_payment, 2) }}</td>
            </tr>
        @endif

        @if($balance < 0)
           <tr>
               <td colspan="4" class="text-right font-bold desc">VUELTO: {{ $document->currency_type->symbol }}</td>
               <td class="text-right font-bold desc">{{ number_format(abs($balance),2, ".", "") }}</td>
           </tr>
        @endif
    </tbody>
</table>
<table class="full-width border-xy">
    <tr>
            <tr>
                <td class="desc pt-3">Son: <span class="font-bold">{{ NumberLetter::convertToLetter($total_paid) }}</span></td>
            </tr>
    </tr>


    @if ($document->detraction)
        <tr>
            <td class="desc pt-3 font-bold">
                Operación sujeta al Sistema de Pago de Obligaciones Tributarias
            </td>
        </tr>
    @endif

    <tr>
        <td class="desc pt-3">
            @foreach($document->additional_information as $information)
                @if ($information)
                    @if ($loop->first)
                        <strong>Información adicional</strong>
                    @endif
                    <p class="desc">@if(\App\CoreFacturalo\Helpers\Template\TemplateHelper::canShowNewLineOnObservation())
                            {!! \App\CoreFacturalo\Helpers\Template\TemplateHelper::SetHtmlTag($information) !!}
                        @else
                            {{$information}}
                        @endif</p>
                @endif
            @endforeach
            <br>
            
        </td>
    </tr>
</table>
<table class="full-width border-xy">
    <tr>
        <td class="text-left pt-3"><img class="qr_code" src="data:image/png;base64, {{ $document->qr }}" /></td>
        <td class="text-left desc">
            @if(in_array($document->document_type->id,['01','03']))
                @foreach($accounts as $account)
                    <p class="desc">
                        <small>
                            <span class="font-bold desc">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                            <span class="font-bold desc">N°:</span> {{$account->number}}
                            @if($account->cci)
                            <span class="font-bold desc">CCI:</span> {{$account->cci}}
                            @endif
                        </small>
                    </p>
                @endforeach
            @endif
            <p>
                Código Hash: {{ $document->hash }}
            </p>
            
            <p>
                <strong>CONDICIÓN DE PAGO: {{ $paymentCondition }} </strong>
            </p>
            <p>
                @if ($document->payment_condition_id === '01')

                    @if($payments->count())
                                <strong>PAGOS:</strong>
                        @foreach($payments as $row)
                            {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}
                        @endforeach
                    @endif
                @else
                    @foreach($document->fee as $key => $quote)
                        &#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }} / Fecha: {{ $quote->date->format('d-m-Y') }} / Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}
                    @endforeach
                @endif
            </p>
            <p>

            </p>
        </td>
    </tr>
</table>
<table class="border-xy border-bottom">
    

    @if ($customer->department_id == 16)
        <tr>
            <td class="text-center desc pt-5">
                Representación impresa del Comprobante de Pago Electrónico.
                <br/>Esta puede ser consultada en:
                <br/> <b>{!! url('/buscar') !!}</b>
                <br/> "Bienes transferidos en la Amazonía
                <br/>para ser consumidos en la misma
            </td>
        </tr>
    @endif
    @if ($document->terms_condition)
        <tr>
            <td class="desc">
                <br>
                <h6 style="font-size: 10px; font-weight: bold;">Términos y condiciones del servicio</h6>
                {!! $document->terms_condition !!}
            </td>
        </tr>
    @endif

    </tr>

    <tr>
        <td class="text-center desc pt-5">Para consultar el comprobante ingresar a {!! url('/buscar') !!}</td>
    </tr>
</table>
<br>
<table class="full-width border-all">
    <tr>
        <td class="text-center"><h4>{{ $company->name }}</h4></td>
    </tr>
    <tr>
        <td class="text-center"><h5>{{ 'RUC '.$company->number }}</h5></td>
    </tr>
    <tr>
        <td class="text-center" style="text-transform: uppercase;">
            {{ ($establishment->address !== '-')? $establishment->address : '' }}
            {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
            {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
            {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
        </td>
    </tr>
    @isset($establishment->trade_address)
    <tr>
        <td class="text-center ">{{  ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : ''  }}</td>
    </tr>
    @endisset
    <tr>
        <td class="text-center ">{{ ($establishment->telephone !== '-')? 'Central telefónica: '.$establishment->telephone : '' }}</td>
    </tr>
    <tr>
        <td class="text-center">{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</td>
    </tr>
    @isset($establishment->web_address)
        <tr>
            <td class="text-center">{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</td>
        </tr>
    @endisset

    @isset($establishment->aditional_information)
        <tr>
            <td class="text-center pb-3">{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</td>
        </tr>
    @endisset
    @foreach ($document->items as $row)
        <tr>
            <td class="text-center pt-3 border-top-bottom"><h4>RECIBO N°: 000000{{$row->credit_amortized}}</h4></td>
        </tr>
    @endforeach
</table>
<table class="full-width border-xy">
    <tr >
        <td class="align-top desc" id="width-20"><p class="desc">F. Emisión:</p></td>
        <td class="text-right desc pr-2" id="width-25"><p class="desc">{{ $document->date_of_issue->format('Y-m-d') }}</p></td>
        <td class="align-top desc pl-2"><p class="desc">H. Emisión:</p></td>
        <td class="text-right desc"><p class="desc">{{ $document->time_of_issue }}</p></td>
    </tr>
</table>
<table class="full-width border-xy">
    <tr>
        <td class="align-top desc">
            <strong>Asesor Financiero:</strong>
        </td>
        @if ($document->seller)
            <td class="text-right desc">{{ $document->seller->name }}</td>
        @else
            <td class="text-right desc">{{ $document->user->name }}</td>
        @endif
    </tr>

    <tr>
        <td class="align-top desc"><p class="desc">Cliente:</p></td>
        <td class="text-right desc"><p class="">{{ $customer->name }}</p></td>
    </tr>
    <tr>
        <td class="desc"><p class="desc">{{ $customer->identity_document_type->description }}:</p></td>
        <td><p class="">{{ $customer->number }}</p></td>
    </tr>
    @foreach ($document->items as $row)
        <tr>
            <td class="align-top"><p class="desc">Cuotas fraccionadas: </p></td>
            <td class="desc"><p class="desc">{{ $row->credit_fees }}</p></td>
        </tr>
    @endforeach
</table>
<table class="full-width border-xy">
    <tr>
        @foreach ($document->items as $row)
        <td class="align-top desc" id="width-20"><p class="desc">Cuota N°:</p></td>
        <td class="text-right desc pr-2" id="width-25"><p class="desc">{{ $row->credit_amortized}}</p></td>
        <td class="align-top desc pl-2"><p class="desc">Pendiente:</p></td>
        <td class="text-right desc"><p class="desc">{{ $row->credit_pending }}</p></td>
    @endforeach
    </tr>
    
</table>
<table class="full-width border-xy border-bottom">
    <thead class="">
    <tr>
        <th class="border-top-bottom desc-9 text-left">CONCEPTO</th>
        <th class="border-top-bottom desc-9 text-left">TOTAL</th>
    </tr>
    </thead>
    <tbody>
        @php
            $credit_capital=0;
            $credit_interest=0;
            $credit_mora=0;
        @endphp
        @foreach ($document->items as $row)
            @if ($row->credit_cap)
            @php
                $credit_capital=$row->credit_cap;
            @endphp
                <tr>
                    <td class="text-left desc-9 align-top">CAPITAL</td>
                    <td class="text-right desc-9 align-top font-bold">{{ number_format($row->credit_capital, 2) }}</td>
                </tr>
            @endif
            @if ($row->credit_int)
            @php
                $credit_interest=$row->credit_int;
            @endphp
                <tr>
                    <td class="text-left desc-9 align-top">INTERESES</td>
                    <td class="text-right desc-9 align-top font-bold">{{ number_format($row->credit_int, 2) }}</td>
                </tr>
            @endif
            @if ($row->credit_mor)
            @php
                $credit_mora=$row->credit_mor;
            @endphp
                <tr>
                    <td class="text-left desc-9 align-top">MORA</td>
                    <td class="text-right desc-9 align-top font-bold">{{ number_format($row->credit_int, 2) }}</td>
                </tr>
            @endif
        @endforeach
        <tr>
            @php
                $total_credit_all=$credit_cap+$credit_int+$credit_mor;
            @endphp
            <td colspan="2" class="text-left font-bold desc border-top-bottom">TOTAL: {{number_format($total_credit_all, 2)}}</td>
            
        </tr>
        <tr>

                <tr>
                    <td class="desc pt-3">Son: <span class="font-bold">{{ NumberLetter::convertToLetter($total_credit_all) }}</span></td>
                </tr>
        </tr>
        <tr>
            @foreach ($document->items as $row)
                <td colspan="2" class="text-center font-bold desc ">PROXIMO VENCIMIENTO: {{$row->credit_dat}}</td>
            @endforeach
            
        </tr>
    </tbody>
</table>
</body>
</html>
