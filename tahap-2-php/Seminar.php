<?php

declare(strict_types=1);

class seminar_kuota {
    public int $kuota;
    private string $judul;

    public function __construct(string $judul = 'Seminar Teknik Informatika', int $kuota = 0)
    {
        $this->judul = $judul;
        $this->kuota = $kuota;
    }

    public function pesan(int $jumlah): bool
    {
        if ($jumlah > $this->kuota) {
            return false;
        }
        $this->kuota -= $jumlah;
        return true;
    }

    public function batal(int $jumlah): void
    {
        $this->kuota += $jumlah;
    }

    public function Sisa_Kursi(): int
    {
        return $this->kuota;
    }
}
