/** Gestion partagée des apprenants : modale CRUD, recherche, tri et export CSV. */
(() => {
    'use strict';

    function init() {
        const fields = document.getElementById('createFields');
        const submit = document.getElementById('submitApprenantForm');
        const body = document.getElementById('learnersBody');
        const exportButton = document.getElementById('exportBtn');

        const fieldMap = {
            nom: 'nom',
            prenoms: 'prenoms',
            email: 'email',
            telephone: 'telephone',
            date_naissance: 'dateNaissance',
            sexe: 'sexe',
            nationalite: 'nationalite'
        };
        const configs = {
            create: { title: 'Nouvel apprenant', eyebrow: 'Dossier apprenant', description: 'Renseigne les informations du nouvel apprenant.', submit: 'Créer l’apprenant', method: 'POST', className: 'rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700', url: button => button.dataset.storeUrl },
            edit: { title: 'Modifier l’apprenant', eyebrow: 'Dossier apprenant', description: 'Mets à jour les informations de l’apprenant.', submit: 'Enregistrer les modifications', method: 'PUT', className: 'rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700', url: button => button.dataset.updateUrl },
            archive: { title: 'Archiver un apprenant', eyebrow: 'Confirmation', description: 'Vérifie le dossier avant de confirmer.', submit: 'Confirmer l’archivage', method: 'PATCH', className: 'rounded-xl bg-amber-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-700', url: button => button.dataset.archiveUrl }
        };



        fields?.querySelectorAll('input, select, textarea').forEach(field => {
            field.dataset.originalRequired = field.required ? 'true' : 'false';
        });

        document.querySelectorAll('.open-apprenant-modal').forEach(button => {
            button.addEventListener('click', () => openModal(button.dataset.modalMode, button));
        });
        modal?.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', closeModal));
        modal?.addEventListener('click', event => { if (event.target === modal) closeModal(); });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeModal();
        });


        exportButton?.addEventListener('click', () => {
            if (!body) return;
            const rows = [...body.querySelectorAll('.learner-row:not([hidden])')];
            const cell = value => `"${String(value).replaceAll('"', '""').replace(/[\\r\\n]+/g, ' ')}"`;
            const data = rows.map(row => [...row.querySelectorAll('td')].slice(0, 6).map(td => td.innerText.trim()));
            const csv = [['Matricule', 'Apprenant', 'Email', 'Téléphone', 'Date de naissance', 'Statut'], ...data]
                .map(row => row.map(cell).join(';')).join('\\r\\n');
            const url = URL.createObjectURL(new Blob(['\\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' }));
            const link = document.createElement('a');
            link.href = url;
            link.download = 'apprenants.csv';
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        });

        form?.addEventListener('submit', () => {
            if (form.checkValidity()) {
                submit.disabled = true;
                submit.classList.add('opacity-60', 'cursor-not-allowed');
                submit.textContent = 'Traitement...';
            }
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
    else init();
})();
