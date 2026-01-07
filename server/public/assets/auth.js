function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

async function postJson(url, data) {
    const res = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrf(),
        },
        body: JSON.stringify(data),
    });

    const text = await res.text();
    let json = null;
    try {
        json = text ? JSON.parse(text) : null;
    } catch {
    }

    if (!res.ok) {
        const msg =
            json?.message ||
            json?.errors?.email?.[0] ||
            json?.errors?.code?.[0] ||
            'Erreur serveur';

        const err = new Error(msg);
        err.status = res.status;
        err.payload = json;
        throw err;
    }

    return json;
}
