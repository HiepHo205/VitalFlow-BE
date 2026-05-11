<?php

namespace App\Http\Resources\Api\V1;

use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JournalEntry */
class JournalEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entry_date' => $this->entry_date,
            'title' => $this->title,
            'content' => $this->content,
            'mood_tag' => $this->mood_tag,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
