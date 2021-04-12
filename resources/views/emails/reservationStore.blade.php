@component('mail::message')
# Reservasi Aset Baru

Melalui surat elektronik ini, kami memberitahukan bahwa ada data reservasi yang kami terima yaitu:
- Nama Pegawai: {{ $reservation->user_fullname }}
- Judul Kegiatan: {{ $reservation->title }}
- Catatan Kegiatan: {{ $reservation->description }}
- Tanggal dan Waktu Kegiatan: {{ date('d-m-Y H:i', strtotime($reservation->start_time)) }} sd. {{ date('d-m-Y H:i', strtotime($reservation->end_time)) }}
- Tanggal Dibuat: {{ date('d-m-Y', strtotime($reservation->created_at)) }}

@component('mail::button', ['url' => $url])
Lihat Reservasi
@endcomponent

Terimakasih,<br>
Tim {{ config('app.name') }}
@endcomponent
