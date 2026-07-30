# Daybreak — Layered Architecture

A PHP layered-architecture implementation of the Daybreak digital evangelization platform.

## Project Structure

```
DigitalEvangelization/
│
├── index.php                  # Front controller
├── .htaccess                  # URL rewriting
│
├── config/
│   ├── app.php                # App constants & error settings
│   └── database.php           # PDO singleton (getDB())
│
├── src/
│   ├── Model/                 # Pure data classes (no DB logic)
│   │   ├── Media.php
│   │   ├── Article.php
│   │   ├── PrayerRequest.php
│   │   ├── Event.php
│   │   ├── Donation.php
│   │   └── ContactMessage.php
│   │
│   ├── Repository/            # Data access layer (SQL lives here)
│   │   ├── MediaRepository.php
│   │   ├── ArticleRepository.php
│   │   ├── PrayerRepository.php
│   │   ├── EventRepository.php
│   │   ├── DonationRepository.php
│   │   └── ContactRepository.php
│   │
│   ├── Service/               # Business logic
│   │   ├── MediaService.php
│   │   ├── ArticleService.php
│   │   ├── PrayerService.php
│   │   ├── EventService.php
│   │   ├── DonationService.php
│   │   └── ContactService.php
│   │
│   └── Controller/            # HTTP layer — validates input, calls Service
│       ├── BaseController.php
│       ├── MediaController.php
│       ├── ArticleController.php
│       ├── PrayerController.php
│       ├── EventController.php
│       ├── DonationController.php
│       └── ContactController.php
│
├── api/
│   └── router.php             # Maps HTTP routes → Controllers
│
├── views/
│   ├── layout.php             # HTML shell (includes all partials + pages)
│   ├── partials/
│   │   ├── header.php
│   │   └── footer.php
│   └── pages/
│       ├── home.php
│       ├── watch.php
│       ├── read.php
│       ├── prayer.php
│       ├── events.php
│       ├── give.php
│       ├── about.php
│       └── connect.php
│
├── public/
│   ├── css/app.css            # All styles
│   └── js/app.js              # SPA routing + API calls
│
└── database/
    └── schema.sql             # CREATE TABLE + seed data
```

## Layer Responsibilities

| Layer | Responsibility |
|---|---|
| **Model** | Plain PHP 8 classes, typed properties, `toArray()` |
| **Repository** | All SQL — hydrates rows into Model objects |
| **Service** | Business rules, validation, orchestration |
| **Controller** | Parses HTTP input, calls Service, returns JSON |
| **API Router** | Pattern-matches route → Controller method |
| **View** | HTML partials; dynamic content fetched by JS |

## Setup

1. Start Apache + MySQL in XAMPP.
2. Create the database and run seed data:
   ```
   mysql -u root daybreak < database/schema.sql
   ```
   Or paste `schema.sql` into phpMyAdmin.
3. Make sure `mod_rewrite` is enabled in Apache.
4. Visit: `http://localhost/DigitalEvangelization/`

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/media` | All media (optional `?type=sermon\|devotional\|...`) |
| GET | `/api/media/featured` | Currently featured message |
| GET | `/api/articles` | All articles |
| GET | `/api/articles/{id}` | Single article |
| GET | `/api/prayers` | Approved prayer requests |
| POST | `/api/prayers` | Submit a new request |
| POST | `/api/prayers/{id}/pray` | Increment pray count |
| GET | `/api/events/weekly` | Recurring weekly schedule |
| GET | `/api/events/upcoming` | Upcoming one-off events |
| POST | `/api/donations` | Initiate a donation |
| GET | `/api/donations/stats` | Total given |
| POST | `/api/contact` | Send a contact message |
