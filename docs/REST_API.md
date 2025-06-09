# REST API Documentation

## Overview

TIKM provides a comprehensive REST API with Laravel Sanctum authentication for mobile apps and third-party integrations. The API supports full CRUD operations for tickets with role-based access control.

## Base URL

- **Development**: `http://localhost:8000/api`
- **Production**: `https://yourdomain.com/api`

## Authentication

The API uses Laravel Sanctum token-based authentication with the following features:
- Personal access tokens with 30-day expiration
- Device-specific token management
- Token refresh capabilities
- Automatic token cleanup on logout

### Authentication Flow

1. **Login**: Exchange credentials for access token
2. **Use Token**: Include in Authorization header for protected endpoints
3. **Refresh**: Extend token expiration before it expires
4. **Logout**: Revoke specific or all tokens

## API Endpoints

### Authentication Endpoints

#### POST /api/auth/login
Authenticate user and receive access token.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password",
  "device_name": "mobile_app"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "customer",
    "avatar_url": "https://ui-avatars.com/api/..."
  },
  "token": "1|abc123...",
  "expires_at": "2025-07-09T10:30:00.000000Z"
}
```

#### GET /api/auth/user
Get authenticated user profile.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "customer",
    "avatar_url": "https://ui-avatars.com/api/...",
    "offices": [
      {
        "id": 1,
        "name": "Technical Support"
      }
    ]
  }
}
```

#### POST /api/auth/logout
Revoke current access token.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "message": "Successfully logged out"
}
```

#### POST /api/auth/logout-all
Revoke all user's access tokens.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "message": "All sessions terminated"
}
```

#### POST /api/auth/refresh
Refresh access token with new expiration.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "token": "2|def456...",
  "expires_at": "2025-07-09T10:30:00.000000Z"
}
```

### Ticket Endpoints

#### GET /api/tickets
List tickets with filtering and pagination.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `search`: Search in subject and content
- `status_id`: Filter by status ID
- `priority_id`: Filter by priority ID
- `office_id`: Filter by office ID
- `assigned_to_id`: Filter by assigned agent
- `per_page`: Results per page (default: 15, max: 100)
- `page`: Page number

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "abc-123-def",
      "subject": "Login Issue",
      "content": "Cannot access my account",
      "creator": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com"
      },
      "office": {
        "id": 1,
        "name": "Technical Support"
      },
      "status": {
        "id": 1,
        "name": "Open",
        "color": "#ef4444"
      },
      "priority": {
        "id": 2,
        "name": "Medium",
        "color": "#f59e0b"
      },
      "created_at": "2025-06-09T10:30:00.000000Z",
      "updated_at": "2025-06-09T10:30:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 25,
    "per_page": 15,
    "last_page": 2
  }
}
```

#### POST /api/tickets
Create new ticket.

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
  "subject": "New ticket subject",
  "content": "Detailed description of the issue",
  "office_id": 1,
  "priority_id": 2
}
```

**Response:**
```json
{
  "data": {
    "id": 2,
    "uuid": "def-456-ghi",
    "subject": "New ticket subject",
    "content": "Detailed description of the issue",
    "creator": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    },
    "office": {
      "id": 1,
      "name": "Technical Support"
    },
    "status": {
      "id": 1,
      "name": "Open",
      "color": "#ef4444"
    },
    "priority": {
      "id": 2,
      "name": "Medium",
      "color": "#f59e0b"
    }
  },
  "message": "Ticket created successfully"
}
```

#### GET /api/tickets/{uuid}
Get specific ticket with replies and timeline.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "data": {
    "id": 1,
    "uuid": "abc-123-def",
    "subject": "Login Issue",
    "content": "Cannot access my account",
    "creator": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    },
    "office": {
      "id": 1,
      "name": "Technical Support"
    },
    "status": {
      "id": 1,
      "name": "Open",
      "color": "#ef4444"
    },
    "priority": {
      "id": 2,
      "name": "Medium",
      "color": "#f59e0b"
    },
    "replies": [
      {
        "id": 1,
        "content": "Thank you for contacting support...",
        "user": {
          "id": 2,
          "name": "Support Agent",
          "role": "agent"
        },
        "created_at": "2025-06-09T11:00:00.000000Z"
      }
    ],
    "attachments": [],
    "timeline": [
      {
        "id": 1,
        "action": "created",
        "description": "Ticket created by John Doe",
        "created_at": "2025-06-09T10:30:00.000000Z"
      }
    ]
  }
}
```

#### PUT /api/tickets/{uuid}
Update ticket (role-based field restrictions).

**Headers:** `Authorization: Bearer {token}`

**Customer Request:**
```json
{
  "subject": "Updated subject",
  "content": "Updated content"
}
```

**Agent/Admin Request:**
```json
{
  "subject": "Updated subject",
  "content": "Updated content",
  "ticket_status_id": 2,
  "ticket_priority_id": 3,
  "assigned_to_id": 5
}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "uuid": "abc-123-def",
    "subject": "Updated subject",
    // ... full ticket data
  },
  "message": "Ticket updated successfully"
}
```

#### DELETE /api/tickets/{uuid}
Delete ticket (admin only).

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "message": "Ticket deleted successfully"
}
```

### Utility Endpoints

#### GET /api/tickets/stats
Get ticket statistics for current user.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "data": {
    "total": 25,
    "open": 10,
    "closed": 15,
    "assigned_to_me": 5
  }
}
```

#### GET /api/tickets/form-data
Get form data for ticket creation/editing.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "data": {
    "offices": [
      {
        "id": 1,
        "name": "Technical Support"
      }
    ],
    "statuses": [
      {
        "id": 1,
        "name": "Open",
        "color": "#ef4444"
      }
    ],
    "priorities": [
      {
        "id": 1,
        "name": "Low",
        "color": "#10b981"
      }
    ]
  }
}
```

### Knowledge Base Endpoints

#### GET /api/knowledge-base/search
Search FAQs and knowledge base articles.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `q`: Search query
- `office_id`: Filter by office (optional)

**Response:**
```json
{
  "data": {
    "faqs": [
      {
        "id": 1,
        "question": "How to reset password?",
        "answer": "To reset your password...",
        "office": {
          "id": 1,
          "name": "Technical Support"
        }
      }
    ]
  }
}
```

#### GET /api/knowledge-base/trending
Get trending FAQs based on recent usage.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "data": {
    "faqs": [
      {
        "id": 1,
        "question": "How to reset password?",
        "answer": "To reset your password...",
        "usage_count": 45,
        "office": {
          "id": 1,
          "name": "Technical Support"
        }
      }
    ]
  }
}
```

#### GET /api/knowledge-base/tickets/{ticket}/suggestions
Get FAQ suggestions for specific ticket.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "data": {
    "suggestions": [
      {
        "id": 1,
        "question": "How to reset password?",
        "answer": "To reset your password...",
        "relevance_score": 0.85,
        "office": {
          "id": 1,
          "name": "Technical Support"
        }
      }
    ]
  }
}
```

### System Endpoints

#### GET /api/status
Public API status endpoint.

**Response:**
```json
{
  "api": "TIKM API",
  "version": "1.0.0",
  "status": "active",
  "documentation": "https://yourdomain.com/api/docs"
}
```

#### GET /api/health
API health check (requires authentication).

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "status": "ok",
  "timestamp": "2025-06-09T10:30:00.000000Z",
  "version": "1.0.0"
}
```

## Authorization Model

### Role-Based Access Control

**Customer:**
- Can view and create their own tickets
- Can update basic fields of their own tickets (subject, content)
- Cannot view other customers' tickets
- Cannot assign or change status/priority

**Agent:**
- Can view tickets from assigned offices
- Can update all ticket fields including status, priority, assignment
- Can create tickets on behalf of customers
- Cannot view tickets from offices they're not assigned to

**Admin:**
- Full access to all tickets and operations
- Can manage all ticket fields and assignments
- Can delete tickets
- Access to all API endpoints

### Security Features

- **Token Expiration**: 30-day automatic expiration
- **Device Management**: Separate tokens per device
- **Rate Limiting**: Built-in protection against abuse
- **Input Validation**: Comprehensive request validation
- **SQL Injection Protection**: Laravel's query builder and Eloquent ORM
- **XSS Protection**: Automatic output escaping

## Error Handling

### HTTP Status Codes

- `200 OK`: Successful request
- `201 Created`: Resource created successfully
- `400 Bad Request`: Invalid request format
- `401 Unauthorized`: Authentication required or failed
- `403 Forbidden`: Access denied (insufficient permissions)
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation failed
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

### Error Response Format

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password field is required."
    ]
  }
}
```

## Rate Limiting

- **Authentication endpoints**: 5 requests per minute
- **API endpoints**: 60 requests per minute per user
- **Global limit**: 1000 requests per minute per IP

## Testing

The API includes comprehensive test coverage:

- **Authentication Tests**: Login, logout, token management
- **Authorization Tests**: Role-based access control
- **CRUD Tests**: Full ticket lifecycle operations
- **Validation Tests**: Input validation and error handling
- **Integration Tests**: End-to-end API workflows

Run tests with:
```bash
php artisan test tests/Feature/Api/
```

## Mobile App Integration

### Recommended Architecture

1. **Authentication Storage**: Securely store tokens using device keychain/keystore
2. **Token Refresh**: Implement automatic token refresh before expiration
3. **Offline Support**: Cache recent tickets and sync when online
4. **Push Notifications**: Integrate with notification service for real-time updates
5. **File Uploads**: Support image/document attachments via multipart requests

### Flutter Example

```dart
class TikmApiClient {
  static const String baseUrl = 'https://yourdomain.com/api';
  String? _token;
  
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
        'device_name': 'flutter_app',
      }),
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      _token = data['token'];
      return data;
    }
    throw Exception('Login failed');
  }
  
  Future<List<Ticket>> getTickets() async {
    final response = await http.get(
      Uri.parse('$baseUrl/tickets'),
      headers: {
        'Authorization': 'Bearer $_token',
        'Accept': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List)
          .map((ticket) => Ticket.fromJson(ticket))
          .toList();
    }
    throw Exception('Failed to load tickets');
  }
}
```

## API Versioning

The current API is version 1.0. Future versions will:
- Maintain backward compatibility where possible
- Use URL versioning (`/api/v2/`) for breaking changes
- Provide migration guides for deprecated endpoints
- Support multiple versions simultaneously during transition periods

## Support and Documentation

- **API Status**: Monitor at `/api/status`
- **Health Check**: Monitor at `/api/health`
- **Issue Reporting**: GitHub repository issues
- **Updates**: API changelog in release notes