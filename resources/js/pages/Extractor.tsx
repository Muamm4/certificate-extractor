import { Head, useForm } from '@inertiajs/react';
import React from 'react';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Icon } from '@/components/ui/icon';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Download, LoaderCircle, TriangleAlert } from 'lucide-react';
import { z } from 'zod';

interface Result {
    id: string;
    validTo: string;
    details: any;
}

type FormData = {
    certificate: File | null;
    password: string;
};

type ExtractorPageProps = {
    result?: Result;
    flash?: {
        error?: string;
    };
};

const schema = z.object({
    certificate: z.any().refine((file) => file instanceof File, { message: 'Arquivo obrigatório' }),
    password: z.string().min(1, 'Senha obrigatória'),
});

export default function Extractor({ result, flash }: ExtractorPageProps) {
    const { data, setData, post, processing, errors, setError } = useForm<FormData>({
        certificate: null as File | null,
        password: '',
    });

    function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();

        const check = schema.safeParse(data);

        if (!check.success) {
            const fieldErrors = check.error.format();
            
            if (fieldErrors.certificate?._errors?.[0]) {
                setError('certificate', fieldErrors.certificate._errors[0]);
            }
            
            if (fieldErrors.password?._errors?.[0]) {
                setError('password', fieldErrors.password._errors[0]);
            }

            return;
        }

        post('/upload', {
            forceFormData: true,
        });
    }

    return (
        <div className="flex min-h-screen w-full items-center justify-center bg-gray-100 p-4 dark:bg-zinc-950">
            <Head title="Certificate Extractor" />

            <Card className="w-full max-w-2xl">
                <CardHeader>
                    <CardTitle className="text-2xl">Certificate Extractor</CardTitle>
                    <CardDescription>Upload a .pfx or .p12 file to extract its contents.</CardDescription>
                </CardHeader>
                <CardContent>
                    {flash?.error && (
                        <Alert variant="destructive" className="mb-4">
                            <Icon iconNode={TriangleAlert} className="h-4 w-4" />
                            <AlertTitle>Error</AlertTitle>
                            <AlertDescription>{flash.error}</AlertDescription>
                        </Alert>
                    )}

                    <form id="upload-form" onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid w-full items-center gap-1.5">
                            <Label htmlFor="certificate">Certificate File</Label>
                            <Input
                                id="certificate"
                                type="file"
                                accept=".pfx,.p12"
                                onChange={(e) => setData('certificate', e.target.files ? e.target.files[0] : null)}
                                required
                            />
                            {errors.certificate && <p className="text-sm text-red-500 dark:text-red-400">{errors.certificate}</p>}
                        </div>
                        <div className="grid w-full items-center gap-1.5">
                            <Label htmlFor="password">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                required
                            />
                            {errors.password && <p className="text-sm text-red-500 dark:text-red-400">{errors.password}</p>}
                        </div>
                    </form>

                    {result && (
                        <div className="mt-6 border-t pt-6">
                            <h3 className="text-lg font-semibold">Extraction Successful</h3>
                            <div className="mt-4 space-y-3">
                                <div className="flex items-center justify-between rounded-md bg-gray-50 p-3 dark:bg-gray-800">
                                    <span className="font-semibold">Expires on:</span>
                                    <span className="font-mono text-sm">{result.validTo}</span>
                                </div>
                                <div className="flex flex-col items-center justify-center space-y-2 pt-2 sm:flex-row sm:space-y-0 sm:space-x-2">
                                    <Button asChild variant="outline" className="w-full">
                                        <a href={`/download/${result.id}/certificate`}>
                                            <Icon iconNode={Download} className="mr-2 h-4 w-4" />
                                            Certificate
                                        </a>
                                    </Button>
                                    <Button asChild variant="outline" className="w-full">
                                        <a href={`/download/${result.id}/public`}>
                                            <Icon iconNode={Download} className="mr-2 h-4 w-4" />
                                            Public Key
                                        </a>
                                    </Button>
                                    <Button asChild variant="outline" className="w-full">
                                        <a href={`/download/${result.id}/private`}>
                                            <Icon iconNode={Download} className="mr-2 h-4 w-4" />
                                            Private Key
                                        </a>
                                    </Button>
                                </div>
                                <div className="pt-2">
                                    <h4 className="font-semibold">Certificate Details:</h4>
                                    <pre className="mt-2 h-48 overflow-auto rounded-md bg-gray-950 p-2 text-xs text-white">
                                        {JSON.stringify(result.details, null, 2)}
                                    </pre>
                                </div>
                            </div>
                        </div>
                    )}
                </CardContent>
                <CardFooter>
                    <Button type="submit" form="upload-form" className="w-full" disabled={processing}>
                        {processing && <Icon iconNode={LoaderCircle} className="mr-2 h-4 w-4 animate-spin" />}
                        {processing ? 'Processing...' : 'Extract Certificate'}
                    </Button>
                </CardFooter>
            </Card>
        </div>
    );
}
