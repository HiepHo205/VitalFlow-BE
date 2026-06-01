<?php

namespace App\Services\Ai;

use App\Contracts\AiAnalysisContract;
use App\Models\AiFeedback;
use App\Models\DailyLog;
use App\Models\HealthProfile;
use App\Models\Routine;
use App\Models\User;

class HeuristicAiAnalysisService implements AiAnalysisContract
{
    /**
     * @param  array<string, mixed>  $preferences
     * @return array{name: string, description: string|null, is_ai_generated: true, items: list<array<string, mixed>>}
     */
    public function generateRoutineDraft(User $user, array $preferences): array
    {
        $profile = $user->healthProfile;
        $workHours = $preferences['typical_work_hours'] ?? '9–18';
        $goal = isset($preferences['goal']) && is_array($preferences['goal']) ? $preferences['goal'] : null;
        $focus = $preferences['focus'] ?? $this->goalFocus($goal);
        $goalTime = $this->goalTimeSummary($goal);

        $items = [
            [
                'title' => 'Khởi động nhẹ & thở',
                'description' => '5–10 phút duỗi cổ, vai, lưng dưới trước khi làm việc căng.',
                'category' => 'morning',
                'start_time' => '07:30:00',
                'end_time' => '07:45:00',
                'duration_minutes' => 15,
                'priority' => 2,
                'recurrence_type' => 'daily',
            ],
            [
                'title' => 'Khối làm việc sâu',
                'description' => "Tập trung theo giờ làm đã nêu ({$workHours}), xen kẽ nghỉ 5 phút mỗi 90 phút.",
                'category' => 'work',
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'duration_minutes' => 180,
                'priority' => 1,
                'recurrence_type' => 'daily',
            ],
            [
                'title' => 'Vận động vừa (đi bộ / yoga nhẹ)',
                'description' => 'Giữ nhịp tim vừa phải, tránh tập quá sát giờ ngủ nếu khó ngủ.',
                'category' => 'exercise',
                'start_time' => '18:00:00',
                'end_time' => '18:30:00',
                'duration_minutes' => 30,
                'priority' => 2,
                'recurrence_type' => 'daily',
            ],
            [
                'title' => 'Chuẩn bị ngủ',
                'description' => 'Giảm ánh sáng xanh, không caffeine sau 14h nếu hay trằn trọc.',
                'category' => 'sleep',
                'start_time' => '22:00:00',
                'end_time' => '22:30:00',
                'duration_minutes' => 30,
                'priority' => 2,
                'recurrence_type' => 'daily',
            ],
        ];

        $ageNote = $profile ? "Tuổi {$profile->age}, BMI xấp xỉ ".$this->approximateBmi($profile).'.' : '';

        return [
            'name' => 'Routine gợi ý — '.$focus,
            'description' => trim("Gợi ý dựa trên hồ sơ, mục tiêu: {$focus}. {$goalTime} {$ageNote} Điều chỉnh giờ cho khớp lịch thực tế."),
            'is_ai_generated' => true,
            'items' => $items,
        ];
    }

    public function analyzeRoutine(User $user, Routine $routine): void
    {
        $routine->load('items');

        $itemSummary = $routine->items->map(fn ($i) => $i->title)->implode(', ');
        $profile = $user->healthProfile;

        $summary = "Routine «{$routine->name}» gồm: {$itemSummary}. ";
        if ($profile) {
            $summary .= 'Hồ sơ: tuổi '.$profile->age.', làm việc '.($profile->work_type ?? 'chưa khai báo').'.';
        } else {
            $summary .= 'Nên bổ sung hồ sơ sức khỏe để đánh giá chính xác hơn.';
        }

        $recommendation = 'Ưu tiên giấc ngủ đủ và nghỉ ngắt quãng khi làm việc dài. '
            .'Nếu thêm tập luyện cường độ cao, đặt xa giờ ngủ ít nhất 3–4 giờ. '
            .'Theo dõi mood/energy hàng ngày trong nhật ký để tinh chỉnh routine sau 1–2 tuần.';

        AiFeedback::query()->create([
            'user_id' => $user->id,
            'related_log_id' => null,
            'related_routine_id' => $routine->id,
            'feedback_type' => 'routine_analysis',
            'summary' => $summary,
            'recommendation' => $recommendation,
            'generated_at' => now(),
        ]);
    }

    public function analyzeDailyLog(User $user, DailyLog $dailyLog): void
    {
        $parts = [];

        if ($dailyLog->mood_score !== null) {
            $parts[] = 'Điểm tâm trạng '.$dailyLog->mood_score.'/10.';
        }
        if ($dailyLog->energy_level !== null) {
            $parts[] = 'Năng lượng '.$dailyLog->energy_level.'/10.';
        }
        if ($dailyLog->stress_level !== null) {
            $parts[] = 'Căng thẳng '.$dailyLog->stress_level.'/10.';
        }
        if ($dailyLog->sleep_hours !== null) {
            $parts[] = 'Ngủ khoảng '.$dailyLog->sleep_hours.' giờ.';
        }
        if ($dailyLog->body_condition) {
            $parts[] = 'Cơ thể: '.$dailyLog->body_condition;
        }

        $summary = $parts !== []
            ? implode(' ', $parts)
            : 'Nhật ký trong ngày chưa có đủ chỉ số — nên điền mood, năng lượng và giấc ngủ.';

        $recommendation = $this->dailyRecommendationHeuristic($dailyLog);

        AiFeedback::query()->create([
            'user_id' => $user->id,
            'related_log_id' => $dailyLog->id,
            'related_routine_id' => null,
            'feedback_type' => 'daily_log',
            'summary' => $summary,
            'recommendation' => $recommendation,
            'generated_at' => now(),
        ]);
    }

    private function approximateBmi(HealthProfile $profile): string
    {
        $h = (float) $profile->height_cm / 100;
        if ($h <= 0) {
            return 'n/a';
        }
        $bmi = round((float) $profile->weight_kg / ($h * $h), 1);

        return (string) $bmi;
    }

    private function goalFocus(?array $goal): string
    {
        if ($goal === null || empty($goal['goal_type'])) {
            return 'Giảm mệt mỏi, duy trì vận động';
        }

        $parts = ['Hoàn thành goal '.$goal['goal_type']];

        if (array_key_exists('target_value', $goal) && $goal['target_value'] !== null) {
            $parts[] = 'target '.$goal['target_value'];
        }

        if (array_key_exists('current_value', $goal) && $goal['current_value'] !== null) {
            $parts[] = 'hiện tại '.$goal['current_value'];
        }

        return implode(', ', $parts);
    }

    private function goalTimeSummary(?array $goal): string
    {
        if ($goal === null) {
            return '';
        }

        $startDate = $goal['start_date'] ?? null;
        $endDate = $goal['end_date'] ?? null;

        if (! $startDate && ! $endDate) {
            return '';
        }

        return 'Thời gian goal: '.($startDate ?: 'không rõ ngày bắt đầu').' đến '.($endDate ?: 'không rõ ngày kết thúc').'.';
    }

    private function dailyRecommendationHeuristic(DailyLog $log): string
    {
        if ($log->sleep_hours !== null && $log->sleep_hours < 6) {
            return 'Giấc ngủ thấp — ưu tiên lịch đi ngủ cố định, hạn chế màn hình trước khi ngủ, tránh caffeine chiều.'
                .' Nếu mệt kéo dài, cân nhắc giảm tải tập hoặc làm việc buổi tối.';
        }

        if ($log->stress_level !== null && $log->stress_level >= 7) {
            return 'Mức căng thẳng cao — thử thở 4-7-8, đi bộ ngắn, chia nhỏ công việc. '
                .'Ghi nhật ký cảm xúc buổi tối để nhận diện trigger.';
        }

        if ($log->energy_level !== null && $log->energy_level <= 4) {
            return 'Năng lượng thấp — kiểm tra ngủ, hydrat hóa và thời điểm bữa ăn. '
                .'Thêm ánh sáng ban ngày và vận động nhẹ 10 phút có thể giúp.';
        }

        return 'Tín hiệu ổn định — duy trì routine hiện tại, tiếp tục ghi log để phát hiện xu hướng tuần.';
    }

    public function analyzeDailyLogs(User $user, array $dailyLogs): ?array
    {
        $count = count($dailyLogs);

        if ($count === 0) {
            return [
                'summary' => 'Không có dữ liệu nhật ký để phân tích.',
                'recommendation' => 'Hãy bắt đầu ghi nhật ký hàng ngày.',
            ];
        }

        $totalMood = 0;
        $totalEnergy = 0;
        $totalStress = 0;
        $totalSleep = 0;

        foreach ($dailyLogs as $log) {
            $totalMood += $log['mood_score'] ?? 0;
            $totalEnergy += $log['energy_level'] ?? 0;
            $totalStress += $log['stress_level'] ?? 0;
            $totalSleep += $log['sleep_hours'] ?? 0;
        }

        $avgMood = round($totalMood / $count, 1);
        $avgEnergy = round($totalEnergy / $count, 1);
        $avgStress = round($totalStress / $count, 1);
        $avgSleep = round($totalSleep / $count, 1);

        $summary =
            "Mood trung bình: {$avgMood}/10, "
            ."Energy: {$avgEnergy}/10, "
            ."Stress: {$avgStress}/10, "
            ."Sleep: {$avgSleep} giờ.";

        $recommendation = "Duy trì thói quen ổn định, ngủ đủ giấc và giảm stress.";

        AiFeedback::query()->create([
            'user_id' => $user->id,
            'related_log_id' => null,
            'related_routine_id' => null,
            'feedback_type' => 'daily_logs_summary',
            'summary' => $summary,
            'recommendation' => $recommendation,
            'generated_at' => now(),
        ]);

        return [
            'summary' => $summary,
            'recommendation' => $recommendation,
        ];
    }
}
