<?php

namespace App\Livewire\Teacher;

use App\Enums\ActivityStatus;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Models\Activity;
use App\Models\ActivityVideo;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\YoutubeService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ActivityManager extends Component
{
    use AuthorizesRequests, WithPagination;

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = 'all'; // 'all', 'draft', 'published', 'closed'

    #[Url(as: 'class')]
    public string $classFilter = 'all';

    // Modal state
    public bool $showFormModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingActivityId = null;

    public ?int $activityToDeleteId = null;

    // Form fields
    public ?int $classId = null;

    public string $title = '';

    public string $description = '';

    public string $instructions = '';

    public string $ageGroup = '3-4 years';

    public string $publishDate = '';

    public string $deadline = '';

    public bool $allowLate = true;

    public string $status = 'draft';

    /**
     * Attached videos for this activity:
     * list of array{id: ?int, youtube_url: string, video_id: string, title: string, thumbnail_url: string, sort_order: int}
     *
     * @var list<array{id: ?int, youtube_url: string, video_id: string, title: string, thumbnail_url: string, sort_order: int}>
     */
    public array $videos = [];

    public string $newVideoUrl = '';

    /**
     * @var array{video_id: string, title: string, thumbnail_url: string, author_name: ?string, author_url: ?string, embed_url: string, is_valid: bool}|null
     */
    public ?array $pendingVideoPreview = null;

    public string $videoError = '';

    // Watch Video Modal state
    public bool $showWatchModal = false;

    public ?string $watchVideoId = null;

    public ?string $watchVideoTitle = null;

    /**
     * @var list<string>
     */
    public array $ageGroupPresets = [
        '1-2 years',
        '2-3 years',
        '3-4 years',
        '4-5 years',
        'All Nursery',
    ];

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'classId' => ['required', 'integer', 'exists:school_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['required', 'string', 'min:5'],
            'ageGroup' => ['required', 'string', 'max:50'],
            'publishDate' => ['required', 'date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:publishDate'],
            'allowLate' => ['boolean'],
            'status' => ['required', 'in:draft,published,closed'],
        ];
    }

    public function mount(): void
    {
        $this->publishDate = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingClassFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->editingActivityId = null;

        /** @var User $user */
        $user = auth()->user();
        $institutionId = current_institution_id() ?? 1;

        // Pick default class (prefer first assigned nursery group)
        $firstClass = SchoolClass::where('institution_id', $institutionId)
            ->when(! $user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value]), function (Builder $q) use ($user) {
                $q->where('teacher_id', $user->id);
            })
            ->first();

        $this->classId = $firstClass?->id;
        $this->title = '';
        $this->description = '';
        $this->instructions = '';
        $this->ageGroup = '3-4 years';
        $this->publishDate = now()->toDateString();
        $this->deadline = '';
        $this->allowLate = true;
        $this->status = 'draft';
        $this->videos = [];
        $this->newVideoUrl = '';
        $this->pendingVideoPreview = null;
        $this->videoError = '';

        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $institutionId = current_institution_id() ?? 1;

        /** @var Activity $activity */
        $activity = Activity::where('institution_id', $institutionId)
            ->with('videos')
            ->findOrFail($id);
        $this->authorize('update', $activity);

        $this->editingActivityId = $activity->id;
        $this->classId = $activity->class_id;
        $this->title = $activity->title;
        $this->description = $activity->description ?? '';
        $this->instructions = $activity->instructions;
        $this->ageGroup = $activity->age_group;
        $this->publishDate = $activity->publish_date ? $activity->publish_date->toDateString() : now()->toDateString();
        $this->deadline = $activity->deadline ? $activity->deadline->format('Y-m-d\TH:i') : '';
        $this->allowLate = $activity->allow_late;
        $this->status = $activity->status->value;

        /** @var list<array{id: ?int, youtube_url: string, video_id: string, title: string, thumbnail_url: string, sort_order: int}> $loadedVideos */
        $loadedVideos = [];
        foreach ($activity->videos as $i => $v) {
            $loadedVideos[] = [
                'id' => $v->id,
                'youtube_url' => $v->youtube_url,
                'video_id' => $v->video_id,
                'title' => $v->title ?? "YouTube Video ({$v->video_id})",
                'thumbnail_url' => $v->thumbnailUrl(),
                'sort_order' => $v->sort_order,
            ];
        }
        $this->videos = $loadedVideos;
        $this->newVideoUrl = '';
        $this->pendingVideoPreview = null;
        $this->videoError = '';

        $this->showFormModal = true;
    }

    public function saveActivity(): void
    {
        $validated = $this->validate();
        $institutionId = current_institution_id() ?? 1;
        /** @var User $user */
        $user = auth()->user();

        // Verify target class belongs to same institution
        $class = SchoolClass::where('institution_id', $institutionId)->findOrFail($this->classId);

        $data = [
            'institution_id' => $institutionId,
            'class_id' => $class->id,
            'title' => trim($this->title),
            'description' => trim($this->description) !== '' ? trim($this->description) : null,
            'instructions' => trim($this->instructions),
            'age_group' => trim($this->ageGroup),
            'publish_date' => $this->publishDate ? Carbon::parse($this->publishDate)->toDateString() : now()->toDateString(),
            'deadline' => $this->deadline ? Carbon::parse($this->deadline) : null,
            'allow_late' => (bool) $this->allowLate,
            'status' => $this->status,
        ];

        if ($this->editingActivityId) {
            /** @var Activity $activity */
            $activity = Activity::where('institution_id', $institutionId)->findOrFail($this->editingActivityId);
            $this->authorize('update', $activity);
            $activity->update($data);
            session()->flash('success', __('Activity updated successfully.'));
        } else {
            $this->authorize('create', Activity::class);
            $data['created_by'] = $user->id;
            $activity = Activity::create($data);
            session()->flash('success', __('New activity created successfully.'));
        }

        // Synchronize activity videos
        $keptVideoIds = [];
        foreach ($this->videos as $i => $videoData) {
            $video = null;
            if (! empty($videoData['id'])) {
                $video = ActivityVideo::where('activity_id', $activity->id)->find($videoData['id']);
            }

            if ($video) {
                $video->update([
                    'youtube_url' => $videoData['youtube_url'],
                    'video_id' => $videoData['video_id'],
                    'title' => $videoData['title'],
                    'thumbnail_url' => $videoData['thumbnail_url'],
                    'sort_order' => $i,
                ]);
            } else {
                $video = ActivityVideo::create([
                    'activity_id' => $activity->id,
                    'youtube_url' => $videoData['youtube_url'],
                    'video_id' => $videoData['video_id'],
                    'title' => $videoData['title'],
                    'thumbnail_url' => $videoData['thumbnail_url'],
                    'sort_order' => $i,
                    'is_healthy' => true,
                ]);
            }
            $keptVideoIds[] = $video->id;
        }

        ActivityVideo::where('activity_id', $activity->id)
            ->whereNotIn('id', $keptVideoIds)
            ->delete();

        $this->showFormModal = false;
        $this->resetPage();
    }

    public function updatedNewVideoUrl(): void
    {
        $url = trim($this->newVideoUrl);
        if ($url === '') {
            $this->pendingVideoPreview = null;
            $this->videoError = '';

            return;
        }

        $youtube = app(YoutubeService::class);
        if (! $youtube->isValid($url)) {
            $this->pendingVideoPreview = null;
            $this->videoError = __('Please enter a valid YouTube video URL (e.g. watch, youtu.be, shorts, or embed).');

            return;
        }

        $this->videoError = '';
        $this->pendingVideoPreview = $youtube->fetchOEmbed($url);
    }

    public function addVideo(): void
    {
        $url = trim($this->newVideoUrl);
        if ($url === '') {
            return;
        }

        $youtube = app(YoutubeService::class);
        $preview = $this->pendingVideoPreview ?? $youtube->fetchOEmbed($url);

        if (! $preview['is_valid']) {
            $this->videoError = __('Please enter a valid YouTube video URL.');

            return;
        }

        // Avoid duplicate video IDs
        foreach ($this->videos as $v) {
            if ($v['video_id'] === $preview['video_id']) {
                $this->videoError = __('This video has already been added.');

                return;
            }
        }

        $this->videos[] = [
            'id' => null,
            'youtube_url' => $url,
            'video_id' => $preview['video_id'],
            'title' => $preview['title'],
            'thumbnail_url' => $preview['thumbnail_url'],
            'sort_order' => count($this->videos),
        ];

        $this->newVideoUrl = '';
        $this->pendingVideoPreview = null;
        $this->videoError = '';
    }

    public function removeVideo(int $index): void
    {
        if (isset($this->videos[$index])) {
            unset($this->videos[$index]);
            $this->videos = array_values($this->videos);
            foreach ($this->videos as $i => $v) {
                $this->videos[$i]['sort_order'] = $i;
            }
        }
    }

    public function moveVideoUp(int $index): void
    {
        if ($index > 0 && isset($this->videos[$index])) {
            $prev = $this->videos[$index - 1];
            $this->videos[$index - 1] = $this->videos[$index];
            $this->videos[$index] = $prev;

            $this->videos[$index - 1]['sort_order'] = $index - 1;
            $this->videos[$index]['sort_order'] = $index;
        }
    }

    public function moveVideoDown(int $index): void
    {
        if ($index < count($this->videos) - 1 && isset($this->videos[$index])) {
            $next = $this->videos[$index + 1];
            $this->videos[$index + 1] = $this->videos[$index];
            $this->videos[$index] = $next;

            $this->videos[$index]['sort_order'] = $index;
            $this->videos[$index + 1]['sort_order'] = $index + 1;
        }
    }

    public function watchVideo(string $videoId, string $title = ''): void
    {
        $this->watchVideoId = $videoId;
        $this->watchVideoTitle = $title;
        $this->showWatchModal = true;
    }

    public function closeWatchModal(): void
    {
        $this->showWatchModal = false;
        $this->watchVideoId = null;
        $this->watchVideoTitle = null;
    }

    public function publishActivity(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Activity $activity */
        $activity = Activity::where('institution_id', $institutionId)->findOrFail($id);
        $this->authorize('publish', $activity);

        $activity->update(['status' => ActivityStatus::Published->value]);
        session()->flash('success', __("Activity ':title' published.", ['title' => $activity->title]));
    }

    public function closeActivity(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Activity $activity */
        $activity = Activity::where('institution_id', $institutionId)->findOrFail($id);
        $this->authorize('close', $activity);

        $activity->update(['status' => ActivityStatus::Closed->value]);
        session()->flash('success', __("Activity ':title' closed.", ['title' => $activity->title]));
    }

    public function reopenActivity(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Activity $activity */
        $activity = Activity::where('institution_id', $institutionId)->findOrFail($id);
        $this->authorize('publish', $activity);

        $activity->update(['status' => ActivityStatus::Published->value]);
        session()->flash('success', __("Activity ':title' reopened.", ['title' => $activity->title]));
    }

    public function confirmDelete(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Activity $activity */
        $activity = Activity::where('institution_id', $institutionId)->findOrFail($id);
        $this->authorize('delete', $activity);

        $this->activityToDeleteId = $activity->id;
        $this->showDeleteModal = true;
    }

    public function deleteActivity(): void
    {
        if (! $this->activityToDeleteId) {
            return;
        }

        $institutionId = current_institution_id() ?? 1;
        /** @var Activity $activity */
        $activity = Activity::where('institution_id', $institutionId)->findOrFail($this->activityToDeleteId);
        $this->authorize('delete', $activity);

        $title = $activity->title;
        $activity->delete();

        $this->showDeleteModal = false;
        $this->activityToDeleteId = null;

        session()->flash('success', __("Activity ':title' deleted.", ['title' => $title]));
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var User $user */
        $user = auth()->user();

        // Available classes for dropdown and filter
        $classes = SchoolClass::where('institution_id', $institutionId)
            ->when(! $user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value]), function (Builder $q) use ($user) {
                $q->where(function (Builder $sub) use ($user) {
                    $sub->where('teacher_id', $user->id)
                        ->orWhere('kind', ClassKind::NurseryGroup);
                });
            })
            ->orderBy('title')
            ->get();

        // Query activities
        $query = Activity::where('institution_id', $institutionId)
            ->with(['schoolClass', 'creator', 'videos', 'materials']);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->classFilter !== 'all') {
            $query->where('class_id', (int) $this->classFilter);
        }

        if (! empty(trim($this->search))) {
            $term = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('instructions', 'like', $term);
            });
        }

        $activities = $query->orderByDesc('created_at')->paginate(10);

        // Stats summary
        $stats = [
            'total' => Activity::where('institution_id', $institutionId)->count(),
            'published' => Activity::where('institution_id', $institutionId)->where('status', ActivityStatus::Published->value)->count(),
            'draft' => Activity::where('institution_id', $institutionId)->where('status', ActivityStatus::Draft->value)->count(),
            'closed' => Activity::where('institution_id', $institutionId)->where('status', ActivityStatus::Closed->value)->count(),
        ];

        return view('livewire.teacher.activity-manager', [
            'activities' => $activities,
            'classes' => $classes,
            'stats' => $stats,
        ]);
    }
}
