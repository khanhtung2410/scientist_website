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
<summary>Example JSON</summary>

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

#### Success and Error Codes

| Code | Meaning                | Example Response                                      |
|------|------------------------|------------------------------------------------------|
| 200  | Success                | `{ "status": "success", "data": [...] }`             |
| 500  | Database/Server Error  | `{ "status": "error", "message": "Database error: ..." }` |