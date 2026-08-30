@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <!-- 🟢 Erfolgsmeldung nach dem Speichern -->
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card shadow">
                <div class="card-header bg-dark text-white d-flex align-items-center">
                    <h5 class="mb-0">🎨 vinicore System-Einstellungen</h5>
                </div>
                
                <div class="card-body">
                    <p class="text-muted small mb-4">
                        Definiere hier die visuellen Parameter deines ERP-Systems. Die Farbcodes werden in Echtzeit für alle Endgeräte und die Katasterkarte übernommen.
                    </p>

                    <form action="{{ route('einstellungen.speichern') }}" method="POST">
                        @csrf

                        <!-- 🏛️ 1. Farbe Eigentum -->
                        <div class="form-group row align-items-center mb-4">
                            <label for="farbe_eigentum" class="col-sm-4 col-form-label font-weight-bold">Eigentumsflächen:</label>
                            <div class="col-sm-3">
                                <input type="color" class="form-control form-control-color w-100" id="farbe_eigentum" name="farbe_eigentum" value="{{ $einstellungen['farbe_eigentum'] ?? '#059669' }}" title="Farbe für Eigentum wählen">
                            </div>
                            <div class="col-sm-5 text-muted small">Standard für Kern-Zonen (Eigenbesitz)</div>
                        </div>

                        <!-- 📜 2. Farbe Pacht -->
                        <div class="form-group row align-items-center mb-4">
                            <label for="farbe_gepachtet" class="col-sm-4 col-form-label font-weight-bold">Pachtflächen:</label>
                            <div class="col-sm-3">
                                <input type="color" class="form-control form-control-color w-100" id="farbe_gepachtet" name="farbe_gepachtet" value="{{ $einstellungen['farbe_gepachtet'] ?? '#2563eb' }}" title="Farbe für Pacht wählen">
                            </div>
                            <div class="col-sm-5 text-muted small">Für zeitlich befristete Verträge</div>
                        </div>

                        <!-- 🚜 3. Farbe Verpachtet -->
                        <div class="form-group row align-items-center mb-4">
                            <label for="farbe_verpachtet" class="col-sm-4 col-form-label font-weight-bold">Verpachtete Eigenflächen:</label>
                            <div class="col-sm-3">
                                <input type="color" class="form-control form-control-color w-100" id="farbe_verpachtet" name="farbe_verpachtet" value="{{ $einstellungen['farbe_verpachtet'] ?? '#64748b' }}" title="Farbe für Verpachtung wählen">
                            </div>
                            <div class="col-sm-5 text-muted small">Aktuell von Fremdbetrieben bewirtschaftet</div>
                        </div>

                        <hr class="my-4">

                        <!-- 💾 Buttons -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('kataster.karte') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Zurück zur Karte
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save"></i> Einstellungen versiegeln
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
