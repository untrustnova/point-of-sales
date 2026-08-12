import React, { useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    LayoutDashboard,
    ShoppingCart,
    Package,
    Clock,
    BarChart3,
    Users,
    Settings,
    LogOut,
    Truck,
    Receipt,
    Warehouse,
    Menu,
    X,
    Store as StoreIcon,
} from 'lucide-react';
import { usePosEcho } from '@/hooks/use-pos-echo';
import { Auth } from '@/types/auth';

interface PosLayoutProps {
    children: React.ReactNode;
    title?: string;
}

interface PageProps {
    auth?: Auth;
    [key: string]: any;
}

export function PosLayout({ children, title = 'POS Nova' }: PosLayoutProps) {
    const { auth } = usePage<PageProps>().props;
    const user: any = auth?.user;
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [currentTime, setCurrentTime] = useState<string>('');

    usePosEcho(user?.store_id ? Number(user.store_id) : 1);

    useEffect(() => {
        const updateTime = () => {
            const now = new Date();
            setCurrentTime(
                now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                })
            );
        };
        updateTime();
        const timer = setInterval(updateTime, 1000);
        return () => clearInterval(timer);
    }, []);

    const navItems = [
        { label: 'POS Kasir', href: '/pos', icon: ShoppingCart },
        { label: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
        { label: 'Shift Kasir', href: '/shifts', icon: Clock },
        { label: 'Katalog Produk', href: '/products', icon: Package },
        { label: 'Stok Opname', href: '/stock', icon: Warehouse },
        { label: 'Purchase Orders', href: '/purchase-orders', icon: Truck },
        { label: 'Pelanggan', href: '/customers', icon: Users },
        { label: 'Pengeluaran', href: '/expenses', icon: Receipt },
        { label: 'Laporan Sales', href: '/reports/sales', icon: BarChart3 },
    ];

    if (Array.isArray(user?.roles) && user.roles.some((r: any) => r.name === 'Owner' || r.name === 'Manager')) {
        navItems.push(
            { label: 'Manajemen Staf', href: '/users', icon: Users },
            { label: 'Pengaturan Toko', href: '/store-settings', icon: Settings }
        );
    }

    const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';

    return (
        <div className="min-h-screen bg-background text-foreground font-sans flex flex-col">
            {/* Top Navigation Bar */}
            <header className="bg-card border-b border-border sticky top-0 z-40 px-4 py-2.5 flex items-center justify-between shadow-sm">
                <div className="flex items-center gap-4">
                    <button
                        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                        className="lg:hidden text-muted-foreground hover:text-foreground p-1 rounded-lg focus:outline-none"
                    >
                        {mobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
                    </button>

                    <Link href="/pos" className="flex items-center gap-2 font-bold text-xl tracking-tight text-foreground">
                        <div className="w-8 h-8 rounded-lg bg-primary text-primary-foreground flex items-center justify-center shadow-sm">
                            <StoreIcon size={18} />
                        </div>
                        <span>POS<span className="text-primary">Nova</span></span>
                    </Link>

                    {/* Quick Nav Links (Desktop) */}
                    <nav className="hidden lg:flex items-center gap-1 ml-4">
                        {navItems.slice(0, 6).map((item) => {
                            const Icon = item.icon;
                            const isActive = currentPath.startsWith(item.href);
                            return (
                                <Link
                                    key={item.label}
                                    href={item.href}
                                    className={`flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${
                                        isActive
                                            ? 'bg-primary/10 text-primary border border-primary/20 font-bold'
                                            : 'text-muted-foreground hover:text-foreground hover:bg-muted'
                                    }`}
                                >
                                    <Icon size={15} />
                                    <span>{item.label}</span>
                                </Link>
                            );
                        })}
                    </nav>
                </div>

                {/* User Profile & Clock */}
                <div className="flex items-center gap-4">
                    <div className="hidden sm:flex items-center gap-2 text-xs font-mono text-muted-foreground bg-muted/60 px-2.5 py-1 rounded-md border border-border">
                        <Clock size={14} className="text-primary" />
                        <span>{currentTime}</span>
                    </div>

                    <div className="flex items-center gap-3 pl-3 border-l border-border">
                        <div className="text-right hidden sm:block">
                            <div className="text-xs font-bold text-foreground">{user?.name}</div>
                            <div className="text-[10px] text-primary font-mono">
                                {Array.isArray(user?.roles) ? (user.roles[0]?.name ?? 'Kasir') : 'Kasir'}
                            </div>
                        </div>

                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            className="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition-colors"
                            title="Keluar"
                        >
                            <LogOut size={18} />
                        </Link>
                    </div>
                </div>
            </header>

            {/* Mobile Drawer Menu */}
            {mobileMenuOpen && (
                <div className="lg:hidden bg-card border-b border-border px-4 py-3 space-y-1">
                    {navItems.map((item) => {
                        const Icon = item.icon;
                        return (
                            <Link
                                key={item.label}
                                href={item.href}
                                onClick={() => setMobileMenuOpen(false)}
                                className="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted"
                            >
                                <Icon size={18} className="text-primary" />
                                <span>{item.label}</span>
                            </Link>
                        );
                    })}
                </div>
            )}

            {/* Main Content Viewport */}
            <main className="flex-1 flex flex-col overflow-hidden">
                {children}
            </main>
        </div>
    );
}
