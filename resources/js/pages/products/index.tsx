import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { PosLayout } from '@/layouts/pos-layout';
import { Package, Plus, Search, FileSpreadsheet, Upload, Edit, Trash2, X, Image as ImageIcon } from 'lucide-react';
import { toast } from 'sonner';

interface Product {
    id: number;
    name: string;
    sku: string;
    barcode: string;
    buy_price: number;
    sell_price: number;
    stock: number;
    min_stock_alert: number;
    is_active: boolean;
    image_path?: string;
    category_id?: number;
    unit_id?: number;
    category?: { id: number; name: string };
    unit?: { id: number; name: string; short_name: string };
}

interface Category {
    id: number;
    name: string;
}

interface Unit {
    id: number;
    name: string;
    short_name: string;
}

interface ProductsPageProps {
    products: {
        data: Product[];
        links: any[];
    };
    categories: Category[];
    units: Unit[];
}

export default function ProductsPage({ products, categories, units }: ProductsPageProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [modalOpen, setModalOpen] = useState(false);
    const [importModalOpen, setImportModalOpen] = useState(false);
    const [editingProduct, setEditingProduct] = useState<Product | null>(null);
    const [importFile, setImportFile] = useState<File | null>(null);

    const [form, setForm] = useState({
        name: '',
        sku: '',
        barcode: '',
        category_id: '',
        unit_id: '',
        buy_price: '',
        sell_price: '',
        stock: '0',
        min_stock_alert: '5',
        image: null as File | null,
    });

    const openCreateModal = () => {
        setEditingProduct(null);
        setForm({
            name: '',
            sku: '',
            barcode: '',
            category_id: '',
            unit_id: '',
            buy_price: '',
            sell_price: '',
            stock: '0',
            min_stock_alert: '5',
            image: null,
        });
        setModalOpen(true);
    };

    const openEditModal = (p: Product) => {
        setEditingProduct(p);
        setForm({
            name: p.name,
            sku: p.sku || '',
            barcode: p.barcode || '',
            category_id: p.category_id?.toString() || '',
            unit_id: p.unit_id?.toString() || '',
            buy_price: p.buy_price.toString(),
            sell_price: p.sell_price.toString(),
            stock: p.stock.toString(),
            min_stock_alert: p.min_stock_alert.toString(),
            image: null,
        });
        setModalOpen(true);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const formData = new FormData();
        Object.entries(form).forEach(([key, val]) => {
            if (val !== null && val !== '') {
                formData.append(key, val as any);
            }
        });

        if (editingProduct) {
            formData.append('_method', 'PUT');
            router.post(`/products/${editingProduct.id}`, formData, {
                onSuccess: () => {
                    toast.success('Produk berhasil diperbarui!');
                    setModalOpen(false);
                },
                onError: () => toast.error('Gagal memperbarui produk.'),
            });
        } else {
            router.post('/products', formData, {
                onSuccess: () => {
                    toast.success('Produk berhasil ditambahkan!');
                    setModalOpen(false);
                },
                onError: () => toast.error('Gagal menambahkan produk.'),
            });
        }
    };

    const handleDelete = (p: Product) => {
        if (confirm(`Hapus produk "${p.name}"?`)) {
            router.delete(`/products/${p.id}`, {
                onSuccess: () => toast.success('Produk berhasil dihapus!'),
            });
        }
    };

    const handleImport = (e: React.FormEvent) => {
        e.preventDefault();
        if (!importFile) return;

        const formData = new FormData();
        formData.append('file', importFile);

        router.post('/products/import', formData, {
            onSuccess: () => {
                toast.success('Import produk berhasil!');
                setImportModalOpen(false);
                setImportFile(null);
            },
            onError: () => toast.error('Gagal mengimport produk.'),
        });
    };

    return (
        <PosLayout title="Katalog Produk">
            <Head title="Katalog Produk" />

            <div className="flex-1 p-6 bg-slate-950 overflow-y-auto space-y-6">
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-xl font-bold text-white flex items-center gap-2">
                            <Package className="text-indigo-400" /> Katalog Produk
                        </h1>
                        <p className="text-xs text-slate-400 mt-1">
                            Kelola data produk, harga jual, stok gudang, dan foto produk
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <a
                            href="/products/export"
                            className="bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold px-3 py-2 rounded-xl text-xs flex items-center gap-1.5 border border-slate-700"
                        >
                            <FileSpreadsheet size={15} className="text-emerald-400" /> Export Excel
                        </a>
                        <button
                            onClick={() => setImportModalOpen(true)}
                            className="bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold px-3 py-2 rounded-xl text-xs flex items-center gap-1.5 border border-slate-700"
                        >
                            <Upload size={15} className="text-indigo-400" /> Import Excel
                        </button>
                        <button
                            onClick={openCreateModal}
                            className="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-4 py-2 rounded-xl text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/30"
                        >
                            <Plus size={16} /> Tambah Produk
                        </button>
                    </div>
                </div>

                {/* Table & Search */}
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
                    <div className="relative max-w-md">
                        <Search className="absolute left-3 top-2.5 text-slate-400" size={16} />
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder="Cari nama produk, SKU, barcode..."
                            className="w-full bg-slate-800 border border-slate-700 rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500"
                        />
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-800/70 text-slate-400 uppercase font-mono border-b border-slate-800">
                                <tr>
                                    <th className="p-3">Produk</th>
                                    <th className="p-3">SKU / Barcode</th>
                                    <th className="p-3">Kategori</th>
                                    <th className="p-3">Harga Beli</th>
                                    <th className="p-3">Harga Jual</th>
                                    <th className="p-3">Stok</th>
                                    <th className="p-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 text-slate-300">
                                {products.data.map((p) => (
                                    <tr key={p.id} className="hover:bg-slate-800/40">
                                        <td className="p-3 font-semibold text-white flex items-center gap-2">
                                            {p.image_path ? (
                                                <img
                                                    src={`/storage/${p.image_path}`}
                                                    alt={p.name}
                                                    className="w-8 h-8 rounded-lg object-cover bg-slate-800"
                                                />
                                            ) : (
                                                <div className="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-500">
                                                    <ImageIcon size={14} />
                                                </div>
                                            )}
                                            <span>{p.name}</span>
                                        </td>
                                        <td className="p-3 font-mono text-slate-400">
                                            {p.sku} {p.barcode ? `(${p.barcode})` : ''}
                                        </td>
                                        <td className="p-3">{p.category?.name || '-'}</td>
                                        <td className="p-3 font-mono">
                                            Rp {Number(p.buy_price).toLocaleString('id-ID')}
                                        </td>
                                        <td className="p-3 font-mono text-indigo-300 font-semibold">
                                            Rp {Number(p.sell_price).toLocaleString('id-ID')}
                                        </td>
                                        <td className="p-3 font-mono">
                                            <span
                                                className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                                                    p.stock <= p.min_stock_alert
                                                        ? 'bg-rose-500/20 text-rose-300'
                                                        : 'bg-emerald-500/20 text-emerald-300'
                                                }`}
                                            >
                                                {p.stock} {p.unit?.short_name || 'pcs'}
                                            </span>
                                        </td>
                                        <td className="p-3 text-right">
                                            <div className="flex items-center justify-end gap-1">
                                                <button
                                                    onClick={() => openEditModal(p)}
                                                    className="p-1.5 text-slate-400 hover:text-indigo-300 hover:bg-slate-800 rounded-lg"
                                                >
                                                    <Edit size={14} />
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(p)}
                                                    className="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg"
                                                >
                                                    <Trash2 size={14} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Modal Form Product */}
            {modalOpen && (
                <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <form
                        onSubmit={handleSubmit}
                        className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl"
                    >
                        <div className="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                            <h3 className="text-sm font-bold text-white">
                                {editingProduct ? 'Edit Produk' : 'Tambah Produk Baru'}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setModalOpen(false)}
                                className="text-slate-400 hover:text-white"
                            >
                                <X size={18} />
                            </button>
                        </div>
                        <div className="p-5 space-y-3 max-h-[75vh] overflow-y-auto">
                            <div>
                                <label className="text-xs font-semibold text-slate-300 mb-1 block">Nama Produk</label>
                                <input
                                    type="text"
                                    required
                                    value={form.name}
                                    onChange={(e) => setForm({ ...form, name: e.target.value })}
                                    className="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                                />
                            </div>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-xs font-semibold text-slate-300 mb-1 block">Kategori</label>
                                    <select
                                        value={form.category_id}
                                        onChange={(e) => setForm({ ...form, category_id: e.target.value })}
                                        className="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                                    >
                                        <option value="">-- Pilih --</option>
                                        {categories.map((c) => (
                                            <option key={c.id} value={c.id}>
                                                {c.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="text-xs font-semibold text-slate-300 mb-1 block">Satuan</label>
                                    <select
                                        value={form.unit_id}
                                        onChange={(e) => setForm({ ...form, unit_id: e.target.value })}
                                        className="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                                    >
                                        <option value="">-- Pilih --</option>
                                        {units.map((u) => (
                                            <option key={u.id} value={u.id}>
                                                {u.name} ({u.short_name})
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-xs font-semibold text-slate-300 mb-1 block">Harga Beli</label>
                                    <input
                                        type="number"
                                        required
                                        value={form.buy_price}
                                        onChange={(e) => setForm({ ...form, buy_price: e.target.value })}
                                        className="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-white focus:outline-none focus:border-indigo-500"
                                    />
                                </div>
                                <div>
                                    <label className="text-xs font-semibold text-slate-300 mb-1 block">Harga Jual</label>
                                    <input
                                        type="number"
                                        required
                                        value={form.sell_price}
                                        onChange={(e) => setForm({ ...form, sell_price: e.target.value })}
                                        className="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-white focus:outline-none focus:border-indigo-500"
                                    />
                                </div>
                            </div>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-xs font-semibold text-slate-300 mb-1 block">Stok Awal</label>
                                    <input
                                        type="number"
                                        required
                                        value={form.stock}
                                        onChange={(e) => setForm({ ...form, stock: e.target.value })}
                                        className="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-white focus:outline-none focus:border-indigo-500"
                                    />
                                </div>
                                <div>
                                    <label className="text-xs font-semibold text-slate-300 mb-1 block">Min Alert Stok</label>
                                    <input
                                        type="number"
                                        required
                                        value={form.min_stock_alert}
                                        onChange={(e) => setForm({ ...form, min_stock_alert: e.target.value })}
                                        className="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-white focus:outline-none focus:border-indigo-500"
                                    />
                                </div>
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-slate-300 mb-1 block">Foto Produk (Auto WebP Compress)</label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={(e) => setForm({ ...form, image: e.target.files?.[0] || null })}
                                    className="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-indigo-300 hover:file:bg-slate-700"
                                />
                            </div>
                        </div>
                        <div className="px-5 py-4 bg-slate-950 border-t border-slate-800 flex items-center justify-end gap-2">
                            <button
                                type="button"
                                onClick={() => setModalOpen(false)}
                                className="px-4 py-2 text-xs text-slate-400 hover:text-white"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                className="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-2 rounded-xl text-xs"
                            >
                                Simpan Produk
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Modal Import Excel */}
            {importModalOpen && (
                <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <form
                        onSubmit={handleImport}
                        className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl"
                    >
                        <div className="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                            <h3 className="text-sm font-bold text-white flex items-center gap-2">
                                <Upload className="text-indigo-400" size={18} /> Import Produk dari Excel / CSV
                            </h3>
                            <button
                                type="button"
                                onClick={() => setImportModalOpen(false)}
                                className="text-slate-400 hover:text-white"
                            >
                                <X size={18} />
                            </button>
                        </div>
                        <div className="p-5 space-y-4">
                            <div>
                                <label className="text-xs font-semibold text-slate-300 mb-1 block">
                                    Pilih File Excel (.xlsx, .xls, .csv)
                                </label>
                                <input
                                    type="file"
                                    required
                                    accept=".xlsx,.xls,.csv"
                                    onChange={(e) => setImportFile(e.target.files?.[0] || null)}
                                    className="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-indigo-300 hover:file:bg-slate-700"
                                />
                            </div>
                        </div>
                        <div className="px-5 py-4 bg-slate-950 border-t border-slate-800 flex items-center justify-end gap-2">
                            <button
                                type="button"
                                onClick={() => setImportModalOpen(false)}
                                className="px-4 py-2 text-xs text-slate-400 hover:text-white"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                className="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-2 rounded-xl text-xs"
                            >
                                Import Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </PosLayout>
    );
}
