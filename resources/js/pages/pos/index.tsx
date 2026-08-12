import React, { useState, useEffect, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import { PosLayout } from '@/layouts/pos-layout';
import {
    Search,
    Plus,
    Minus,
    Trash2,
    CreditCard,
    QrCode,
    Banknote,
    Clock,
    PauseCircle,
    CheckCircle2,
    AlertTriangle,
    X,
    Printer,
    ShoppingCart,
} from 'lucide-react';
import { toast } from 'sonner';

interface ProductVariant {
    id: number;
    name: string;
    sku?: string;
    buy_price: number;
    sell_price: number;
    stock: number;
}

interface Product {
    id: number;
    name: string;
    sku: string;
    barcode: string;
    sell_price: number;
    buy_price: number;
    stock: number;
    category_id?: number;
    image_path?: string;
    category?: { id: number; name: string };
    unit?: { short_name: string };
    variants?: ProductVariant[];
}

interface Category {
    id: number;
    name: string;
}

interface Shift {
    id: number;
    opened_at: string;
    starting_cash: number;
    status: string;
}

interface CartItem {
    product_id: number;
    product_variant_id?: number;
    product_name: string;
    unit_name: string;
    sell_price: number;
    quantity: number;
    discount_amount: number;
}

interface PosPageProps {
    products: Product[];
    categories: Category[];
    customers: any[];
    active_shift: Shift | null;
    qr_code_svg?: string;
}

export default function PosPage({
    products = [],
    categories = [],
    customers = [],
    active_shift = null,
    qr_code_svg,
}: PosPageProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedCategory, setSelectedCategory] = useState<number | 'all'>('all');
    const [cart, setCart] = useState<CartItem[]>([]);
    const [selectedCustomer, setSelectedCustomer] = useState<number | ''>('');
    const [orderDiscount, setOrderDiscount] = useState<number>(0);
    const [taxPercentage, setTaxPercentage] = useState<number>(0);
    
    // Modal states
    const [paymentModalOpen, setPaymentModalOpen] = useState(false);
    const [paymentMethod, setPaymentMethod] = useState<'cash' | 'card' | 'qris' | 'debt'>('cash');
    const [paidAmountInput, setPaidAmountInput] = useState<string>('');
    const [holdDrawerOpen, setHoldDrawerOpen] = useState(false);
    const [parkedOrders, setParkedOrders] = useState<{ id: string; time: string; cart: CartItem[] }[]>([]);
    const [lastCompletedTransaction, setLastCompletedTransaction] = useState<any>(null);

    // Keyboard Shortcuts Listener
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'F2') {
                e.preventDefault();
                document.getElementById('pos-search-input')?.focus();
            } else if (e.key === 'F8' && cart.length > 0) {
                e.preventDefault();
                setPaymentModalOpen(true);
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [cart]);

    // Filter Products
    const filteredProducts = useMemo(() => {
        return products.filter((p) => {
            const matchesCategory = selectedCategory === 'all' || p.category_id === selectedCategory;
            const q = searchQuery.toLowerCase();
            const matchesSearch =
                !searchQuery ||
                p.name.toLowerCase().includes(q) ||
                p.sku.toLowerCase().includes(q) ||
                (p.barcode && p.barcode.toLowerCase().includes(q));
            return matchesCategory && matchesSearch;
        });
    }, [products, selectedCategory, searchQuery]);

    // Cart Calculations
    const subtotal = useMemo(() => {
        return cart.reduce((acc, item) => acc + item.sell_price * item.quantity - item.discount_amount, 0);
    }, [cart]);

    const taxAmount = useMemo(() => {
        return Math.round((subtotal - orderDiscount) * (taxPercentage / 100));
    }, [subtotal, orderDiscount, taxPercentage]);

    const totalAmount = useMemo(() => {
        return Math.max(0, subtotal - orderDiscount + taxAmount);
    }, [subtotal, orderDiscount, taxAmount]);

    const paidAmount = parseFloat(paidAmountInput) || 0;
    const changeAmount = Math.max(0, paidAmount - totalAmount);

    // Cart Actions
    const addToCart = (product: Product) => {
        if (product.stock <= 0) {
            toast.error('Stok produk habis!');
            return;
        }

        setCart((prev) => {
            const existingIndex = prev.findIndex((item) => item.product_id === product.id);
            if (existingIndex > -1) {
                const updated = [...prev];
                const newQty = updated[existingIndex].quantity + 1;
                if (newQty > product.stock) {
                    toast.warning('Jumlah melebihi stok yang tersedia');
                    return prev;
                }
                updated[existingIndex].quantity = newQty;
                return updated;
            }
            return [
                ...prev,
                {
                    product_id: product.id,
                    product_name: product.name,
                    unit_name: product.unit?.short_name || 'pcs',
                    sell_price: product.sell_price,
                    quantity: 1,
                    discount_amount: 0,
                },
            ];
        });
    };

    const updateQuantity = (index: number, delta: number) => {
        setCart((prev) => {
            const updated = [...prev];
            const newQty = updated[index].quantity + delta;
            if (newQty <= 0) {
                return updated.filter((_, i) => i !== index);
            }
            const prod = products.find((p) => p.id === updated[index].product_id);
            if (prod && newQty > prod.stock) {
                toast.warning('Stok tidak mencukupi');
                return prev;
            }
            updated[index].quantity = newQty;
            return updated;
        });
    };

    const removeFromCart = (index: number) => {
        setCart((prev) => prev.filter((_, i) => i !== index));
    };

    const handleHoldOrder = () => {
        if (cart.length === 0) return;
        const newParked = {
            id: `HOLD-${Date.now().toString().slice(-4)}`,
            time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
            cart: [...cart],
        };
        setParkedOrders((prev) => [newParked, ...prev]);
        setCart([]);
        toast.info('Transaksi berhasil ditahan (Parked)');
    };

    const handleRestoreOrder = (id: string) => {
        const target = parkedOrders.find((p) => p.id === id);
        if (target) {
            setCart(target.cart);
            setParkedOrders((prev) => prev.filter((p) => p.id !== id));
            setHoldDrawerOpen(false);
            toast.success('Transaksi berhasil dipulihkan');
        }
    };

    const handleCheckout = () => {
        if (!active_shift) {
            toast.error('Silakan buka shift kasir terlebih dahulu!');
            return;
        }

        if (paymentMethod === 'cash' && paidAmount < totalAmount) {
            toast.error('Jumlah pembayaran tunai kurang dari total tagihan!');
            return;
        }

        router.post(
            '/pos/transactions',
            {
                store_id: 1,
                cashier_shift_id: active_shift.id,
                customer_id: selectedCustomer || null,
                payment_method: paymentMethod,
                discount_amount: orderDiscount,
                tax_amount: taxAmount,
                paid_amount: paymentMethod === 'cash' ? paidAmount : totalAmount,
                items: cart.map((c) => ({
                    product_id: c.product_id,
                    product_variant_id: c.product_variant_id || null,
                    quantity: c.quantity,
                    sell_price: c.sell_price,
                    discount_amount: c.discount_amount,
                })),
            },
            {
                onSuccess: (page) => {
                    toast.success('Transaksi berhasil diproses!');
                    setPaymentModalOpen(false);
                    setCart([]);
                    setPaidAmountInput('');
                },
                onError: (errors) => {
                    toast.error(errors.message || 'Gagal memproses transaksi.');
                },
            }
        );
    };

    return (
        <PosLayout title="POS Kasir">
            <Head title="POS Kasir" />

            {/* Shift Guard Warning Banner */}
            {!active_shift && (
                <div className="bg-amber-500/15 border-b border-amber-500/30 px-4 py-2 flex items-center justify-between text-xs text-amber-200">
                    <div className="flex items-center gap-2">
                        <AlertTriangle size={16} className="text-amber-400" />
                        <span>Shift kasir belum aktif. Anda harus membuka shift sebelum dapat bertransaksi.</span>
                    </div>
                    <button
                        onClick={() => router.get('/shifts')}
                        className="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-3 py-1 rounded-md text-xs transition-colors"
                    >
                        Buka Shift Sekarang
                    </button>
                </div>
            )}

            <div className="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-0 overflow-hidden h-[calc(100vh-53px)]">
                {/* Left Panel: Catalog & Search (Col 7/12) */}
                <div className="lg:col-span-7 flex flex-col border-r border-border bg-muted/20 p-4 overflow-hidden">
                    {/* Search & Shortcuts */}
                    <div className="flex items-center gap-2 mb-3">
                        <div className="relative flex-1">
                            <Search className="absolute left-3 top-2.5 text-muted-foreground" size={18} />
                            <input
                                id="pos-search-input"
                                type="text"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                placeholder="Cari nama produk, SKU, scan barcode (F2)..."
                                className="w-full bg-background border border-border rounded-xl pl-10 pr-4 py-2 text-sm text-foreground placeholder-muted-foreground focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            />
                            {searchQuery && (
                                <button
                                    onClick={() => setSearchQuery('')}
                                    className="absolute right-3 top-2.5 text-muted-foreground hover:text-foreground"
                                >
                                    <X size={16} />
                                </button>
                            )}
                        </div>

                        <button
                            onClick={() => setHoldDrawerOpen(true)}
                            className="relative bg-card hover:bg-muted text-card-foreground px-3 py-2 rounded-xl text-xs font-semibold border border-border flex items-center gap-1.5 shadow-sm"
                        >
                            <PauseCircle size={16} className="text-amber-500" />
                            <span>Hold ({parkedOrders.length})</span>
                        </button>
                    </div>

                    {/* Category Filter Pills */}
                    <div className="flex items-center gap-1.5 overflow-x-auto pb-2 mb-3 scrollbar-none">
                        <button
                            onClick={() => setSelectedCategory('all')}
                            className={`px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap transition-colors ${
                                selectedCategory === 'all'
                                    ? 'bg-primary text-primary-foreground font-bold'
                                    : 'bg-card border border-border text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            Semua Produk ({products.length})
                        </button>
                        {categories.map((cat) => (
                            <button
                                key={cat.id}
                                onClick={() => setSelectedCategory(cat.id)}
                                className={`px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap transition-colors ${
                                    selectedCategory === cat.id
                                        ? 'bg-primary text-primary-foreground font-bold'
                                        : 'bg-card border border-border text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                {cat.name}
                            </button>
                        ))}
                    </div>

                    {/* Product Cards Grid */}
                    <div className="flex-1 overflow-y-auto pr-1 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 content-start">
                        {filteredProducts.map((product) => (
                            <button
                                key={product.id}
                                onClick={() => addToCart(product)}
                                className={`group text-left bg-card hover:bg-accent border rounded-xl p-3 flex flex-col justify-between transition-all duration-150 relative overflow-hidden shadow-sm ${
                                    product.stock <= 0
                                        ? 'opacity-40 border-border cursor-not-allowed'
                                        : 'border-border hover:border-primary/50 hover:shadow-md'
                                }`}
                            >
                                <div>
                                    <div className="flex items-center justify-between mb-1">
                                        <span className="text-[10px] font-mono text-muted-foreground">{product.sku}</span>
                                        <span
                                            className={`text-[9px] px-1.5 py-0.5 rounded font-mono font-bold ${
                                                product.stock <= 5
                                                    ? 'bg-destructive/20 text-destructive'
                                                    : 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400'
                                            }`}
                                        >
                                            Stok: {product.stock}
                                        </span>
                                    </div>
                                    <h4 className="text-xs font-semibold text-card-foreground line-clamp-2 mb-2 group-hover:text-primary transition-colors">
                                        {product.name}
                                    </h4>
                                </div>
                                <div className="text-sm font-bold text-primary font-mono">
                                    Rp {Number(product.sell_price).toLocaleString('id-ID')}
                                </div>
                            </button>
                        ))}
                    </div>
                </div>

                {/* Right Panel: Cart & Checkout (Col 5/12) */}
                <div className="lg:col-span-5 flex flex-col bg-card p-4 overflow-hidden border-l border-border">
                    <div className="flex items-center justify-between pb-3 border-b border-border">
                        <div className="flex items-center gap-2">
                            <ShoppingCart size={18} className="text-primary" />
                            <h3 className="text-sm font-bold text-card-foreground">Keranjang Belanja</h3>
                            <span className="text-xs font-mono bg-primary/10 text-primary px-2 py-0.5 rounded-full font-bold">
                                {cart.reduce((a, c) => a + c.quantity, 0)} item
                            </span>
                        </div>
                        {cart.length > 0 && (
                            <button
                                onClick={() => setCart([])}
                                className="text-xs text-destructive hover:underline flex items-center gap-1 font-medium"
                            >
                                <Trash2 size={13} /> Clear
                            </button>
                        )}
                    </div>

                    {/* Cart Item List */}
                    <div className="flex-1 overflow-y-auto my-3 pr-1 space-y-2">
                        {cart.length === 0 ? (
                            <div className="h-full flex flex-col items-center justify-center text-center text-muted-foreground py-12">
                                <ShoppingCart size={48} className="mb-2 opacity-30 stroke-1" />
                                <p className="text-xs">Keranjang masih kosong</p>
                                <p className="text-[10px] text-muted-foreground mt-1">Pilih produk di sebelah kiri untuk bertransaksi</p>
                            </div>
                        ) : (
                            cart.map((item, idx) => (
                                <div
                                    key={idx}
                                    className="bg-muted/50 border border-border rounded-xl p-2.5 flex items-center justify-between gap-2"
                                >
                                    <div className="flex-1 min-w-0">
                                        <div className="text-xs font-semibold text-foreground truncate">
                                            {item.product_name}
                                        </div>
                                        <div className="text-[11px] font-mono text-primary font-bold">
                                            Rp {Number(item.sell_price).toLocaleString('id-ID')}
                                        </div>
                                    </div>

                                    {/* Qty Controls */}
                                    <div className="flex items-center gap-1.5 bg-background p-1 rounded-lg border border-border">
                                        <button
                                            onClick={() => updateQuantity(idx, -1)}
                                            className="w-6 h-6 rounded bg-muted hover:bg-accent flex items-center justify-center text-foreground"
                                        >
                                            <Minus size={12} />
                                        </button>
                                        <span className="w-6 text-center text-xs font-bold font-mono text-foreground">
                                            {item.quantity}
                                        </span>
                                        <button
                                            onClick={() => updateQuantity(idx, 1)}
                                            className="w-6 h-6 rounded bg-muted hover:bg-accent flex items-center justify-center text-foreground"
                                        >
                                            <Plus size={12} />
                                        </button>
                                    </div>

                                    <div className="text-right min-w-[70px]">
                                        <div className="text-xs font-bold font-mono text-foreground">
                                            Rp {Number(item.sell_price * item.quantity).toLocaleString('id-ID')}
                                        </div>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>

                    {/* Cart Summary & Payment Trigger */}
                    <div className="pt-3 border-t border-border space-y-2">
                        <div className="space-y-1 text-xs">
                            <div className="flex justify-between text-muted-foreground">
                                <span>Subtotal</span>
                                <span className="font-mono text-foreground">Rp {subtotal.toLocaleString('id-ID')}</span>
                            </div>
                            <div className="flex justify-between text-muted-foreground">
                                <span>Diskon</span>
                                <span className="font-mono text-destructive">- Rp {orderDiscount.toLocaleString('id-ID')}</span>
                            </div>
                            <div className="flex justify-between text-base font-bold text-card-foreground pt-2 border-t border-border">
                                <span>TOTAL</span>
                                <span className="font-mono text-primary text-lg">
                                    Rp {totalAmount.toLocaleString('id-ID')}
                                </span>
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-2 pt-2">
                            <button
                                onClick={handleHoldOrder}
                                disabled={cart.length === 0}
                                className="w-full bg-secondary hover:bg-secondary/80 disabled:opacity-50 text-secondary-foreground font-semibold py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors border border-border"
                            >
                                <PauseCircle size={15} className="text-amber-500" /> Tahan (F4)
                            </button>
                            <button
                                onClick={() => setPaymentModalOpen(true)}
                                disabled={cart.length === 0 || !active_shift}
                                className="w-full bg-primary hover:bg-primary/90 disabled:opacity-50 text-primary-foreground font-bold py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-sm transition-colors"
                            >
                                <Banknote size={15} /> Bayar (F8)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Payment Modal */}
            {paymentModalOpen && (
                <div className="fixed inset-0 z-50 bg-background/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="bg-card border border-border rounded-2xl w-full max-w-lg overflow-hidden shadow-xl">
                        <div className="px-5 py-4 border-b border-border flex items-center justify-between">
                            <h3 className="text-base font-bold text-card-foreground flex items-center gap-2">
                                <Banknote size={20} className="text-primary" />
                                Pembayaran Kasir
                            </h3>
                            <button
                                onClick={() => setPaymentModalOpen(false)}
                                className="text-muted-foreground hover:text-foreground"
                            >
                                <X size={20} />
                            </button>
                        </div>

                        <div className="p-5 space-y-4">
                            {/* Amount Due Banner */}
                            <div className="bg-muted/50 border border-border rounded-xl p-4 text-center">
                                <div className="text-xs text-muted-foreground mb-1">Total Tagihan</div>
                                <div className="text-3xl font-extrabold font-mono text-primary">
                                    Rp {totalAmount.toLocaleString('id-ID')}
                                </div>
                            </div>

                            {/* Payment Method Selector */}
                            <div>
                                <label className="text-xs font-semibold text-slate-300 mb-2 block">
                                    Metode Pembayaran
                                </label>
                                <div className="grid grid-cols-4 gap-2">
                                    {[
                                        { id: 'cash', label: 'Tunai', icon: Banknote },
                                        { id: 'qris', label: 'QRIS', icon: QrCode },
                                        { id: 'card', label: 'Kartu', icon: CreditCard },
                                        { id: 'debt', label: 'Kredit', icon: Clock },
                                    ].map((m) => {
                                        const Icon = m.icon;
                                        return (
                                            <button
                                                key={m.id}
                                                onClick={() => setPaymentMethod(m.id as any)}
                                                className={`p-3 rounded-xl border flex flex-col items-center gap-1.5 transition-all ${
                                                    paymentMethod === m.id
                                                        ? 'bg-indigo-600/20 border-indigo-500 text-indigo-300'
                                                        : 'bg-slate-800/50 border-slate-700/60 text-slate-400 hover:text-white'
                                                }`}
                                            >
                                                <Icon size={20} />
                                                <span className="text-xs font-medium">{m.label}</span>
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Cash Input & Quick Presets */}
                            {paymentMethod === 'cash' && (
                                <div className="space-y-3">
                                    <div>
                                        <label className="text-xs font-semibold text-slate-300 mb-1 block">
                                            Jumlah Uang Tunai Diterima
                                        </label>
                                        <input
                                            type="number"
                                            value={paidAmountInput}
                                            onChange={(e) => setPaidAmountInput(e.target.value)}
                                            placeholder="Masukkan nominal tunai..."
                                            className="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-mono text-white focus:outline-none focus:border-indigo-500"
                                        />
                                    </div>

                                    {/* Quick Amount Pills */}
                                    <div className="flex flex-wrap gap-1.5">
                                        {[totalAmount, 10000, 20000, 50000, 100000].map((amt, idx) => (
                                            <button
                                                key={idx}
                                                onClick={() => setPaidAmountInput(amt.toString())}
                                                className="bg-slate-800 hover:bg-slate-700 border border-slate-700/80 px-2.5 py-1 rounded-lg text-xs font-mono text-slate-300"
                                            >
                                                {amt === totalAmount ? 'Uang Pas' : `Rp ${amt.toLocaleString('id-ID')}`}
                                            </button>
                                        ))}
                                    </div>

                                    {/* Change Amount */}
                                    <div className="flex justify-between items-center bg-slate-950 p-3 rounded-xl border border-slate-800">
                                        <span className="text-xs text-slate-400">Kembalian</span>
                                        <span className="text-lg font-bold font-mono text-emerald-400">
                                            Rp {changeAmount.toLocaleString('id-ID')}
                                        </span>
                                    </div>
                                </div>
                            )}

                            {/* QRIS SVG Display */}
                            {paymentMethod === 'qris' && (
                                <div className="bg-slate-950 p-4 rounded-xl border border-slate-800 flex flex-col items-center justify-center text-center">
                                    <div className="bg-white p-2 rounded-xl mb-2">
                                        {qr_code_svg ? (
                                            <div dangerouslySetInnerHTML={{ __html: qr_code_svg }} />
                                        ) : (
                                            <QrCode size={120} className="text-slate-900" />
                                        )}
                                    </div>
                                    <span className="text-xs text-slate-400">Scan QRIS di atas untuk membayar</span>
                                </div>
                            )}
                        </div>

                        <div className="px-5 py-4 bg-slate-950 border-t border-slate-800 flex items-center justify-end gap-2">
                            <button
                                onClick={() => setPaymentModalOpen(false)}
                                className="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white"
                            >
                                Batal
                            </button>
                            <button
                                onClick={handleCheckout}
                                className="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-2.5 rounded-xl text-xs shadow-lg shadow-indigo-600/30"
                            >
                                Konfirmasi & Selesai
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </PosLayout>
    );
}
