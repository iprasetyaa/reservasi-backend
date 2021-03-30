@component('mail::message')
# {{ ($reservation->approval_status == 'ALREADY_APPROVED') ? 'Persutujuan' : 'Status Permohonan' }} Reservasi Command Center

Terima kasih Anda sudah melakukan permohonan reservasi Command Center.
Melalui surat elektronik ini, berdasarkan data reservasi yang kami terima yaitu:
- Kode reservasi: {{ $reservation->reservation_code }}
- PIC: {{ $reservation->name }}
@if($reservation->organization_name)
- Instansi: {{ $reservation->organization_name }}
@endif
- Waktu: {{ date('d-m-Y', strtotime($reservation->reservation_date)) }}
- Sesi: {{ $reservation->commandCenterShift->name . ' (' . $reservation->commandCenterShift->time . ')' }}
- Status: {{ __('message.' . strtolower($reservation->approval_status)) }}

@if($reservation->note)
{{ $reservation->note }}
@endif

@component('mail::button', ['url' => $url])
Lihat Reservasi
@endcomponent

Terimakasih,<br>
{{ $from }}<br>
Email: {{ config('mail.from.address') }}
@endcomponent

