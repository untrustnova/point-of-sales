import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { PosLayout } from '@/layouts/pos-layout';
import { Clock, Banknote, AlertCircle, CheckCircle, X, Plus } from 'lucide-react';
import { toast } from 'sonner';

interface Shift {
    id: number;
    opened_at: string;
    closed_at?: string;
    starting_cash: number;
    ending_cash_expected?: number;
    ending_cash_actual?: number;
    cash_difference?: number;
    status: string;
    notes?: string;
    user?: { name: string };
}

interface ShiftsPageProps {
    active_shift: Shift | null;
    shift_history: {
        data: Shift[];
        links: any[];
    };
}

export default function ShiftsPage({ active_shift, shift_history }: ShiftsPageProps) {
    const [openModal, setOpenModal] = useState(false);
    const [closeModal, setCloseModal] = useState(false);
    const [startingCash, setStartingCash] = useState('');
    const [actualEndingCash, setActualEndingCash] = useState('');
    const [notes, setNotes] = useState('');

    const handleOpenShift = (e: React.FormEvent) => {
        e.preventDefault();
        router.post(
            '/shifts/open',
            {
                starting_cash: parseFloat(startingCash) || 0,
                notes: notes || null,
            },
            {
                onSuccess: () => {
                    toast.success('Shift kasir berhasil dibuka!');
                    setOpenModal(false);
                    setStartingCash('');
                    setNotes('');
                },
                onError: (err) => toast.error(err.message || 'Gagal membuka shift.'),
            }
        );
    };

    const handleCloseShift = (e: React.FormEvent) => {
        e.preventDefault();
        if (!active_shift) return;

        router.post(
            `/shifts/${active_shift.id}/close`,
            {
                actual_ending_cash: parseFloat(actualEndingCash) || 0,
                notes: notes || null,
            },
            {
                onSuccess: () => {
                    toast.success('Shift kasir berhasil ditutup!');
                    setCloseModal(false);
                    setActualEndingCash('');
                    setNotes('');
                },
                onError: (err) => toast.error(err.message || 'Gagal menutup shift.'),
            }
        );
    };

    return (
        <PosLayout title="Shift Kasir">
            <Head title="Shift Kasir" />

            <div className="flex-1 p-6 bg-slate-950 overflow-y-auto space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-bold text-white flex items-center gap-2">
                            <Clock className="text-indigo-400" /> Manajemen Shift Kasir
                        </h1>
                        <p className="text-xs text-slate-400 mt-1">
                            Kelola modal awal kas, rekonsiliasi kas harian, dan tutup shift kasir
                        </p>
                    </div>

                    {!active_shift ? (
                        <button
                            onClick={() => setOpenModal(true)}
                            className="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-4 py-2 rounded-xl text-xs flex items-center gap-2 shadow-lg shadow-indigo-600/30"
                        >
                            <Plus size={16} /> Buka Shift Kasir Baru
                        </button>
                    ) : (
                        <button
                            onClick={() => setCloseModal(true)}
                            className="bg-rose-600 hover:bg-rose-500 text-white font-bold px-4 py-2 rounded-xl text-xs flex items-center gap-2 shadow-lg shadow-rose-600/30"
                        >
                            <Clock size={16} /> Tutup Shift Kasir Sekarang
                        </button>
                    )}
                </div>

                {/* Active Shift Card */}
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
                    <h3 className="text-sm font-semibold text-slate-300 mb-3 uppercase tracking-wider">
                        Status Shift Saat Ini
                    </h3>

                    {active_shift ? (
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div className="bg-slate-800/60 p-4 rounded-xl border border-slate-700/50">
                                <div className="text-xs text-slate-400">Status</div>
                                <div className="text-lg font-bold text-emerald-400 flex items-center gap-1.5 mt-1">
                                    <CheckCircle size={18} /> Aktif (Open)
                                </div>
                            </div>
                            <div className="bg-slate-800/60 p-4 rounded-xl border border-slate-700/50">
                                <div className="text-xs text-slate-400">Waktu Buka</div>
                                <div className="text-sm font-mono font-semibold text-white mt-1">
                                    {new Date(active_shift.opened_at).toLocaleString('id-ID')}
                                </div>
                            </div>
                            <div className="bg-slate-800/60 p-4 rounded-xl border border-slate-700/50">
                                <div className="text-xs text-slate-400">Modal Awal Kas</div>
                                <div className="text-lg font-mono font-bold text-indigo-400 mt-1">
                                    Rp {Number(active_shift.starting_cash).toLocaleString('id-ID')}
                                </div>
                            </div>
                            <div className="bg-slate-800/60 p-4 rounded-xl border border-slate-700/50">
                                <div className="text-xs text-slate-400">Petugas Kasir</div>
                                <div className="text-sm font-semibold text-white mt-1">
                                    {active_shift.user?.name || 'Kasir'}
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="bg-slate-800/30 border border-dashed border-slate-800 rounded-xl p-8 text-center text-slate-400">
                            <AlertCircle size={36} className="mx-auto mb-2 text-amber-400/80" />
                            <p className="text-sm font-medium">Tidak ada shift kasir yang aktif saat ini.</p>
                            <p className="text-xs text-slate-500 mt-1">
                                Klik tombol "Buka Shift Kasir Baru" di kanan atas untuk memulai sesi kasir.
                            </p>
                        </div>
                    )}
                </div>

                {/* Shift History Table */}
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
                    <h3 className="text-sm font-semibold text-slate-300 mb-4">Riwayat Shift Kasir</h3>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-800/70 text-slate-400 uppercase font-mono border-b border-slate-800">
                                <tr>
                                    <th className="p-3">Waktu Buka</th>
                                    <th className="p-3">Waktu Tutup</th>
                                    <th className="p-3">Kasir</th>
                                    <th className="p-3">Modal Awal</th>
                                    <th className="p-3">Kas Ekspektasi</th>
                                    <th className="p-3">Kas Fisik</th>
                                    <th className="p-3">Selisih</th>
                                    <th className="p-3">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 text-slate-300">
                                {shift_history.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={8} className="p-6 text-center text-slate-500">
                                            Belum ada riwayat shift
                                        </td>
                                    </tr>
                                ) : (
                                    shift_history.data.map((s) => (
                                        <tr key={s.id} className="hover:bg-slate-800/40">
                                            <td className="p-3 font-mono">
                                                {new Date(s.opened_at).toLocaleString('id-ID')}
                                            </td>
                                            <td className="p-3 font-mono">
                                                {s.closed_at ? new Date(s.closed_at).toLocaleString('id-ID') : '-'}
                                            </td>
                                            <td className="p-3 font-semibold text-white">{s.user?.name}</td>
                                            <td className="p-3 font-mono">
                                                Rp {Number(s.starting_cash).toLocaleString('id-ID')}
                                            </td>
                                            <td className="p-3 font-mono text-indigo-300">
                                                {s.ending_cash_expected ? `Rp ${Number(s.ending_cash_expected).toLocaleString('id-ID')}` : '-'}
                                            </td>
                                            <td className="p-3 font-mono text-emerald-300">
                                                {s.ending_cash_actual ? `Rp ${Number(s.ending_cash_actual).toLocaleString('id-ID')}` : '-'}
                                            </td>
                                            <td className={`p-3 font-mono font-bold ${
                                                (s.cash_difference || 0) < 0
                                                    ? 'text-rose-400'
                                                    : (s.cash_difference || 0) > 0
                                                    ? 'text-amber-400'
                                                    : 'text-slate-400'
                                            }`}>
                                                {s.cash_difference !== undefined ? `Rp ${Number(s.cash_difference).toLocaleString('id-ID')}` : '-'}
                                            </td>
                                            <td className="p-3">
                                                <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                                                    s.status === 'open' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-400'
                                                }`}>
                                                    {s.status.toUpperCase()}
                                                </span>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Modal Open Shift */}
            {openModal && (
                <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <form onSubmit={handleOpenShift} className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
                        <div className="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                            <h3 className="text-sm font-bold text-white flex items-center gap-2">
                                <Banknote className="text-indigo-400" size={18} /> Buka Shift Kasir Baru
                            </h3>
                            <button type="button" onClick={() => setOpenModal(false)} className="text-slate-400 hover:text-white">
                                <X size={18} />
                            </button>
                        </div>
                        <div className="p-5 space-y-4">
                            <div>
                                <label className="text-xs font-semibold text-slate-300 mb-1 block">Modal Awal Kas (Laci Cash Drawer)</label>
                                <input
                                    type="number"
                                    required
                                    value={startingCash}
                                    onChange={(e) => setStartingCash(e.target.value)}
                                    placeholder="Contoh: 200000"
                                    className="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm font-mono text-white focus:outline-none focus:border-indigo-500"
                                />
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-slate-300 mb-1 block">Catatan Buka Shift (Opsional)</label>
                                <textarea
                                    value={notes}
                                    onChange={(e) => setNotes(e.target.value)}
                                    placeholder="Keterangan..."
                                    className="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 h-20"
                                />
                            </div>
                        </div>
                        <div className="px-5 py-4 bg-slate-950 border-t border-slate-800 flex items-center justify-end gap-2">
                            <button type="button" onClick={() => setOpenModal(false)} className="px-4 py-2 text-xs text-slate-400 hover:text-white">
                                Batal
                            </button>
                            <button type="submit" className="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-2 rounded-xl text-xs">
                                Buka Shift
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Modal Close Shift */}
            {closeModal && active_shift && (
                <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <form onSubmit={handleCloseShift} className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
                        <div className="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                            <h3 className="text-sm font-bold text-white flex items-center gap-2">
                                <Clock className="text-rose-400" size={18} /> Tutup Shift Kasir
                            </h3>
                            <button type="button" onClick={() => setCloseModal(false)} className="text-slate-400 hover:text-white">
                                <X size={18} />
                            </button>
                        </div>
                        <div className="p-5 space-y-4">
                            <div>
                                <label className="text-xs font-semibold text-slate-300 mb-1 block">Total Kas Fisik Saat Ini (Laci Drawer)</label>
                                <input
                                    type="number"
                                    required
                                    value={actualEndingCash}
                                    onChange={(e) => setActualEndingCash(e.target.value)}
                                    placeholder="Masukkan jumlah kas fisik..."
                                    className="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm font-mono text-white focus:outline-none focus:border-indigo-500"
                                />
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-slate-300 mb-1 block">Catatan Rekonsiliasi (Opsional)</label>
                                <textarea
                                    value={notes}
                                    onChange={(e) => setNotes(e.target.value)}
                                    placeholder="Catatan..."
                                    className="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 h-20"
                                />
                            </div>
                        </div>
                        <div className="px-5 py-4 bg-slate-950 border-t border-slate-800 flex items-center justify-end gap-2">
                            <button type="button" onClick={() => setCloseModal(false)} className="px-4 py-2 text-xs text-slate-400 hover:text-white">
                                Batal
                            </button>
                            <button type="submit" className="bg-rose-600 hover:bg-rose-500 text-white font-bold px-5 py-2 rounded-xl text-xs">
                                Konfirmasi & Tutup Shift
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </PosLayout>
    );
}
