export async function postJson(url, body) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let res;
    try {
        res = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        });
    } catch {
        return { ok: false, status: 0, data: { message: 'Tidak ada koneksi. Coba lagi.' } };
    }
    let data = {};
    try {
        data = await res.json();
    } catch {
        /* respons bukan JSON */
    }
    if (res.status === 419) data.message = 'Sesi habis. Muat ulang halaman lalu coba lagi.';
    if (res.status === 401) data.message = 'Masuk dulu untuk memakai fitur ini.';
    if (res.status === 429) data.message = 'Terlalu cepat atau kuota harian habis. Tunggu sebentar.';
    if (res.status === 422 && data.errors) data.message = Object.values(data.errors).flat()[0];
    return { ok: res.ok, status: res.status, data };
}
