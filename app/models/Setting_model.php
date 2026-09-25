<?php
/**
 * System Settings Model
 */

class Setting_model extends Model {
    public function getAllSettings(): array {
        $rows = $this->fetchAll("SELECT * FROM settings ORDER BY id ASC");
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = [
                'value' => $r['setting_value'],
                'description' => $r['description']
            ];
        }
        return $settings;
    }

    public function getSetting(string $key, string $default = ''): string {
        $row = $this->fetchOne("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1", [$key]);
        return $row ? $row['setting_value'] : $default;
    }

    public function updateSetting(string $key, string $value, ?string $description = null): void {
        $exists = $this->fetchOne("SELECT id FROM settings WHERE setting_key = ? LIMIT 1", [$key]);
        if ($exists) {
            $this->update('settings', ['setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s')], "setting_key = ?", [$key]);
        } else {
            $this->insert('settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'description' => $description,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
