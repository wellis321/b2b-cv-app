# Export and import certifications and skills

This guide describes how to **securely** export certifications and skills from your local database and import them into production for a specific user (by email).

## Overview

- **Export** (run on **local**): reads from your local DB and writes a JSON file. The file contains only certification and skill **data** (no profile IDs, no credentials).
- **Transfer**: move the JSON file to the production server via **SCP**, **SFTP**, or another secure channel. Do **not** email it or put it in version control.
- **Import** (run on **production**): reads the JSON and inserts into the production DB for the user identified by email. Uses the production `.env` (and thus production DB).

## 1. Export on local

From your project root:

```bash
php scripts/export-certifications-skills.php williamjamesellis@outlook.com
```

- Uses the **local** `.env` and database.
- Output file: `scripts/exports/certifications-skills-williamjamesellis-outlook-com-YYYY-MM-DD-HHMMSS.json`
- The file contains only: `certifications` (name, issuer, date_obtained, expiry_date) and `skills` (name, level, category). No `profile_id`, no passwords, no tokens.

## 2. Transfer to production securely

Use **SCP** or **SFTP** (or your host’s secure file transfer). Example with SCP:

```bash
scp scripts/exports/certifications-skills-williamjamesellis-outlook-com-*.json user@your-production-server:/path/to/your/app/
```

- Do **not** commit the export file to git (it is ignored via `scripts/exports/*.json`).
- Prefer a directory that is not public (e.g. project root or a private folder, not `public/` or `storage/` if that is web-accessible).

## 3. Import on production

SSH into the production server, go to the app directory, and run:

```bash
php scripts/import-certifications-skills.php williamjamesellis@outlook.com /path/to/certifications-skills-williamjamesellis-outlook-com-2025-01-25-120000.json
```

- Uses the **production** `.env` and database.
- The user (profile) must already exist for that email; the script will not create an account.
- It inserts all certifications and skills from the file with **new UUIDs** and the target user’s `profile_id`.

### Dry run (optional)

To see what would be imported without writing to the database:

```bash
php scripts/import-certifications-skills.php williamjamesellis@outlook.com /path/to/export.json --dry-run
```

## 4. After import

- You can delete the JSON on the server after a successful import.
- Optionally delete the local export in `scripts/exports/` if you no longer need it.

## Requirements and notes

- **Production user**: the profile for `williamjamesellis@outlook.com` must exist before import (e.g. via sign-up or `scripts/create-production-account.php`).
- **Certification dates**: if you have certifications **without** a “date obtained” and production still has `date_obtained NOT NULL`, the import will fail. Ensure the migration `database/20241107_make_certification_dates_nullable.sql` has been applied on production if you use such certifications.
- **Duplicates**: the import always **adds** rows. It does not detect or skip duplicates. If you run it twice with the same file, you will get duplicate certifications and skills. Re-run only with a new export or after manually removing previously imported rows.
