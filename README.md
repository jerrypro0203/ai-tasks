# Laravel AI Task Manager

Hey DIJ-ers!

Aangezien ik veel projecten heb waarin AI een grote rol speelt, heb ik iets kleins neergezet om te laten zien hoe ik dat aanpak. Dit is een Laravel REST API waarmee je taken kunt beheren, met een AI-laag die elke nieuwe taak automatisch analyseert op prioriteit en tijdsinschatting. Zelfgeschreven code trouwens, geen AI die alles voor me heeft neergezet :).

---

## Wat doet het?

Wanneer je een taak aanmaakt, wordt er automatisch een background job gestart die de taak naar een AI-model stuurt. Die geeft terug:

- **Prioriteit** — laag, middel of hoog
- **Verbeterde omschrijving** — een nettere versie van je input
- **Tijdsinschatting** — geschat aantal minuten

De rest is standaard CRUD via een JSON API.

---

## Stack

- **Laravel 13**
- **Laravel AI SDK** (`laravel/ai`) — voor AI-integratie met Anthropic/OpenAI
- **SQLite** — als database
- **Queue** — voor achtergrond verwerking van AI-analyse

---

## Installatie

```bash
git clone <repo-url>
cd laravel-ai-task-manager

composer install

cp .env.example .env
php artisan key:generate
```

Stel je API key in in `.env`:

```env
ANTHROPIC_API_KEY=sk-...
```

Daarna:

```bash
touch database/database.sqlite
php artisan migrate
php artisan queue:work
```

---

## Endpoints

| Method | Endpoint | Beschrijving |
|--------|----------|--------------|
| GET | `/api/tasks` | Alle taken ophalen |
| POST | `/api/tasks` | Nieuwe taak aanmaken |
| GET | `/api/tasks/{id}` | Één taak ophalen |
| PUT | `/api/tasks/{id}` | Taak bijwerken |
| DELETE | `/api/tasks/{id}` | Taak verwijderen |

### Voorbeeld request

```json
POST /api/tasks
{
    "title": "Homepage redesign",
    "description": "De homepage moet moderner en sneller worden"
}
```

### Voorbeeld response (na AI-analyse)

```json
{
    "id": 1,
    "title": "Homepage redesign",
    "description": "De homepage moet moderner en sneller worden",
    "priority": "hoog",
    "ai_description": "Ontwerp en implementeer een moderne, performante homepage met verbeterde UX en laadtijden.",
    "estimated_minutes": 240,
    "created_at": "2026-05-03T12:00:00Z"
}
```

---

## Hoe werkt de AI-integratie?

Na het aanmaken van een taak wordt `AnalyzeTaskJob` gedispatcht. Die gebruikt de Laravel AI SDK met structured output:

```php
$response = agent(
    instructions: 'Je bent een productiviteitsassistent.',
    schema: fn (JsonSchema $schema) => [
        'priority'          => $schema->string()->enum(['laag', 'middel', 'hoog'])->required(),
        'ai_description'    => $schema->string()->required(),
        'estimated_minutes' => $schema->integer()->required(),
    ],
)->prompt("Analyseer deze taak: {$title}. {$description}");
```

---

## Toelichting gebruik AI SDK

De Laravel AI SDK is voor dit project wat overkill. Een directe API call had ook gekund. Ik wilde de SDK alvast uitproberen.

---

## Projectstructuur

```
app/
├── Http/
│   ├── Controllers/Api/TaskController.php
│   ├── Requests/StoreTaskRequest.php
│   ├── Requests/UpdateTaskRequest.php
│   └── Resources/TaskResource.php
├── Jobs/
│   └── AnalyzeTaskJob.php
├── Models/
│   └── Task.php
└── Services/
    └── AiService.php
```

---