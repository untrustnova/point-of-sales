import { useEffect } from 'react';
import { toast } from 'sonner';

interface EchoEventData {
    message?: string;
    product_name?: string;
    stock?: number;
    min_stock_alert?: number;
    invoice_number?: string;
    total_amount?: number;
    status?: string;
}

export function usePosEcho(storeId: number | undefined) {
    useEffect(() => {
        if (!storeId || typeof window === 'undefined' || !window.Echo) {
            return;
        }

        // 1. Channel Alert Stok Menipis
        const stockChannel = window.Echo.private(`store.${storeId}.stock`);
        stockChannel.listen('.LowStockAlertEvent', (data: EchoEventData) => {
            toast.warning('Peringatan Stok Menipis!', {
                description: `Stok ${data.product_name} tersisa ${data.stock} (Batas min: ${data.min_stock_alert})`,
                duration: 6000,
            });
        });

        // 2. Channel Penjualan Realtime
        const salesChannel = window.Echo.private(`store.${storeId}.sales`);
        salesChannel.listen('.NewSaleTransactionEvent', (data: EchoEventData) => {
            toast.success('Transaksi Penjualan Baru!', {
                description: `Invoice ${data.invoice_number} sebesar Rp ${Number(data.total_amount).toLocaleString('id-ID')}`,
                duration: 4000,
            });
        });

        // 3. Channel Status Shift Kasir
        const shiftChannel = window.Echo.private(`store.${storeId}.shifts`);
        shiftChannel.listen('.ShiftStatusChangedEvent', (data: EchoEventData) => {
            toast.info('Status Shift Kasir Berubah', {
                description: `Shift kasir saat ini ber-status: ${data.status?.toUpperCase()}`,
                duration: 4000,
            });
        });

        return () => {
            stockChannel.stopListening('.LowStockAlertEvent');
            salesChannel.stopListening('.NewSaleTransactionEvent');
            shiftChannel.stopListening('.ShiftStatusChangedEvent');
        };
    }, [storeId]);
}
