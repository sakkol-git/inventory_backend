# Project Presentation: Laboratory / Research Management System

This slide deck summarizes what has been implemented, what remains, timeline, risks, and next actions.

---
Slide 1 — Title
- Project: Laboratory / Research Management System
- Team: (Your team)
- Date: 2026-05-08
- Status snapshot: Partial MVP

---
Slide 2 — One-line summary
- Current state: Core inventory models and APIs implemented; safety-critical workflows (expiry alerts, approvals) and production hardening incomplete.

---
Slide 3 — Completion overview (by module)
- Module 1: Plant Management — 80% (models, controllers, resources, transactions, policies)
- Module 2: Chemical Management — 70% (models, batches, usage logging; missing expiry alerts)
- Module 3: Equipment Management — 70% (models, borrow/maintenance; missing approval workflow)
- Module 4: Auth & RBAC — 60% (users, roles, policies present; incomplete permission enforcement)
- Module 5: User Profile — 60% (resources, documents; missing contribution aggregation)

---
Slide 4 — What’s implemented (high level)
- Data models: species, variety, samples, stocks, chemicals, batches, equipment, borrow records, transactions, achievements
- Controllers + FormRequests for CRUD operations
- API Resources for standardized payloads
- Image upload services and basic storage
- Policies scaffolded for major resources
- Composer tooling: Pint, Rector; NPX/npm frontend packages installed

---
Slide 5 — Critical missing features (must ship before production)
- Database integrity: missing FK/index migrations and non-negative stock enforcement
- Inventory ledger/locking to prevent race conditions and negative stock
- Chemical expiry alerts + scheduled notifications
- Borrow/Return approval workflows with audit trail and notifications
- Full RBAC enforcement and seeded permissions
- Signed/secure file downloads and virus-scan for user documents

---
Slide 6 — Security & compliance gaps
- Mass-assignment surfaces not fully audited
- Upload validation & storage visibility controls incomplete
- Policies exist but unchecked on many routes — risk of unauthorized actions
- No centralized audit log viewer or retention policy

---
Slide 7 — Performance & scaling gaps
- Potential N+1 in resource responses (eager-loading audit suggested)
- Missing indexes on expiry_date and polymorphic keys
- No caching strategy for permissions and heavy reads
- No queue usage for email/long-running tasks beyond basic scaffolds

---
Slide 8 — Testing status
- Unit tests: limited (factories/seeds present)
- Feature tests: sparse for critical flows (stock concurrency, borrow approvals)
- Recommendation: add test matrix for business-critical paths (transaction ledger, expiry job, approval flows)

---
Slide 9 — Production readiness checklist (top priority)
- Add FK and indexes migrations (transactions, stocks, batches)
- Implement DB transactions + row locking for stock updates
- Add scheduled job for chemical expiry alerts and tests
- Enforce `authorizeResource` in controllers and seed roles/permissions
- Harden file upload pipeline and storage access

---
Slide 10 — Timeline & effort estimate
- Phase A (2–3 weeks): DB constraints, transaction robustness, policy enforcement
- Phase B (2–3 weeks): Chemical expiry alerts, borrow approvals, notifications
- Phase C (1–2 weeks): Testing coverage, caching, performance tuning
- Phase D (1–2 weeks): CI/CD, monitoring, backups, Docker hardening
- Total: ~6–10 weeks depending on team size and parallelization

---
Slide 11 — Top risks & mitigations
- Risk: Data integrity issues in production -> Mitigation: Add FKs and ledger, run reconciliation scripts before deploy
- Risk: Unauthorized actions due to missing policies -> Mitigation: Enforce middleware and run policy coverage audits
- Risk: Missed expiry/unsafe chemical usage -> Mitigation: Add scheduled alerts and dashboards

---
Slide 12 — Quick wins for next sprint (2 weeks)
- Add FK/index migrations for transactions and batches
- Wire `authorizeResource` for all inventory controllers
- Add expiry detection job + unit test skeleton
- Add caching for role/permission lookups

---
Slide 13 — Call to action
- Approve DB-migration PRs and schedule maintenance window
- Allocate 1 senior backend engineer for architecture fixes
- Create CI pipeline job for `composer quality` (Pint + Rector) and `phpunit`

---
Slide 14 — Appendix: Where to find detailed analysis
- Full GAP analysis doc: [PROJECT_GAP_ANALYSIS.md](PROJECT_GAP_ANALYSIS.md)
- This slides doc: [PROJECT_SLIDES.md](PROJECT_SLIDES.md)


Notes for presenter:
- Keep each slide to 2–4 talking points.
- Use visuals: a four-quadrant chart for modules vs readiness, and a timeline Gantt for phases.
- Emphasize safety and integrity items first (DB constraints, expiry alerts, RBAC).

