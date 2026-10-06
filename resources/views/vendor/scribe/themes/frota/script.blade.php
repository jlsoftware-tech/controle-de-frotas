@verbatim
(function () {
    var root = document.documentElement;
    var languages = [];
    try { languages = JSON.parse(document.body.getAttribute('data-languages') || '[]'); } catch (e) {}

    function load(key) { try { return localStorage.getItem(key); } catch (e) { return null; } }
    function save(key, value) { try { localStorage.setItem(key, value); } catch (e) {} }

    /* Realce de sintaxe leve (JSON, bash, javascript, php, python) */
    function escapeHtml(text) {
        return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
    function highlight(el, forcedLang) {
        var match = /language-([\w-]+)/.exec(el.className || '');
        var lang = forcedLang || (match ? match[1] : '');
        if (lang === 'http') { return; }
        var isJson = lang === 'json';
        var pattern = isJson
            ? /("(?:\\.|[^"\\])*")(\s*:)?|\b(true|false|null)\b|(-?\b\d+(?:\.\d+)?\b)/g
            : /(\/\/[^\n]*|#[^\n]*)|("(?:\\.|[^"\\\n])*"|'(?:\\.|[^'\\\n])*')|\b(true|false|null|NULL|TRUE|FALSE|None|True|False)\b|(-?\b\d+(?:\.\d+)?\b)|(\s--?[a-zA-Z][\w-]*)/g;
        var text = el.textContent;
        var out = '';
        var last = 0;
        var m;
        while ((m = pattern.exec(text)) !== null) {
            out += escapeHtml(text.slice(last, m.index));
            var token = escapeHtml(m[0]);
            if (isJson) {
                if (m[1]) { out += m[2] ? '<span class="tok-k">' + escapeHtml(m[1]) + '</span>' + escapeHtml(m[2]) : '<span class="tok-s">' + token + '</span>'; }
                else if (m[3]) { out += '<span class="tok-l">' + token + '</span>'; }
                else { out += '<span class="tok-n">' + token + '</span>'; }
            } else if (m[1]) { out += '<span class="tok-c">' + token + '</span>'; }
            else if (m[2]) { out += '<span class="tok-s">' + token + '</span>'; }
            else if (m[3]) { out += '<span class="tok-l">' + token + '</span>'; }
            else if (m[4]) { out += '<span class="tok-n">' + token + '</span>'; }
            else { out += '<span class="tok-f">' + token + '</span>'; }
            last = m.index + m[0].length;
            if (m[0].length === 0) { pattern.lastIndex++; }
        }
        out += escapeHtml(text.slice(last));
        el.innerHTML = out;
    }
    /* tryitout.js chama hljs.highlightElement nas respostas executadas */
    window.hljs = {
        highlightElement: function (el) { highlight(el, 'json'); },
        highlightAll: function () {}
    };
    document.querySelectorAll('pre code[class*="language-"]').forEach(function (el) { highlight(el); });

    /* Idioma dos exemplos de requisição (compartilhado entre endpoints) */
    function setLanguage(lang) {
        document.querySelectorAll('.example').forEach(function (el) { el.hidden = el.getAttribute('data-lang') !== lang; });
        document.querySelectorAll('.lang-tab').forEach(function (tab) {
            var on = tab.getAttribute('data-lang') === lang;
            tab.classList.toggle('is-active', on);
            tab.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        save('frota-lang', lang);
    }
    var savedLang = load('frota-lang');
    setLanguage(languages.indexOf(savedLang) !== -1 ? savedLang : languages[0]);
    document.querySelectorAll('.lang-tab').forEach(function (tab) {
        tab.addEventListener('click', function () { setLanguage(tab.getAttribute('data-lang')); });
    });

    /* Abas de resposta por endpoint */
    document.querySelectorAll('.res-tabs').forEach(function (tabs) {
        var panel = tabs.closest('.panel');
        tabs.addEventListener('click', function (event) {
            var tab = event.target.closest('.res-tab');
            if (!tab) { return; }
            var index = tab.getAttribute('data-index');
            tabs.querySelectorAll('.res-tab').forEach(function (other) {
                other.setAttribute('aria-selected', other === tab ? 'true' : 'false');
            });
            panel.querySelectorAll('.res-panel').forEach(function (item) {
                item.hidden = item.getAttribute('data-index') !== index;
            });
        });
    });

    /* Botões de copiar */
    document.addEventListener('click', function (event) {
        var button = event.target.closest('.copy');
        if (!button) { return; }
        var scope = button.closest('.box');
        var visible = Array.prototype.filter.call(scope.querySelectorAll('pre code'), function (code) {
            return !code.closest('[hidden]') && !code.closest('.res-headers');
        })[0];
        if (!visible) { return; }
        var done = function (label) {
            button.textContent = label;
            setTimeout(function () { button.textContent = 'Copiar'; }, 1500);
        };
        var fallback = function () {
            var range = document.createRange();
            range.selectNodeContents(visible);
            var selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            done('Selecionado');
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(visible.textContent).then(function () { done('Copiado'); }, fallback);
        } else {
            fallback();
        }
    });

    /* Tema claro/escuro */
    document.getElementById('theme-toggle').addEventListener('click', function () {
        var dark = getComputedStyle(root).colorScheme.indexOf('dark') !== -1;
        var next = dark ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        save('frota-theme', next);
    });

    /* Menu no celular */
    var menuButton = document.getElementById('menu-button');
    function toggleNav(open) {
        document.body.classList.toggle('nav-open', open);
        menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    menuButton.addEventListener('click', function () { toggleNav(!document.body.classList.contains('nav-open')); });
    document.getElementById('sidebar').addEventListener('click', function (event) {
        if (event.target.closest('a')) { toggleNav(false); }
    });

    /* Grupos recolhíveis (página e menu lateral sincronizados) */
    function setGroup(slug, open) {
        document.querySelectorAll('[data-group="' + slug + '"]').forEach(function (el) {
            el.classList.toggle('is-collapsed', !open);
        });
        document.querySelectorAll('[data-group="' + slug + '"] .group-toggle, [data-group="' + slug + '"] .nav-chevron').forEach(function (btn) {
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        var heading = document.querySelector('.group[data-group="' + slug + '"] h1');
        var toggle = document.querySelector('.group[data-group="' + slug + '"] .group-toggle');
        if (toggle && heading) { toggle.setAttribute('aria-label', (open ? 'Recolher grupo ' : 'Expandir grupo ') + heading.textContent); }
    }
    function isOpen(slug) {
        var group = document.querySelector('.group[data-group="' + slug + '"]');
        return group ? !group.classList.contains('is-collapsed') : true;
    }
    function allSlugs() {
        return Array.prototype.map.call(document.querySelectorAll('.group[data-group]'), function (el) { return el.getAttribute('data-group'); });
    }
    function revealTarget(id) {
        var target = id && document.getElementById(id);
        var group = target && target.closest('.group[data-group]');
        if (group && group.classList.contains('is-collapsed')) { setGroup(group.getAttribute('data-group'), true); }
        return target;
    }
    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('.group-toggle, .nav-chevron');
        if (toggle) {
            var holder = toggle.closest('[data-group]');
            var slug = holder.getAttribute('data-group');
            setGroup(slug, !isOpen(slug));
            return;
        }
        var link = event.target.closest('a[href^="#"]');
        if (link) { revealTarget(link.getAttribute('href').slice(1)); }
    });
    document.getElementById('expand-all').addEventListener('click', function () { allSlugs().forEach(function (slug) { setGroup(slug, true); }); });
    document.getElementById('collapse-all').addEventListener('click', function () { allSlugs().forEach(function (slug) { setGroup(slug, false); }); });
    if (location.hash.length > 1) {
        var initial = revealTarget(decodeURIComponent(location.hash.slice(1)));
        if (initial) { initial.scrollIntoView(); }
    }

    /* Busca na navegação */
    var searchInput = document.getElementById('nav-search');
    searchInput.addEventListener('input', function () {
        var term = searchInput.value.trim().toLowerCase();
        document.getElementById('nav-list').classList.toggle('is-searching', term !== '');
        document.querySelectorAll('#nav-list .nav-group').forEach(function (group) {
            var matches = 0;
            group.querySelectorAll('.nav-item').forEach(function (item) {
                var show = term === '' || item.textContent.toLowerCase().indexOf(term) !== -1;
                item.hidden = !show;
                if (show) { matches++; }
            });
            var title = group.querySelector('.nav-title');
            var titleMatches = term !== '' && title.textContent.toLowerCase().indexOf(term) !== -1;
            if (titleMatches) { group.querySelectorAll('.nav-item').forEach(function (item) { item.hidden = false; }); }
            group.hidden = term !== '' && matches === 0 && !titleMatches;
        });
    });

    /* Destaque do item atual na navegação */
    var links = {};
    document.querySelectorAll('#nav-list [data-spy]').forEach(function (link) { links[link.getAttribute('data-spy')] = link; });
    var targets = Array.prototype.filter.call(
        document.querySelectorAll('.main [id]'),
        function (el) { return links[el.id]; }
    );
    if ('IntersectionObserver' in window) {
        var current = null;
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) { return; }
                if (current) { current.classList.remove('is-active'); }
                current = links[entry.target.id];
                current.classList.add('is-active');
                var list = document.getElementById('nav-list');
                var listRect = list.getBoundingClientRect();
                var rect = current.getBoundingClientRect();
                if (rect.top < listRect.top + 40 || rect.bottom > listRect.bottom - 40) {
                    list.scrollTop += rect.top - listRect.top - listRect.height / 2;
                }
            });
        }, { rootMargin: '-10% 0px -80% 0px' });
        targets.forEach(function (el) { observer.observe(el); });
    }
})();
@endverbatim
