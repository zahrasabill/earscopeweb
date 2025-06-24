<h2 style="text-align: center;">Hasil Pemeriksaan</h2>

<h3>Data Pasien</h3>
<p><strong>Nama Pasien:</strong> {{ $penanganan->user->name }}</p>
<p><strong>Kode Akses / No Rekam Medis:</strong> {{ $penanganan->user->kode_akses ?? '-' }}</p>
<p><strong>Tanggal Lahir:</strong> {{ \Carbon\Carbon::parse($penanganan->user->tanggal_lahir)->format('d-m-Y') ?? '-' }}</p>
<p><strong>Usia:</strong> 
  {{ \Carbon\Carbon::parse($penanganan->user->tanggal_lahir)->age ?? '-' }} tahun
</p>
<p><strong>No Telepon:</strong> {{ $penanganan->user->no_telp ?? '-' }}</p>
<p><strong>Jenis Kelamin:</strong> 
  {{ $penanganan->user->gender == 'L' ? 'Laki-laki' : ($penanganan->user->gender == 'P' ? 'Perempuan' : '-') }}
</p>

<hr>

<h3>Detail Pemeriksaan</h3>
<p><strong>Tanggal Pemeriksaan:</strong> {{ \Carbon\Carbon::parse($penanganan->tanggal_penanganan)->format('d-m-Y') }}</p>
<p><strong>Keluhan:</strong> {{ $penanganan->keluhan }}</p>
<p><strong>Riwayat Penyakit:</strong> {{ $penanganan->riwayat_penyakit ?? '-' }}</p>
<p><strong>Diagnosis:</strong> {{ $penanganan->diagnosis_manual }}</p>
<p><strong>Tindakan:</strong> {{ $penanganan->tindakan }}</p>
