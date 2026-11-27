<?php

declare(strict_types=1);

namespace App\Livewire\Teacher;

use App\Enums\AssignmentStatus;
use App\Enums\RoleName;
use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\DeadlineExtensionService;
use App\Services\FileService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AssignmentManager extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;
    use WithPagination;

    // Filters
    public string $search = '';

    public string $statusFilter = 'all'; // all, draft, published, closed

    public string $classFilter = 'all';

    // Modal state
    public bool $showModal = false;

    public ?int $editingAssignmentId = null;

    // Form fields
    public ?int $classId = null;

    public string $title = '';

    public string $description = '';

    public string $instructions = '';

    public string $status = 'draft';

    /** @var array<string> */
    public array $allowedFileTypes = ['pdf', 'docx', 'zip', 'jpg', 'png'];

    public int $maxFileSizeMb = 50;

    public string $deadline = '';

    public bool $allowLate = false;

    public int $resubmissionLimit = 3;

    public int $totalMarks = 100;

    public ?int $passMarks = 50;

    // Attachments
    /** @var list<array{id: ?int, type: string, title: string, url: ?string, file_asset_id: ?int, file_name: ?string, file_size: ?string, sort_order: int}> */
    public array $attachments = [];

    public string $attachmentTab = 'file'; // 'file' or 'link'

    public string $attachmentTitle = '';

    public string $linkTitle = '';

    public string $linkUrl = '';

    public mixed $attachmentFile = null;

    public string $attachmentError = '';

    // Extension Modal state (§8, T-5.3)
    public bool $showExtensionModal = false;

    public ?int $extendingAssignmentId = null;

    public string $extensionTarget = 'everyone'; // 'everyone' or 'individual'

    public ?int $extensionStudentId = null;

    public string $extensionNewDeadline = '';

    public string $extensionReason = '';

    public string $extensionError = '';

    // Extension Log History Modal state
    public bool $showExtensionLogModal = false;

    public ?int $extensionLogAssignmentId = null;

    /**
     * Preset of available file extensions for selection (§8, §10).
     *
     * @var array<string, string>
     */
    public const AVAILABLE_FILE_TYPES = [
        'pdf' => 'PDF Document (.pdf)',
        'docx' => 'Word Document (.docx, .doc)',
        'zip' => 'ZIP Archive (.zip)',
        'jpg' => 'JPEG Image (.jpg, .jpeg)',
        'png' => 'PNG Image (.png)',
        'mp4' => 'Video (.mp4, .mov, .webm)',
    ];

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
        $this->editingAssignmentId = null;

        $institutionId = current_institution_id() ?? 1;
        /** @var User $user */
        $user = auth()->user();

        // Default to first accessible class
        $firstClass = SchoolClass::where('institution_id', $institutionId)
            ->when(! $user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value]), fn ($q) => $q->where('teacher_id', $user->id))
            ->first();

        $this->classId = $firstClass?->id;
        $this->title = '';
        $this->description = '';
        $this->instructions = '';
        $this->status = AssignmentStatus::Draft->value;
        $this->allowedFileTypes = ['pdf', 'docx', 'zip', 'jpg', 'png'];
        $this->maxFileSizeMb = 50;
        $this->deadline = now()->addDays(7)->setTime(23, 59)->format('Y-m-d\TH:i');
        $this->allowLate = false;
        $this->resubmissionLimit = 3;
        $this->totalMarks = 100;
        $this->passMarks = 50;

        $this->attachments = [];
        $this->attachmentTab = 'file';
        $this->attachmentTitle = '';
        $this->linkTitle = '';
        $this->linkUrl = '';
        $this->attachmentFile = null;
        $this->attachmentError = '';

        $this->showModal = true;
    }

    public function openEditModal(int $assignmentId): void
    {
        $this->resetValidation();
        $institutionId = current_institution_id() ?? 1;

        /** @var Assignment $assignment */
        $assignment = Assignment::where('institution_id', $institutionId)
            ->with(['attachments.fileAsset'])
            ->findOrFail($assignmentId);

        $this->authorize('update', $assignment);

        $this->editingAssignmentId = $assignment->id;
        $this->classId = $assignment->class_id;
        $this->title = $assignment->title;
        $this->description = $assignment->description ?? '';
        $this->instructions = $assignment->instructions ?? '';
        $this->status = $assignment->status->value;
        $this->allowedFileTypes = $assignment->allowed_file_types ?: ['pdf', 'docx', 'zip', 'jpg', 'png'];
        $this->maxFileSizeMb = $assignment->max_file_size_mb;
        $this->deadline = $assignment->deadline->format('Y-m-d\TH:i');
        $this->allowLate = $assignment->allow_late;
        $this->resubmissionLimit = $assignment->resubmission_limit;
        $this->totalMarks = $assignment->total_marks;
        $this->passMarks = $assignment->pass_marks;

        $loadedAttachments = [];
        foreach ($assignment->attachments as $att) {
            $loadedAttachments[] = [
                'id' => $att->id,
                'type' => $att->type,
                'title' => $att->title,
                'url' => $att->url,
                'file_asset_id' => $att->file_asset_id,
                'file_name' => $att->fileAsset?->original_name,
                'file_size' => $att->formattedSize(),
                'sort_order' => $att->sort_order,
            ];
        }

        $this->attachments = $loadedAttachments;
        $this->attachmentTab = 'file';
        $this->attachmentTitle = '';
        $this->linkTitle = '';
        $this->linkUrl = '';
        $this->attachmentFile = null;
        $this->attachmentError = '';

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingAssignmentId = null;
        $this->resetValidation();
    }

    public function addLinkAttachment(): void
    {
        $this->attachmentError = '';
        $title = trim($this->linkTitle);
        $url = trim($this->linkUrl);

        if ($title === '') {
            $this->attachmentError = __('Please provide a title for the external resource link.');

            return;
        }

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            $this->attachmentError = __('Please provide a valid web URL (e.g. https://...).');

            return;
        }

        $this->attachments[] = [
            'id' => null,
            'type' => 'link',
            'title' => $title,
            'url' => $url,
            'file_asset_id' => null,
            'file_name' => null,
            'file_size' => null,
            'sort_order' => count($this->attachments),
        ];

        $this->linkTitle = '';
        $this->linkUrl = '';
        $this->attachmentError = '';
    }

    public function uploadFileAttachment(): void
    {
        $this->attachmentError = '';

        if (! $this->attachmentFile) {
            $this->attachmentError = __('Please select a file to upload.');

            return;
        }

        $this->validate([
            'attachmentFile' => ['required', 'file', 'max:51200'], // 50MB
        ]);

        /** @var User $user */
        $user = auth()->user();
        $fileService = app(FileService::class);

        $title = trim($this->attachmentTitle);
        if ($title === '') {
            $origName = $this->attachmentFile->getClientOriginalName();
            $title = pathinfo($origName, PATHINFO_FILENAME);
        }

        try {
            $fileAsset = $fileService->store(
                $this->attachmentFile,
                'assignment_attachment',
                ['pdf', 'jpg', 'jpeg', 'png', 'zip', 'doc', 'docx', 'mp4'],
                $user
            );

            $this->attachments[] = [
                'id' => null,
                'type' => 'file',
                'title' => $title,
                'url' => null,
                'file_asset_id' => $fileAsset->id,
                'file_name' => $fileAsset->original_name,
                'file_size' => $fileAsset->size > 0 ? number_format($fileAsset->size / 1048576, 1).' MB' : '0 MB',
                'sort_order' => count($this->attachments),
            ];

            $this->attachmentTitle = '';
            $this->attachmentFile = null;
            $this->attachmentError = '';
        } catch (\Throwable $e) {
            $this->attachmentError = $e->getMessage();
        }
    }

    public function removeAttachment(int $index): void
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }

    public function saveAssignment(): void
    {
        $this->validate([
            'classId' => ['required', 'exists:school_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'instructions' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'in:draft,published,closed'],
            'allowedFileTypes' => ['required', 'array', 'min:1'],
            'allowedFileTypes.*' => ['string'],
            'maxFileSizeMb' => ['required', 'integer', 'min:1', 'max:50'],
            'deadline' => ['required', 'date'],
            'allowLate' => ['boolean'],
            'resubmissionLimit' => ['required', 'integer', 'min:0', 'max:50'],
            'totalMarks' => ['required', 'integer', 'min:1', 'max:10000'],
            'passMarks' => ['nullable', 'integer', 'min:0', 'lte:totalMarks'],
        ]);

        $institutionId = current_institution_id() ?? 1;
        /** @var User $user */
        $user = auth()->user();

        $class = SchoolClass::where('institution_id', $institutionId)->findOrFail($this->classId);

        $data = [
            'institution_id' => $institutionId,
            'class_id' => $class->id,
            'title' => trim($this->title),
            'description' => trim($this->description) !== '' ? trim($this->description) : null,
            'instructions' => trim($this->instructions) !== '' ? trim($this->instructions) : null,
            'status' => $this->status,
            'allowed_file_types' => array_values($this->allowedFileTypes),
            'max_file_size_mb' => $this->maxFileSizeMb,
            'deadline' => Carbon::parse($this->deadline),
            'allow_late' => (bool) $this->allowLate,
            'resubmission_limit' => $this->resubmissionLimit,
            'total_marks' => $this->totalMarks,
            'pass_marks' => $this->passMarks ?: null,
        ];

        if ($this->editingAssignmentId) {
            /** @var Assignment $assignment */
            $assignment = Assignment::where('institution_id', $institutionId)->findOrFail($this->editingAssignmentId);
            $this->authorize('update', $assignment);
            $assignment->update($data);

            audit('assignment.updated', "Updated assignment '{$assignment->title}'", $assignment);
            session()->flash('success', __('Assignment updated successfully.'));
        } else {
            $this->authorize('create', Assignment::class);
            $data['created_by'] = $user->id;
            $assignment = Assignment::create($data);

            audit('assignment.created', "Created new assignment '{$assignment->title}'", $assignment);
            session()->flash('success', __('New assignment created successfully.'));
        }

        // Synchronize attachments
        $keptAttachmentIds = [];
        foreach ($this->attachments as $i => $attData) {
            $att = null;
            if (! empty($attData['id'])) {
                $att = AssignmentAttachment::where('assignment_id', $assignment->id)->find($attData['id']);
            }

            if ($att) {
                $att->update([
                    'type' => $attData['type'],
                    'title' => $attData['title'],
                    'url' => $attData['url'],
                    'file_asset_id' => $attData['file_asset_id'],
                    'sort_order' => $i,
                ]);
            } else {
                $att = AssignmentAttachment::create([
                    'assignment_id' => $assignment->id,
                    'type' => $attData['type'],
                    'title' => $attData['title'],
                    'url' => $attData['url'],
                    'file_asset_id' => $attData['file_asset_id'],
                    'sort_order' => $i,
                ]);
            }
            $keptAttachmentIds[] = $att->id;
        }

        AssignmentAttachment::where('assignment_id', $assignment->id)
            ->whereNotIn('id', $keptAttachmentIds)
            ->delete();

        $this->showModal = false;
        $this->editingAssignmentId = null;
        $this->resetPage();
    }

    public function publishAssignment(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Assignment $assignment */
        $assignment = Assignment::where('institution_id', $institutionId)->findOrFail($id);

        $this->authorize('publish', $assignment);

        $assignment->update(['status' => AssignmentStatus::Published->value]);

        audit('assignment.published', "Published assignment '{$assignment->title}'", $assignment);
        session()->flash('success', __('Assignment ":title" has been published.', ['title' => $assignment->title]));
    }

    public function closeAssignment(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Assignment $assignment */
        $assignment = Assignment::where('institution_id', $institutionId)->findOrFail($id);

        $this->authorize('publish', $assignment);

        $assignment->update(['status' => AssignmentStatus::Closed->value]);

        audit('assignment.closed', "Closed assignment '{$assignment->title}'", $assignment);
        session()->flash('success', __('Assignment ":title" is now closed.', ['title' => $assignment->title]));
    }

    public function reopenAssignment(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Assignment $assignment */
        $assignment = Assignment::where('institution_id', $institutionId)->findOrFail($id);

        $this->authorize('publish', $assignment);

        $assignment->update(['status' => AssignmentStatus::Published->value]);

        audit('assignment.reopened', "Reopened assignment '{$assignment->title}'", $assignment);
        session()->flash('success', __('Assignment ":title" has been reopened.', ['title' => $assignment->title]));
    }

    public function deleteAssignment(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Assignment $assignment */
        $assignment = Assignment::where('institution_id', $institutionId)->findOrFail($id);

        $this->authorize('delete', $assignment);

        $title = $assignment->title;
        $assignment->delete();

        audit('assignment.deleted', "Deleted assignment '{$title}'", $assignment);
        session()->flash('success', __('Assignment ":title" has been deleted.', ['title' => $title]));
    }

    public function openExtensionModal(int $assignmentId): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Assignment $assignment */
        $assignment = Assignment::where('institution_id', $institutionId)->findOrFail($assignmentId);
        $this->authorize('update', $assignment);

        $this->extendingAssignmentId = $assignmentId;
        $this->extensionTarget = 'everyone';
        $this->extensionStudentId = null;
        $this->extensionReason = '';
        $this->extensionError = '';

        $effectiveDeadline = $assignment->effectiveDeadlineForStudent();
        $this->extensionNewDeadline = $effectiveDeadline->copy()->addDays(2)->format('Y-m-d\TH:i');

        $this->showExtensionModal = true;
    }

    public function closeExtensionModal(): void
    {
        $this->showExtensionModal = false;
        $this->extendingAssignmentId = null;
        $this->extensionTarget = 'everyone';
        $this->extensionStudentId = null;
        $this->extensionNewDeadline = '';
        $this->extensionReason = '';
        $this->extensionError = '';
    }

    public function saveExtension(?DeadlineExtensionService $extensionService = null): void
    {
        $extensionService ??= app(DeadlineExtensionService::class);

        if ($this->extendingAssignmentId === null) {
            return;
        }

        $institutionId = current_institution_id() ?? 1;
        /** @var Assignment $assignment */
        $assignment = Assignment::where('institution_id', $institutionId)->findOrFail($this->extendingAssignmentId);
        $this->authorize('update', $assignment);

        $rules = [
            'extensionTarget' => ['required', 'in:everyone,individual'],
            'extensionNewDeadline' => ['required', 'date'],
            'extensionReason' => ['required', 'string', 'min:5', 'max:500'],
        ];

        if ($this->extensionTarget === 'individual') {
            $rules['extensionStudentId'] = ['required', 'integer', 'exists:students,id'];
        }

        $this->validate($rules);

        $newDeadlineCarbon = Carbon::parse($this->extensionNewDeadline);
        $currentDeadline = $assignment->effectiveDeadlineForStudent($this->extensionTarget === 'individual' ? $this->extensionStudentId : null);

        if ($newDeadlineCarbon->isPast()) {
            $this->extensionError = __('The new extended deadline cannot be in the past.');

            return;
        }

        if ($newDeadlineCarbon->lte($currentDeadline)) {
            $this->extensionError = __('The new deadline must be strictly after the current deadline (:date).', [
                'date' => $currentDeadline->format('M j, Y • g:i A'),
            ]);

            return;
        }

        try {
            /** @var User $currentUser */
            $currentUser = auth()->user();

            $extensionService->extendAssignmentDeadline(
                assignment: $assignment,
                extendedBy: $currentUser,
                newDeadline: $newDeadlineCarbon,
                reason: $this->extensionReason,
                studentId: $this->extensionTarget === 'individual' ? $this->extensionStudentId : null,
            );

            session()->flash('success', __('Deadline extension granted successfully and recipients notified.'));
            $this->closeExtensionModal();
        } catch (\Throwable $e) {
            $this->extensionError = $e->getMessage();
        }
    }

    public function openExtensionLogModal(int $assignmentId): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Assignment $assignment */
        $assignment = Assignment::where('institution_id', $institutionId)->findOrFail($assignmentId);
        $this->authorize('view', $assignment);

        $this->extensionLogAssignmentId = $assignmentId;
        $this->showExtensionLogModal = true;
    }

    public function closeExtensionLogModal(): void
    {
        $this->showExtensionLogModal = false;
        $this->extensionLogAssignmentId = null;
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var User $user */
        $user = auth()->user();

        // 1. Classes query for teacher filter dropdown
        $classesQuery = SchoolClass::where('institution_id', $institutionId)->orderBy('title');
        if (! $user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            $classesQuery->where('teacher_id', $user->id);
        }
        $classes = $classesQuery->get();

        // 2. Metrics calculation
        $baseQuery = Assignment::where('institution_id', $institutionId);
        if (! $user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            $baseQuery->where(function (Builder $q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('schoolClass', fn ($sc) => $sc->where('teacher_id', $user->id));
            });
        }

        $allAssignments = (clone $baseQuery)->get();
        $metrics = [
            'total' => $allAssignments->count(),
            'published' => $allAssignments->where('status', AssignmentStatus::Published)->count(),
            'draft' => $allAssignments->where('status', AssignmentStatus::Draft)->count(),
            'closed' => $allAssignments->where('status', AssignmentStatus::Closed)->count(),
        ];

        // 3. Paginated assignments query
        $query = (clone $baseQuery)->with(['schoolClass', 'creator', 'attachments.fileAsset', 'extensions.student', 'extensions.extendedBy']);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->classFilter !== 'all') {
            $query->where('class_id', $this->classFilter);
        }

        if (trim($this->search) !== '') {
            $searchTerm = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        $assignments = $query
            ->orderByRaw("CASE WHEN status = 'published' THEN 0 WHEN status = 'draft' THEN 1 ELSE 2 END")
            ->orderBy('deadline')
            ->orderByDesc('id')
            ->paginate(10);

        $extendingAssignment = null;
        if ($this->extendingAssignmentId !== null) {
            $extendingAssignment = Assignment::where('institution_id', $institutionId)
                ->with(['schoolClass.students' => function ($q) {
                    $q->wherePivot('status', 'active')->orderBy('first_name');
                }])
                ->find($this->extendingAssignmentId);
        }

        $extensionLogAssignment = null;
        if ($this->extensionLogAssignmentId !== null) {
            $extensionLogAssignment = Assignment::where('institution_id', $institutionId)
                ->with(['extensions.extendedBy', 'extensions.student'])
                ->find($this->extensionLogAssignmentId);
        }

        return view('livewire.teacher.assignment-manager', [
            'assignments' => $assignments,
            'classes' => $classes,
            'metrics' => $metrics,
            'availableFileTypes' => self::AVAILABLE_FILE_TYPES,
            'extendingAssignment' => $extendingAssignment,
            'extensionLogAssignment' => $extensionLogAssignment,
        ]);
    }
}
