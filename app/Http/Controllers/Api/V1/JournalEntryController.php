<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreJournalEntryRequest;
use App\Http\Requests\Api\V1\UpdateJournalEntryRequest;
use App\Http\Resources\Api\V1\JournalEntryResource;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class JournalEntryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $entries = $request->user()->journalEntries()->orderByDesc('entry_date')->paginate(30);

        return JournalEntryResource::collection($entries);
    }

    public function store(StoreJournalEntryRequest $request): JournalEntryResource
    {
        $entry = $request->user()->journalEntries()->create($request->validated());

        return new JournalEntryResource($entry);
    }

    public function show(Request $request, JournalEntry $journalEntry): JournalEntryResource
    {
        $this->authorize('view', $journalEntry);

        return new JournalEntryResource($journalEntry);
    }

    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): JournalEntryResource
    {
        $this->authorize('update', $journalEntry);

        $journalEntry->update($request->validated());

        return new JournalEntryResource($journalEntry->fresh());
    }

    public function destroy(Request $request, JournalEntry $journalEntry): Response
    {
        $this->authorize('delete', $journalEntry);

        $journalEntry->delete();

        return response()->noContent();
    }
}
