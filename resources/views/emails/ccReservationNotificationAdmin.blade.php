@component('mail::message')
# Permohonan Reservasi Command Center

Data pemohon:
- Kode reservasi: {{ $reservation->reservation_code }}
- PIC: {{ $reservation->name }}
@if($reservation->organization_name)
- Instansi: {{ $reservation->organization_name }}
@endif
- Waktu: {{ date('d-m-Y', strtotime($reservation->reservation_date)) }}
- Sesi: {{ $reservation->commandCenterShift->name . ' (' . $reservation->commandCenterShift->time . ')' }}

@component('mail::button', ['url' => $url])
Lihat Reservasi
@endcomponent

@endcomponent
