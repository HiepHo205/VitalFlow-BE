<?php

namespace App\Services\Ai;

use App\Contracts\AiAnalysisContract;
use App\Models\AiFeedback;
use App\Models\DailyLog;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OpenAiAnalysisService implements AiAnalysisContract
{
    public function generateRoutineDraft(User $user, array $preferences): array
    {
        $apiKey = $this->openAiApiKey();

        if ($apiKey === null) {
            return $this->fallback()->generateRoutineDraft($user, $preferences);
        }

        $prompt = $this->buildRoutinePrompt($user, $preferences);
        $response = $this->sendChatCompletion($prompt);

        if ($response === null) {
            return $this->fallback()->generateRoutineDraft($user, $preferences);
        }

        $parsed = $this->decodeJson($response);
        if (! $this->isValidRoutineDraft($parsed)) {
            return $this->fallback()->generateRoutineDraft($user, $preferences);
        }

        return $parsed;
    }

    public function analyzeRoutine(User $user, Routine $routine): void
    {
        $apiKey = $this->openAiApiKey();
        $payload = $this->buildRoutineAnalysisPayload($user, $routine);
        $response = $apiKey ? $this->sendChatCompletion($payload) : null;
        $parsed = $response ? $this->decodeJson($response) : null;

        if (! $parsed || ! isset($parsed['summary'], $parsed['recommendation'])) {
            $this->fallback()->analyzeRoutine($user, $routine);

            return;
        }

        AiFeedback::query()->create([
            'user_id' => $user->id,
            'related_log_id' => null,
            'related_routine_id' => $routine->id,
            'feedback_type' => 'routine_analysis',
            'summary' => trim($parsed['summary']),
            'recommendation' => trim($parsed['recommendation']),
            'generated_at' => now(),
        ]);
    }

    public function analyzeDailyLog(User $user, DailyLog $dailyLog): void
    {
        $apiKey = $this->openAiApiKey();
        $payload = $this->buildDailyLogAnalysisPayload($dailyLog);
        $response = $apiKey ? $this->sendChatCompletion($payload) : null;
        $parsed = $response ? $this->decodeJson($response) : null;

        if (! $parsed || ! isset($parsed['summary'], $parsed['recommendation'])) {
            $this->fallback()->analyzeDailyLog($user, $dailyLog);

            return;
        }

        AiFeedback::query()->create([
            'user_id' => $user->id,
            'related_log_id' => $dailyLog->id,
            'related_routine_id' => null,
            'feedback_type' => 'daily_log',
            'summary' => trim($parsed['summary']),
            'recommendation' => trim($parsed['recommendation']),
            'generated_at' => now(),
        ]);
    }

    public function analyzeDailyLogs(User $user, array $dailyLogs): ?array
    {
        $apiKey = $this->openAiApiKey();

        $payload = $this->buildDailyLogsAnalysisPayload($dailyLogs);

        $response = $apiKey ? $this->sendChatCompletion($payload) : null;
        $parsed = $response ? $this->decodeJson($response) : null;

        if (! $parsed || ! isset($parsed['summary'], $parsed['recommendation'])) {
            // Fallback: simple heuristic summary
            $heuristic = $this->heuristicDailyLogsAnalysis($dailyLogs);

            AiFeedback::query()->create([
                'user_id' => $user->id,
                'related_log_id' => null,
                'related_routine_id' => null,
                'feedback_type' => 'daily_logs_summary',
                'summary' => trim($heuristic['summary']),
                'recommendation' => trim($heuristic['recommendation']),
                'generated_at' => now(),
            ]);

            return $heuristic;
        }

        AiFeedback::query()->create([
            'user_id' => $user->id,
            'related_log_id' => null,
            'related_routine_id' => null,
            'feedback_type' => 'daily_logs_summary',
            'summary' => trim($parsed['summary']),
            'recommendation' => trim($parsed['recommendation']),
            'generated_at' => now(),
        ]);

        return $parsed;
    }

    private function buildDailyLogsAnalysisPayload(array $dailyLogs): string
    {
        $lines = [];
        foreach ($dailyLogs as $log) {
            $date = isset($log['log_date']) ? $log['log_date'] : ($log->log_date ?? null);
            $lines[] = "Date: " . ($date ?? 'unknown');
            if (isset($log['mood_score'])) $lines[] = "- Mood: {$log['mood_score']}/10";
            if (isset($log['energy_level'])) $lines[] = "- Energy: {$log['energy_level']}/10";
            if (isset($log['stress_level'])) $lines[] = "- Stress: {$log['stress_level']}/10";
            if (isset($log['sleep_hours'])) $lines[] = "- Sleep: {$log['sleep_hours']}";
            $lines[] = "";
        }

        $summary = $lines !== [] ? implode("\n", $lines) : '- Không có dữ liệu.';

        return trim(
            'Phân tích các nhật ký sau bằng tiếng Việt và trả về JSON có hai trường: summary, recommendation.' . "\n"
            . 'Không thêm text ngoài JSON.' . "\n\n"
            . 'Nhật ký:' . "\n"
            . "{$summary}\n\n"
            . 'Yêu cầu:' . "\n"
            . ' - Tóm tắt ngắn gọn các xu hướng chính (mood/energy/sleep).' . "\n"
            . ' - Đưa ra 2-3 khuyến nghị hành động cụ thể và thực tế.'
        );
    }

    private function heuristicDailyLogsAnalysis(array $dailyLogs): array
    {
        $count = count($dailyLogs);
        if ($count === 0) {
            return ['summary' => 'Không có nhật ký để phân tích.', 'recommendation' => 'Hãy bắt đầu ghi nhật ký hàng ngày.'];
        }

        $sumMood = 0; $sumEnergy = 0; $sumSleep = 0; $sumStress = 0; $haveMood = $haveEnergy = $haveSleep = $haveStress = 0;

        foreach ($dailyLogs as $log) {
            if (isset($log['mood_score'])) { $sumMood += $log['mood_score']; $haveMood++; }
            if (isset($log['energy_level'])) { $sumEnergy += $log['energy_level']; $haveEnergy++; }
            if (isset($log['sleep_hours'])) { $sumSleep += $log['sleep_hours']; $haveSleep++; }
            if (isset($log['stress_level'])) { $sumStress += $log['stress_level']; $haveStress++; }
        }

        $avgMood = $haveMood ? round($sumMood / $haveMood, 1) : null;
        $avgEnergy = $haveEnergy ? round($sumEnergy / $haveEnergy, 1) : null;
        $avgSleep = $haveSleep ? round($sumSleep / $haveSleep, 1) : null;
        $avgStress = $haveStress ? round($sumStress / $haveStress, 1) : null;

        $summaryParts = [];
        if ($avgMood !== null) $summaryParts[] = "Tâm trạng trung bình: {$avgMood}/10.";
        if ($avgEnergy !== null) $summaryParts[] = "Năng lượng trung bình: {$avgEnergy}/10.";
        if ($avgSleep !== null) $summaryParts[] = "Giấc ngủ trung bình: {$avgSleep} giờ.";
        if ($avgStress !== null) $summaryParts[] = "Stress trung bình: {$avgStress}/10.";

        $recommendations = [];
        if ($avgSleep !== null && $avgSleep < 6) {
            $recommendations[] = 'Cải thiện thói quen ngủ: đặt giờ ngủ cố định và giảm thiết bị trước khi ngủ.';
        }
        if ($avgMood !== null && $avgMood < 5) {
            $recommendations[] = 'Tìm hoạt động nâng cao mood (vận động, tương tác xã hội) hoặc cân nhắc tham vấn.';
        }
        if ($avgStress !== null && $avgStress > 6) {
            $recommendations[] = 'Áp dụng kỹ thuật thở hoặc ngắt quãng làm việc để giảm stress.';
        }
        if ($recommendations === []) $recommendations[] = 'Tiếp tục duy trì thói quen tốt và ghi nhật ký đều đặn.';

        return [
            'summary' => implode(' ', $summaryParts),
            'recommendation' => implode(' ', $recommendations),
        ];
    }

    private function openAiApiKey(): ?string
    {
        $key = config('services.openai.key');

        return is_string($key) && trim($key) !== '' ? trim($key) : null;
    }

    private function sendChatCompletion(string $prompt): ?string
    {
        try {
            $response = Http::timeout(30)
                ->withToken($this->openAiApiKey())
                ->acceptJson()
                ->post($this->openAiUrl(), [
                    'model' => $this->openAiModel(),
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an assistant that responds with valid JSON only. Do not include any explanation outside the JSON object.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 800,
                ]);
        } catch (ConnectionException $exception) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        return $response->json('choices.0.message.content');
    }

    private function decodeJson(string $payload): ?array
    {
        $decoded = json_decode($payload, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($payload, '{');
        $end = strrpos($payload, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $decoded = json_decode(substr($payload, $start, $end - $start + 1), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function isValidRoutineDraft(?array $draft): bool
    {
        return is_array($draft)
            && isset($draft['name'], $draft['items'], $draft['is_ai_generated'])
            && is_array($draft['items']);
    }

    private function buildRoutinePrompt(User $user, array $preferences): string
    {
        $profile = null;
        if (isset($preferences['health_profile']) && is_array($preferences['health_profile'])) {
            $profile = $preferences['health_profile'];
        } else {
            $profile = $user->healthProfile;
        }

        $profileText = $this->profileSummary($profile);
        $goalText = $this->goalSummary($preferences['goal'] ?? null);
        $focus = $preferences['focus'] ?? 'Tập trung vào thói quen sức khỏe hàng ngày.';
        $workHours = $preferences['typical_work_hours'] ?? '9–18';
        $activities = isset($preferences['activities']) ? implode(', ', $preferences['activities']) : 'Không có hoạt động đặc biệt.';
        $constraints = $preferences['constraints'] ?? 'Không có ràng buộc thêm.';

        return trim(
            'Hãy tạo một bản nháp routine bằng tiếng Việt, trả về đúng JSON với các trường sau: name, description, is_ai_generated, items.' . "\n"
            . 'Mỗi item phải có: title, description, category, start_time, end_time, duration_minutes, priority, recurrence_type, bucket.' . "\n"
            . 'Giá trị bucket phải là một trong: "before_work", "during_work", "after_work", "evening".' . "\n"
            . 'Không trả về văn bản nào khác ngoài JSON.' . "\n\n"
            . 'Thông tin người dùng và yêu cầu:' . "\n"
            . "- Hồ sơ: {$profileText}\n"
            . "- Goal hiện tại: {$goalText}\n"
            . "- Mục tiêu / focus: {$focus}\n"
            . "- Giờ làm việc điển hình: {$workHours} (format HH:MM-HH:MM nếu có)\n"
            . "- Hoạt động sẵn có: {$activities}\n"
            . "- Hạn chế / constraints: {$constraints}\n\n"
            . 'Yêu cầu:' . "\n"
            . ' - Routine phải phục vụ trực tiếp goal hiện tại, phù hợp với tiến độ, giá trị mục tiêu và thời hạn của goal.' . "\n"
            . ' - Ưu tiên các item có tác động trực tiếp đến hoàn thành goal.' . "\n"
            . ' - Nếu goal có end_date gần, ưu tiên item ngắn, thực tế và dễ duy trì hằng ngày.' . "\n"
            . ' - Giữ cấu trúc dữ liệu hợp lệ.' . "\n"
            . ' - Chỉ tạo 3-8 item thiết thực.' . "\n"
            . ' - Phân loại mỗi item vào bucket phù hợp dựa trên giờ làm việc.' . "\n"
            . ' - Tránh thời gian chồng chéo không hợp lý.'
        );
    }

    private function buildRoutineAnalysisPayload(User $user, Routine $routine): string
    {
        $profileText = $this->profileSummary($user->healthProfile);
        $items = $routine->items->map(fn ($item) => "- {$item->title}: {$item->description}")->implode("\n");

        return trim(
            'Phân tích routine sau bằng tiếng Việt và trả về JSON có hai trường: summary, recommendation.'."\n"
            .'Không thêm text ngoài JSON.'."\n\n"
            .'Routine:'."\n"
            ."- Tên: {$routine->name}\n"
            .'- Nội dung:'."\n"
            ."{$items}\n\n"
            .'Thông tin hồ sơ người dùng:'."\n"
            ."{$profileText}\n\n"
            .'Yêu cầu:'."\n"
            .' - Summary ngắn gọn, dễ hiểu.'."\n"
            .' - Recommendation rõ ràng, hướng dẫn cải thiện hoặc điều chỉnh.'
        );
    }

    private function buildDailyLogAnalysisPayload(DailyLog $dailyLog): string
    {
        $fields = [];

        if ($dailyLog->mood_score !== null) {
            $fields[] = "- Mood score: {$dailyLog->mood_score}/10";
        }
        if ($dailyLog->energy_level !== null) {
            $fields[] = "- Energy level: {$dailyLog->energy_level}/10";
        }
        if ($dailyLog->stress_level !== null) {
            $fields[] = "- Stress level: {$dailyLog->stress_level}/10";
        }
        if ($dailyLog->sleep_hours !== null) {
            $fields[] = "- Sleep hours: {$dailyLog->sleep_hours}";
        }
        if ($dailyLog->body_condition) {
            $fields[] = "- Body condition: {$dailyLog->body_condition}";
        }

        $summary = $fields !== [] ? implode("\n", $fields) : '- Không có dữ liệu nhật ký cụ thể.';

        return trim(
            'Phân tích nhật ký hàng ngày sau bằng tiếng Việt và trả về JSON có hai trường: summary, recommendation.'."\n"
            .'Không thêm text ngoài JSON.'."\n\n"
            .'Thông tin nhật ký:'."\n"
            ."{$summary}\n\n"
            .'Yêu cầu:'."\n"
            .' - Summary ngắn gọn, dễ hiểu.'."\n"
            .' - Recommendation hữu ích cho ngày kế tiếp.'
        );
    }

    private function profileSummary($profile): string
    {
        if ($profile === null) {
            return 'Chưa có hồ sơ sức khỏe.';
        }

        if (is_array($profile)) {
            $parts = [];
            if (! empty($profile['age'])) {
                $parts[] = 'Tuổi '.$profile['age'];
            }
            if (! empty($profile['work_type'])) {
                $parts[] = 'Công việc: '.$profile['work_type'];
            }
            if (! empty($profile['height_cm'])) {
                $parts[] = 'Chiều cao: '.$profile['height_cm'].' cm';
            }
            if (! empty($profile['weight_kg'])) {
                $parts[] = 'Cân nặng: '.$profile['weight_kg'].' kg';
            }

            return implode(' ', $parts) ?: 'Chưa có hồ sơ sức khỏe.';
        }
        return implode(' ', array_filter([
            $profile->age !== null ? "Tuổi {$profile->age}" : null,
            $profile->work_type ? "Công việc: {$profile->work_type}" : null,
            $profile->height_cm !== null ? "Chiều cao: {$profile->height_cm} cm" : null,
            $profile->weight_kg !== null ? "Cân nặng: {$profile->weight_kg} kg" : null,
        ]));
    }

    private function goalSummary($goal): string
    {
        if (! is_array($goal)) {
            return 'Chưa có thông tin goal cụ thể.';
        }

        $parts = [];

        if (! empty($goal['goal_type'])) {
            $parts[] = 'Loại goal: '.$goal['goal_type'];
        }

        if (array_key_exists('target_value', $goal) && $goal['target_value'] !== null) {
            $parts[] = 'Target: '.$goal['target_value'];
        }

        if (array_key_exists('current_value', $goal) && $goal['current_value'] !== null) {
            $parts[] = 'Hiện tại: '.$goal['current_value'];
        }

        if (! empty($goal['status'])) {
            $parts[] = 'Trạng thái: '.$goal['status'];
        }

        $startDate = $goal['start_date'] ?? null;
        $endDate = $goal['end_date'] ?? null;

        if ($startDate || $endDate) {
            $parts[] = 'Thời gian goal: '.($startDate ?: 'không rõ ngày bắt đầu').' đến '.($endDate ?: 'không rõ ngày kết thúc');
        }

        return $parts !== [] ? implode('; ', $parts).'.' : 'Chưa có thông tin goal cụ thể.';
    }

    private function openAiUrl(): string
    {
        return config('services.openai.url', 'https://api.openai.com/v1/chat/completions');
    }

    private function openAiModel(): string
    {
        return config('services.openai.model', 'gpt-3.5-turbo');
    }

    private function fallback(): HeuristicAiAnalysisService
    {
        return new HeuristicAiAnalysisService;
    }
}
