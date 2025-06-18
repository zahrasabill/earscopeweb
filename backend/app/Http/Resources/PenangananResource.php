<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenangananResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'tanggal_penanganan' => $this->tanggal_penanganan,
            'keluhan' => $this->keluhan,
            'riwayat_penyakit' => $this->riwayat_penyakit,
            'diagnosis_manual' => $this->diagnosis_manual,
            'telinga_terkena' => $this->telinga_terkena,
            'tindakan' => $this->tindakan,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            
            // Relasi user (pasien yang ditangani)
            'user' => $this->when($this->relationLoaded('user'), function () {
                return [
                    'id' => $this->user->id,
                    'nama' => $this->user->name,
                    'kode_akses' => $this->user->kode_akses,
                    'tanggal_lahir' => $this->user->tanggal_lahir,
                    'usia' => $this->user->usia,
                    'gender' => $this->user->gender,
                    'no_telp' => $this->user->no_telp,
                ];
            }),
            
            // Relasi assigned_to_user (dokter yang di-assign)
            'assigned_to_user' => $this->when($this->relationLoaded('assignedToUser') && $this->assignedToUser, function () {
                return [
                    'id' => $this->assignedToUser->id,
                    'nama' => $this->assignedToUser->name,
                    'kode_akses' => $this->assignedToUser->kode_akses,
                    'tanggal_lahir' => $this->assignedToUser->tanggal_lahir,
                    'usia' => $this->assignedToUser->usia,
                    'gender' => $this->assignedToUser->gender,
                    'no_telp' => $this->assignedToUser->no_telp,
                ];
            }),
            
            // Relasi created_by_user (user yang membuat record)
            'created_by_user' => $this->when($this->relationLoaded('createdByUser') && $this->createdByUser, function () {
                return [
                    'id' => $this->createdByUser->id,
                    'nama' => $this->createdByUser->name,
                    'kode_akses' => $this->createdByUser->kode_akses,
                    'tanggal_lahir' => $this->createdByUser->tanggal_lahir,
                    'usia' => $this->createdByUser->usia,
                    'gender' => $this->createdByUser->gender,
                    'no_telp' => $this->createdByUser->no_telp,
                ];
            }),
        ];
    }
}