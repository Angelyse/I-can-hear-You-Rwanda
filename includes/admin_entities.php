<?php
/*
| Field definitions for the simple admin-managed records.
| Used by Admin/crud-actions.php (validation) and the list pages (rendering).
| field: [label, input type, required, options|null]
*/
function admin_entities(): array
{
    $status3 = ["active" => "Active", "pending" => "Pending", "inactive" => "Inactive"];
    return [
        "schools" => [
            "title" => "School", "plural" => "Schools", "table" => "schools", "status_options" => $status3,
            "fields" => [
                "name" => ["School name", "text", true, null],
                "type" => ["Type", "select", true, ["Primary" => "Primary", "Secondary" => "Secondary", "TVET" => "TVET", "University" => "University"]],
                "location" => ["Location", "text", true, null],
                "contact_person" => ["Contact person", "text", false, null],
                "phone" => ["Phone", "tel", false, null],
                "email" => ["Email", "email", false, null],
                "student_count" => ["Students", "number", false, null],
                "status" => ["Status", "select", true, $status3],
            ],
            "columns" => ["name", "type", "location", "contact_person", "student_count"],
        ],
        "organizations" => [
            "title" => "Organization", "plural" => "Organizations", "table" => "organizations", "status_options" => $status3,
            "fields" => [
                "name" => ["Organization name", "text", true, null],
                "type" => ["Type", "text", false, null],
                "location" => ["Location", "text", false, null],
                "contact_person" => ["Contact person", "text", false, null],
                "phone" => ["Phone", "tel", false, null],
                "email" => ["Email", "email", false, null],
                "website" => ["Website", "url", false, null],
                "contact_information" => ["Notes", "textarea", false, null],
                "status" => ["Status", "select", true, $status3],
            ],
            "columns" => ["name", "type", "location", "contact_person", "email"],
        ],
        "programs" => [
            "title" => "Program", "plural" => "Programs", "table" => "programs",
            "status_options" => ["upcoming" => "Upcoming", "active" => "Active", "completed" => "Completed", "archived" => "Archived"],
            "fields" => [
                "name" => ["Program name", "text", true, null],
                "program_type" => ["Type", "text", false, null],
                "description" => ["Description", "textarea", false, null],
                "start_date" => ["Start date", "date", false, null],
                "end_date" => ["End date", "date", false, null],
                "status" => ["Status", "select", true, ["upcoming" => "Upcoming", "active" => "Active", "completed" => "Completed", "archived" => "Archived"]],
            ],
            "columns" => ["name", "program_type", "start_date", "end_date"],
        ],
    ];
}

/** Validate POSTed values against an entity definition. Returns [values, error|null]. */
function admin_validate_entity(array $def, array $post): array
{
    $values = [];
    foreach ($def["fields"] as $key => [$label, $type, $required, $options]) {
        $v = trim((string)($post[$key] ?? ""));
        if ($required && $v === "") return [[], "$label is required."];
        if ($v === "") { $values[$key] = null; continue; }
        if ($type === "select" && !array_key_exists($v, $options)) return [[], "Invalid $label."];
        if ($type === "email" && !filter_var($v, FILTER_VALIDATE_EMAIL)) return [[], "Enter a valid email."];
        if ($type === "url" && !preg_match('#^https?://#i', $v)) return [[], "Website must start with http:// or https://"];
        if ($type === "date" && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return [[], "Invalid date for $label."];
        if ($type === "number") $v = (string)max(0, (int)$v);
        $values[$key] = mb_substr($v, 0, $type === "textarea" ? 5000 : 255);
    }
    return [$values, null];
}
