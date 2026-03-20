# CurrencyRate (PrestaShop module)

Modul realizuje zadanie rekrutacyjne:
- historia kursow walut z ostatnich 30 dni (FO),
- tabela cen produktu przeliczonych na aktywne waluty sklepu (FO),
- synchronizacja kursow przez API NBP oraz cron.

## NBP API i endpointy

W module sa wydzielone 3 endpointy:
- `GET /api/exchangerates/tables/A/last/1/` - aktualna tabela A (aktualizacja `conversion_rate` w PrestaShop),
- `GET /api/exchangerates/tables/A/{startDate}/{endDate}/` - import historii 30 dni,
- `GET /api/exchangerates/rates/A/{code}/last/{topCount}/` - fallback dla pojedynczej waluty.

Kazdy endpoint ma oddzielna klase, odpowiedzi mapowane sa do DTO i kolekcji.

## Architektura

- `src/Infrastructure/*` - endpointy, klient HTTP, mapowanie odpowiedzi NBP,
- `src/Domain/*` - DTO i kolekcje,
- `src/Application/*` - logika biznesowa (synchronizacja, aktualizacja kursow, provider widokow),
- `controllers/front/*` - FO: strona historii,
- `views/templates/*` - widoki FO.
- autoload klas przez Composer PSR-4 (`CurrencyRate\\` -> `src/`).

## Instalacja i uruchomienie

```bash
docker compose up -d
```

`composer dump-autoload` dla modulu uruchamia sie automatycznie podczas startu kontenera.

Po uruchomieniu:
- sklep: `http://localhost:8080`,
- panel admin: `http://localhost:8080/admin-dev`.

Całość jest przygotowana pod środowisko developerskie. Wszystkie skryptu Docker i uruchomieniowe produkcyjnie powinny wyglądać inaczej.