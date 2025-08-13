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