# CurrencyRate (PrestaShop module)

## Instalacja i uruchomienie

```bash
cp .env.example .env
```

```bash
docker compose up -d
```

`composer dump-autoload` dla modulu uruchamia sie automatycznie podczas startu kontenera.

Uruchomienie testów:

```bash
docker compose up tests
```

Po uruchomieniu:
- sklep: `http://localhost:8080`,
- panel admin: `http://localhost:8080/admin-dev`.

Całość jest przygotowana pod środowisko developerskie. Wszystkie skryptu Docker i uruchomieniowe produkcyjnie powinny wyglądać inaczej.