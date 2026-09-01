@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">🏪 Kunden-Kartei</h1>
            <p class="text-muted small mb-0">Zentrales Register für Privatkunden, Gastronomie und Fachhandel.</p>
        </div>
        <button class="btn btn-primary btn-sm font-mono shadow-sm" data-bs-toggle="modal" data-bs-target="#partnerModal">
            ➕ Neukunde anlegen
        </button>
    </div>

    <!-- Kunden-Tabelle -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0 bg-white table-hover">
                <thead class="bg-light text-muted font-mono uppercase text-xs">
                    <tr>
                        <th class="ps-4">Kd-Nr.</th>
                        <th>Name / Firma</th>
                        <th>Kategorie</th>
                        <th>Zahlungsziel</th>
                        <th>E-Mail</th>
                        <th class="pe-4 text-end">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($kunden as $kunde)
                        <tr>
                            <td class="ps-4 font-mono fw-bold text-emerald-600">K-{{ $kunde->kundennummer }}</td>
                            <td>
                                <div class="fw-bold text-gray-900">{{ $kunde->nachname }}, {{ $kunde->vorname }}</div>
                                @if($kunde->firma)
                                    <div class="text-muted small">{{ $kunde->firma }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge @if($kunde->kunden_kategorie === 'handel') bg-purple @elseif($kunde->kunden_kategorie === 'gastro') bg-info @else bg-secondary @endif bg-opacity-10 text-dark">
                                    {{ ucfirst($kunde->kunden_kategorie) }}
                                </span>
                            </td>
                            <td class="font-mono text-muted">{{ $kunde->standard_zahlungsziel_tage }} Tage</td>
                            <td class="text-muted">{{ $kunde->email ?? '-' }}</td>
                            <td class="pe-4 text-end">
                                <a href="#" class="btn btn-light btn-xs font-mono">🔍 Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted font-mono">
                                ⚠️ Noch keine Weinkunden im System registriert.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Der universelle Neuanlage-Modal-Dialog binden wir beim nächsten Schritt an -->
@include('crm::_partner_modal', ['istKunde' => true, 'istLieferant' => false])
@endsection
