// Parser markup X-ray. Format: TAG{kata kata} dengan TAG salah satu dari
// P frasa depan, M penerang, S subject, V verb sejati, C connector,
// R relative/noun connector yang sekaligus subject (dihitung C dan S).
const TAG_RE = /([PMSVCR])\{([^}]*)\}/g;
const PUNCT_ONLY = /^[.,;:!?"'\u201d\u2019)]+$/;

export function parseMarked(marked) {
    const tokens = [];
    const counts = { S: 0, V: 0, C: 0, R: 0, M: 0, P: 0 };

    const pushWords = (text, tag) => {
        for (const piece of text.split(/\s+/).filter(Boolean)) {
            if (!tag && PUNCT_ONLY.test(piece) && tokens.length) {
                tokens[tokens.length - 1].trail += piece;
            } else {
                tokens.push({ i: tokens.length, w: piece, trail: '', tag });
            }
        }
    };

    let last = 0;
    let m;
    TAG_RE.lastIndex = 0;
    while ((m = TAG_RE.exec(marked)) !== null) {
        if (m.index > last) pushWords(marked.slice(last, m.index), '');
        counts[m[1]] += 1;
        pushWords(m[2], m[1]);
        last = m.index + m[0].length;
    }
    if (last < marked.length) pushWords(marked.slice(last), '');

    return { tokens, counts };
}

/** Tag yang diharapkan saat pengguna menandai kata (R diperlakukan sebagai connector). */
export function expectedTag(tok) {
    return tok.tag === 'R' ? 'C' : tok.tag;
}

export function verdictFor(counts, implicit = 0) {
    const subj = counts.S + counts.R;
    const verb = counts.V;
    const conn = counts.C + counts.R + implicit;
    const clauses = verb;
    let ok = false;
    let text;

    if (verb === 0) text = 'Kurang verb sejati. Tidak ada bagian yang membawa tense.';
    else if (subj === 0) text = 'Kurang subject. Frasa depan dan penerang tidak bisa jadi subject.';
    else if (subj > verb) text = `Subject berlebih: ${subj} subject untuk ${verb} verb sejati.`;
    else if (verb > subj) text = `Verb berlebih: ${verb} verb sejati tapi hanya ${subj} subject. Jadikan satu verb sebagai penerang, atau tambah connector dan subject.`;
    else if (verb > 1 && conn < verb - 1) text = `Connector hilang: ${verb} klausa butuh ${verb - 1} connector, baru ada ${conn}.`;
    else if (conn > verb - 1) text = `Connector berlebih: ${verb} klausa hanya butuh ${Math.max(verb - 1, 0)} connector.`;
    else {
        ok = true;
        text = 'Pas: setiap klausa punya tepat satu subject dan satu verb sejati.';
    }

    return { ok, text, clauses, subj, verb, conn };
}
