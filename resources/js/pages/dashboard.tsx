import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    Activity,
    AlertCircle,
    AlertTriangle,
    ArrowUpRight,
    Banknote,
    BarChart3,
    CheckCircle2,
    Clock,
    CreditCard,
    DollarSign,
    Package,
    PackageCheck,
    PlayCircle,
    QrCode,
    Receipt,
    ShieldCheck,
    ShoppingCart,
    Store,
    TrendingUp,
    Users,
    Warehouse,
    Zap,
} from 'lucide-react';
import { useState } from 'react';
import PendingInvitationsModal from '@/components/pending-invitations-modal';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate, formatRupiah } from '@/lib/utils';
import { dashboard } from '@/routes';
import type { DashboardInvitation } from '@/types';

type Props = {
    pendingInvitations?: DashboardInvitation[];
    metrics?: {
        today_sales_gross: number;
        today_sales_net: number;
        today_transaction_count: number;
        monthly_net_profit: number;
        monthly_revenue: number;
    };
    low_stock_products?: Array<{
        id: number;
        name: string;
        sku: string;
        stock: number;
        min_stock_alert: number;
    }>;
    recent_transactions?: Array<{
        id: number;
        invoice_number: string;
        cashier_name: string;
        customer_name: string;
        total_amount: number;
        payment_method: string;
        status: string;
        created_at: string;
    }>;
    active_shift?: {
        id: number;
        opened_at: string;
        starting_cash: number;
    } | null;
    top_products?: Array<{
        product_name: string;
        total_qty: number;
        total_sales: number;
    }>;
};

export default function Dashboard({
    pendingInvitations = [],
    metrics = {
        today_sales_gross: 0,
        today_sales_net: 0,
        today_transaction_count: 0,
        monthly_net_profit: 0,
        monthly_revenue: 0,
    },
    low_stock_products = [],
    recent_transactions = [],
    active_shift = null,
    top_products = [],
}: Props) {
    const [showInvitations, setShowInvitations] = useState(
        pendingInvitations.length > 0,
    );

    const maxTopProductSales = Math.max(...top_products.map((p) => p.total_sales), 1);

    const getPaymentBadge = (method: string) => {
        const m = (method || '').toLowerCase();
        if (m === 'qris') return { label: 'QRIS', icon: QrCode, class: 'border-emerald-500/30 text-emerald-600 dark:text-emerald-400 bg-emerald-500/10' };
        if (m === 'card') return { label: 'KARTU', icon: CreditCard, class: 'border-sky-500/30 text-sky-600 dark:text-sky-400 bg-sky-500/10' };
        return { label: 'TUNAI', icon: Banknote, class: 'border-primary/30 text-primary bg-primary/10' };
    };

    return (
        <>
            <Head title="Dashboard Exec & Operasional - POS Nova" />

            <PendingInvitationsModal
                invitations={pendingInvitations}
                open={pendingInvitations.length > 0 && showInvitations}
                onOpenChange={setShowInvitations}
            />

            <div className="flex flex-col gap-6 p-4 md:p-6 max-w-[1600px] mx-auto w-full font-sans text-foreground">
                {/* 1. Header Command Bar & Shift Status Widget */}
                <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-card border border-border p-5 rounded-2xl shadow-sm">
                    <div className="space-y-1">
                        <div className="flex items-center gap-2.5">
                            <div className="w-9 h-9 rounded-xl bg-primary text-primary-foreground flex items-center justify-center shadow-sm">
                                <Store className="w-5 h-5" />
                            </div>
                            <div>
                                <div className="flex items-center gap-2">
                                    <h1 className="text-xl font-bold tracking-tight text-card-foreground">
                                        Toko POS Nova Pusat
                                    </h1>
                                    <Badge variant="outline" className="text-[10px] font-mono border-primary/30 text-primary bg-primary/10">
                                        Realtime Sync
                                    </Badge>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Pusat Kontrol Operasional, Kasir POS, Inventori & Laporan Keuangan
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Active Shift Indicator Panel */}
                    <div className="flex items-center gap-3 bg-muted/60 border border-border p-2 rounded-xl">
                        {active_shift ? (
                            <>
                                <div className="flex items-center gap-3 px-3 py-1">
                                    <span className="relative flex h-3 w-3">
                                        <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span className="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                    </span>
                                    <div>
                                        <div className="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                            Shift Kasir Aktif
                                        </div>
                                        <div className="text-[11px] text-muted-foreground font-mono">
                                            Kas Awal: <span className="font-semibold text-foreground">{formatRupiah(active_shift.starting_cash)}</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2">
                                    <Button asChild size="sm" className="font-semibold text-xs shadow-sm">
                                        <Link href="/pos">
                                            <ShoppingCart className="w-3.5 h-3.5 mr-1.5" /> Buka Kasir (F2)
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline" className="text-xs border-border">
                                        <Link href="/shifts">
                                            <Clock className="w-3.5 h-3.5 mr-1.5" /> Kelola Shift
                                        </Link>
                                    </Button>
                                </div>
                            </>
                        ) : (
                            <>
                                <div className="flex items-center gap-3 px-3 py-1">
                                    <AlertTriangle className="w-4 h-4 text-amber-500" />
                                    <div>
                                        <div className="text-xs font-bold text-amber-600 dark:text-amber-400">Shift Belum Dibuka</div>
                                        <div className="text-[11px] text-muted-foreground">Buka shift kasir sebelum transaksi</div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2">
                                    <Button asChild size="sm" className="font-semibold text-xs shadow-sm bg-amber-600 hover:bg-amber-500 text-white">
                                        <Link href="/shifts">
                                            <PlayCircle className="w-3.5 h-3.5 mr-1.5" /> Buka Shift Sekarang
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline" className="text-xs">
                                        <Link href="/pos">
                                            <ShoppingCart className="w-3.5 h-3.5 mr-1.5" /> POS
                                        </Link>
                                    </Button>
                                </div>
                            </>
                        )}
                    </div>
                </div>

                {/* 2. Executive KPI Cards Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* Today Net Sales (Primary KPI Hero Card) */}
                    <motion.div initial={{ opacity: 0, y: 15 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.3, delay: 0.05 }}>
                        <Card className="bg-card border-border shadow-sm relative overflow-hidden h-full">
                            <CardHeader className="pb-2">
                                <CardDescription className="text-xs font-medium uppercase tracking-wider flex items-center justify-between text-muted-foreground">
                                    <span>Penjualan Bersih Hari Ini</span>
                                    <span className="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                        <TrendingUp className="w-4 h-4" />
                                    </span>
                                </CardDescription>
                                <CardTitle className="text-3xl font-extrabold text-card-foreground font-mono tabular-nums">
                                    {formatRupiah(metrics.today_sales_net)}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="pt-0 text-xs text-muted-foreground space-y-1">
                                <div className="flex justify-between items-center">
                                    <span>Gross Sales:</span>
                                    <span className="font-mono text-foreground font-semibold">{formatRupiah(metrics.today_sales_gross)}</span>
                                </div>
                                <div className="flex justify-between items-center text-[11px]">
                                    <span>Rata-rata / Struk:</span>
                                    <span className="font-mono text-foreground">
                                        {metrics.today_transaction_count > 0 
                                            ? formatRupiah(metrics.today_sales_net / metrics.today_transaction_count)
                                            : 'Rp 0'}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </motion.div>

                    {/* Today Transactions */}
                    <motion.div initial={{ opacity: 0, y: 15 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.3, delay: 0.1 }}>
                        <Card className="bg-card border-border shadow-sm relative overflow-hidden h-full">
                            <CardHeader className="pb-2">
                                <CardDescription className="text-xs font-medium uppercase tracking-wider flex items-center justify-between text-muted-foreground">
                                    <span>Transaksi Sukses Hari Ini</span>
                                    <span className="p-1.5 rounded-lg bg-primary/10 text-primary">
                                        <ShoppingCart className="w-4 h-4" />
                                    </span>
                                </CardDescription>
                                <CardTitle className="text-3xl font-extrabold text-card-foreground font-mono tabular-nums">
                                    {metrics.today_transaction_count} <span className="text-sm font-normal text-muted-foreground">Struk</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="pt-0 text-xs text-muted-foreground space-y-1">
                                <div className="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-medium">
                                    <CheckCircle2 className="w-3.5 h-3.5" /> Transaksi Kasir Berjalan Normal
                                </div>
                            </CardContent>
                        </Card>
                    </motion.div>

                    {/* Monthly Revenue */}
                    <motion.div initial={{ opacity: 0, y: 15 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.3, delay: 0.15 }}>
                        <Card className="bg-card border-border shadow-sm relative overflow-hidden h-full">
                            <CardHeader className="pb-2">
                                <CardDescription className="text-xs font-medium uppercase tracking-wider flex items-center justify-between text-muted-foreground">
                                    <span>Omset Bulan Ini</span>
                                    <span className="p-1.5 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400">
                                        <DollarSign className="w-4 h-4" />
                                    </span>
                                </CardDescription>
                                <CardTitle className="text-3xl font-extrabold text-card-foreground font-mono tabular-nums">
                                    {formatRupiah(metrics.monthly_revenue)}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="pt-0 text-xs text-muted-foreground">
                                Akumulasi omset toko periode bulan berjalan.
                            </CardContent>
                        </Card>
                    </motion.div>

                    {/* Monthly Net Profit */}
                    <motion.div initial={{ opacity: 0, y: 15 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.3, delay: 0.2 }}>
                        <Card className="bg-card border-border shadow-sm relative overflow-hidden h-full">
                            <CardHeader className="pb-2">
                                <CardDescription className="text-xs font-medium uppercase tracking-wider flex items-center justify-between text-muted-foreground">
                                    <span>Estimasi Laba Bersih</span>
                                    <span className="p-1.5 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400">
                                        <Activity className="w-4 h-4" />
                                    </span>
                                </CardDescription>
                                <CardTitle className="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">
                                    {formatRupiah(metrics.monthly_net_profit)}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="pt-0 text-xs text-muted-foreground">
                                Bersih setelah HPP & pengeluaran operasional.
                            </CardContent>
                        </Card>
                    </motion.div>
                </div>

                {/* 3. Quick Action Toolbar */}
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <Link
                        href="/pos"
                        className="p-3.5 rounded-xl bg-primary/10 border border-primary/20 hover:bg-primary/20 transition-all flex items-center justify-between group shadow-sm"
                    >
                        <div className="flex items-center gap-3">
                            <div className="w-9 h-9 rounded-lg bg-primary text-primary-foreground flex items-center justify-center font-bold">
                                <ShoppingCart className="w-4 h-4" />
                            </div>
                            <div>
                                <div className="text-xs font-bold text-foreground">Kasir POS</div>
                                <div className="text-[10px] text-muted-foreground font-mono">Shortcuts: F2</div>
                            </div>
                        </div>
                        <ArrowUpRight className="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                    </Link>

                    <Link
                        href="/products"
                        className="p-3.5 rounded-xl bg-card border border-border hover:bg-muted/70 transition-all flex items-center justify-between group shadow-sm"
                    >
                        <div className="flex items-center gap-3">
                            <div className="w-9 h-9 rounded-lg bg-muted text-foreground flex items-center justify-center font-bold">
                                <Package className="w-4 h-4" />
                            </div>
                            <div>
                                <div className="text-xs font-bold text-card-foreground">Katalog Produk</div>
                                <div className="text-[11px] text-muted-foreground">Kelola Barang & Harga</div>
                            </div>
                        </div>
                        <ArrowUpRight className="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-colors" />
                    </Link>

                    <Link
                        href="/stock"
                        className="p-3.5 rounded-xl bg-card border border-border hover:bg-muted/70 transition-all flex items-center justify-between group shadow-sm"
                    >
                        <div className="flex items-center gap-3">
                            <div className="w-9 h-9 rounded-lg bg-muted text-foreground flex items-center justify-center font-bold">
                                <Warehouse className="w-4 h-4" />
                            </div>
                            <div>
                                <div className="text-xs font-bold text-card-foreground">Stok & Opname</div>
                                <div className="text-[11px] text-muted-foreground">Riwayat Pergerakan</div>
                            </div>
                        </div>
                        <ArrowUpRight className="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-colors" />
                    </Link>

                    <Link
                        href="/reports/sales"
                        className="p-3.5 rounded-xl bg-card border border-border hover:bg-muted/70 transition-all flex items-center justify-between group shadow-sm"
                    >
                        <div className="flex items-center gap-3">
                            <div className="w-9 h-9 rounded-lg bg-muted text-foreground flex items-center justify-center font-bold">
                                <BarChart3 className="w-4 h-4" />
                            </div>
                            <div>
                                <div className="text-xs font-bold text-card-foreground">Laporan Sales</div>
                                <div className="text-[11px] text-muted-foreground">Export Excel & PDF</div>
                            </div>
                        </div>
                        <ArrowUpRight className="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-colors" />
                    </Link>
                </div>

                {/* 4. Workbench Main Content Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Left Column (2 Cols wide): Live Transaction Stream & Top Products */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Live Recent Transactions Feed */}
                        <Card className="bg-card border-border shadow-sm">
                            <CardHeader className="flex flex-row items-center justify-between pb-3">
                                <div>
                                    <CardTitle className="text-base font-bold text-card-foreground flex items-center gap-2">
                                        <Receipt className="w-4.5 h-4.5 text-primary" /> Transaksi Kasir Terbaru
                                    </CardTitle>
                                    <CardDescription className="text-xs text-muted-foreground">
                                        5 Transaksi kasir paling baru yang berhasil diproses.
                                    </CardDescription>
                                </div>
                                <Button asChild variant="outline" size="sm" className="text-xs">
                                    <Link href="/reports/sales">Lihat Semua Laporan</Link>
                                </Button>
                            </CardHeader>
                            <CardContent className="p-0">
                                {recent_transactions.length > 0 ? (
                                    <div className="divide-y divide-border">
                                        {recent_transactions.map((tx) => {
                                            const pBadge = getPaymentBadge(tx.payment_method);
                                            const PaymentIcon = pBadge.icon;
                                            return (
                                                <div key={tx.id} className="p-4 flex items-center justify-between hover:bg-muted/40 transition-colors">
                                                    <div className="space-y-1">
                                                        <div className="flex items-center gap-2">
                                                            <span className="font-mono text-xs font-bold text-primary">{tx.invoice_number}</span>
                                                            <span className={`inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full border ${pBadge.class}`}>
                                                                <PaymentIcon className="w-3 h-3" />
                                                                {pBadge.label}
                                                            </span>
                                                        </div>
                                                        <div className="text-xs text-muted-foreground">
                                                            Kasir: <span className="text-foreground font-medium">{tx.cashier_name}</span> &bull; Pelanggan: <span className="text-foreground font-medium">{tx.customer_name}</span>
                                                        </div>
                                                    </div>

                                                    <div className="text-right">
                                                        <div className="text-sm font-bold text-foreground font-mono tabular-nums">
                                                            {formatRupiah(tx.total_amount)}
                                                        </div>
                                                        <div className="text-[10px] text-muted-foreground font-mono">
                                                            {formatDate(tx.created_at)}
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                ) : (
                                    <div className="p-8 text-center text-xs text-muted-foreground">
                                        Belum ada transaksi hari ini. Klik <Link href="/pos" className="text-primary font-bold hover:underline">Buka Kasir (POS)</Link> untuk memproses transaksi.
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Top Selling Products Widget */}
                        <Card className="bg-card border-border shadow-sm">
                            <CardHeader className="pb-3">
                                <CardTitle className="text-base font-bold text-card-foreground flex items-center gap-2">
                                    <TrendingUp className="w-4.5 h-4.5 text-emerald-500" /> Top 5 Produk Terlaris Hari Ini
                                </CardTitle>
                                <CardDescription className="text-xs text-muted-foreground">
                                    Volume dan omset produk peringkat tertinggi hari ini.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="p-0">
                                {top_products.length > 0 ? (
                                    <div className="divide-y divide-border">
                                        {top_products.map((item, index) => {
                                            const percentage = Math.min(100, Math.round((item.total_sales / maxTopProductSales) * 100));
                                            return (
                                                <div key={item.product_name} className="p-4 space-y-2 hover:bg-muted/40 transition-colors">
                                                    <div className="flex items-center justify-between">
                                                        <div className="flex items-center gap-3">
                                                            <span className="w-6 h-6 rounded-full bg-muted text-foreground text-xs font-bold flex items-center justify-center border border-border">
                                                                #{index + 1}
                                                            </span>
                                                            <div>
                                                                <div className="text-xs font-bold text-card-foreground">{item.product_name}</div>
                                                                <div className="text-[11px] text-muted-foreground">
                                                                    Volume: <span className="text-emerald-600 dark:text-emerald-400 font-bold">{item.total_qty} pcs</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div className="text-right font-mono text-xs font-bold text-card-foreground tabular-nums">
                                                            {formatRupiah(item.total_sales)}
                                                        </div>
                                                    </div>
                                                    {/* Relative Sales Progress Bar */}
                                                    <div className="w-full bg-muted h-1.5 rounded-full overflow-hidden">
                                                        <div
                                                            className="bg-emerald-500 h-full rounded-full transition-all duration-500"
                                                            style={{ width: `${percentage}%` }}
                                                        />
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                ) : (
                                    <div className="p-8 text-center text-xs text-muted-foreground">
                                        Belum ada data penjualan produk terlaris hari ini.
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Right Column (1 Col wide): Alert Center & System Operational Summary */}
                    <div className="space-y-6">
                        {/* Low Stock Alert Center Card */}
                        <Card className="bg-card border-border shadow-sm">
                            <CardHeader className="pb-3 flex flex-row items-center justify-between">
                                <div>
                                    <CardTitle className="text-base font-bold text-card-foreground flex items-center gap-2">
                                        <AlertTriangle className="w-4.5 h-4.5 text-amber-500" /> Peringatan Stok Menipis
                                    </CardTitle>
                                    <CardDescription className="text-xs text-muted-foreground">
                                        Produk yang menyentuh batas minimum stok alert.
                                    </CardDescription>
                                </div>
                                {low_stock_products.length > 0 && (
                                    <Badge variant="outline" className="border-amber-500/40 text-amber-600 dark:text-amber-400 bg-amber-500/10 font-bold">
                                        {low_stock_products.length} Item
                                    </Badge>
                                )}
                            </CardHeader>
                            <CardContent className="p-0">
                                {low_stock_products.length > 0 ? (
                                    <div className="divide-y divide-border">
                                        {low_stock_products.map((p) => (
                                            <div key={p.id} className="p-3.5 flex items-center justify-between hover:bg-muted/40 transition-colors">
                                                <div className="space-y-0.5">
                                                    <div className="text-xs font-bold text-card-foreground">{p.name}</div>
                                                    <div className="text-[10px] text-muted-foreground font-mono">SKU: {p.sku}</div>
                                                </div>
                                                <div className="text-right">
                                                    <Badge variant="outline" className="border-amber-500/40 text-amber-600 dark:text-amber-400 bg-amber-500/10 text-xs font-bold font-mono">
                                                        Sisa: {p.stock} pcs
                                                    </Badge>
                                                    <div className="text-[10px] text-muted-foreground mt-0.5">Min: {p.min_stock_alert} pcs</div>
                                                </div>
                                            </div>
                                        ))}
                                        <div className="p-3 text-center border-t border-border bg-muted/20">
                                            <Button asChild variant="link" size="sm" className="text-xs text-primary font-bold">
                                                <Link href="/stock">Kelola & Restok Barang &rarr;</Link>
                                            </Button>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="p-6 text-center text-xs text-emerald-600 dark:text-emerald-400 flex flex-col items-center gap-2">
                                        <CheckCircle2 className="w-7 h-7" />
                                        <span className="font-semibold">Seluruh produk stok terpantau aman.</span>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Store Operational Info Card */}
                        <Card className="bg-card border-border shadow-sm">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-bold text-card-foreground flex items-center gap-2">
                                    <ShieldCheck className="w-4 h-4 text-primary" /> Informasi Sistem Toko
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-xs text-muted-foreground">
                                <p className="leading-relaxed">
                                    POS Nova menjalankan validasi stok dan transaksi keuangan secara terenkripsi.
                                </p>
                                <div className="p-3 rounded-xl bg-muted/60 border border-border space-y-1">
                                    <div className="font-bold text-foreground flex items-center gap-1.5">
                                        <Zap className="w-3.5 h-3.5 text-primary" /> SOP Kasir Harian:
                                    </div>
                                    <div className="text-[11px] text-muted-foreground leading-relaxed">
                                        Lakukan <strong>Buka Shift</strong> saat toko buka dan <strong>Tutup Shift</strong> di akhir jam operasional untuk rekonsiliasi saldo kas fisik.
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = (props: { currentTeam?: { slug: string } | null }) => ({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/',
        },
    ],
});
