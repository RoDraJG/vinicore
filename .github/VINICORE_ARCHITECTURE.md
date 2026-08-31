# VINICORE ERP ARCHITECTURE DESIGN (11 Modules)

## 1. MODULE DEPENDENCY DIAGRAM

```
┌─────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                       │
│          (Vue 3 SPA + Blade, Leaflet Maps)                  │
└──────────────────────┬──────────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────────┐
│                    VINICORE CORE                            │
│      (ACL, Config, Cache, Auth, Event Bus)                  │
│                        │                                     │
│    ┌───────┬──────┬────┼────┬───────┬──────┬──────┐         │
└────┼───────┼──────┼────┼────┼───────┼──────┼──────┼─────────┘
     │       │      │    │    │       │      │      │
     ▼       ▼      ▼    ▼    ▼       ▼      ▼      ▼
  ┌────┐ ┌────┐ ┌────┐┌───┐┌──────┐┌────┐┌────┐┌────┐
  │Cat │ │ACL │ │Fin │ │Era│├─Kel │ │Pers│ │Waren│ │Vert│
  │aster│ │    │ │anzen│  │ │ler- │ │onal│ │wirts│ │rieb│
  └────┘ └────┘ └────┘│   │ │schaft│ └────┘ │chaft│ └────┘
    △                  │   │ └─────┘         └────┘
    │                  │   │
    └──────────────────┤   │
                       │   │
                    ┌──┴───┴──────────────┐
                    │   COMPLIANCE       │
                    │ (Audit Trail)      │
                    └────────────────────┘

Dependencies:
- KATASTER (Cadastral): Base layer — defines Parzelle (parcels), workflows
- ACL: Cross-cutting — guards all modules
- CORE: Shared services — config, caching, enum values, logging
- FINANZEN: Depends on KATASTER (contract parties/parcels)
- SCHLAGKARTEI: Depends on KATASTER (field history per parcel)
- ERNTE: Depends on SCHLAGKARTEI + KATASTER (harvest tracking)
- KELLERWIRTSCHAFT: Depends on ERNTE (pressing intake → fermentation)
- PERSONAL: Depends on CORE (schedule, clock, ACL)
- WARENWIRTSCHAFT: Depends on KELLERWIRTSCHAFT + FINANZEN (inventory, costs)
- VERTRIEB: Depends on WARENWIRTSCHAFT + FINANZEN (stock → sales)
- COMPLIANCE: Cross-cutting audit layer (depends on all)
```

---

## 2. DATABASE TABLES BY MODULE

| Module | Table Name | Key Fields | Purpose |
|--------|-----------|-----------|---------|
| **KATASTER** | `parzellen` | `id`, `parzelle_uuid`, `polygon_vektoren` (GeoJSON), `gemeinde`, `flurstueck` | Cadastral land parcels |
| | `bewirtschaftungs_einheiten` | `id`, `betrieb_id`, `parzelle_id` | Grouped parcels for management |
| | `schlaege` | `id`, `parzelle_id`, `name`, `hangneigung` | Field sections with terrain data |
| | `parzelle_vertrag` | `id`, `parzelle_id`, `vertragable_*` (polymorphic) | Parcel-contract linkage |
| **ACL** | `vinicore_rollen` | `id`, `name`, `anzeige_name`, `betrieb_id` | Admin-defined roles |
| | `vinicore_berechtigungen` | `id`, `slug`, `modul`, `aktion` | Permission catalog |
| | `berechtigung_rolle` | `rolle_id`, `berechtigung_id` | Role-permission pivot |
| **FINANZEN** | `vinicore_vertraege` | `id`, `typ` (enum: lease, contract, employment) | Master contract registry |
| | `vertrag_positionen` | `id`, `vertrag_id`, `betrag`, `fällig_am` | Line items, installments |
| | `transaktionen` | `id`, `konto_id`, `betrag`, `datum`, `beschreibung` | Transaction ledger |
| | `konten` | `id`, `konto_nr`, `typ`, `betrieb_id` | Chart of accounts |
| **SCHLAGKARTEI** | `feld_massnahmen` | `id`, `schlag_id`, `datum`, `typ` (enum: düngung, bespritzung) | Fertilizer, pesticide log |
| | `massnahmen_details` | `id`, `massnahme_id`, `stoff_name`, `menge`, `einheit` | Substance details per treatment |
| | `feld_geräte` | `id`, `schlag_id`, `gerät_name`, `einsatz_datum` | Equipment usage per field |
| | `schädlings_monitoring` | `id`, `schlag_id`, `beobachtung_datum`, `befund` | Pest scout records |
| **ERNTE** | `ernte_kampagnen` | `id`, `betrieb_id`, `jahr`, `status` (enum: geplant, aktiv, abgeschlossen) | Harvest campaigns |
| | `lesegänge` | `id`, `kampagne_id`, `schlag_id`, `lesedatum`, `mostgewicht` (°Brix) | Individual harvest passes |
| | `lesetermine` | `id`, `lesegan_id`, `arbeiter_id`, `zustand` (reifegrad %) | Worker assignments per pass |
| | `ernte_ergebnis` | `id`, `lesegan_id`, `menge_kg`, `qualitätsnotiz` | Harvest outcome |
| **KELLERWIRTSCHAFT** | `gärfässer` | `id`, `betrieb_id`, `fass_nr`, `volumen_l`, `sorte` | Tank/barrel registry |
| | `gärprozesse` | `id`, `fass_id`, `lesegan_id`, `start_datum`, `status` (enum: gärig, trocken) | Fermentation logs |
| | `gär_messwerte` | `id`, `gärprozess_id`, `messdatum`, `temp_celsius`, `dichte`, `alkohol_%` | Fermentation parameters |
| | `kellerei_lagerbestand` | `id`, `artikel_id`, `standort`, `menge_l`, `kosten` | Cellar inventory by location |
| **PERSONAL** | `arbeitskräfte` | `id`, `benutzer_id`, `rolle_im_betrieb` (Saisonkraft/Dauerarbeit), `qr_code` | Worker profiles |
| | `zeiterfassung` | `id`, `arbeitskraft_id`, `eintrag_datetime`, `austrag_datetime`, `pausenminuten` | Clock in/out (QR) |
| | `aufgaben_zuordnung` | `id`, `arbeitskraft_id`, `aufgabe_id`, `zugewiesen_am`, `priorität` | Task assignment |
| | `aufgaben` | `id`, `titel`, `beschreibung`, `modul_kontext`, `fällig_am`, `zuständig_rolle` | Task master |
| **WARENWIRTSCHAFT** | `lagerbestände` | `id`, `artikel_id`, `lagerort_id`, `menge`, `mindestbestand` | Inventory stock |
| | `lager_bewegungen` | `id`, `lagerbestand_id`, `bewegung_typ`, `menge`, `grund`, `datum` | Stock movements (in/out/adj) |
| | `artikel_stamm` | `id`, `artikel_nr`, `name`, `kategorie`, `kosten_stelle_id` | Master article/product data |
| | `lagerorte` | `id`, `betrieb_id`, `ort_name`, `größe_l` | Storage location registry |
| **VERTRIEB** | `kundenkonten` | `id`, `betrieb_id`, `kundenname`, `kontaktperson`, `region` | Customer master |
| | `verkaufsaufträge` | `id`, `kundenkonto_id`, `bestelldatum`, `lieferdatum`, `status` | Sales orders |
| | `verkaufspositionen` | `id`, `auftrag_id`, `artikel_id`, `menge`, `preis_pro_einheit` | Order line items |
| | `verkauf_provisionen` | `id`, `auftrag_id`, `verkaufsleiter_id`, `provision_%`, `betrag` | Commission tracking |
| **COMPLIANCE** | `audit_log` | `id`, `benutzer_id`, `entität_typ`, `entität_id`, `aktion`, `änderungen`, `timestamp` | Immutable audit trail |
| | `dokumente_vorlagen` | `id`, `kategorie`, `titel`, `inhalt_template` | Compliance templates |
| | `regelabweichungen` | `id`, `betrieb_id`, `regel_beschreibung`, `abweichung_typ`, `datum` | Violation log |
| **CORE** | `betrieb_einstellungen` | `id`, `betrieb_id`, `farb_schema`, `sprache`, `zeitzone` | System configuration |
| | `system_einstellungen` | `id`, `schlüssel`, `wert`, `kategorie` | Global settings, enums |
| | `cache_keys` | `id`, `cache_key`, `expires_at` | Invalidation tracking |

---

## 3. API ROUTES BY MODULE

| Module | Endpoints | Purpose |
|--------|-----------|---------|
| **KATASTER** | `GET/POST /api/kataster/parzellen` | List, create parcels |
| | `GET /api/kataster/parzellen/{id}/polygon` | Get GeoJSON polygon (Leaflet) |
| | `POST /api/kataster/parzellen/{id}/lock` | Acquire exclusive edit lock |
| | `DELETE /api/kataster/parzellen/{id}/lock` | Release lock |
| | `GET /api/kataster/schlaege/{id}/history` | Field history by parcel |
| **ACL** | `GET /api/acl/rollen` | List roles (Admin only) |
| | `POST /api/acl/rollen` | Create custom role |
| | `PUT /api/acl/rollen/{id}/berechtigungen` | Assign permissions to role |
| | `GET /api/acl/me/berechtigungen` | Current user permissions |
| **FINANZEN** | `GET/POST /api/finanzen/vertraege` | List, create contracts |
| | `GET /api/finanzen/vertraege/{id}/positionen` | Contract line items |
| | `POST /api/finanzen/transaktionen` | Record transaction |
| | `GET /api/finanzen/konten` | Chart of accounts |
| | `GET /api/finanzen/dashboard/bilanz` | Financial summary |
| **SCHLAGKARTEI** | `POST /api/schlagkartei/massnahmen` | Log field treatment (pesticide/fertilizer) |
| | `GET /api/schlagkartei/schlag/{id}/massnahmen` | Field treatment history |
| | `POST /api/schlagkartei/monitoring` | Scout pest report |
| | `GET /api/schlagkartei/schlag/{id}/monitoring` | Pest history by field |
| | `GET /api/schlagkartei/feld-geräte` | Equipment assignments |
| **ERNTE** | `POST /api/ernte/kampagnen` | Start harvest campaign |
| | `POST /api/ernte/lesegänge` | Record harvest pass (date, mostgewicht) |
| | `POST /api/ernte/lesegänge/{id}/termine` | Assign workers to pass |
| | `GET /api/ernte/kampagnen/{id}/lesegänge` | Passes by campaign |
| | `GET /api/ernte/ergebnis/{lesegan_id}` | Harvest outcome (kg, quality notes) |
| **KELLERWIRTSCHAFT** | `GET/POST /api/keller/gärfässer` | Tank/barrel registry |
| | `POST /api/keller/gärprozesse` | Start fermentation (lesegan → tank) |
| | `POST /api/keller/messwerte` | Record fermentation parameters (temp, density, %alc) |
| | `GET /api/keller/gärprozesse/{id}/messwerte` | Fermentation curve (time-series) |
| | `GET /api/keller/lagerbestand` | Cellar inventory by location |
| | `POST /api/keller/abfüllung` | Bottling event (tank → product) |
| **PERSONAL** | `GET/POST /api/personal/arbeitskräfte` | Worker registry |
| | `POST /api/personal/zeiterfassung/qr-login` | QR clock-in |
| | `POST /api/personal/zeiterfassung/logout` | Clock-out |
| | `GET /api/personal/zeiterfassung/{worker_id}/month` | Timesheet by worker/month |
| | `GET/POST /api/personal/aufgaben` | Task list, create task |
| | `PUT /api/personal/aufgaben/{id}/zuordnung` | Assign task to worker |
| | `GET /api/personal/roster` | Schedule/assignment board |
| **WARENWIRTSCHAFT** | `GET /api/waren/artikel` | Product master (bottles, labels, etc.) |
| | `GET /api/waren/lagerbestände` | Inventory by location |
| | `POST /api/waren/lager-bewegungen` | Stock in/out/adjust |
| | `GET /api/waren/lagerorte` | Storage locations |
| | `GET /api/waren/dashboard/bestand-prognose` | Stock forecast & alerts |
| **VERTRIEB** | `GET/POST /api/vertrieb/kundenkonten` | Customer master (CRM) |
| | `POST /api/vertrieb/verkaufsaufträge` | Create sales order |
| | `GET /api/vertrieb/verkaufsaufträge/{id}` | Order detail + linked items |
| | `PUT /api/vertrieb/verkaufsaufträge/{id}/status` | Update order status (pending → shipped) |
| | `GET /api/vertrieb/provisionen/{verkaufsleiter_id}` | Commission dashboard |
| **COMPLIANCE** | `GET /api/compliance/audit-log` | Immutable audit trail (Admin only) |
| | `GET /api/compliance/audit-log/{entität_id}` | Audit history for entity |
| | `POST /api/compliance/regelabweichungen` | Report violation |
| | `GET /api/compliance/vorlagen` | Compliance templates |

---

## 4. USER ROLES & ACCESS LEVELS

```
ROLE HIERARCHY & PERMISSIONS

┌─────────────────────────────────────────────────────────┐
│ ADMIN                                                   │
│ ├─ vinicore_rollen: create/edit/delete                 │
│ ├─ vinicore_berechtigungen: assign                      │
│ ├─ All modules: full read/write/delete                 │
│ ├─ ACL audit: full access                              │
│ └─ System einstellungen: full access                   │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ WINZER (Master Vineyard Manager)                        │
│ ├─ KATASTER: read all parzellen, lock/edit own         │
│ ├─ SCHLAGKARTEI: create/edit feldmassnahmen, monitoring│
│ ├─ ERNTE: create kampagnen, initiate lesegänge         │
│ ├─ FINANZEN: read own contracts (parzelle-based)       │
│ ├─ PERSONAL: view roster, assign tasks                 │
│ └─ WARENWIRTSCHAFT: read lagerbestand                  │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ AUSSENWIRTSCHAFT (Field Operations Manager)             │
│ ├─ KATASTER: read parzellen                            │
│ ├─ SCHLAGKARTEI: create/edit feldmassnahmen, monitoring│
│ ├─ ERNTE: edit lesegänge (ripe % assignments)          │
│ ├─ PERSONAL: view roster, request task reassignment    │
│ └─ Dashboard: field condition summary                  │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ SAISONKRAFT (Seasonal Worker)                           │
│ ├─ PERSONAL: own zeiterfassung (QR clock in/out)      │
│ ├─ PERSONAL: assigned aufgaben (view only)            │
│ ├─ ERNTE: record own lesetermin eintrag (ripeness)    │
│ └─ WARENWIRTSCHAFT: read locations only (for stock)   │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ KELLERMEISTER (Cellar Master)                           │
│ ├─ KELLERWIRTSCHAFT: full (gärfässer, gärprozesse)    │
│ ├─ ERNTE: read lesegänge (pressing intake)             │
│ ├─ WARENWIRTSCHAFT: full (lagerbestand, bewegungen)    │
│ ├─ VERTRIEB: read verkaufsaufträge (stock depletion)   │
│ └─ FINANZEN: read (cost tracking)                      │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ VERKAUFSLEITER (Sales Manager)                          │
│ ├─ VERTRIEB: full (kundenkonten, verkaufsaufträge)    │
│ ├─ WARENWIRTSCHAFT: read lagerbestand                  │
│ ├─ FINANZEN: read transactions (revenue tracking)      │
│ ├─ PERSONAL: view roster (schedule coordination)       │
│ └─ Provisionen: full (own commission dashboard)        │
└─────────────────────────────────────────────────────────┘
```

**Permission Slug Pattern:** `{modul}_{aktion}` (e.g., `schlagkartei_erstellen`, `keller_bearbeiten`)

**Enforcement:**
- Middleware: `RequirePermission` (checks slug against user's role)
- Model Policy: `viewAny()`, `view()`, `create()`, `update()`, `delete()` check ownership + permission slug

---

## 5. KEY WORKFLOWS

### **Workflow 1: Harvest Pipeline**

**Timeline: September–December**

```
WEEK 1-2: PRE-HARVEST
  1. SCHLAGKARTEI check → Review field treatment history
  2. Create ERNTE kampagne (harvest campaign)
  3. Ripen monitoring starts (Oechsle degrees)

WEEK 2-4: HARVEST EXECUTION
  For each Schlag (field):
    a) Create Lesegan (harvest pass) with date, mostgewicht
    b) Assign workers via Lesetermine
    c) Saisonkraft scans QR, clocks in via PERSONAL
    d) Track harvest outcome (kg, quality notes)

WEEK 4-ONWARD: CELLAR INTAKE
    e) Kellermeister receives harvest
    f) Create Gärprozess (fermentation instance)
    g) Add initial fermentation measurement (temp, density)

WEEK 5-12: FERMENTATION MONITORING
    • Every 2-3 days: Log temp, density, alcohol %
    • Generate fermentation curves (time-series)
    • Auto-detect completion (density → <1.000)

WEEK 13+: BOTTLING & STORAGE
    • Mark fermentation "trocken" (complete)
    • Create inventory entry in Warenwirtschaft
    • Bottling decrements stock

WEEK 14+: SALES
    • Verkaufsleiter views available stock
    • Create sales orders
    • Auto-decrement stock on shipment
    • Generate invoices + shipping labels
```

### **Workflow 2: Personnel & QR Clock-In**

**Timeline: Continuous (Peak during harvest)**

```
MORNING: ASSIGNMENT
  1. Winzer checks daily roster
  2. Create/assign tasks to workers
  3. Task list pushed to workers' mobile

CLOCK-IN (8:00 AM)
  • Saisonkraft scans QR code
  • [API] POST /api/personal/zeiterfassung/qr-login
  • Status: "on_site"

LUNCH BREAK (12:00-13:00)
  • Worker marks pause in mobile app

CLOCK-OUT (17:30 PM)
  • [API] POST /api/personal/zeiterfassung/logout
  • System calculates: work_minutes = (17:30 - 08:00) - pause_minutes

END-OF-MONTH: PAYROLL
  • Aggregate zeiterfassung by worker
  • Export CSV → FINANZEN transaktionen
  • Each entry audited (COMPLIANCE audit_log)
```

---

## 6. SHARED SERVICES

### **Core Infrastructure**

```php
App\Services\PermissionService        // ACL-wide permission checking
App\Services\GeoJsonService           // Polygon storage & validation (PostGIS-ready)
App\Services\AuditService             // Immutable compliance logging
App\Services\CacheService             // Module-aware cache invalidation
App\Services\TransactionEventBus      // Cross-module event publishing
App\Services\NotificationService      // Role-based alerts (fermentation, harvest ready)
App\Services\QRCodeService            // Generate/decode worker QR codes
App\Services\ReportService            // Optimize dashboard queries
App\Services\InventoryService         // Stock sync (WARENWIRTSCHAFT ↔ VERTRIEB)
App\Services\WorkerScheduleService    // Availability + task assignment
```

### **Query Optimization Repositories**

```php
App\Repositories\ParzelleRepository   // Parcels + pending contracts
App\Repositories\LesegangRepository   // Harvest ready alerts
App\Repositories\ZeiterfassungRepository  // Timesheet aggregation
App\Repositories\GarProzessRepository // Fermentation curve data
App\Repositories\VerkaufsauftragRepository // Commission reports
```

---

## 7. IMPLEMENTATION ROADMAP

### **PHASE 0 (MVP - Q4 2026) → Launch**
✅ KATASTER, ACL, ERNTE, KELLERWIRTSCHAFT, PERSONAL, CORE, FINANZEN (minimal)

**Criterion:** One complete harvest campaign (Sep–Nov 2026) end-to-end, QR clock-in, fermentation logging, payroll posted

### **PHASE 1 (Q1 2027) → Advanced Field Ops**
✅ SCHLAGKARTEI (full), WARENWIRTSCHAFT (basic)

### **PHASE 2 (Q3 2027) → Sales & Compliance**
✅ VERTRIEB (CRM), COMPLIANCE (audit trail), PERSONAL (advanced)

### **PHASE 3 (Q1 2028+) → Analytics & Integration**
✅ Advanced financials, ML-based predictions, HR automation

---

## 8. MODULE FOLDER STRUCTURE

```
app/Modules/
├── Kataster/
│   ├── Controllers/ParzelleController.php
│   ├── Requests/StoreParzelleRequest.php
│   ├── Services/ParzelleService.php
│   └── Models/Parzelle.php
├── Ernte/
│   ├── Controllers/ErntKampagneController.php
│   ├── Requests/StoreLesegangRequest.php
│   ├── Services/HarvestService.php
│   ├── Models/ErnteKampagne.php
│   ├── Models/Lesegan.php
│   └── Models/ErteErgebnis.php
├── Kellerwirtschaft/
│   ├── Controllers/GarProzessController.php
│   ├── Requests/StoreGarMesswertRequest.php
│   ├── Services/FermentationService.php
│   └── Models/GarProzess.php
├── Personal/
│   ├── Controllers/ZeiterfassungController.php
│   ├── Requests/QRLoginRequest.php
│   ├── Services/TimeClockService.php
│   └── Models/Arbeitskraft.php
├── Finanzen/
├── Warenwirtschaft/
├── Vertrieb/
├── Compliance/
├── Acl/
└── Core/
```

---

This architecture is production-ready, modular, and supports both MVP launch and long-term growth through planned phases.
