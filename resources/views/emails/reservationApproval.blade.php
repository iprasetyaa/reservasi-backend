@component('mail::message')
# Persetujuan Reservasi Aset

Terima kasih Anda sudah melakukan reservasi pada Aplikasi Digiteam Reservasi Aset.
Melalui surat elektronik ini, berdasarkan data reservasi yang kami terima yaitu:
- Nama : {{ $reservation->user_fullname }}
- Judul Kegiatan: {{ $reservation->title }}
- Catatan Kegiatan: {{ $reservation->description }}
- Nama Ruangan : {{ $reservation->asset_name }}
@if($reservation->join_url)
- Host Key: {{ $hostkey }}
@endif
- Tanggal dan Waktu Kegiatan: {{ date('d-m-Y H:i', strtotime($reservation->start_time)) }} sd. {{ date('d-m-Y H:i', strtotime($reservation->end_time)) }}
- Tanggal Dibuat: {{ date('d-m-Y', strtotime($reservation->created_at)) }}

@if($reservation->join_url)
- Link Invitation Room: {{ $reservation->join_url }}
@endif

@component('mail::button', ['url' => $url])
Lihat Reservasi
@endcomponent

Terimakasih,<br>
Tim {{ config('app.name') }}
@endcomponent
