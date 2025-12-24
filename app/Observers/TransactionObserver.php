<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\MasterSaldo;
use App\Models\HistoriSaldo;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     * Auto-update saldo when new transaction is created
     */
    public function created(Transaction $transaction): void
    {
        $this->updateActiveSaldo($transaction, 'create');
    }

    /**
     * Handle the Transaction "updated" event.
     * Auto-update saldo when transaction is modified
     */
    public function updated(Transaction $transaction): void
    {
        $this->updateActiveSaldo($transaction, 'update', $transaction->getOriginal());
    }

    /**
     * Handle the Transaction "deleted" event.
     * Auto-update saldo when transaction is removed
     */
    public function deleted(Transaction $transaction): void
    {
        $this->updateActiveSaldo($transaction, 'delete');
    }

    /**
     * Core logic to update active master_saldo
     */
    private function updateActiveSaldo(Transaction $transaction, string $action, ?array $original = null)
    {
        // Get or create active saldo period
        $masterSaldo = MasterSaldo::where('status', 'aktif')->first();
        
        if (!$masterSaldo) {
            // Auto-create first period if not exists
            $masterSaldo = MasterSaldo::create([
                'periode' => date('Y-m'),
                'saldo_awal' => 0,
                'total_masuk' => 0,
                'total_keluar' => 0,
                'saldo_akhir' => 0,
                'status' => 'aktif',
            ]);
        }

        $saldoSebelum = $masterSaldo->saldo_akhir;
        
        // Calculate based on action
        if ($action === 'create') {
            // Add new transaction
            if ($transaction->category->jenis === 'Pemasukan') {
                $masterSaldo->total_masuk += $transaction->nominal;
                $masterSaldo->saldo_akhir += $transaction->nominal;
            } else {
                $masterSaldo->total_keluar += $transaction->nominal;
                $masterSaldo->saldo_akhir -= $transaction->nominal;
            }
            
        } elseif ($action === 'update') {
            // Reverse old transaction values
            $oldCategory = \App\Models\Category::find($original['category_id']);
            if ($oldCategory->jenis === 'Pemasukan') {
                $masterSaldo->total_masuk -= $original['nominal'];
                $masterSaldo->saldo_akhir -= $original['nominal'];
            } else {
                $masterSaldo->total_keluar -= $original['nominal'];
                $masterSaldo->saldo_akhir += $original['nominal'];
            }
            
            // Apply new transaction values
            if ($transaction->category->jenis === 'Pemasukan') {
                $masterSaldo->total_masuk += $transaction->nominal;
                $masterSaldo->saldo_akhir += $transaction->nominal;
            } else {
                $masterSaldo->total_keluar += $transaction->nominal;
                $masterSaldo->saldo_akhir -= $transaction->nominal;
            }
            
        } elseif ($action === 'delete') {
            // Reverse transaction
            if ($transaction->category->jenis === 'Pemasukan') {
                $masterSaldo->total_masuk -= $transaction->nominal;
                $masterSaldo->saldo_akhir -= $transaction->nominal;
            } else {
                $masterSaldo->total_keluar -= $transaction->nominal;
                $masterSaldo->saldo_akhir += $transaction->nominal;
            }
        }
        
        $masterSaldo->save();
        
        // Record to histori_saldo as audit trail
        HistoriSaldo::create([
            'id_saldo' => $masterSaldo->id,
            'transaction_id' => $action === 'delete' ? null : $transaction->id,
            'nominal' => $transaction->nominal,
            'saldo_sebelum' => $saldoSebelum,
            'saldo_sesudah' => $masterSaldo->saldo_akhir,
            'keterangan' => ucfirst($action) . ' transaksi: ' . $transaction->category->nama_kategori,
        ]);
    }
}
