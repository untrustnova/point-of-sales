import { Head, Link } from '@inertiajs/react';
import { login } from '@/routes';

export default function Register() {
    return (
        <>
            <Head title="Pendaftaran Dinonaktifkan" />
            <div className="flex flex-col items-center justify-center text-center space-y-4 py-6">
                <h2 className="text-lg font-bold text-white">Registrasi Mandiri Dinonaktifkan</h2>
                <p className="text-xs text-slate-400 max-w-sm">
                    Pendaftaran akun baru POS Nova hanya dapat dilakukan oleh Owner atau Manager Toko.
                    Silakan hubungi administrator toko Anda untuk mendapatkan akun kasir / staf.
                </p>
                <Link
                    href={login()}
                    className="inline-block bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-2 rounded-xl text-xs shadow-lg shadow-indigo-600/30"
                >
                    Kembali ke Halaman Log In
                </Link>
            </div>
        </>
    );
}

Register.layout = {
    title: 'Pendaftaran Akun',
    description: 'Informasi Pendaftaran Akun Staf',
};
