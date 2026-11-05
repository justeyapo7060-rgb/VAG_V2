/** Gestion partagée de la page Formations : modales CRUD, recherche, tri, export. */
(() => {
    'use strict';

    function init() {
        const modal = document.getElementById('formationModal');
        const form = document.getElementById('formationForm');
        const methodInput = document.getElementById('formMethod');
        const modeInput = document.getElementById('modalModeInput');
        const actionInput = document.getElementById('modalActionInput');
        const fields = document.getElementById('formationFields');
        const deleteFields = document.getElementById('deleteFields');
        const title = document.getElementById('modalTitle');
        const eyebrow = document.getElementById('modalEyebrow');
        const description = document.getElementById('modalDescription');
        const submit = document.getElementById('submitFormationForm');
        const deleteName = document.getElementById('deleteFormationName');
        const body = document.getElementById('formationsBody');
        const search = document.getElementById('searchInput');
        const resultCount = document.getElementById('resultCount');
        const sortButton = document.getElementById('sortBtn');
        const sortLabel = document.getElementById('sortLabel');
        const exportButton = document.getElementById('exportBtn');

        const fieldNames = ['intitule', 'description', 'prix', 'duree'];
        const configs = {
            create: { title: 'Nouvelle formation', eyebrow: 'Catalogue', description: 'Renseigne les informations de la formation.', submit: 'Créer la formation', method: 'POST', className: 'rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700', url: button => button.dataset.storeUrl },
            edit: { title: 'Modifier la formation', eyebrow: 'Mise à jour', description: 'Modifie les informations de cette formation.', submit: 'Enregistrer les modifications', method: 'PUT', className: 'rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700', url: button => button.dataset.updateUrl },
            delete: { title: 'Supprimer une formation', eyebrow: 'Confirmation', description: 'Vérifie la formation avant de confirmer.', submit: 'Confirmer la suppression', method: 'DELETE', className: 'rounded-xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-700', url: button => button.dataset.deleteUrl }
        };

        function openModal(mode, trigger, restoreOld = false) {
            if (!modal || !form) return;
            const config = configs[mode] || configs.create;
            const isDelete = mode === 'delete';
            const url = config.url(trigger) || '';

            form.action = url;
            methodInput.value = config.method;
            modeInput.value = mode;
            if (actionInput) actionInput.value = url;
            fields.classList.toggle('hidden', isDelete);
            deleteFields.classList.toggle('hidden', !isDelete);
            fields.querySelectorAll('input, textarea').forEach(input => {
                input.disabled = isDelete;
                input.required = !isDelete;
            });
            // Le jeton CSRF reste actif pour les trois actions.
            form.querySelector('input[name="_token"]').disabled = false;

            if (!restoreOld) {
                // Ouverture normale : on vide les erreurs/anciennes valeurs Laravel,
                // puis on préremplit (edit) ou on laisse vide (create).
                form.querySelectorAll('.field-error').forEach(el => el.remove());
                document.getElementById('formation-validation-errors')?.remove();
                fieldNames.forEach(name => {
                    const input = form.elements.namedItem(name);
                    if (input) input.value = mode === 'edit' ? (trigger.dataset[name] ?? '') : '';
                });
            }
            if (isDelete) fieldNames.forEach(name => { const input = form.elements.namedItem(name); if (input) input.disabled = true; });

            eyebrow.textContent = config.eyebrow;
            title.textContent = config.title;
            description.textContent = config.description;
            submit.textContent = config.submit;
            submit.className = config.className;
            deleteName.textContent = trigger.dataset.formationName || '';
            submit.disabled = false;
            modal.classList.remove('hidden');
            modal.classList.add('grid');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            if (!isDelete) form.elements.namedItem('intitule')?.focus();
            if (window.lucide) window.lucide.createIcons();
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('grid');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        }

        document.querySelectorAll('.open-formation-modal').forEach(button => {
            button.addEventListener('click', () => openModal(button.dataset.modalMode, button));
        });
        modal?.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', closeModal));
        modal?.addEventListener('click', event => { if (event.target === modal) closeModal(); });
        document.addEventListener('keydown', event => { if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeModal(); });

        // Recherche instantanée dans les lignes actuellement affichées.
        function filterRows() {
            if (!body || !search) return;
            const query = search.value.trim().toLocaleLowerCase('fr');
            let count = 0;
            body.querySelectorAll('.formation-row').forEach(row => {
                row.hidden = !row.innerText.toLocaleLowerCase('fr').includes(query);
                if (!row.hidden) count++;
            });
            if (resultCount) resultCount.textContent = `${count} résultat${count > 1 ? 's' : ''}`;
        }
        search?.addEventListener('input', filterRows);

        let ascending = true;
        sortButton?.addEventListener('click', () => {
            if (!body) return;
            const rows = [...body.querySelectorAll('.formation-row')];
            rows.sort((a, b) => ascending ? (a.dataset.name || '').localeCompare(b.dataset.name || '', 'fr') : (b.dataset.name || '').localeCompare(a.dataset.name || '', 'fr'));
            rows.forEach(row => body.appendChild(row));
            ascending = !ascending;
            if (sortLabel) sortLabel.textContent = `Trier ${ascending ? 'A–Z' : 'Z–A'}`;
            if (window.lucide) window.lucide.createIcons();
        });

        // Export CSV des lignes visibles.
        exportButton?.addEventListener('click', () => {
            if (!body) return;
            const rows = [...body.querySelectorAll('.formation-row:not([hidden])')];
            const cell = value => `"${String(value).replaceAll('"', '""').replace(/[\r\n]+/g, ' ')}"`;
            const data = rows.map(row => [...row.querySelectorAll('td')].slice(0, 5).map(td => td.innerText.trim()));
            const csv = [['Libellé', 'Description', 'Prix', 'Durée', 'Créée le'], ...data].map(row => row.map(cell).join(';')).join('\r\n');
            const url = URL.createObjectURL(new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' }));
            const link = document.createElement('a');
            link.href = url; link.download = 'formations.csv'; document.body.appendChild(link); link.click(); link.remove(); URL.revokeObjectURL(url);
        });

        // Empêche le double envoi après validation du formulaire.
        form?.addEventListener('submit', () => {
            if (form.checkValidity()) {
                submit.disabled = true;
                submit.classList.add('opacity-60', 'cursor-not-allowed');
                submit.textContent = 'Traitement...';
            }
        });

        // Après une erreur de validation Laravel, réouvrir la modale avec les anciennes valeurs.
        const hasErrors = document.getElementById('formation-validation-errors');
        if (hasErrors && modal) {
            const oldMode = hasErrors.dataset.mode || 'create';
            const oldAction = hasErrors.dataset.action || '';
            const trigger = { dataset: { storeUrl: oldAction || form.action, updateUrl: oldAction || form.action, deleteUrl: oldAction || form.action } };
            openModal(oldMode === 'edit' ? 'edit' : 'create', trigger, true);
        }

        if (window.lucide) window.lucide.createIcons();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
    else init();
})();
