import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import { ShieldCheck, UserCheck, Zap } from 'lucide-react';
import InputError from '@/components/input-error';
import PasskeyVerify from '@/components/passkey-verify';
import PasswordInput from '@/components/password-input';
import TeamInvitationAlert from '@/components/team-invitation-alert';
import TextLink from '@/components/text-link';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import type { TeamInvitationContext } from '@/types';

type Props = {
    status?: string;
    canResetPassword: boolean;
    teamInvitation?: TeamInvitationContext | null;
};

export default function Login({
    status,
    canResetPassword,
    teamInvitation,
}: Props) {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');

    const demoAccounts = [
        { label: 'Owner', email: 'owner@posnova.test' },
        { label: 'Manager', email: 'manager@posnova.test' },
        { label: 'Kasir', email: 'kasir@posnova.test' },
        { label: 'Staff Gudang', email: 'gudang@posnova.test' },
    ];

    const fillDemoAccount = (demoEmail: string) => {
        setEmail(demoEmail);
        setPassword('password');
    };

    return (
        <>
            <Head title="Masuk Akun - POS Nova" />

            {teamInvitation && (
                <TeamInvitationAlert
                    invitation={teamInvitation}
                    action="Log in"
                />
            )}

            <PasskeyVerify />

            {/* Quick Demo Fill Selector */}
            <div className="space-y-2.5 p-3.5 rounded-xl bg-muted/60 border border-border">
                <div className="flex items-center justify-between text-xs font-semibold text-muted-foreground">
                    <span className="inline-flex items-center gap-1.5 text-primary font-bold">
                        <Zap className="w-3.5 h-3.5" /> Pilih Akun Demo Seeder:
                    </span>
                    <span className="text-[10px] text-muted-foreground font-mono">pass: password</span>
                </div>
                <div className="grid grid-cols-2 gap-1.5 sm:grid-cols-4">
                    {demoAccounts.map((acc) => (
                        <button
                            key={acc.email}
                            type="button"
                            onClick={() => fillDemoAccount(acc.email)}
                            className="px-2 py-1.5 rounded-lg text-xs font-medium border border-border bg-background hover:bg-accent hover:text-accent-foreground transition-all text-center flex items-center justify-center gap-1 text-foreground"
                        >
                            <UserCheck className="w-3 h-3 text-primary" /> {acc.label}
                        </button>
                    ))}
                </div>
            </div>

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-5 mt-1"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="email" className="text-foreground">Email address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder="owner@posnova.test"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password" className="text-foreground">Password</Label>
                                    {canResetPassword && (
                                        <TextLink
                                            href={request()}
                                            className="text-xs text-primary hover:underline"
                                            tabIndex={5}
                                        >
                                            Lupa Password?
                                        </TextLink>
                                    )}
                                </div>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    value={password}
                                    onChange={(e) => setPassword(e.target.value)}
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    placeholder="Masukkan password"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center space-x-2 pt-1">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    tabIndex={3}
                                />
                                <Label htmlFor="remember" className="text-xs text-muted-foreground font-normal cursor-pointer">
                                    Ingat saya di perangkat ini
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 w-full font-semibold py-2.5 shadow-sm"
                                tabIndex={4}
                                disabled={processing}
                                data-test="login-button"
                            >
                                {processing && <Spinner className="mr-2" />}
                                Masuk ke Kasir / Admin
                            </Button>
                        </div>

                        <div className="flex items-center justify-center gap-2 text-center text-xs text-muted-foreground pt-2 border-t border-border">
                            <ShieldCheck className="w-3.5 h-3.5 text-primary" />
                            <span>Sistem Kasir Aman POS Nova</span>
                        </div>
                    </>
                )}
            </Form>

            {status && (
                <div className="mt-4 text-center text-sm font-medium text-emerald-600 p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                    {status}
                </div>
            )}
        </>
    );
}

Login.layout = {
    title: 'Masuk ke POS Nova',
    description: 'Pilih role atau masukkan email & password Anda untuk melanjutkan',
};
