import { Head, Link, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    ArrowRight,
    CheckCircle2,
    Clock,
    PackageCheck,
    ShieldCheck,
    ShoppingCart,
    Sparkles,
    Store,
    TrendingUp,
    Zap,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard, login } from '@/routes';

export default function Welcome() {
    const { auth, currentTeam } = usePage<any>().props;
    const dashboardUrl = currentTeam ? dashboard(currentTeam.slug).url : '/pos';

    const features = [
        {
            icon: ShoppingCart,
            title: 'Kasir Cepat & Touch-Friendly',
            description:
                'Interaksi super responsif, pencarian produk kilat, scan barcode, serta dukungan pembayaran Tunai, QRIS, dan Split Payment.',
            badge: 'POS Realtime',
        },
        {
            icon: PackageCheck,
            title: 'Manajemen Stok & Multi-Satuan',
            description:
                'Notifikasi otomatis saat stok barang menipis, pencatatan opname, varian produk, serta konversi unit (pcs, box, dus).',
            badge: 'Inventori',
        },
        {
            icon: Clock,
            title: 'Shift & Rekonsiliasi Kasir',
            description:
                'Buka dan tutup shift kasir secara disiplin untuk pencatatan modal awal dan rekonsiliasi kas fisik secara akurat.',
            badge: 'Shift System',
        },
        {
            icon: TrendingUp,
            title: 'Analitik & Laporan Penjualan',
            description:
                'Pantau omset harian, produk terlaris, serta laporan laba-rugi realtime dengan ekspor langsung ke Excel & PDF.',
            badge: 'Analytics',
        },
    ];

    const demoAccounts = [
        { role: 'Owner Toko', email: 'owner@posnova.test', desc: 'Akses penuh ke seluruh sistem & laporan' },
        { role: 'Manager Toko', email: 'manager@posnova.test', desc: 'Manajemen produk, stok, & operasional' },
        { role: 'Kasir Utama', email: 'kasir@posnova.test', desc: 'Akses khusus POS & transaksi kasir' },
        { role: 'Staff Gudang', email: 'gudang@posnova.test', desc: 'Manajemen barang & stok masuk/keluar' },
    ];

    return (
        <>
            <Head title="POS Nova - Sistem Kasir Modern & Terintegrasi" />

            <div className="relative min-h-screen bg-background text-foreground font-sans selection:bg-primary selection:text-primary-foreground overflow-hidden">
                {/* Background Ambient Glow */}
                <div className="pointer-events-none absolute -top-40 -left-40 h-[500px] w-[500px] rounded-full bg-primary/10 blur-[130px]" />
                <div className="pointer-events-none absolute top-1/3 -right-40 h-[600px] w-[600px] rounded-full bg-primary/5 blur-[150px]" />

                {/* Top Navbar */}
                <header className="sticky top-0 z-50 backdrop-blur-md bg-background/80 border-b border-border px-6 py-4">
                    <div className="max-w-7xl mx-auto flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 bg-primary text-primary-foreground rounded-xl flex items-center justify-center shadow-md">
                                <Store className="w-5 h-5" />
                            </div>
                            <div className="flex items-center gap-2">
                                <span className="text-xl font-bold tracking-tight text-foreground">
                                    POS <span className="text-primary">Nova</span>
                                </span>
                                <Badge variant="secondary" className="hidden sm:inline-flex text-[10px]">
                                    v1.0 Ready
                                </Badge>
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            {auth?.user ? (
                                <Button asChild className="font-medium shadow-sm">
                                    <Link href={dashboardUrl}>
                                        Dashboard POS <ArrowRight className="w-4 h-4 ml-1.5" />
                                    </Link>
                                </Button>
                            ) : (
                                <Button asChild className="font-medium shadow-sm">
                                    <Link href={login.url()}>
                                        Masuk Akun <ArrowRight className="w-4 h-4 ml-1.5" />
                                    </Link>
                                </Button>
                            )}
                        </div>
                    </div>
                </header>

                {/* Hero Section */}
                <section className="relative pt-12 pb-20 px-6">
                    <div className="max-w-4xl mx-auto text-center space-y-8">
                        <motion.div
                            initial={{ opacity: 0, y: 15 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.5 }}
                            className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-muted border border-border text-muted-foreground text-xs font-medium"
                        >
                            <Sparkles className="w-4 h-4 text-primary" />
                            <span>Sistem Kasir & Toko Retail Generasi Terbaru</span>
                        </motion.div>

                        <motion.h1
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.6, delay: 0.1 }}
                            className="text-4xl sm:text-6xl font-extrabold tracking-tight text-foreground leading-tight"
                        >
                            Solusi Point of Sale <br className="hidden sm:inline" />
                            <span className="text-primary">Super Cepat, Presisi, & Responsif</span>
                        </motion.h1>

                        <motion.p
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.6, delay: 0.2 }}
                            className="max-w-2xl mx-auto text-muted-foreground text-base sm:text-lg leading-relaxed"
                        >
                            Optimalkan operasional toko retail & F&B Anda. Kelola kasir, stok barang, pembukaan shift, dan analisa laba-rugi realtime dalam satu tempat.
                        </motion.p>

                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.6, delay: 0.3 }}
                            className="flex flex-wrap items-center justify-center gap-4 pt-2"
                        >
                            {auth?.user ? (
                                <Button asChild size="lg" className="font-semibold text-base px-8 py-6 rounded-xl shadow-md">
                                    <Link href={dashboardUrl}>
                                        Buka Dashboard POS <ArrowRight className="w-5 h-5 ml-2" />
                                    </Link>
                                </Button>
                            ) : (
                                <Button asChild size="lg" className="font-semibold text-base px-8 py-6 rounded-xl shadow-md">
                                    <Link href={login.url()}>
                                        Masuk ke Kasir / Admin <ArrowRight className="w-5 h-5 ml-2" />
                                    </Link>
                                </Button>
                            )}
                        </motion.div>
                    </div>
                </section>

                {/* Features Section */}
                <section className="py-16 px-6 max-w-7xl mx-auto border-t border-border">
                    <div className="text-center space-y-3 mb-12">
                        <h2 className="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">
                            Fitur Unggulan POS Nova
                        </h2>
                        <p className="text-muted-foreground text-sm max-w-xl mx-auto">
                            Dirancang khusus untuk mendukung efisiensi kasir non-teknis dan memudahkan owner mengambil keputusan bisnis.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {features.map((feature, idx) => (
                            <motion.div
                                key={feature.title}
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.1 * idx }}
                            >
                                <Card className="bg-card border-border hover:border-ring transition-all duration-300 h-full shadow-sm">
                                    <CardContent className="p-6 space-y-4">
                                        <div className="flex items-center justify-between">
                                            <div className="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                                                <feature.icon className="w-6 h-6" />
                                            </div>
                                            <Badge variant="outline" className="text-[10px]">
                                                {feature.badge}
                                            </Badge>
                                        </div>
                                        <h3 className="text-lg font-bold text-card-foreground">{feature.title}</h3>
                                        <p className="text-muted-foreground text-xs leading-relaxed">{feature.description}</p>
                                    </CardContent>
                                </Card>
                            </motion.div>
                        ))}
                    </div>
                </section>

                {/* Demo Credentials Card */}
                <section className="py-16 px-6 max-w-4xl mx-auto">
                    <Card className="bg-card border-border shadow-md">
                        <CardContent className="p-8 space-y-6">
                            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-border pb-6">
                                <div>
                                    <div className="inline-flex items-center gap-2 text-primary text-xs font-semibold uppercase tracking-wider mb-1">
                                        <Zap className="w-4 h-4" /> Kredensial Pengujian Seeder
                                    </div>
                                    <h3 className="text-xl font-bold text-card-foreground">Akun Demo Siap Pakai</h3>
                                </div>
                                <Badge variant="secondary" className="text-xs font-mono">
                                    Password: <code className="text-primary font-bold ml-1">password</code>
                                </Badge>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {demoAccounts.map((acc) => (
                                    <div
                                        key={acc.email}
                                        className="p-4 rounded-xl bg-muted/50 border border-border hover:border-primary/40 transition-all flex items-center justify-between group"
                                    >
                                        <div className="space-y-1">
                                            <div className="flex items-center gap-2">
                                                <span className="text-sm font-semibold text-foreground">{acc.role}</span>
                                            </div>
                                            <div className="text-xs text-muted-foreground font-mono">{acc.email}</div>
                                            <p className="text-[11px] text-muted-foreground">{acc.desc}</p>
                                        </div>
                                        <Link
                                            href={login.url()}
                                            className="text-xs font-semibold text-primary hover:underline flex items-center gap-1 ml-2"
                                        >
                                            Login <ArrowRight className="w-3.5 h-3.5" />
                                        </Link>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </section>

                {/* Footer */}
                <footer className="border-t border-border py-8 px-6 text-center text-xs text-muted-foreground">
                    <div className="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div className="flex items-center gap-2">
                            <Store className="w-4 h-4 text-primary" />
                            <span className="font-semibold text-foreground">POS Nova</span> &copy; {new Date().getFullYear()} — Sistem Kasir Modern & Terintegrasi
                        </div>
                        <div className="flex items-center gap-3">
                            <span>Laravel 13</span>
                            <span>&bull;</span>
                            <span>React Inertia</span>
                            <span>&bull;</span>
                            <span>shadcn/ui</span>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
