# User Course Completion Report (Moodle Local Plugin)

## Overview

This plugin provides an administrative report that allows site administrators to:

* Select a user
* View courses associated with that user
* See course completion status and completion date
* Navigate directly to each course

The report is available under:

> Site administration → Reports → User Course Completion Report

---

## Features

* Restricted to site administrators only
* User selection via Moodle Form API
* Displays:

    * Course name (linked to course page)
    * Completion status (Complete / Not complete)
    * Completion timestamp (if available)
* Rendered using a Mustache template
* Efficient data retrieval using a single query

---

## Installation

1. Copy the plugin into:

   ```
   /local/completion_report
   ```

2. Log in as administrator

3. Go to:

   ```
   Site administration → Notifications
   ```

4. Complete installation

---

## Usage

1. Navigate to:

   ```
   Site administration → Reports → User Course Completion Report
   ```

2. Select a user

3. Click **View report**

---

## Project Structure

```text
completion_report/
├── classes/
│   ├── form/
│   ├── report_service.php
│   └── user_repository.php
├── lang/en/
├── templates/
├── tests/
├── index.php
├── lib.php
├── settings.php
└── version.php
```

---

## Technical Approach

The implementation is intentionally simple and focused while maintaining clear separation of responsibilities:

* `index.php` – request handling and orchestration
* `report_service` – report construction logic
* `user_repository` – data access
* `moodleform` – user input handling
* Mustache template – presentation layer

---

## Performance

* Completion data retrieved in a **single query** using `get_in_or_equal`
* Avoids N+1 query patterns
* Uses core Moodle APIs (`enrol_get_all_users_courses`, `userdate`, `format_string`)

---

## Design Considerations

The solution avoids unnecessary complexity and focuses on clarity and maintainability.

For a larger implementation, this could be extended with:

* richer filtering and pagination
* more granular capability handling
* additional test coverage

---

## Security

* Access restricted to site administrators
* Input handled via Moodle Form API
* Safe parameter handling via Moodle DB layer

---

## Notes

Completion status is derived from:

```
course_completions.timecompleted
```

* `NULL` or `0` → Not complete
* Non-zero → Complete

---

## Compatibility

* Moodle 4.3+

---

## Author

Mher Avetyan
