@component('mail::message')
# Persetujuan Reservasi Aset Berulang

Terima kasih Anda sudah melakukan reservasi pada Aplikasi Digiteam Reservasi Aset.
Melalui surat elektronik ini, berdasarkan data reservasi yang kami terima yaitu:
- Nama : {{ $data[0]['reservation']->user_fullname }}
- Judul Kegiatan: {{ $data[0]['reservation']->title }}
- Catatan Kegiatan: {{ $data[0]['reservation']->description ?? '-' }}
- Hari: @foreach ($request['days'] as $day) {{ \Carbon\Carbon::create($day)->locale('id_ID')->dayName . ' ' }} @endforeach
<br>
@if ($request['repeat_type'] === 'WEEKLY')
- Berulang: {{ $request['week'] }} minggu sekali
@endif
@if ($request['repeat_type'] === 'MONTHLY')
- Minggu ke: {{ $request['week'] }}
- Berulang: {{ $request['month'] }} bulan sekali
@endif
- Tanggal dan Waktu Dimulai: {{ $data[0]['reservation']->start_time->format('d-m-Y H:i') }}
- Tanggal dan Waktu Selesai: {{ $lastRecurring->start_time->format('d-m-Y H:i') }}
- Tanggal Dibuat: {{ $data[0]['reservation']->created_at->format('d-m-Y') }}

#### Informasi Ruangan
@foreach ($data as $item)
- Nama Ruangan: {{ $item['reservation']->asset_name }}
@if($item['reservation']->join_url)
- Host Key: {{ $item['user']->host_key }}
- Link Invitation: {{ $item['reservation']->join_url }}
@endif
---
@endforeach

@component('mail::button', ['url' => $url])
Lihat Reservasi
@endcomponent

Terima kasih,<br>
Tim {{ config('app.name') }}
@endcomponent
