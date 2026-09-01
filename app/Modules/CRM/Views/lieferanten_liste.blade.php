@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">📦 Lieferanten-Kartei</h1>
            <p class="text-muted small mb-0">Register für Weinbau-Ausstattung, Kellerwirtschaft und Dienstleister.</p>
        </div>
        <button class="btn btn-dark btn-sm font-mono shadow-sm" data-bs-toggle="modal" data-bs-target="#partnerModal">
            ➕ Lieferant anlegen
        </button>
    </div>

    <!-- Lieferanten-Tabelle -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0 bg-white table-hover">
                <thead class="bg-light text-muted font-mono uppercase text-xs">
                    <tr>
                        <th class="ps-4">Lief-Nr.</th>
                        <th>Unternehmen</th>
                        <th>Ansprechpartner</th>
                        <th>USt-IdNr.</th>
                        <th>Telefon</th>
                        <th class="pe-4 text-end">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($lieferanten as $lieferant)
                        <tr>
                            <td class="ps-4 font-mono fw-bold text-slate-600">L-{{ $lieferant->lieferantennummer }}</td>
                            <td class="fw-bold text-gray-900">{{ $lieferant->firma ?? 'Einzelfirma' }}</td>
                            <td class="text-muted">{{ $lieferant->nachname }}, {{ $lieferant->vorname }}</td>
                            <td class="font-mono text-xs text-muted">{{ $lieferant->ust_id ?? '-' }}</td>
                            <td class="text-muted">{{ $lieferant->telefon ?? '-' }}</td>
                            <td class="pe-4 text-end">
                                <a href="#" class="btn btn-light btn-xs font-mono">🔍 Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted font-mono">
                                ⚠️ Noch keine Betriebsmittel-Lieferanten im System hinterlegt.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('crm::_partner_modal', ['istKunde' => false, 'istLieferant' => true])
@endsection
