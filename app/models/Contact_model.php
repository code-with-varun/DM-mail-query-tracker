<?php
/**
 * Contact Model
 * Handles CRUD for Contact Manager
 */

class Contact_model extends Model {
    public function getContacts(?string $orgType = null): array {
        $sql = "
            SELECT c.*, u.full_name as creator_name 
            FROM contacts c
            LEFT JOIN users u ON c.created_by = u.id
        ";
        $params = [];

        if (!empty($orgType) && in_array($orgType, ['Client', 'Internal', 'Third Party'])) {
            $sql .= " WHERE c.organisation_type = ?";
            $params[] = $orgType;
        }

        $sql .= " ORDER BY c.id DESC";

        return $this->fetchAll($sql, $params);
    }

    public function getContactById(int $id): ?array {
        return $this->fetchOne("
            SELECT c.*, u.full_name as creator_name 
            FROM contacts c
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.id = ?
        ", [$id]);
    }

    public function createContact(array $data): int {
        $insertData = [
            'name' => $data['name'],
            'contact_number' => $data['contact_number'] ?? null,
            'email' => $data['email'] ?? null,
            'organisation_type' => $data['organisation_type'] ?? 'Client',
            'remarks' => $data['remarks'] ?? null,
            'created_by' => $data['created_by'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $contactId = $this->insert('contacts', $insertData);
        $this->logAudit('Contacts', 'CREATE_CONTACT', null, $insertData);

        return $contactId;
    }

    public function updateContact(int $id, array $data): bool {
        $oldData = $this->getContactById($id);
        if (!$oldData) return false;

        $updateData = [
            'name' => $data['name'],
            'contact_number' => $data['contact_number'] ?? null,
            'email' => $data['email'] ?? null,
            'organisation_type' => $data['organisation_type'] ?? 'Client',
            'remarks' => $data['remarks'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->update('contacts', $updateData, 'id = ?', [$id]);
        $this->logAudit('Contacts', 'UPDATE_CONTACT', $oldData, $updateData);

        return true;
    }

    public function deleteContact(int $id): bool {
        $oldData = $this->getContactById($id);
        if (!$oldData) return false;

        $this->delete('contacts', 'id = ?', [$id]);
        $this->logAudit('Contacts', 'DELETE_CONTACT', $oldData, null);

        return true;
    }

    public function getStats(): array {
        $stats = [
            'total' => 0,
            'client' => 0,
            'internal' => 0,
            'third_party' => 0
        ];

        $rows = $this->fetchAll("
            SELECT organisation_type, COUNT(*) as cnt 
            FROM contacts 
            GROUP BY organisation_type
        ");

        foreach ($rows as $row) {
            $type = $row['organisation_type'];
            $count = (int)$row['cnt'];
            $stats['total'] += $count;

            if ($type === 'Client') {
                $stats['client'] = $count;
            } elseif ($type === 'Internal') {
                $stats['internal'] = $count;
            } elseif ($type === 'Third Party') {
                $stats['third_party'] = $count;
            }
        }

        return $stats;
    }
}
