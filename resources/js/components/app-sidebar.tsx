import { Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    Building2,
    Clock,
    FileText,
    LayoutGrid,
    Package,
    Receipt,
    Settings,
    ShoppingCart,
    Store,
    Truck,
    UserCheck,
    Users,
    Warehouse,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { TeamSwitcher } from '@/components/team-switcher';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

export function AppSidebar() {
    const page = usePage<any>();
    const user = page.props.auth?.user;
    const dashboardUrl = page.props.currentTeam
        ? dashboard(page.props.currentTeam.slug)
        : '/dashboard';

    const isOwnerOrManager = Array.isArray(user?.roles)
        ? user.roles.some((r: any) => r.name === 'Owner' || r.name === 'Manager')
        : false;

    // 1. Operasional Kasir
    const operationalNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboardUrl,
            icon: LayoutGrid,
        },
        {
            title: 'POS Kasir',
            href: '/pos',
            icon: ShoppingCart,
        },
        {
            title: 'Shift Kasir',
            href: '/shifts',
            icon: Clock,
        },
    ];

    // 2. Katalog & Stok
    const catalogNavItems: NavItem[] = [
        {
            title: 'Katalog Produk',
            href: '/products',
            icon: Package,
        },
        {
            title: 'Stok & Opname',
            href: '/stock',
            icon: Warehouse,
        },
        {
            title: 'Purchase Orders',
            href: '/purchase-orders',
            icon: Truck,
        },
        {
            title: 'Supplier',
            href: '/suppliers',
            icon: Building2,
        },
    ];

    // 3. CRM & Laporan
    const financeNavItems: NavItem[] = [
        {
            title: 'Pelanggan (CRM)',
            href: '/customers',
            icon: Users,
        },
        {
            title: 'Pengeluaran',
            href: '/expenses',
            icon: Receipt,
        },
        {
            title: 'Laporan Sales',
            href: '/reports/sales',
            icon: BarChart3,
        },
    ];

    // 4. Manajemen & Pengaturan (Owner & Manager)
    const adminNavItems: NavItem[] = isOwnerOrManager
        ? [
              {
                  title: 'Manajemen Staf',
                  href: '/users',
                  icon: UserCheck,
              },
              {
                  title: 'Pengaturan Toko',
                  href: '/store-settings',
                  icon: Settings,
              },
              {
                  title: 'Activity Logs',
                  href: '/activity-logs',
                  icon: FileText,
              },
          ]
        : [];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboardUrl} prefetch className="flex items-center gap-2">
                                <div className="w-8 h-8 rounded-lg bg-primary text-primary-foreground flex items-center justify-center font-bold">
                                    <Store size={18} />
                                </div>
                                <div className="ml-1 grid flex-1 text-left text-sm leading-tight">
                                    <span className="truncate font-extrabold text-foreground">POS Nova</span>
                                    <span className="truncate text-[10px] text-muted-foreground">Sistem Kasir Toko</span>
                                </div>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <TeamSwitcher />
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="space-y-1">
                <NavMain items={operationalNavItems} label="Operasional Kasir" />
                <NavMain items={catalogNavItems} label="Katalog & Inventori" />
                <NavMain items={financeNavItems} label="Laporan & CRM" />
                {adminNavItems.length > 0 && (
                    <NavMain items={adminNavItems} label="Manajemen Toko" />
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
