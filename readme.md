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

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": [
        {
            "major_code": 901,
            "major_name": "Khoa học máy tính",
            "groups": [
                {
                    "group_code": "90101",
                    "group_name": "Nhóm A",
                    "specializations": [
                        {
                            "specialize_code": "9010101",
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

#### Success and Error Codes

| Code | Meaning                | Example Response                                      |
|------|------------------------|------------------------------------------------------|
| 200  | Success                | `{ "status": "success", "data": [...] }`             |
| 500  | Database/Server Error  | `{ "status": "error", "message": "Database error: ..." }` |

---

### 2.2 Get Major/Group/Specialization by Code

- **Method:** `GET`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/major/{field_code}
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "id": 1,
        "field_name": "Khoa học máy tính",
        "field_code": "901",
        "level": "major",
        "parent_id": null
    }
}
```
</details>

<details>
<summary>Example Error (Missing field code)</summary>

```json
{
    "status": "error",
    "message": "Missing field code"
}
```
</details>

<details>
<summary>Example Error (Code must start with 9)</summary>

```json
{
    "status": "error",
    "message": "Code must start with 9"
}
```
</details>

<details>
<summary>Example Error (Field not found)</summary>

```json
{
    "status": "error",
    "message": "Field not found"
}
```
</details>

#### Success and Error Codes

| Code | Meaning                | Example Response                                      |
|------|------------------------|------------------------------------------------------|
| 200  | Success                | `{ "status": "success", "data": {...} }`             |
| 400  | Missing/invalid code   | `{ "status": "error", "message": "Missing field code" }`<br>`{ "status": "error", "message": "Code must start with 9" }` |
| 404  | Not found              | `{ "status": "error", "message": "Field not found" }`|
| 500  | Database/Server Error  | `{ "status": "error", "message": "Database error: ..." }` |

---

### 2.3 List Specialization Groups by Major

- **Method:** `GET`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/major/{major_code}/specialization-group
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": [
        {
            "group_code": "90101",
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
<summary>Example Error (Major code must be 3 digits and start with 9)</summary>

```json
{
    "status": "error",
    "message": "Major code must be 3 digits and start with 9"
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

#### Success and Error Codes

| Code | Meaning                | Example Response                                      |
|------|------------------------|------------------------------------------------------|
| 200  | Success                | `{ "status": "success", "data": [...] }`             |
| 400  | Missing/invalid code   | `{ "status": "error", "message": "Missing major code" }`<br>`{ "status": "error", "message": "Major code must be 3 digits and start with 9" }` |
| 404  | Major not found        | `{ "status": "error", "message": "Major not found" }`|
| 500  | Database/Server Error  | `{ "status": "error", "message": "Database error: ..." }` |

---

### 2.4 List Specializations by Major and Group

- **Method:** `GET`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/major/{major_code}/specialization-group/{group_code}
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": [
        {
            "specialization_code": "9010101",
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
<summary>Example Error (Major code must be 3 digits and start with 9)</summary>

```json
{
    "status": "error",
    "message": "Major code must be 3 digits and start with 9"
}
```
</details>

<details>
<summary>Example Error (Group code must be 5 digits)</summary>

```json
{
    "status": "error",
    "message": "Group code must be 5 digits"
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

#### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": [...] }`                |
| 400  | Missing/invalid code           | `{ "status": "error", "message": "Missing code(s)" }`<br>`{ "status": "error", "message": "Major code must be 3 digits and start with 9" }`<br>`{ "status": "error", "message": "Group code must be 5 digits" }` |
| 404  | Major/group not found          | `{ "status": "error", "message": "Major not found" }`<br>`{ "status": "error", "message": "Specialization group not found" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Database error: ..." }` |

---

### 2.5 Get Specialization by Major, Group, and Specialization Code

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
        "specialize_code": "9010101",
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
<summary>Example Error (Major code must be 3 digits and start with 9)</summary>

```json
{
    "status": "error",
    "message": "Major code must be 3 digits and start with 9"
}
```
</details>

<details>
<summary>Example Error (Group code must be 5 digits)</summary>

```json
{
    "status": "error",
    "message": "Group code must be 5 digits"
}
```
</details>

<details>
<summary>Example Error (Specialization code must be 7 digits)</summary>

```json
{
    "status": "error",
    "message": "Specialization code must be 7 digits"
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

#### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": {...} }`                |
| 400  | Missing/invalid code           | `{ "status": "error", "message": "Missing code(s)" }`<br>`{ "status": "error", "message": "Major code must be 3 digits and start with 9" }`<br>`{ "status": "error", "message": "Group code must be 5 digits" }`<br>`{ "status": "error", "message": "Specialization code must be 7 digits" }` |
| 404  | Not found                      | `{ "status": "error", "message": "Specialization not found" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Database error: ..." }` |

---

### 2.6 Add Major

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/major/add
  ```
- **Body:**
  ```json
  {
      "major_name": "Tên ngành",
      "major_code": "901"
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
<summary>Example Error (Major code must be 3 digits and start with 9)</summary>

```json
{
    "status": "error",
    "message": "Major code must be 3 digits and start with 9"
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

#### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": { "major_id": 123 } }`  |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input data" }`<br>`{ "status": "error", "message": "Major code must be 3 digits and start with 9" }` |
| 409  | Major code already exists      | `{ "status": "error", "message": "Major code already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Failed to add major" }` |

---

### 2.7 Add Specialization Group

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/specialization-group/add
  ```
- **Body:**
  ```json
  {
      "major_code": "901",
      "group_name": "Nhóm A",
      "group_code": "90101"
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
<summary>Example Error (Group code must be 5 digits)</summary>

```json
{
    "status": "error",
    "message": "Group code must be 5 digits"
}
```
</details>

<details>
<summary>Example Error (Major code must be 3 digits and start with 9)</summary>

```json
{
    "status": "error",
    "message": "Major code must be 3 digits and start with 9"
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

#### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": { "group_id": 456 } }`  |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input data" }`<br>`{ "status": "error", "message": "Group code must be 5 digits" }`<br>`{ "status": "error", "message": "Major code must be 3 digits and start with 9" }` |
| 404  | Major not found                | `{ "status": "error", "message": "Major not found" }`   |
| 409  | Group code already exists      | `{ "status": "error", "message": "Specialization group code already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Failed to add specialization group" }` |

---

### 2.8 Add Specialization

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/specialization/add
  ```
- **Body:**
  ```json
  {
      "major_code": "901",
      "group_code": "90101",
      "specialization_name": "Chuyên ngành 1",
      "specialization_code": "9010101"
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
<summary>Example Error (Specialization code must be 7 digits)</summary>

```json
{
    "status": "error",
    "message": "Specialization code must be 7 digits"
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

#### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": { "specialization_id": 789 } }` |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input data" }`<br>`{ "status": "error", "message": "Specialization code must be 7 digits" }` |
| 404  | Major/group not found          | `{ "status": "error", "message": "Major not found" }`<br>`{ "status": "error", "message": "Specialization group not found" }` |
| 409  | Specialization code exists     | `{ "status": "error", "message": "Specialization code already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Failed to add specialization" }` |

---

### 2.9 Update Major

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/major/update/{major_code}
  ```
- **Body:** (any or both fields)
  ```json
  {
      "new_name": "Tên ngành mới",
      "new_code": "902"
  }
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "message": "Major updated successfully",
        "updated_fields": {
            "field_name": "Tên ngành mới",
            "field_code": "902"
        }
    }
}
```
</details>

<details>
<summary>Example Error (No changes detected)</summary>

```json
{
    "status": "error",
    "message": "No changes detected"
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
<summary>Example Error (Major code must be 3 digits and start with 9)</summary>

```json
{
    "status": "error",
    "message": "Major code must be 3 digits and start with 9"
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

#### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": { ... } }`              |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input data" }`<br>`{ "status": "error", "message": "Major code must be 3 digits and start with 9" }` |
| 404  | Major not found                | `{ "status": "error", "message": "Major not found" }`   |
| 409  | Major code already exists      | `{ "status": "error", "message": "Major code already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Failed to update major" }` |

---

### 2.10 Update Specialization Group

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/specialization-group/update/{group_code}
  ```
- **Body:** (any or both fields)
  ```json
  {
      "new_name": "Tên nhóm mới",
      "new_code": "90102"
  }
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "message": "Group updated successfully",
        "updated_fields": {
            "field_name": "Tên nhóm mới",
            "field_code": "90102"
        }
    }
}
```
</details>

<details>
<summary>Example Error (No changes detected)</summary>

```json
{
    "status": "error",
    "message": "No changes detected"
}
```
</details>

<details>
<summary>Example Error (Specialization group code already exists)</summary>

```json
{
    "status": "error",
    "message": "Group code already exists"
}
```
</details>

<details>
<summary>Example Error (Group code must be 5 digits)</summary>

```json
{
    "status": "error",
    "message": "Group code must be 5 digits"
}
```
</details>

<details>
<summary>Example Error (Group not found)</summary>

```json
{
    "status": "error",
    "message": "Group not found"
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
    "message": "Failed to update group"
}
```
</details>

#### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": { ... } }`              |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input data" }`<br>`{ "status": "error", "message": "Group code must be 5 digits" }` |
| 404  | Group not found                | `{ "status": "error", "message": "Group not found" }`   |
| 409  | Group code already exists      | `{ "status": "error", "message": "Group code already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Failed to update group" }` |

---

### 2.11 Update Specialization

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/specialization/update/{specialization_code}
  ```
- **Body:** (any or both fields)
  ```json
  {
      "new_name": "Tên chuyên ngành mới",
      "new_code": "9010102"
  }
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "message": "Specialization updated successfully",
        "updated_fields": {
            "field_name": "Tên chuyên ngành mới",
            "field_code": "9010102"
        }
    }
}
```
</details>

<details>
<summary>Example Error (No changes detected)</summary>

```json
{
    "status": "error",
    "message": "No changes detected"
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
<summary>Example Error (Specialization code must be 7 digits)</summary>

```json
{
    "status": "error",
    "message": "Specialization code must be 7 digits"
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

#### Success and Error Codes

| Code | Meaning                        | Example Response                                         |
|------|--------------------------------|---------------------------------------------------------|
| 200  | Success                        | `{ "status": "success", "data": { ... } }`              |
| 400  | Invalid input                  | `{ "status": "error", "message": "Invalid input data" }`<br>`{ "status": "error", "message": "Specialization code must be 7 digits" }` |
| 404  | Specialization not found       | `{ "status": "error", "message": "Specialization not found" }` |
| 409  | Specialization code exists     | `{ "status": "error", "message": "Specialization code already exists" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Failed to update specialization" }` |

---

## 3. Scientist Images

### 3.1 Get Scientist Image

- **Method:** `GET`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/scientist/{id}/image
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "image_link": "https://scientist.local/wp-content/uploads/2024/05/image.jpg"
    }
}
```
</details>

<details>
<summary>Example Error (Invalid scientist ID)</summary>

```json
{
    "status": "error",
    "message": "Invalid scientist ID"
}
```
</details>

<details>
<summary>Example Error (Image not found)</summary>

```json
{
    "status": "error",
    "message": "Image not found for this scientist"
}
```
</details>

#### Success and Error Codes

| Code | Meaning                | Example Response                                      |
|------|------------------------|------------------------------------------------------|
| 200  | Success                | `{ "status": "success", "data": { "image_link": "..." } }` |
| 400  | Invalid scientist ID   | `{ "status": "error", "message": "Invalid scientist ID" }` |
| 404  | Image not found        | `{ "status": "error", "message": "Image not found for this scientist" }` |

---

### 3.2 Add Scientist Image

- **Method:** `POST`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/scientist/{id}/image/add
  ```
- **Body:**  
  Form-data with file field named `picture`.

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "message": "Image added successfully",
        "id": 10,
        "image_link": "https://scientist.local/wp-content/uploads/2024/05/image.jpg"
    }
}
```
</details>

<details>
<summary>Example Error (Invalid scientist ID)</summary>

```json
{
    "status": "error",
    "message": "Invalid scientist ID"
}
```
</details>

<details>
<summary>Example Error (No image file provided)</summary>

```json
{
    "status": "error",
    "message": "No image file provided"
}
```
</details>

<details>
<summary>Example Error (Image upload failed)</summary>

```json
{
    "status": "error",
    "message": "Image upload failed: ...details..."
}
```
</details>

#### Success and Error Codes

| Code | Meaning                | Example Response                                      |
|------|------------------------|------------------------------------------------------|
| 200  | Success                | `{ "status": "success", "data": { ... } }`           |
| 400  | Invalid input/upload   | `{ "status": "error", "message": "Invalid scientist ID" }`<br>`{ "status": "error", "message": "No image file provided" }`<br>`{ "status": "error", "message": "Image upload failed: ..." }` |

---

### 3.3 Remove Scientist Image

- **Method:** `DELETE`
- **Endpoint:**
  ```
  https://scientist.local/wp-json/scientist/v1/scientist/{id}/image/remove/{image_id}
  ```

#### Response

<details>
<summary>Example Success</summary>

```json
{
    "status": "success",
    "data": {
        "message": "Image removed successfully"
    }
}
```
</details>

<details>
<summary>Example Error (Invalid ID)</summary>

```json
{
    "status": "error",
    "message": "Invalid ID"
}
```
</details>

<details>
<summary>Example Error (Image not found)</summary>

```json
{
    "status": "error",
    "message": "Image not found for this scientist"
}
```
</details>

#### Success and Error Codes

| Code | Meaning                | Example Response                                      |
|------|------------------------|------------------------------------------------------|
| 200  | Success                | `{ "status": "success", "data": { "message": "Image removed successfully" } }` |
| 400  | Invalid ID             | `{ "status": "error", "message": "Invalid ID" }`     |
| 404  | Image not found        | `{ "status": "error", "message": "Image not found for this scientist" }` |

---