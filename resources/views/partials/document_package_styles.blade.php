<style>
.bundle-picker {
    position: relative;
    margin-bottom: 1.25rem;
    padding: 1.1rem;
    border: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 18px;
    background:
        radial-gradient(120% 120% at 100% 0%, rgba(14, 165, 164, 0.12), transparent 45%),
        linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.96));
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
}

.bundle-picker__eyebrow {
    display: inline-flex;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #0f766e;
}

.bundle-picker__header h5 {
    margin: 0.35rem 0 0.15rem;
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
}

.bundle-picker__header p,
.bundle-picker__status span {
    margin: 0;
    color: #475569;
    font-size: 0.9rem;
}

.bundle-picker__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 14px;
    margin-top: 1rem;
}

.bundle-card {
    text-align: left;
    width: 100%;
    padding: 1rem;
    border: 1px solid rgba(148, 163, 184, 0.26);
    border-radius: 18px;
    background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.96));
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.bundle-card:hover {
    transform: translateY(-3px);
    border-color: rgba(37, 99, 235, 0.35);
    box-shadow: 0 18px 34px rgba(37, 99, 235, 0.14);
}

.bundle-card.is-selected {
    border-color: rgba(14, 165, 164, 0.48);
    box-shadow: 0 20px 42px rgba(14, 165, 164, 0.16);
}

.bundle-card.is-exact {
    background: linear-gradient(180deg, rgba(240,253,250,0.98), rgba(255,255,255,0.98));
}

.bundle-card.is-mismatch {
    border-color: rgba(245, 158, 11, 0.4);
    box-shadow: 0 18px 34px rgba(245, 158, 11, 0.14);
}

.bundle-card__badge,
.bundle-card__chips span,
.bundle-card__items span {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 6px 10px;
    font-size: 12px;
    font-weight: 700;
}

.bundle-card__badge { background: rgba(14, 165, 164, 0.1); color: #0f766e; }
.bundle-card__chips,
.bundle-card__items { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 0.8rem; }
.bundle-card__chips span,
.bundle-card__items span { background: rgba(15, 23, 42, 0.04); color: #334155; }
.bundle-card h6 { margin: 0.8rem 0 0.4rem; font-size: 1rem; font-weight: 800; color: #0f172a; }
.bundle-card p { margin: 0; color: #475569; font-size: 0.88rem; min-height: 38px; }
.bundle-card__price { display: flex; align-items: baseline; gap: 10px; margin-top: 0.95rem; }
.bundle-card__price strong { font-size: 1.05rem; font-weight: 800; color: #0f172a; }
.bundle-card__price span { font-size: 0.86rem; color: #94a3b8; text-decoration: line-through; }

.bundle-picker__status {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-top: 1rem;
    padding: 0.95rem 1rem;
    border-radius: 16px;
    background: rgba(255,255,255,0.84);
    border: 1px solid rgba(148, 163, 184, 0.2);
}

.bundle-picker__status.is-exact {
    border-color: rgba(14, 165, 164, 0.28);
    background: linear-gradient(180deg, rgba(240,253,250,0.96), rgba(255,255,255,0.92));
}

.bundle-picker__status.is-mismatch {
    border-color: rgba(245, 158, 11, 0.3);
    background: linear-gradient(180deg, rgba(255,251,235,0.96), rgba(255,255,255,0.92));
}

.bundle-picker__title {
    display: block;
    font-size: 0.96rem;
    font-weight: 800;
    color: #0f172a;
}

.bundle-picker__empty {
    margin-top: 1rem;
    padding: 1rem 1.1rem;
    border-radius: 16px;
    background: rgba(255,255,255,0.9);
    border: 1px dashed rgba(148, 163, 184, 0.5);
    color: #64748b;
}

.bundle-discount-locked {
    background: linear-gradient(180deg, rgba(240,253,250,0.92), rgba(255,255,255,0.98));
    border-color: rgba(14, 165, 164, 0.32) !important;
    color: #0f766e;
    font-weight: 700;
}

@media (max-width: 767px) {
    .bundle-picker__status {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
