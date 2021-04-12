@component('mail::message')
# Reservasi Aset Baru

Melalui surat elektronik ini, kami memberitahukan bahwa ada data reservasi yang kami terima yaitu:
- Nama Pegawai: {{ $reservation->user_fullname }}
- Judul Kegiatan: {{ $reservation->title }}
- Catatan Kegiatan: {{ $reservation->description }}
- Tanggal dan Waktu Kegiatan: {{ $reservation->start_time->format('d-m-Y H:i') }} sd. {{ $reservation->end_time->format('d-m-Y H:i') }}
- Tanggal Dibuat: {{ $reservation->created_at->format('d-m-Y') }}

@component('mail::button', ['url' => $url])
Lihat Reservasi
@endcomponent

Terimakasih,<br>
Tim {{ config('app.name') }}
@endcomponent
