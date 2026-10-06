@verbatim
/* Layout: sidebar fixa | documentação do endpoint | painel escuro com exemplos de requisição e resposta */
:root {
    --bg: #f6f7f4; --surface: #ffffff; --fg: #17201b; --muted: #5d6b63; --line: #dfe4dc; --rule: #bcc5ba;
    --accent: #0b6b4f; --accent-soft: #e1f1ea; --on-accent: #ffffff;
    --ok: #0b7a43; --ok-bg: #e0f3e8; --warn: #9a5b00; --warn-bg: #fdf0d5; --err: #b3261e; --err-bg: #fbe4e1;
    --get: #1d5fa8; --post: #0b6b4f; --put: #9a5b00; --patch: #7a4aa8; --delete: #b3261e;
    --code-bg: #101714; --code-fg: #d6e0da; --code-line: #25302a; --code-muted: #7d8d84;
    --j-key: #8fd3b6; --j-str: #e9c98b; --j-num: #8cb8f0; --j-lit: #e39a92; --j-com: #6d7d74;
    --sans: "Public Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
    --mono: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
    color-scheme: light;
}
@media (prefers-color-scheme: dark) {
    :root:not([data-theme="light"]) {
        --bg: #0d1210; --surface: #141b17; --fg: #e4ebe6; --muted: #97a69d; --line: #26312b; --rule: #3c4b42;
        --accent: #4fc59b; --accent-soft: #17301f; --on-accent: #0d1210;
        --ok: #5fd18f; --ok-bg: #15301f; --warn: #e8b45b; --warn-bg: #33270f; --err: #f08a82; --err-bg: #361a18;
        --get: #7ab4f0; --post: #4fc59b; --put: #e8b45b; --patch: #c7a0ee; --delete: #f08a82;
        --code-bg: #060a08; --code-line: #1f2923;
        color-scheme: dark;
    }
}
:root[data-theme="dark"] {
    --bg: #0d1210; --surface: #141b17; --fg: #e4ebe6; --muted: #97a69d; --line: #26312b; --rule: #3c4b42;
    --accent: #4fc59b; --accent-soft: #17301f; --on-accent: #0d1210;
    --ok: #5fd18f; --ok-bg: #15301f; --warn: #e8b45b; --warn-bg: #33270f; --err: #f08a82; --err-bg: #361a18;
    --get: #7ab4f0; --post: #4fc59b; --put: #e8b45b; --patch: #c7a0ee; --delete: #f08a82;
    --code-bg: #060a08; --code-line: #1f2923;
    color-scheme: dark;
}

*, *::before, *::after { box-sizing: border-box; }
[hidden] { display: none !important; }
html { scroll-behavior: smooth; scroll-padding-top: 16px; }
body { margin: 0; background: var(--bg); color: var(--fg); font: 15px/1.6 var(--sans); -webkit-font-smoothing: antialiased; }
a { color: var(--accent); }
code { font-family: var(--mono); }
:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

.shell { display: grid; grid-template-columns: 270px minmax(0, 1fr); min-height: 100vh; }

/* Sidebar */
.sidebar { position: sticky; top: 0; align-self: start; height: 100vh; overflow: hidden; padding: 22px 14px 14px; background: var(--surface); border-right: 1px solid var(--line); display: flex; flex-direction: column; }
.brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 15px; line-height: 1.25; }
.brand-mark { flex: none; width: 28px; height: 28px; border-radius: 7px; background: var(--accent); color: var(--on-accent); display: grid; place-items: center; font-weight: 700; }
.brand-logo { max-width: 100%; max-height: 40px; }
.brand-meta { margin: 8px 0 14px; font-size: 12px; color: var(--muted); overflow-wrap: anywhere; }
.brand-meta code { font-size: 11.5px; }
.nav-search { width: 100%; padding: 8px 10px; border: 1px solid var(--line); border-radius: 8px; background: var(--bg); color: var(--fg); font: inherit; font-size: 13px; margin-bottom: 8px; }
.nav-list { scrollbar-width: thin; scrollbar-color: var(--line) transparent; flex: 1; min-height: 0; overflow-y: auto; padding-bottom: 12px; }
.nav-title-row { display: flex; align-items: center; justify-content: space-between; margin: 16px 0 4px; }
.nav-title { display: block; flex: 1; min-width: 0; margin: 0 8px; font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); text-decoration: none; }
.nav-chevron, .group-toggle { flex: none; display: grid; place-items: center; color: var(--muted); background: none; border: 0; border-radius: 6px; cursor: pointer; transition: transform .15s ease, color .15s ease; }
.nav-chevron { width: 24px; height: 24px; }
.nav-chevron:hover, .group-toggle:hover { color: var(--fg); background: var(--bg); }
.nav-group.is-collapsed .nav-chevron, .group.is-collapsed .group-toggle { transform: rotate(-90deg); }
.nav-group.is-collapsed .nav-item { display: none; }
.nav-list.is-searching .nav-group.is-collapsed .nav-item { display: flex; }
.nav-actions { display: flex; gap: 12px; padding: 0 8px 4px; }
.nav-action { font: inherit; font-size: 12px; color: var(--muted); background: none; border: 0; padding: 2px 0; cursor: pointer; text-decoration: underline; text-underline-offset: 2px; }
.nav-action:hover { color: var(--accent); }
.nav-item { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 7px; color: var(--fg); text-decoration: none; font-size: 13.5px; }
.nav-item:hover { background: var(--bg); }
.nav-item.is-active { background: var(--accent-soft); font-weight: 600; }
.nav-title.is-active { color: var(--accent); }
.nav-label { min-width: 0; overflow-wrap: anywhere; }
.m { flex: none; width: 36px; font: 500 10px var(--mono); }
.m-get { color: var(--get); } .m-post { color: var(--post); } .m-put { color: var(--put); } .m-patch { color: var(--patch); } .m-delete, .m-del { color: var(--delete); }
.nav-foot { flex: none; border-top: 1px solid var(--line); padding-top: 10px; font-size: 12px; color: var(--muted); }
.nav-foot ul { list-style: none; margin: 0 0 8px; padding: 0 4px; display: flex; flex-direction: column; gap: 2px; }
.nav-foot-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 0 4px; }
.theme-toggle { font: inherit; font-size: 12px; color: var(--fg); background: var(--bg); border: 1px solid var(--line); border-radius: 99px; padding: 4px 10px; cursor: pointer; }
.menu-button { display: none; }

/* Conteúdo geral (introdução, autenticação) */
.main { min-width: 0; }
.intro { max-width: 820px; padding: 40px clamp(16px, 4vw, 48px) 8px; }
.intro h1 { font-size: 30px; line-height: 1.15; letter-spacing: -.01em; margin: 24px 0 12px; text-wrap: balance; }
.intro h2 { font-size: 20px; margin: 24px 0 8px; }
.intro p, .intro li { color: var(--fg); }
.intro aside, .intro blockquote { margin: 16px 0; padding: 12px 14px; border-radius: 10px; background: var(--accent-soft); font-size: 14px; }
.intro blockquote { border-left: 3px solid var(--accent); border-radius: 0 10px 10px 0; }
.intro code, .lead code, .group-desc code, .param code, .desc code { font-size: .86em; background: var(--surface); border: 1px solid var(--line); padding: 1px 5px; border-radius: 4px; overflow-wrap: anywhere; }
.intro pre { overflow-x: auto; background: var(--code-bg); color: var(--code-fg); padding: 12px 14px; border-radius: 10px; }
.intro pre code { background: none; border: 0; padding: 0; }
.intro table { border-collapse: collapse; display: block; overflow-x: auto; }
.intro th, .intro td { border: 1px solid var(--line); padding: 6px 10px; }

/* Grupos: faixa de cabeçalho com régua de destaque */
.group { padding-bottom: 0; }
.group + .group { margin-top: 56px; }
.group-head { position: relative; background: var(--surface); border-top: 4px solid var(--accent); border-bottom: 1px solid var(--line); padding: 34px clamp(16px, 4vw, 48px) 28px; margin-top: 40px; }
.group-toggle { position: absolute; top: 30px; right: clamp(16px, 4vw, 48px); width: 36px; height: 36px; border: 1px solid var(--line); }
.group-head h1, .group-desc { padding-right: 52px; }
.group-eyebrow { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; font-size: 11px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--accent); }
.group-count { font-weight: 500; letter-spacing: .02em; text-transform: none; color: var(--muted); border: 1px solid var(--line); border-radius: 99px; padding: 1px 9px; }
.group-head h1 { margin: 0 0 6px; font-size: 30px; line-height: 1.15; letter-spacing: -.01em; text-wrap: balance; }
.group-desc { max-width: 70ch; color: var(--muted); }
.group-desc p { margin: 0; }
.subgroup-head { padding: 24px clamp(16px, 4vw, 48px) 0; }

/* Endpoint: documentação à esquerda, exemplos à direita */
.endpoint { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 480px); margin: 0; border-top: 3px solid var(--rule); }
.group-head + .endpoint, .subgroup-head + .endpoint { border-top: 0; }
.doc { min-width: 0; padding: 32px clamp(16px, 4vw, 48px) 40px; }
.doc h2 { margin: 0 0 12px; font-size: 24px; line-height: 1.2; letter-spacing: -.01em; text-wrap: balance; }
.route { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 10px; padding: 9px 12px; background: var(--surface); border: 1px solid var(--line); border-radius: 10px; min-width: 0; }
.verb { font: 600 12px var(--mono); padding: 3px 8px; border-radius: 5px; color: var(--on-accent); background: var(--get); }
.verb-post { background: var(--post); } .verb-put { background: var(--put); } .verb-patch { background: var(--patch); } .verb-delete { background: var(--delete); } .verb-head, .verb-options { background: var(--muted); }
.route-uri { font-weight: 500; font-size: 14px; overflow-wrap: anywhere; min-width: 0; }
.tags { display: flex; gap: 6px; flex-wrap: wrap; margin-left: auto; }
.tag { font-size: 12px; padding: 2px 9px; border-radius: 99px; border: 1px solid var(--line); color: var(--muted); }
.tag-auth { color: var(--accent); border-color: var(--accent); }
.tag-dep { color: var(--warn); border-color: var(--warn); }
.lead { color: var(--muted); max-width: 66ch; margin: 14px 0 6px; }
.lead p { margin: 0 0 8px; }

.section-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 26px; }
.section-head h3 { margin: 0; font-size: 12px; letter-spacing: .09em; text-transform: uppercase; color: var(--muted); font-weight: 600; }
.param-group { margin: 18px 0 8px; font-size: 13px; font-weight: 600; }
.params { border: 1px solid var(--line); border-radius: 10px; background: var(--surface); overflow: hidden; }
.param { display: grid; grid-template-columns: minmax(0, 200px) minmax(0, 1fr); gap: 6px 20px; padding: 12px 14px; border-top: 1px solid var(--line); }
.params > .param:first-child, .params > .nest:first-child > summary .param, .params > .param-note:first-child + .param { border-top: 0; }
.param-id { display: flex; flex-wrap: wrap; align-items: baseline; gap: 2px 8px; min-width: 0; align-content: flex-start; }
.pn { font-weight: 500; font-size: 13px; overflow-wrap: anywhere; }
.pt { font: 12px var(--mono); color: var(--muted); }
.req, .opt, .dep { font-size: 10px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; }
.req { color: var(--err); } .opt { color: var(--muted); } .dep { color: var(--warn); }
.param-body { min-width: 0; font-size: 14px; }
.param-desc p { margin: 0 0 4px; }
.param-meta { font-size: 12.5px; color: var(--muted); margin-top: 3px; display: flex; flex-wrap: wrap; gap: 4px 6px; align-items: baseline; }
.param-note { padding: 12px 14px; font-size: 14px; border-top: 1px solid var(--line); }
.param-note p { margin: 0; }
.nest > summary { cursor: pointer; list-style: none; }
.nest > summary::-webkit-details-marker { display: none; }
.nest > summary .param { border-top: 1px solid var(--line); }
.nest-body { margin-left: 14px; border-left: 2px solid var(--line); }
.try-input { width: 100%; max-width: 360px; margin-top: 8px; padding: 6px 9px; border: 1px solid var(--line); border-radius: 7px; background: var(--bg); color: var(--fg); font: 13px var(--mono); }
.try-radio { margin: 8px 12px 0 0; font-size: 13px; }

.try-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.btn { font: 600 12.5px var(--sans); padding: 6px 12px; border-radius: 8px; border: 1px solid var(--line); background: var(--surface); color: var(--fg); cursor: pointer; }
.btn:hover { border-color: var(--accent); }
.btn-send { background: var(--accent); border-color: var(--accent); color: var(--on-accent); }
.btn-cancel { color: var(--err); }

/* Painel de código */
.code { border-left: 1px solid var(--code-line); background: var(--code-bg); color: var(--code-fg); min-width: 0; color-scheme: dark; scrollbar-width: thin; scrollbar-color: #33413a transparent; }
.code * { scrollbar-width: thin; scrollbar-color: #33413a transparent; }
.code-inner { padding: 24px 18px 28px; display: flex; flex-direction: column; gap: 22px; }
.panel-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 10px; }
.panel-head b { font-size: 11px; letter-spacing: .09em; text-transform: uppercase; color: var(--code-muted); font-weight: 600; }
.tabs { display: flex; gap: 4px; flex-wrap: wrap; }
.res-tabs { margin-bottom: 10px; }
.tab { font: 500 12px var(--mono); color: var(--code-muted); background: none; border: 1px solid transparent; border-radius: 6px; padding: 4px 9px; cursor: pointer; }
.tab:hover { color: var(--code-fg); }
.tab.is-active, .tab[aria-selected="true"] { color: var(--code-fg); background: var(--code-line); border-color: #33413a; }
.dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: 6px; background: #5fd18f; }
.res-tab.is-4xx .dot { background: #e8b45b; } .res-tab.is-5xx .dot { background: #f08a82; }
.box { border: 1px solid var(--code-line); border-radius: 10px; overflow: hidden; }
.bar { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 12px; border-bottom: 1px solid var(--code-line); font: 12px var(--mono); color: var(--code-muted); min-width: 0; }
.bar > span { min-width: 0; overflow-wrap: anywhere; }
.stp { font-weight: 600; } .stp.is-2xx { color: #5fd18f; } .stp.is-4xx { color: #e8b45b; } .stp.is-5xx { color: #f08a82; }
.copy { flex: none; font: 500 11.5px var(--sans); background: none; color: var(--code-muted); border: 1px solid var(--code-line); border-radius: 6px; padding: 3px 9px; cursor: pointer; }
.copy:hover { color: var(--code-fg); }
.code pre { margin: 0; padding: 14px; font: 12.5px/1.65 var(--mono); overflow-x: auto; overflow-y: hidden; tab-size: 2; color: var(--code-fg); background: none; white-space: pre; }
.code pre code { font: inherit; background: none; border: 0; padding: 0; color: inherit; }
.res-headers { border-bottom: 1px solid var(--code-line); }
.res-headers summary { cursor: pointer; padding: 8px 12px; font-size: 12px; color: var(--code-muted); }
.desc { padding: 9px 12px; border-top: 1px solid var(--code-line); font-size: 12.5px; color: var(--code-muted); }
.tok-k { color: var(--j-key); } .tok-s { color: var(--j-str); } .tok-n { color: var(--j-num); } .tok-l { color: var(--j-lit); } .tok-c { color: var(--j-com); font-style: italic; } .tok-f { color: var(--j-key); }

/* Tablet e celular */
@media (max-width: 1180px) {
    .endpoint { grid-template-columns: minmax(0, 1fr); }
    .code-inner { padding-inline: clamp(16px, 4vw, 48px); }
}
@media (max-width: 860px) {
    .shell { grid-template-columns: minmax(0, 1fr); }
    .sidebar { position: fixed; z-index: 20; inset: 0 auto 0 0; width: min(300px, 86vw); height: 100%; transform: translateX(-102%); transition: transform .2s ease; box-shadow: 0 0 40px rgba(0, 0, 0, .3); }
    body.nav-open .sidebar { transform: none; }
    .menu-button { display: inline-flex; align-items: center; gap: 6px; position: sticky; top: 0; z-index: 10; width: 100%; padding: 10px 16px; border: 0; border-bottom: 1px solid var(--line); background: var(--surface); color: var(--fg); font: 600 14px var(--sans); cursor: pointer; }
    .param { grid-template-columns: minmax(0, 1fr); }
    .tags { margin-left: 0; }
}
@media (prefers-reduced-motion: reduce) {
    html { scroll-behavior: auto; }
    .sidebar { transition: none; }
    .nav-chevron, .group-toggle { transition: none; }
}
@endverbatim
