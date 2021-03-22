@component('mail::message')
# Persetujuan Reservasi Command Center

Terima kasih Anda sudah melakukan permohonan reservasi Command Center.
Melalui surat elektronik ini, berdasarkan data reservasi yang kami terima yaitu:
- Kode reservasi: {{ $reservation->reservation_code }}
- PIC: {{ $reservation->name }}
@if($reservation->organization_name)
- Instansi: {{ $reservation->organization_name }}
@endif
- Waktu: {{ $reservation->reservation_date_formated }}
- Sesi: {{ $reservation->commandCenterShift->name }}
- Status: {{ $reservation->approval_status }}

@if($reservation->note)
{{ $reservation->note }}
@endif

Terimakasih,<br>
{{ $from }}<br>
Email: {{ config('mail.from.address') }}
@endcomponent

