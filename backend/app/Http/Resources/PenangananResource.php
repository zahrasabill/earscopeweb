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
            'tanggal_penanganan' => $this->tanggal_penanganan->format('Y-m-d'),
            'tanggal_penanganan_formatted' => $this->tanggal_penanganan->format('d F Y'),
            'keluhan' => $this->keluhan,
            'riwayat_penyakit' => $this->riwayat_penyakit,
            'diagnosis_manual' => $this->diagnosis_manual,
            'telinga_terkena' => $this->telinga_terkena,
            'tindakan' => $this->tindakan,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Relasi user (pasien)
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'role' => $this->user->role,
                    'phone' => $this->user->phone ?? null,
                    'date_of_birth' => $this->user->date_of_birth ?? null,
                    'gender' => $this->user->gender ?? null,
                ];
            }),
            
            // Relasi created by user (dokter/admin yang membuat record)
            'created_by_user' => $this->whenLoaded('createdByUser', function () {
                return [
                    'id' => $this->createdByUser->id,
                    'name' => $this->createdByUser->name,
                    'email' => $this->createdByUser->email,
                    'role' => $this->createdByUser->role,
                ];
            }),
            
            // Computed attributes
            'patient_name' => $this->user->name ?? 'Unknown',
            'doctor_name' => $this->createdByUser->name ?? 'System',
            
            // Additional formatted data for frontend
            'telinga_display' => $this->getTelingaDisplay(),
            'summary' => $this->getSummary(),
        ];
    }
    
    /**
     * Get display text for telinga terkena
     */
    private function getTelingaDisplay(): string
    {
        return match($this->telinga_terkena) {
            'kiri' => 'Telinga Kiri',
            'kanan' => 'Telinga Kanan',
            'keduanya' => 'Kedua Telinga',
            default => $this->telinga_terkena
        };
    }
    
    /**
     * Get summary of the penanganan for quick view
     */
    private function getSummary(): string
    {
        $summary = $this->diagnosis_manual;
        if (strlen($summary) > 50) {
            $summary = substr($summary, 0, 47) . '...';
        }
        return $summary;
    }
}