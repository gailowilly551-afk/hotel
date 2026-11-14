# Database Schema & Migrations

Ang folder na ito ay naglalaman ng pangunahing database dump para sa **Marvelous Hotel**.

## Mga Nilalaman:
- `marvelous_hotel.sql`: Buong database schema, initial tables, foreign key constraints, at initial settings.

## Paano Mag-import:
```bash
# Sa XAMPP Linux:
/opt/lampp/bin/mysql -u root -p marvelous_hotel < database/marvelous_hotel.sql

# Sa XAMPP Windows (CMD / PowerShell):
C:\xampp\mysql\bin\mysql.exe -u root marvelous_hotel < database\marvelous_hotel.sql
```

## Incremental Migrations:
Para sa mga pagbabago pagkatapos ng initial import, gamitin ang CLI migration runner mula sa project root:
```bash
php migrate.php           # Ilapat ang mga bagong migration files mula sa migrations/
php migrate.php --status  # Tingnan ang estado ng mga na-apply na migration
```

