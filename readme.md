# Custom API Documentation

## 1. Academic Ranks

### 1.1 List Academic Ranks

- **Method:** `GET`
- **Endpoint:**  
  ```
  https://scientist.local/wp-json/scientist/v1/academicranks/list
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": [
        {
            "id": "1",
            "name": "Giáo sư",
            "abbreviation": "GS."
        },
        {
            "id": "2",
            "name": "Phó Giáo sư",
            "abbreviation": "PGS."
        }
    ]
}
```
</details>

<details>
<summary>Example Error (Database error)</summary>

```json
{
    "status": "error",
    "message": "Database error: ...details..."
}
```
</details>

#### Success and Error Codes

| Code | Meaning                | Example Response                                      |
|------|------------------------|------------------------------------------------------|
| 200  | Success                | `{ "status": "success", "data": [...] }`             |
| 500  | Database/Server Error  | `{ "status": "error", "message": "Database error: ..." }` |

---

### 1.2 Add Academic Rank

- **Method:** `POST`
- **Endpoint:**  
  ```
  https://scientist.local/wp-json/scientist/v1/academicranks/add
  ```
- **Body:**  
  ```json
  {
      "name": "Tên học hàm",
      "abbreviation": "Viết tắt"
  }
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "message": "Academic rank added successfully"
}
```
</details>

<details>
<summary>Example Error (Invalid input)</summary>

```json
{
    "status": "error",
    "message": "Invalid input"
}
```
</details>

<details>
<summary>Example Error (Already exists)</summary>

```json
{
    "status": "error",
    "message": "Academic rank already exists"
}
```
</details>

<details>
<summary>Example Error (Database error)</summary>

```json
{
    "status": "error",
    "message": "Error adding academic rank: ...details..."
}
```
</details>

#### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "message": "Academic rank added successfully" }` |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input" }`     |
| 409  | Academic rank already exists   | `{ "status": "error", "message": "Academic rank already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Error adding academic rank: ..." }` |

---

## 2. Majors

### 2.1 List Majors

- **Method:** `GET`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/major/list
  ```

##### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": [
        {
            "major_code": 1,
            "major_name": "Khoa học máy tính",
            "groups": [
                {
                    "group_code": "A",
                    "group_name": "Nhóm A",
                    "specializations": [
                        {
                            "specialize_code": "A1",
                            "specialize_name": "Chuyên ngành 1"
                        }
                    ]
                }
            ]
        }
    ]
}
```
</details>

<details>
<summary>Example Error (Database error)</summary>

```json
{
    "status": "error",
    "message": "Database error: ...details..."
}
```
</details>

##### Success and Error Codes

| Code | Meaning                | Example Response                                      |
|------|------------------------|------------------------------------------------------|
| 200  | Success                | `{ "status": "success", "data": [...] }`             |
| 500  | Database/Server Error  | `{ "status": "error", "message": "Database error: ..." }` |

---

### 2.2 List Specialization Groups by Major

- **Method:** `GET`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/major/{major_code}/specialization-group
  ```

##### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": [
        {
            "group_code": "A",
            "group_name": "Nhóm A"
        }
    ]
}
```
</details>

<details>
<summary>Example Error (Missing major code)</summary>

```json
{
    "status": "error",
    "message": "Missing major code"
}
```
</details>

<details>
<summary>Example Error (Major not found)</summary>

```json
{
    "status": "error",
    "message": "Major not found"
}
```
</details>

<details>
<summary>Example Error (Database error)</summary>

```json
{
    "status": "error",
    "message": "Database error: ...details..."
}
```
</details>

##### Success and Error Codes

| Code | Meaning                | Example Response                                      |
|------|------------------------|------------------------------------------------------|
| 200  | Success                | `{ "status": "success", "data": [...] }`             |
| 400  | Missing major code     | `{ "status": "error", "message": "Missing major code" }` |
| 404  | Major not found        | `{ "status": "error", "message": "Major not found" }` |
| 500  | Database/Server Error  | `{ "status": "error", "message": "Database error: ..." }` |

---

### 2.3 List Specializations by Major and Group

- **Method:** `GET`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/major/{major_code}/specialization-group/{group_code}
  ```

##### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": [
        {
            "specialization_code": "A1",
            "specialization_name": "Chuyên ngành 1"
        }
    ]
}
```
</details>

<details>
<summary>Example Error (Missing code(s))</summary>

```json
{
    "status": "error",
    "message": "Missing code(s)"
}
```
</details>

<details>
<summary>Example Error (Major not found)</summary>

```json
{
    "status": "error",
    "message": "Major not found"
}
```
</details>

<details>
<summary>Example Error (Specialization group not found)</summary>

```json
{
    "status": "error",
    "message": "Specialization group not found"
}
```
</details>

<details>
<summary>Example Error (Database error)</summary>

```json
{
    "status": "error",
    "message": "Database error: ...details..."
}
```
</details>

##### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": [...] }`                 |
| 400  | Missing code(s)                | `{ "status": "error", "message": "Missing code(s)" }`   |
| 404  | Major/group not found          | `{ "status": "error", "message": "Major not found" }` or `{ "status": "error", "message": "Specialization group not found" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Database error: ..." }` |

---

### 2.4 Get Specialization by Major, Group, and Specialization Code

- **Method:** `GET`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/major/{major_code}/specialization-group/{group_code}/{specialization_code}
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "specialize_code": "A1",
        "specialization_name": "Chuyên ngành 1"
    }
}
```
</details>

<details>
<summary>Example Error (Missing code(s))</summary>

```json
{
    "status": "error",
    "message": "Missing code(s)"
}
```
</details>

<details>
<summary>Example Error (Specialization not found)</summary>

```json
{
    "status": "error",
    "message": "Specialization not found"
}
```
</details>

<details>
<summary>Example Error (Database error)</summary>

```json
{
    "status": "error",
    "message": "Database error: ...details..."
}
```
</details>

##### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": {...} }`                |
| 400  | Missing code(s)                | `{ "status": "error", "message": "Missing code(s)" }`   |
| 404  | Not found                      | `{ "status": "error", "message": "Specialization not found" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Database error: ..." }` |

---

### 2.5 Add Major

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/major/add
  ```
- **Body:**
  ```json
  {
      "major_name": "Tên ngành",
      "major_code": "M01"
  }
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "major_id": 123
    }
}
```
</details>

<details>
<summary>Example Error (Major code already exists)</summary>

```json
{
    "status": "error",
    "message": "Major code already exists"
}
```
</details>

<details>
<summary>Example Error (Invalid input)</summary>

```json
{
    "status": "error",
    "message": "Invalid input data"
}
```
</details>

<details>
<summary>Example Error (Database error)</summary>

```json
{
    "status": "error",
    "message": "Failed to add major"
}
```
</details>

##### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": { "major_id": 123 } }`  |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input data" }`|
| 409  | Major code already exists      | `{ "status": "error", "message": "Major code already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Failed to add major" }` |

---

### 2.6 Add Specialization Group

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/specialization-group/add
  ```
- **Body:**
  ```json
  {
      "major_code": "M01",
      "group_name": "Nhóm A",
      "group_code": "A"
  }
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "group_id": 456
    }
}
```
</details>

<details>
<summary>Example Error (Specialization group code already exists)</summary>

```json
{
    "status": "error",
    "message": "Specialization group code already exists"
}
```
</details>

<details>
<summary>Example Error (Major not found)</summary>

```json
{
    "status": "error",
    "message": "Major not found"
}
```
</details>

<details>
<summary>Example Error (Invalid input)</summary>

```json
{
    "status": "error",
    "message": "Invalid input data"
}
```
</details>

<details>
<summary>Example Error (Database error)</summary>

```json
{
    "status": "error",
    "message": "Failed to add specialization group"
}
```
</details>

##### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": { "group_id": 456 } }`  |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input data" }`|
| 404  | Major not found                | `{ "status": "error", "message": "Major not found" }`   |
| 409  | Group code already exists      | `{ "status": "error", "message": "Specialization group code already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Failed to add specialization group" }` |

---

### 2.7 Add Specialization

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/specialization/add
  ```
- **Body:**
  ```json
  {
      "major_code": "M01",
      "group_code": "A",
      "specialization_name": "Chuyên ngành 1",
      "specialization_code": "A1"
  }
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "specialization_id": 789
    }
}
```
</details>

<details>
<summary>Example Error (Specialization code already exists)</summary>

```json
{
    "status": "error",
    "message": "Specialization code already exists"
}
```
</details>

<details>
<summary>Example Error (Major not found)</summary>

```json
{
    "status": "error",
    "message": "Major not found"
}
```
</details>

<details>
<summary>Example Error (Specialization group not found)</summary>

```json
{
    "status": "error",
    "message": "Specialization group not found"
}
```
</details>

<details>
<summary>Example Error (Invalid input)</summary>

```json
{
    "status": "error",
    "message": "Invalid input data"
}
```
</details>

<details>
<summary>Example Error (Database error)</summary>

```json
{
    "status": "error",
    "message": "Failed to add specialization"
}
```
</details>

##### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": { "specialization_id": 789 } }` |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input data" }`|
| 404  | Major/group not found          | `{ "status": "error", "message": "Major not found" }` or `{ "status": "error", "message": "Specialization group not found" }` |
| 409  | Specialization code exists     | `{ "status": "error", "message": "Specialization code already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Failed to add specialization" }` |

---

### 2.8 Update Major

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/major/update/{major_code}
  ```
- **Body:** (any or both fields)
  ```json
  {
      "new_major_name": "Tên ngành mới",
      "new_major_code": "M02"
  }
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "message": "Updated successfully",
        "updated_fields": {
            "field_name": "Tên ngành mới",
            "field_code": "M02"
        }
    }
}
```
</details>

<details>
<summary>Example No Change</summary>

```json
{
    "status": "success",
    "data": {
        "success": false,
        "message": "No changes detected"
    }
}
```
</details>

<details>
<summary>Example Error (Major code already exists)</summary>

```json
{
    "status": "error",
    "message": "Major code already exists"
}
```
</details>

<details>
<summary>Example Error (Major not found)</summary>

```json
{
    "status": "error",
    "message": "Major not found"
}
```
</details>

<details>
<summary>Example Error (Invalid input)</summary>

```json
{
    "status": "error",
    "message": "Invalid input data"
}
```
</details>

<details>
<summary>Example Error (Database error)</summary>

```json
{
    "status": "error",
    "message": "Failed to update major"
}
```
</details>

##### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success/No change              | `{ "status": "success", "data": { ... } }`              |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input data" }`|
| 404  | Major not found                | `{ "status": "error", "message": "Major not found" }`   |
| 409  | Major code already exists      | `{ "status": "error", "message": "Major code already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Failed to update major" }` |

---

### 2.9 Update Specialization Group

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/specialization-group/update/{group_code}
  ```
- **Body:** (any or both fields)
  ```json
  {
      "new_group_name": "Tên nhóm mới",
      "new_group_code": "B"
  }
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "message": "Updated successfully",
        "updated_fields": {
            "field_name": "Tên nhóm mới",
            "field_code": "B"
        }
    }
}
```
</details>

<details>
<summary>Example No Change</summary>

```json
{
    "status": "success",
    "data": {
        "success": false,
        "message": "No changes detected"
    }
}
```
</details>

<details>
<summary>Example Error (Specialization group code already exists)</summary>

```json
{
    "status": "error",
    "message": "Specialization group code already exists"
}
```
</details>

<details>
<summary>Example Error (Group not found)</summary>

```json
{
    "status": "error",
    "message": "Specialization group not found"
}
```
</details>

<details>
<summary>Example Error (Invalid input)</summary>

```json
{
    "status": "error",
    "message": "Invalid input data"
}
```
</details>

<details>
<summary>Example Error (Database error)</summary>

```json
{
    "status": "error",
    "message": "Failed to update specialization group"
}
```
</details>

##### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success/No change              | `{ "status": "success", "data": { ... } }`              |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input data" }`|
| 404  | Group not found                | `{ "status": "error", "message": "Specialization group not found" }` |
| 409  | Group code already exists      | `{ "status": "error", "message": "Specialization group code already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Failed to update specialization group" }` |

---

### 2.10 Update Specialization

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/specialization/update/{specialization_code}
  ```
- **Body:** (any or both fields)
  ```json
  {
      "new_specialization_name": "Tên chuyên ngành mới",
      "new_specialization_code": "A2"
  }
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "message": "Updated successfully",
        "updated_fields": {
            "field_name": "Tên chuyên ngành mới",
            "field_code": "A2"
        }
    }
}
```
</details>

<details>
<summary>Example No Change</summary>

```json
{
    "status": "success",
    "data": {
        "success": false,
        "message": "No changes detected"
    }
}
```
</details>

<details>
<summary>Example Error (Specialization code already exists)</summary>

```json
{
    "status": "error",
    "message": "Specialization code already exists"
}
```
</details>

<details>
<summary>Example Error (Specialization not found)</summary>

```json
{
    "status": "error",
    "message": "Specialization not found"
}
```
</details>

<details>
<summary>Example Error (Invalid input)</summary>

```json
{
    "status": "error",
    "message": "Invalid input data"
}
```
</details>

<details>
<summary>Example Error (Database error)</summary>

```json
{
    "status": "error",
    "message": "Failed to update specialization"
}
```
</details>

##### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success/No change              | `{ "status": "success", "data": { ... } }`              |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input data" }`|
| 404  | Specialization not found       | `{ "status": "error", "message": "Specialization not found" }` |
| 409  | Specialization code exists     | `{ "status": "error", "message": "Specialization code already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Failed to update specialization" }` |

---