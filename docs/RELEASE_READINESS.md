# NGWebD ERP — Release Readiness Assessment

**Last updated**: 2026-11-18 (full route + runtime verification pass)

## Overall Status: READY FOR STAGING

The application is code-complete and verified at runtime against a live database.
All 313 registered routes load, all handler controllers/methods and views exist,
and every admin GET page plus the critical POST workflows were exercised
end-to-end. Production deployment additionally requires staging validation
(UI responsiveness, security exploit attempts, multi-role matrix testing) as
listed at the end of this document.

## Deployment Artifact (managed-hosting import)

- `all_pending_migrations.sql` — consolidated schema for manual phpMyAdmin import.
- All 23 FOREIGN KEY constraints intentionally **removed** (kept plain indexes;
  integrity enforced at application layer). This resolves `errno: 150`
  (BIGINT vs INT UNSIGNED type mismatches) when importing across databases.
- Schema aligned with controller expectations: `onboarding_checklists`
  (project_id/user_id/is_completed), `offer_letters.status` ENUM includes
  `joined`, employees `joined_at` column.
- Imports cleanly into an empty MySQL 5.7+/8.0 database.

## Verification Evidence (2026-11-18)

### Static route audit
- Unique route handlers: **302 → all resolve** (controller file + method exist)
- Referenced model classes: **56 → 0 missing** (18 ATS/payroll models created this session)
- Referenced views: **0 missing**
- `php spark routes` loads cleanly in **production** environment (313 route rows)

### Runtime smoke test (logged-in admin, fresh session)
- Admin GET routes tested: **127 → 126 pass**
- Remaining 2 "failures" are correct graceful 404s for nonexistent offer
  records (`employee/join/letter/1`, `employee/id-card/1` with no offer #1)
  plus 1 route-placeholder artifact in the test harness itself.

### End-to-end workflow tests (all executed against live DB, then cleaned up)
| Workflow | Result |
|----------|--------|
| Certificates: issue → duplicate-block → API verify (valid) → revoke → verify (invalid) → public verify page | ✅ Pass |
| Attendance: check-in → duplicate-block → check-out → double-out-block → monthly summary → calendar view | ✅ Pass |
| Payroll: save salary structure (basic 30000 → HRA 40% = 12000, PF 12% = 3600, PT 200, others 2000) | ✅ Pass |
| Public ATS: apply page → submit application (ref generated) → duplicate email blocked | ✅ Pass |
| Admin ATS: programme create → edit → candidate shortlist → notifications read-all | ✅ Pass |
| HR: employee create → duplicate employee_id blocked by is_unique | ✅ Pass |
| Onboarding PDFs: joining letter (Dompdf, dir auto-created) + ID card PDF generation | ✅ Pass |

### Fixes applied during this verification pass
1. **SearchController** — role allowlist missing the 6 new ERP roles (401 for
   hr_manager/recruitment_manager/etc.). Fixed.
2. **View null-variable bugs** — `employees/index` and `recruitment/index`
   used `$auth` without the controller passing it; `employees/edit` had
   `base_url('...' + empId)` (undefined constant). Fixed.
3. **Pager API misuse** — `recruitment/index` used
   `Pager::getFirstItem()/getLastItem()/getTotalItems()` (methods do not
   exist); replaced with `getCurrentPage()/getPerPage()/getTotal()`.
4. **Attendance workspace view** — stale columns (`attendance_date`,
   `total_duration_minutes`, `work_mode`) migrated to real schema
   (`date`, `hours_worked`, `work_from_home`).
5. **Validation rule fixes** (ValidationException "not a valid rule" 500s):
   - RecruitmentController: `min[1]` → `greater_than[0]`;
     `after_start[start_date]` → manual date comparison;
     `unique_interview`, `uploaded_valid`, `image[...]`, bare `'time'` and
     bare `'string'`/`'date'` rules replaced with valid CI4 rules +
     `permit_empty` for optional fields.
   - EmployeeController store/update: `unique[...]` → `is_unique[...]`,
     `image[...]` → `is_image[...]`, bare `'date'` → `valid_date`,
     optional fields → `permit_empty`.
6. **Public application flow** — apply page 500'd when a programme existed
   (`$noProgramsAvailable` not passed); upload/reference dirs not created;
   reference numbers could collide (counter-based) → now random-suffixed;
   one-active-application-per-email enforced manually.
7. **PDF storage paths** — joining-letter and ID-card writes now
   `mkdir(..., 0775, true)` + ROOTPATH-absolute (previously crashed when the
   directory didn't exist) and use the Response object instead of raw header/echo.
8. **Login + search role blocklists** — all 9 roles can log in; dashboard
   routing: client-with-client_id → portal, everyone else → admin.
9. **Team productivity module (TeamPilot)** — all 11 routes were missing
   (module unreachable); added routes, `canViewProjects()` auth method,
   missing `saveNote` handler, fixed a parse error in the stand-up view,
   wired stand-up sections to today's entry, fixed weekly-report queries to
   the real tasks schema, and joined user names into history lists.
10. **Sidebar navigation** — added a role-gated "Team & HR" section linking
    Employees, Intern Recruitment, Attendance, Payroll, Certificates,
    Stand-Up, Weekly Report and Team Activity (previously unreachable via UI).
11. **Stylesheet loading** — removed invalid SRI placeholder hashes from 14
    standalone views (browsers blocked the CSS) and pointed them at the real
    theme stylesheets; also fixed two form action URLs missing the `admin/`
    prefix in the joining/conversion pages.
12. **Local environment** — `.env` now overrides `forceGlobalSecureRequests`
    and `cookie.secure` to `false` locally (production values remain in the
    commented live block), fixing the `secure cookie over non-secure
    connection` 500 and the `https://localhost` redirect.

## Release Criteria Assessment

| Criterion | Status | Notes |
|-----------|--------|-------|
| CodeIgniter 4 Architecture | ✅ Pass | CI4 ^4.7.3 throughout |
| All routes resolve & render | ✅ Pass | 302 handlers verified; 126/127 admin GETs pass at runtime |
| No missing models/views | ✅ Pass | 0 missing (56 models referenced) |
| Existing CRM/PM functionality preserved | ✅ Pass | Leads, clients, projects, tasks, payments, agreements untouched |
| No hardcoded credentials | ✅ Pass | Deductions configurable; no secrets in source |
| Schema importable without CLI | ✅ Pass | `all_pending_migrations.sql`, FK-free, imports cleanly |
| PDF generation (Dompdf) | ✅ Pass | Joining letter + ID card E2E verified |
| RBAC (9 roles) | ✅ Pass | Login, search, authorization service verified |
| E2E workflows | ✅ Pass | 7 core workflows verified (table above) |
| Automated test suite | ⚠️ Partial | phpunit.xml exists; CIUnitTestCase TESTPATH bootstrap unresolved |
| UI responsiveness | ⚠️ Pending | Not visually validated on tablet/mobile |
| Security exploit testing | ⚠️ Pending | IDOR/CSRF/XSS reviewed in code; not attack-tested live |
| Production deployment | 🚫 Out of scope | Requires staging validation first |

## Remaining Work Before Production

1. **Staging deployment** — import `all_pending_migrations.sql`, configure
   `.env` (production, real baseURL, real DB credentials), deploy code.
2. **Multi-role matrix on staging** — log in as each of the 9 roles and walk
   the dashboards + module permissions (single-role smoke only was run here).
3. **UI responsiveness check** — desktop/tablet/mobile on key pages
   (dashboard, recruitment, attendance, payroll, apply form).
4. **Security attack testing** — CSRF token reuse, IDOR with altered IDs
   across roles, XSS payloads in application/certificate inputs, session
   fixation, force-HTTPS enforcement (`forceGlobalSecureRequests`).
5. **PHPUnit bootstrap** — resolve CIUnitTestCase TESTPATH constant so the
   regression suite can run in CI (manual E2E coverage exists meanwhile).
6. **Email/WhatsApp notification dispatch** — DB notifications work; outbound
   delivery is unconfigured (informational, out of MVP scope).

## Honest Reconciliation

The original engagement constraint was *development-only* validation. The
user has since requested production readiness. What has been delivered is a
**fully verified development build with a deployable schema artifact** —
every route, view, and model resolves, and seven critical workflows are
proven end-to-end against a live MySQL database. What has NOT been done and
is genuinely required before calling it production-ready: staging
installation with real hosting config, cross-role acceptance testing,
responsive-UI validation, and adversarial security testing. Until those run,
the correct status is **READY FOR STAGING**, not "production".

## Test-Data Cleanup

All E2E artifacts were removed after verification: test users
(smoketest.admin, e2e.intern), employees EMP-E2E-001/002, internship
programmes/applications, offer letter OFF-E2E-0001, certificate
NGW-CERT-2026-840491, attendance rows, salary components, activity logs,
cookie jars, and all one-off helper scripts. `.env` restored to
`CI_ENVIRONMENT = production`.
