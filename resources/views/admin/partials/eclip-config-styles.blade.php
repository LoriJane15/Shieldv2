<style>
    .eclip-config {
        --config-border: #e5ebf3;
        --config-muted: #718096;
        --config-navy: #172b4d;
        --config-primary: #2f6fed;
    }

    .config-hero {
        align-items: center;
        background: linear-gradient(125deg, #173b74 0%, #2f6fed 70%, #5795ff 100%);
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(47, 111, 237, .16);
        color: #fff;
        display: flex;
        justify-content: space-between;
        overflow: hidden;
        padding: 1.5rem 1.65rem;
        position: relative;
    }

    .config-hero::after {
        background: rgba(255, 255, 255, .08);
        border-radius: 50%;
        content: '';
        height: 180px;
        position: absolute;
        right: -45px;
        top: -85px;
        width: 180px;
    }

    .config-hero-copy { max-width: 700px; position: relative; z-index: 1; }
    .config-eyebrow { font-size: .7rem; font-weight: 700; letter-spacing: .1em; opacity: .78; text-transform: uppercase; }
    .config-hero h2 { color: #fff; font-size: 1.45rem; font-weight: 700; }
    .config-hero p { font-size: .8rem; line-height: 1.55; opacity: .86; }
    .config-total { align-items: center; background: rgba(255, 255, 255, .15); border: 1px solid rgba(255, 255, 255, .28); border-radius: 12px; display: flex; gap: .75rem; min-width: 145px; padding: .75rem 1rem; position: relative; z-index: 1; }
    .config-total i { font-size: 1.4rem; }
    .config-total strong { display: block; font-size: 1.3rem; line-height: 1; }
    .config-total span { display: block; font-size: .68rem; margin-top: .25rem; opacity: .8; text-transform: uppercase; }

    .config-card { border: 1px solid var(--config-border); border-radius: 14px; box-shadow: 0 4px 16px rgba(23, 43, 77, .045); overflow: hidden; }
    .config-card .card-body { padding: 1.4rem; }
    .config-card-header { align-items: flex-start; border-bottom: 1px solid #edf1f7; display: flex; gap: .75rem; padding: 1.25rem 1.4rem; }
    .config-card-icon { align-items: center; background: #eaf1ff; border-radius: 9px; color: var(--config-primary); display: flex; flex: 0 0 38px; font-size: 1.05rem; height: 38px; justify-content: center; }
    .config-title { color: var(--config-navy); font-size: .98rem; font-weight: 700; margin: 0 0 .2rem; }
    .config-subtitle { color: #8492a6; font-size: .74rem; line-height: 1.45; margin: 0; }
    .config-card label { color: #45556c; font-size: .74rem; font-weight: 700; margin-bottom: .4rem; }
    .config-card .form-control { border-color: #dce4ee; border-radius: 8px; font-size: .8rem; min-height: 42px; }
    .config-card textarea.form-control { min-height: 92px; resize: vertical; }
    .config-card .form-control:focus { border-color: #84aaf4; box-shadow: 0 0 0 3px rgba(47, 111, 237, .1); }
    .field-hint { color: #94a3b8; display: block; font-size: .67rem; line-height: 1.4; margin-top: .35rem; }
    .required-mark { color: #d64555; }
    .config-check { align-items: flex-start; background: #f7f9fc; border: 1px solid #e8edf5; border-radius: 9px; display: flex; gap: .65rem; padding: .8rem; }
    .config-check input { margin-top: .2rem; }
    .config-check label { margin: 0; }
    .config-check small { color: #8492a6; display: block; font-size: .67rem; font-weight: 400; margin-top: .15rem; }
    .config-submit { border-radius: 8px; font-size: .78rem; font-weight: 600; min-height: 42px; }

    .config-table { margin: 0; }
    .config-table thead th { background: #f7f9fc; border: 0; color: #718096; font-size: .68rem; font-weight: 700; letter-spacing: .045em; padding: .8rem 1rem; text-transform: uppercase; white-space: nowrap; }
    .config-table tbody td { border-color: #edf1f7; color: #52616f; font-size: .76rem; padding: .9rem 1rem; vertical-align: middle; }
    .config-name { color: #263a59; display: block; font-size: .78rem; font-weight: 700; }
    .config-description { color: #8492a6; display: block; font-size: .68rem; line-height: 1.4; margin-top: .2rem; max-width: 360px; }
    .config-code { background: #f1f5f9; border-radius: 5px; color: #475569; display: inline-block; font-family: monospace; font-size: .69rem; font-weight: 700; padding: .25rem .45rem; }
    .config-badge { align-items: center; border-radius: 20px; display: inline-flex; font-size: .66rem; font-weight: 700; gap: .3rem; padding: .3rem .55rem; white-space: nowrap; }
    .config-badge::before { background: currentColor; border-radius: 50%; content: ''; height: 6px; width: 6px; }
    .config-badge-active { background: #e8f8f1; color: #16845e; }
    .config-badge-inactive { background: #f1f4f8; color: #718096; }
    .config-type { color: #64748b; font-size: .7rem; font-weight: 600; white-space: nowrap; }
    .config-action { border-radius: 7px; font-size: .69rem; font-weight: 600; min-width: 86px; padding: .4rem .65rem; }
    .config-empty { color: #8492a6; padding: 3rem 1rem; text-align: center; }
    .config-empty i { color: #c1ccda; display: block; font-size: 2rem; margin-bottom: .5rem; }
    .config-empty strong { color: #52616f; display: block; font-size: .8rem; margin-bottom: .2rem; }

    @media (max-width: 767px) {
        .config-hero { align-items: flex-start; flex-direction: column; gap: 1rem; padding: 1.2rem; }
        .config-hero h2 { font-size: 1.25rem; }
        .config-total { min-width: 0; width: 100%; }
        .config-table thead { display: none; }
        .config-table, .config-table tbody, .config-table tr, .config-table td { display: block; width: 100%; }
        .config-table tr { border-bottom: 1px solid var(--config-border); padding: .65rem 0; }
        .config-table tbody td { border: 0; padding: .3rem 1rem; }
        .config-table tbody td::before { color: #94a3b8; content: attr(data-label); display: block; font-size: .62rem; font-weight: 700; margin-bottom: .18rem; text-transform: uppercase; }
        .config-action { width: 100%; }
    }
</style>
