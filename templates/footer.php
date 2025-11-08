</main>

<footer class="mt-5 py-3 text-center text-muted border-top">
    <div class="container">
        <p class="mb-0">&copy; <?= date('Y'); ?> SMART-Green Campus · PA Akademi. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<script>
    (function(){
        const key = 'pa_akademi_theme';
        const toggle = document.getElementById('themeToggle');
        function applyTheme(t) {
            if (t === 'dark') document.body.classList.add('dark-mode');
            else document.body.classList.remove('dark-mode');
            // update toggle icon
            if (toggle) toggle.innerText = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
        }
        // Prefer saved setting, then system preference
        try {
            const saved = localStorage.getItem(key);
            if (saved) applyTheme(saved);
            else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) applyTheme('dark');
        } catch(e){}
        // Toggle
        if (toggle) toggle.addEventListener('click', function(){
            const isDark = document.body.classList.toggle('dark-mode');
            try { localStorage.setItem(key, isDark ? 'dark' : 'light'); } catch(e){}
            applyTheme(isDark ? 'dark' : 'light');
        });
    })();
</script>

<script>
    // Poll notifications every 12 seconds
    (function(){
        const url = '/pa_akademi/api/notifications.php';
        const badge = document.getElementById('notifCount');
        const menu = document.getElementById('notifMenu');
        async function fetchNotifs(){
            try{
                const r = await fetch(url, {credentials: 'same-origin'});
                if (!r.ok) return;
                const data = await r.json();
                if (data && data.count) {
                    badge.style.display = 'inline-block'; badge.innerText = data.count;
                } else { badge.style.display = 'none'; }
                // Build menu
                if (menu) {
                    menu.innerHTML = '';
                    if (!data.items || data.items.length === 0) {
                        menu.innerHTML = '<li class="dropdown-item small text-muted">Tidak ada notifikasi.</li>';
                    } else {
                                        data.items.slice(0,6).forEach(it => {
                                            if (it.type === 'logbook') {
                                                const li = document.createElement('li');
                                                // for dosen: items include nim+name; for mahasiswa logbook count only
                                                if (it.nim && it.name) {
                                                    li.innerHTML = `<a class="dropdown-item notif-logbook" href="detail_mahasiswa.php?nim=${encodeURIComponent(it.nim)}" data-nim="${encodeURIComponent(it.nim)}">${escapeHtml(it.name)} menambah ${it.count} catatan</a>`;
                                                } else {
                                                    li.innerHTML = `<a class="dropdown-item notif-logbook" href="#">Dosen menambah ${it.count} catatan</a>`;
                                                }
                                                menu.appendChild(li);
                                            } else if (it.type === 'krs') {
                                                const li = document.createElement('li');
                                                li.innerHTML = `<a class="dropdown-item notif-krs" href="#">${escapeHtml(it.message)}</a>`;
                                                menu.appendChild(li);
                                            }
                                        });
                    }
                }
            } catch(e) { /* ignore */ }
        }
        function escapeHtml(s){ return String(s).replace(/[&<>"]+/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c] || c)); }
        fetchNotifs(); setInterval(fetchNotifs, 12000);
        // delegate click handler after menu is populated
        function attachNotifClicks(){
            try{
                if (!menu) return;
                menu.querySelectorAll('.notif-krs').forEach(el => {
                    // remove existing handlers to avoid duplicates
                    el.replaceWith(el.cloneNode(true));
                });
                // re-select after clone
                menu.querySelectorAll('.notif-krs').forEach(el => {
                    el.addEventListener('click', async function(e){
                        e.preventDefault();
                        try{ await fetch('/pa_akademi/api/notifications_mark_read.php', {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'type=krs'}); }catch(e){}
                        if (badge) { badge.style.display='none'; badge.innerText='0'; }
                        if (menu) { menu.innerHTML = '<li class="dropdown-item small text-muted">Tidak ada notifikasi.</li>'; }
                    });
                });
                menu.querySelectorAll('.notif-logbook').forEach(el => {
                    el.replaceWith(el.cloneNode(true));
                });
                menu.querySelectorAll('.notif-logbook').forEach(el => {
                    el.addEventListener('click', async function(e){
                        const nim = this.dataset && this.dataset.nim ? decodeURIComponent(this.dataset.nim) : null;
                        try{
                            const body = nim ? ('type=logbook&nim=' + encodeURIComponent(nim)) : 'type=logbook';
                            await fetch('/pa_akademi/api/notifications_mark_read.php', {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: body});
                        }catch(e){}
                        if (this.getAttribute && this.getAttribute('href') === '#'){
                            if (badge) { badge.style.display='none'; badge.innerText='0'; }
                            if (menu) { menu.innerHTML = '<li class="dropdown-item small text-muted">Tidak ada notifikasi.</li>'; }
                        }
                    });
                });
            } catch(e) {
                // swallow errors so page doesn't break
                console && console.error && console.error('attachNotifClicks error', e);
            }
        }
        // ensure attach is invoked after each fetch
        const _origFetchNotifs = fetchNotifs;
        fetchNotifs = async function(){ try{ await _origFetchNotifs(); } catch(e){} attachNotifClicks(); };
    })();
</script>

    <!-- removed auto-mark-on-open to ensure notifications are marked read only when clicked -->