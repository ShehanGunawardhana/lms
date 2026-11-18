<?php

namespace App\Livewire\Teacher;

use App\Enums\PortfolioStatus;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class PortfolioEditor extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public ?TeacherProfile $profile = null;

    public string $headline = '';

    public string $bio = '';

    public string $qualifications = '';

    public string $subjectsInput = '';

    /**
     * @var list<array{title: string, description: string, url: string, type: string}>
     */
    public array $sampleMaterials = [];

    /**
     * @var mixed
     */
    public $photo = null;

    public ?string $existingPhotoUrl = null;

    public string $portfolioStatus = 'draft';

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $institutionId = current_institution_id() ?? 1;

        $profile = TeacherProfile::firstOrCreate(
            [
                'institution_id' => $institutionId,
                'user_id' => $user->id,
            ],
            [
                'portfolio_status' => PortfolioStatus::Draft->value,
                'subjects' => [],
                'sample_materials' => [],
            ]
        );

        $this->authorize('update', $profile);

        $this->profile = $profile;
        $this->headline = (string) ($profile->headline ?? '');
        $this->bio = (string) ($profile->bio ?? '');
        $this->qualifications = (string) ($profile->qualifications ?? '');
        $this->portfolioStatus = (string) ($profile->portfolio_status ?? PortfolioStatus::Draft->value);

        if (is_array($profile->subjects)) {
            $this->subjectsInput = implode(', ', $profile->subjects);
        }

        if (is_array($profile->sample_materials)) {
            $this->sampleMaterials = $profile->sample_materials;
        }

        if ($profile->photo !== null) {
            $this->existingPhotoUrl = $profile->photo->temporarySignedUrl(now()->addHour());
        }
    }

    public function addSampleMaterial(): void
    {
        $this->sampleMaterials[] = [
            'title' => '',
            'description' => '',
            'url' => '',
            'type' => 'document',
        ];
    }

    public function removeSampleMaterial(int $index): void
    {
        if (isset($this->sampleMaterials[$index])) {
            unset($this->sampleMaterials[$index]);
            $this->sampleMaterials = array_values($this->sampleMaterials);
        }
    }

    public function saveDraft(FileService $fileService): void
    {
        if ($this->profile === null) {
            return;
        }

        $this->authorize('update', $this->profile);

        $this->validate([
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'qualifications' => ['nullable', 'string', 'max:2000'],
            'subjectsInput' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'max:10240'], // 10MB
            'sampleMaterials.*.title' => ['nullable', 'string', 'max:255'],
            'sampleMaterials.*.description' => ['nullable', 'string', 'max:1000'],
            'sampleMaterials.*.url' => ['nullable', 'string', 'max:1000'],
            'sampleMaterials.*.type' => ['nullable', 'string', 'in:document,video,link'],
        ]);

        $subjects = array_values(array_filter(array_map('trim', explode(',', $this->subjectsInput))));

        $data = [
            'headline' => $this->headline,
            'bio' => $this->bio,
            'qualifications' => $this->qualifications,
            'subjects' => $subjects,
            'sample_materials' => $this->sampleMaterials,
        ];

        if ($this->photo !== null) {
            /** @var User $user */
            $user = auth()->user();
            $asset = $fileService->store(
                $this->photo,
                purpose: 'profile_photo',
                allowedTypes: ['jpg', 'jpeg', 'png', 'webp'],
                uploader: $user
            );
            $data['photo_file_id'] = $asset->id;
            $this->existingPhotoUrl = $asset->temporarySignedUrl(now()->addHour());
            $this->photo = null;
        }

        $this->profile->update($data);

        audit(
            'portfolio.saved',
            'Teacher updated lecturer portfolio draft',
            $this->profile,
            ['status' => $this->profile->portfolio_status]
        );

        session()->flash('success', __('Portfolio draft saved successfully.'));
    }

    public function submitForApproval(FileService $fileService): void
    {
        if ($this->profile === null) {
            return;
        }

        $this->authorize('submit', $this->profile);

        $this->validate([
            'headline' => ['required', 'string', 'min:5', 'max:255'],
            'bio' => ['required', 'string', 'min:20', 'max:5000'],
            'qualifications' => ['required', 'string', 'min:5', 'max:2000'],
            'subjectsInput' => ['required', 'string', 'min:2', 'max:500'],
            'photo' => ['nullable', 'image', 'max:10240'],
            'sampleMaterials.*.title' => ['required_with:sampleMaterials.*.url', 'string', 'max:255'],
            'sampleMaterials.*.url' => ['nullable', 'url', 'max:1000'],
        ]);

        $subjects = array_values(array_filter(array_map('trim', explode(',', $this->subjectsInput))));

        if (empty($subjects)) {
            $this->addError('subjectsInput', __('Please specify at least one subject taught.'));

            return;
        }

        $data = [
            'headline' => $this->headline,
            'bio' => $this->bio,
            'qualifications' => $this->qualifications,
            'subjects' => $subjects,
            'sample_materials' => $this->sampleMaterials,
            'portfolio_status' => PortfolioStatus::Pending->value,
        ];

        if ($this->photo !== null) {
            /** @var User $user */
            $user = auth()->user();
            $asset = $fileService->store(
                $this->photo,
                purpose: 'profile_photo',
                allowedTypes: ['jpg', 'jpeg', 'png', 'webp'],
                uploader: $user
            );
            $data['photo_file_id'] = $asset->id;
            $this->existingPhotoUrl = $asset->temporarySignedUrl(now()->addHour());
            $this->photo = null;
        }

        $this->profile->update($data);
        $this->portfolioStatus = PortfolioStatus::Pending->value;

        audit(
            'portfolio.submitted',
            'Teacher submitted lecturer portfolio for administrative review',
            $this->profile,
            ['status' => PortfolioStatus::Pending->value]
        );

        session()->flash('success', __('Your portfolio has been submitted for administrative review.'));
    }

    public function recallToDraft(): void
    {
        if ($this->profile === null) {
            return;
        }

        $this->authorize('update', $this->profile);

        if ($this->profile->portfolio_status === PortfolioStatus::Pending->value) {
            $this->profile->update(['portfolio_status' => PortfolioStatus::Draft->value]);
            $this->portfolioStatus = PortfolioStatus::Draft->value;

            audit(
                'portfolio.recalled',
                'Teacher recalled portfolio from review back to draft',
                $this->profile,
                ['status' => PortfolioStatus::Draft->value]
            );

            session()->flash('success', __('Portfolio recalled to draft mode. You may continue editing.'));
        }
    }

    public function render(): View
    {
        return view('livewire.teacher.portfolio-editor');
    }
}
