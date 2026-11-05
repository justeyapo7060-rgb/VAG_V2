
@extends('base')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- En-tête de la formation --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row
                sm:items-center sm:justify-between">

        <div>
            <p class="text-sm font-medium text-blue-600">
                Gestion des formations
            </p>

            <h1 class="mt-1 text-2xl font-bold text-slate-800">
                {{ $formation->intitule }}
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Liste des apprenants inscrits à cette formation.
            </p>
        </div>

        <a href="{{ route('formation.index') }}"
           class="inline-flex items-center rounded-lg border
                  border-slate-300 px-4 py-2 text-sm font-medium
                  text-slate-700 hover:bg-slate-50">
            Retour aux formations
        </a>
    </div>

    {{-- Tableau des apprenants --}}
    <div class="overflow-hidden rounded-xl border border-slate-200
                bg-white shadow-sm">

        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="font-semibold text-slate-800">
                Apprenants
                <span class="ml-2 rounded-full bg-blue-100 px-2 py-1
                             text-xs text-blue-700">
                    {{ $apprenants->count() }}
                </span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs
                                   font-semibold uppercase text-slate-500">
                            #
                        </th>

                        <th class="px-6 py-3 text-left text-xs
                                   font-semibold uppercase text-slate-500">
                            Nom
                        </th>

                        <th class="px-6 py-3 text-left text-xs
                                   font-semibold uppercase text-slate-500">
                            Prénom
                        </th>

                        <th class="px-6 py-3 text-left text-xs
                                   font-semibold uppercase text-slate-500">
                            Email
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($apprenants as $index => $apprenant)
                        <tr class="hover:bg-slate-50">

                            <td class="whitespace-nowrap px-6 py-4
                                       text-sm text-slate-500">
                                {{ $index + 1 }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4
                                       text-sm font-medium text-slate-800">
                                {{ $apprenant->nom }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4
                                       text-sm text-slate-600">
                                {{ $apprenant->prenoms }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4
                                       text-sm text-slate-600">
                                {{ $apprenant->email }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4"
                                class="px-6 py-10 text-center
                                       text-sm text-slate-500">
                                Aucun apprenant n'est inscrit
                                à cette formation.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>
</div>
@endsection
