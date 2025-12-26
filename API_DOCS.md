# API Documentation - Perpustakaan Digital

Base URL: `http://localhost:8001/api`

## Authentication
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/auth/register` | Register new student | No |
| POST | `/auth/login` | Login and get JWT | No |
| POST | `/auth/logout` | Invalidate token | Yes |
| POST | `/auth/refresh` | Refresh expired token | Yes |
| GET | `/auth/me` | Get current user info | Yes |

## Books
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/books` | List all books (filter: search, category_id) | No |
| GET | `/books/{id}` | Get book details | No |
| POST | `/books/{id}/rate` | Rate a book (1-5 stars) | Yes |
| DELETE | `/books/{id}/rate` | Remove rating | Yes |

## Categories
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/categories` | List book categories | No |

## Transactions (Borrowing)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/transactions` | List user's loan history | Yes |
| POST | `/transactions/borrow` | Borrow a book | Yes |
| POST | `/transactions/{id}/return` | Return a borrowed book | Yes |

## Member Profile
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/member/profile` | Get profile details | Yes |
| PUT | `/member/profile` | Update profile (name, phone, password) | Yes |

## Integration (Admin Portal)
These endpoints are for the external Admin system.
**Header Required:** `X-INTEGRATION-SECRET: <your-secret-key>`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/integration/users` | List all registered students |

## Error Responses
All endpoints return JSON in the following format for errors:
```json
{
    "success": false,
    "message": "Error description"
}
```
HTTP Status Codes:
- `200`: Success
- `400`: Bad Request (Validation failed)
- `401`: Unauthorized (Invalid/Missing Token)
- `404`: Not Found
- `520`: Unknown Error
