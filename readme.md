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
            "specialization_groups": [
                {
                    "specialization_group_code": "A",
                    "specialization_group_name": "Nhóm A",
                    "specializations": [
                        {
                            "specialization_code": "A1",
                            "specialization_name": "Chuyên ngành 1"
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
            "specialization_group_code": "A",
            "specialization_group_name": "Nhóm A"
        }
    ]
}
```
</details>

<details>
<summary>Example Error (Invalid major code)</summary>

```json
{
    "status": "error",
    "message": "Invalid major code"
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
| 400  | Invalid major code     | `{ "status": "error", "message": "Invalid major code" }` |
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
<summary>Example Error (Invalid parameters)</summary>

```json
{
    "status": "error",
    "message": "Invalid parameters"
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
| 400  | Invalid parameters             | `{ "status": "error", "message": "Invalid parameters" }`|
| 404  | Major/group not found          | `{ "status": "error", "message": "Major not found" }` or `{ "status": "error", "message": "Specialization group not found" }` |
| 500  | Database/Server Error          | `{ "status": "error", "message": "Database error: ..." }` |

---