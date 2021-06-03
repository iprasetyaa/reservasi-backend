@component('mail::message')
# Persetujuan Reservasi Aset Berulang

Terima kasih Anda sudah melakukan reservasi pada Aplikasi Digiteam Reservasi Aset.
Melalui surat elektronik ini, berdasarkan data reservasi yang kami terima yaitu:
- Nama: {{ $data[0]['reservation']->user_fullname }}
@if ($data[0]['reservation']->holder)
- Email Penanggung Jawab: {{ $data[0]['reservation']->holder }}
@endif
- Judul Kegiatan: {{ $data[0]['reservation']->title }}
- Catatan Kegiatan: {{ $data[0]['reservation']->description ?? '-' }}
- Hari: @foreach ($request['days_formated'] as $day) {{ $day['name'] }} @endforeach
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
- Meeting ID: {{ $item['zoom_data']['meeting_id'] }}
- Passcode: {{ $item['zoom_data']['password'] }}
- Host Key: {{ $item['zoom_data']['host_key'] }}
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
