# Architectural & Product Decisions

## 1. Multi-Tenancy Architecture
- **Decision:** Start with a single active institution by default, but design every database table with `institution_id` and apply the `BelongsToInstitution` global scope trait from day one.
- **Rationale:** Ensures clean code separation and instant switchability to multi-institution tenancy (T-17.5) with zero migration refactoring.

## 2. Unpaid Students Attendance Policy
- **Decision:** Allow attendance marking by default (`attendance.block_if_unpaid = false`), but provide an institution configuration flag to block attendance for unpaid students if desired.
- **Rationale:** Educational institutions prioritize attendance and child safety; fee enforcement is handled via reports, reminders, and learning material gating.

## 3. Grace Periods and Print Limits
- **Decision:** 7-day grace period for monthly fee renewal before access to lecture materials is locked; 1 print copy per material per student per month unless an administrative reprint is authorized.
- **Rationale:** Balances fairness to students with institutional printing cost controls.

## 4. Notification Channels
- **Decision:** Mail (via Mailpit in development, SMTP in production) and Database in-app notifications enabled by default. SMS and WhatsApp channel interfaces defined with a `log` driver ready for provider plug-ins.
- **Rationale:** Fulfills free/open-source requirement while keeping architectural extensibility.

## 5. Video Hosting Strategy
- **Decision:** Support YouTube unlisted video embeds via `youtube-nocookie.com` with automated URL parsing and thumbnail generation for Nursery activities, alongside direct MP4 upload support through `FileService` (up to 50 MB) for institute lecture clips.
- **Rationale:** Provides zero-bandwidth costs for teachers using YouTube while supporting private institute clips.

## 6. Student ID Cards
- **Decision:** System generates vector-quality printable PDF ID cards with student photograph, full name, unique student code, and high-contrast QR code.
- **Rationale:** Enables instant in-house printing on plastic or card stock using standard office printers.

## 7. Localization & Theming
- **Decision:** Multi-language architecture using Laravel language strings (`lang/en`), with a cohesive, modern theme featuring rich micro-interactions, distinct role dashboards, and high-contrast accessible layouts.
- **Rationale:** Meets accessibility, mobile usability, and clean customization standards.
