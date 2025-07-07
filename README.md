# GrantCosmetic API Documentation

This document provides a detailed overview of the public API endpoints available in the GrantCosmetic project. The API is built using Laravel and provides resources for cosmetics, brands, categories, and booking transactions.

---

## Table of Contents

- [Authentication](#authentication)
- [Endpoints](#endpoints)
  - [User](#user)
  - [Cosmetics](#cosmetics)
  - [Categories](#categories)
  - [Brands](#brands)
  - [Booking Transactions](#booking-transactions)
- [Query Parameters & Filtering](#query-parameters--filtering)
- [Response Format](#response-format)
- [Error Handling](#error-handling)
- [Example Requests](#example-requests)
- [Notes](#notes)

---

## Authentication

- The `/api/user` endpoint requires authentication using [Laravel Sanctum](https://laravel.com/docs/sanctum).
- All other endpoints are public unless otherwise noted.
- To authenticate, send a valid bearer token in the `Authorization` header:  
  `Authorization: Bearer {token}`

---

## Endpoints

### User

#### Get Current User

- **Endpoint:** `GET /api/user`
- **Description:** Returns the authenticated user's information.
- **Authentication:** Required (`auth:sanctum`)
- **Response:** Authenticated user object.

---

### Cosmetics

#### List Cosmetics

- **Endpoint:** `GET /api/cosmetic`
- **Description:** List all cosmetics. Supports filtering.
- **Query Parameters:**
  - `category_id` (optional): Filter by category ID.
  - `brand_id` (optional): Filter by brand ID.
  - `is_popular` (optional): Filter for popular cosmetics (`1` or `0`).
  - `limit` (optional): Limit the number of results.
- **Response:**  
  ```json
  [
    {
      "id": 1,
      "name": "Cosmetic Name",
      "slug": "cosmetic-name",
      "brand": {...},
      "category": {...},
      // ...other fields
    }
  ]
  ```

#### Get Cosmetic Details

- **Endpoint:** `GET /api/cosmetic/{slug}`
- **Description:** Get a single cosmetic by its slug.
- **Response:**  
  ```json
  {
    "id": 1,
    "name": "Cosmetic Name",
    "slug": "cosmetic-name",
    "brand": { ... },
    "category": { ... },
    "benefits": [ ... ],
    "testimonials": [ ... ],
    "photos": [ ... ],
    // ...other fields
  }
  ```

#### Create Cosmetic

- **Endpoint:** `POST /api/cosmetic`
- **Description:** Create a new cosmetic.
- **Request Headers:**  
  `Content-Type: application/json`
- **Request Body Example:**  
  ```json
  {
    "name": "New Cosmetic",
    "slug": "new-cosmetic",
    "brand_id": 1,
    "category_id": 2,
    "is_popular": 1,
    // ...other fields as required
  }
  ```
- **Response:**  
  Returns the created cosmetic object, including all attributes and nested relations.
  ```json
  {
    "id": 2,
    "name": "New Cosmetic",
    "slug": "new-cosmetic",
    "brand": { ... },
    "category": { ... },
    // ...other fields
  }
  ```

#### Update Cosmetic

- **Endpoint:** `PUT /api/cosmetic/{id}`
- **Description:** Update an existing cosmetic by ID.
- **Request Headers:**  
  `Content-Type: application/json`
- **Request Body Example:**  
  ```json
  {
    "name": "Updated Name",
    "is_popular": 0
    // ...other fields to update
  }
  ```
- **Response:**  
  Updated cosmetic object.

#### Delete Cosmetic

- **Endpoint:** `DELETE /api/cosmetic/{id}`
- **Description:** Delete a cosmetic by ID.
- **Response:**  
  On success, returns a JSON message:
  ```json
  {
    "message": "Cosmetic deleted successfully."
  }
  ```
  On failure, returns an error object.

---

### Categories

#### List Categories

- **Endpoint:** `GET /api/category`
- **Description:** List all categories. Supports limiting results.
- **Query Parameters:**
  - `limit` (optional): Limit the number of results.
- **Response:**  
  ```json
  [
    {
      "id": 1,
      "name": "Skincare",
      "cosmetics_count": 10,
      // ...other fields
    }
  ]
  ```

#### Get Category Details

- **Endpoint:** `GET /api/category/{slug}`
- **Description:** Get a single category by its slug, including related cosmetics and brands.
- **Response:**  
  ```json
  {
    "id": 1,
    "name": "Skincare",
    "cosmetics_count": 10,
    "cosmetics": [
      { ... }
    ],
    "popularCosmetics": [
      { ... }
    ],
    // ...other fields
  }
  ```

#### Create Category

- **Endpoint:** `POST /api/category`
- **Description:** Create a new category.
- **Request Headers:**  
  `Content-Type: application/json`
- **Request Body Example:**  
  ```json
  {
    "name": "Body Care",
    "slug": "body-care"
    // ...other fields as required
  }
  ```
- **Response:**  
  Returns the created category object.

#### Update Category

- **Endpoint:** `PUT /api/category/{id}`
- **Description:** Update an existing category by ID.
- **Request Headers:**  
  `Content-Type: application/json`
- **Request Body Example:**  
  ```json
  {
    "name": "Updated Category Name"
    // ...other fields
  }
  ```
- **Response:**  
  Updated category object.

#### Delete Category

- **Endpoint:** `DELETE /api/category/{id}`
- **Description:** Delete a category by ID.
- **Response:**  
  ```json
  {
    "message": "Category deleted successfully."
  }
  ```

---

### Brands

#### List Brands

- **Endpoint:** `GET /api/brand`
- **Description:** List all brands.
- **Response:**  
  ```json
  [
    {
      "id": 1,
      "name": "Brand Name",
      // ...other fields
    }
  ]
  ```

#### Get Brand Details

- **Endpoint:** `GET /api/brand/{slug}`
- **Description:** Get a single brand by its slug.
- **Response:**  
  ```json
  {
    "id": 1,
    "name": "Brand Name",
    // ...other fields
  }
  ```

#### Create Brand

- **Endpoint:** `POST /api/brand`
- **Description:** Create a new brand.
- **Request Headers:**  
  `Content-Type: application/json`
- **Request Body Example:**  
  ```json
  {
    "name": "Brand New",
    "slug": "brand-new"
    // ...other fields as required
  }
  ```
- **Response:**  
  Returns the created brand object.

#### Update Brand

- **Endpoint:** `PUT /api/brand/{id}`
- **Description:** Update an existing brand by ID.
- **Request Headers:**  
  `Content-Type: application/json`
- **Request Body Example:**  
  ```json
  {
    "name": "New Brand Name"
    // ...other fields
  }
  ```
- **Response:**  
  Updated brand object.

#### Delete Brand

- **Endpoint:** `DELETE /api/brand/{id}`
- **Description:** Delete a brand by ID.
- **Response:**  
  ```json
  {
    "message": "Brand deleted successfully."
  }
  ```

---

### Booking Transactions

#### Create Booking Transaction

- **Endpoint:** `POST /api/booking-transaction`
- **Description:** Create a new booking transaction.
- **Request Headers:**  
  `Content-Type: application/json`
- **Request Body Example:**  
  ```json
  {
    "cosmetic_id": 1,
    "user_id": 5,
    "date": "2025-08-01",
    // ...other fields as required
  }
  ```
- **Response:**  
  Returns the created booking transaction object.
  ```json
  {
    "id": 20,
    "cosmetic_id": 1,
    "user_id": 5,
    "date": "2025-08-01",
    // ...other fields
  }
  ```

#### Check Booking Transaction

- **Endpoint:** `POST /api/check-transaction`
- **Description:** Check details of a booking transaction.
- **Request Headers:**  
  `Content-Type: application/json`
- **Request Body Example:**  
  ```json
  {
    "transaction_id": "XYZ123"
  }
  ```
- **Response:**  
  Booking transaction details.
  ```json
  {
    "id": 20,
    "cosmetic_id": 1,
    "user_id": 5,
    "date": "2025-08-01",
    "status": "confirmed",
    // ...other fields
  }
  ```

---

## Query Parameters & Filtering

Most `GET` list endpoints support filtering and limiting results via query parameters.  
Example:  
`GET /api/cosmetic?category_id=3&is_popular=1&limit=10`

---

## Response Format

- All responses are returned as JSON.
- Collections are wrapped using Laravel API Resources for consistency.
- Typical structure for collections:
  ```json
  [
    {
      "id": 1,
      "name": "Cosmetic Name",
      // ...other fields
    },
    {
      "id": 2,
      "name": "Another Cosmetic",
      // ...other fields
    }
  ]
  ```
- For detail endpoints, nested relations may be included (e.g., `brand`, `category`, etc.).

---

## Error Handling

- Errors are returned as JSON objects with an `error` message and appropriate HTTP status code.
  ```json
  {
    "error": "Not Found"
  }
  ```
- Validation errors return a 422 status and details about the failed fields:
  ```json
  {
    "message": "The given data was invalid.",
    "errors": {
      "field_name": [
        "The field_name field is required."
      ]
    }
  }
  ```

---

## Example Requests

### List Cosmetics

```http
GET /api/cosmetic?limit=5
```

### Get a Category by Slug

```http
GET /api/category/skincare
```

### Create a Booking Transaction

```http
POST /api/booking-transaction
Content-Type: application/json

{
  "cosmetic_id": 1,
  "user_id": 5,
  "date": "2025-08-01"
}
```

### Delete a Brand

```http
DELETE /api/brand/4
```

**Response:**
```json
{
  "message": "Brand deleted successfully."
}
```

### Check a Transaction

```http
POST /api/check-transaction
Content-Type: application/json

{
  "transaction_id": "XYZ123"
}
```

---

## Notes

- All endpoints follow RESTful conventions.
- For details on request and response fields, refer to the source code for the relevant API Resources and Controllers.
- For additional configuration (e.g., third-party integrations, authentication), see `config/services.php` and `config/sanctum.php`.
- This API is part of the GrantCosmetic Laravel application.

---
