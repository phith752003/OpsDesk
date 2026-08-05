# OpsDesk — RESTful API Specification

All API endpoints are prefixed with `/api/v1` and use standard HTTP response codes and JSON formatting.

## 1. Authentication Endpoints

### `POST /api/v1/auth/login`
Authenticates a user and issues a Laravel Sanctum personal access token.

- **Request Body**:
  ```json
  {
    "email": "agent@opsdesk.local",
    "password": "Password123!"
  }
  ```
- **Response `200 OK`**:
  ```json
  {
    "token": "1|sanctum_token_string...",
    "user": {
      "id": 2,
      "name": "Alex Mercer",
      "email": "agent@opsdesk.local",
      "role": "agent",
      "department": { "id": 1, "name": "IT Infrastructure" }
    }
  }
  ```

### `GET /api/v1/auth/me`
Retrieves currently authenticated user profile.
- **Headers**: `Authorization: Bearer <token>`
- **Response `200 OK`**: User profile object.

### `POST /api/v1/auth/logout`
Revokes current access token.
- **Headers**: `Authorization: Bearer <token>`
- **Response `200 OK`**: `{"message": "Logged out successfully"}`

---

## 2. Ticket Management Endpoints

### `GET /api/v1/tickets`
Returns paginated list of tickets. Supports query filters.
- **Headers**: `Authorization: Bearer <token>`
- **Query Parameters**:
  - `status`: `open`, `assigned`, `in_progress`, `pending`, `resolved`, `closed`
  - `priority`: `low`, `medium`, `high`, `critical`
  - `category_id`: integer
  - `agent_id`: integer
  - `search`: string (matches reference_no, title, description)
  - `page`: integer (default 1)
  - `per_page`: integer (default 15)
- **Response `200 OK`**: Paginated array of `TicketResource`.

### `POST /api/v1/tickets`
Creates a new support ticket.
- **Request Body**:
  ```json
  {
    "title": "VPN connection drops every 15 minutes",
    "description": "When connecting through the EMEA gateway, authentication resets repeatedly.",
    "category_id": 2,
    "priority": "high"
  }
  ```
- **Response `201 Created`**: Single `TicketResource`.

### `GET /api/v1/tickets/{id}`
Returns complete details of a single ticket including comments, attachments, and audit trail.
- **Response `200 OK`**: `TicketDetailResource`.

### `PATCH /api/v1/tickets/{id}/status`
Transitions a ticket's status.
- **Request Body**:
  ```json
  {
    "status": "in_progress"
  }
  ```
- **Response `200 OK`**: Updated `TicketResource`.

### `PATCH /api/v1/tickets/{id}/assign`
Assigns a ticket to a support agent.
- **Request Body**:
  ```json
  {
    "agent_id": 3
  }
  ```
- **Response `200 OK`**: Updated `TicketResource`.

---

## 3. Communication & Comments

### `POST /api/v1/tickets/{id}/comments`
Adds a discussion comment or internal engineering note.
- **Request Body**:
  ```json
  {
    "body": "Checked firewall logs. User's token lease was set to 900 seconds instead of 8 hours.",
    "is_internal": true
  }
  ```
- **Response `201 Created`**: `CommentResource`.

---

## 4. SLA & Operational Monitoring

### `GET /api/v1/sla/policies`
Returns defined SLA policies by priority.

### `GET /api/v1/sla/at-risk`
Returns tickets that are within 30 minutes of breaching response or resolution deadlines, or already breached.

---

## 5. Dashboard & Analytics

### `GET /api/v1/dashboard/metrics`
Returns core operational counters:
- `open_tickets_count`
- `unassigned_tickets_count`
- `sla_compliance_percentage`
- `avg_response_time_minutes`
- `avg_resolution_time_minutes`
- `tickets_by_priority`: `{ "low": 4, "medium": 12, "high": 5, "critical": 2 }`
- `tickets_by_status`: `{ "open": 6, "assigned": 8, "in_progress": 7, "resolved": 14 }`

### `GET /api/v1/reports/workload`
Returns workload distribution across all support agents (active tickets, completed today, breach count).

### `GET /api/v1/reports/trends`
Returns ticket volume and resolution time trend data for ECharts graphs.

---

## 6. Administration

### `GET /api/v1/admin/users` (Admin only)
List system users with role assignments and department associations.

### `POST /api/v1/admin/users` (Admin only)
Create a new user with designated role.
