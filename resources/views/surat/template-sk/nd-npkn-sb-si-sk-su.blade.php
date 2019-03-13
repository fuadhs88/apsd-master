@extends('layouts.mst_template')
@section('rincian')
    <table>
        <tr>
            <td width="65%">&nbsp;</td>
            <td width="35%">Madiun, {{strftime('%d %B %Y', strtotime($sk->tgl_surat))}}<br><br>Kepada</td>
        </tr>
        <tr>
            <td width="65%">
                <table>
                    <tr>
                        <td>Nomor</td>
                        <td>&nbsp;:</td>
                        <td>{{$sk->no_surat}}</td>
                    </tr>
                    <tr>
                        <td>Sifat</td>
                        <td>&nbsp;:</td>
                        <td>{{ucfirst($sk->sifat_surat)}}</td>
                    </tr>
                    <tr>
                        <td>Lampiran</td>
                        <td>&nbsp;:</td>
                        <td>{{$sk->lampiran}}</td>
                    </tr>
                    <tr>
                        <td>Perihal</td>
                        <td>&nbsp;:</td>
                        <td>{{$sk->perihal}}</td>
                    </tr>
                </table>
            </td>
            <td width="35%">
                <p class="penerima" style="margin-top: -45px;">Yth. Sdr. {{$sk->nama_penerima}}</p><br>
                di&nbsp;&ndash;&nbsp;<span
                        style="letter-spacing: 5px;text-transform: uppercase">{{$sk->kota_penerima}}</span>
            </td>
        </tr>
    </table>
@endsection