<?php

class AdminOperatingHourController extends Controller
{
    private OperatingHour $operatingHours;

    public function __construct()
    {
        $this->operatingHours = new OperatingHour();
    }

    public function index(): void
    {
        AuthHelper::requireAdmin();
        $this->render($this->toDayMap($this->operatingHours->all()));
    }

    public function update(): void
    {
        AuthHelper::requireAdmin();
        if (!SecurityHelper::verifyCsrfToken()) {
            http_response_code(400);
            echo '400 - Invalid CSRF token';
            return;
        }

        $submitted = $_POST['hours'] ?? null;
        $hours = $this->toDayMap($this->operatingHours->all());
        $errors = [];

        if (!is_array($submitted)) {
            $errors[] = 'Jadwal operasional tidak valid.';
        } else {
            for ($day = 0; $day <= 6; $day++) {
                $row = $submitted[$day] ?? null;
                if (!is_array($row)) {
                    $errors[] = 'Lengkapi jadwal untuk setiap hari.';
                    continue;
                }

                $openTime = $this->stringValue($row['open_time'] ?? null);
                $closeTime = $this->stringValue($row['close_time'] ?? null);
                $isClosed = $this->stringValue($row['is_closed'] ?? null) === '1';

                if (!$this->validTime($openTime) || !$this->validTime($closeTime)) {
                    $errors[] = 'Jam buka dan tutup harus menggunakan format waktu yang valid.';
                    continue;
                }
                if ($openTime >= $closeTime) {
                    $errors[] = 'Jam tutup harus setelah jam buka.';
                    continue;
                }

                $hours[$day] = [
                    'day_of_week' => $day,
                    'open_time' => $openTime,
                    'close_time' => $closeTime,
                    'is_closed' => $isClosed,
                ];
            }
        }

        if ($errors) {
            http_response_code(422);
            $this->render($hours, array_values(array_unique($errors)));
            return;
        }

        $this->operatingHours->saveAll(array_values($hours));
        FlashHelper::success('Jam operasional berhasil diperbarui.');
        $this->redirect($this->appBaseUrl() . '/admin/operating-hours');
    }

    private function toDayMap(array $rows): array
    {
        $days = [];
        foreach ($rows as $row) {
            $day = (int) $row['day_of_week'];
            $days[$day] = [
                'day_of_week' => $day,
                'open_time' => substr($row['open_time'], 0, 5),
                'close_time' => substr($row['close_time'], 0, 5),
                'is_closed' => (bool) $row['is_closed'],
            ];
        }

        for ($day = 0; $day <= 6; $day++) {
            if (!isset($days[$day])) {
                $days[$day] = [
                    'day_of_week' => $day,
                    'open_time' => '08:00',
                    'close_time' => '22:00',
                    'is_closed' => false,
                ];
            }
        }

        ksort($days);
        return $days;
    }

    private function render(array $hours, array $errors = []): void
    {
        $this->view('admin/operating_hours', [
            'pageTitle' => 'Operating Hours',
            'hours' => $hours,
            'errors' => $errors,
        ]);
    }

    private function validTime(string $value): bool
    {
        return (bool) preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value);
    }

    private function stringValue($value): string
    {
        return is_string($value) ? $value : '';
    }
}
