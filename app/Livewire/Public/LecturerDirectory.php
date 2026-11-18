<?php

namespace App\Livewire\Public;

use App\Enums\PortfolioStatus;
use App\Models\TeacherProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
class LecturerDirectory extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedSubject = 'all';

    public int $perPage = 12;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedSubject(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;

        $query = TeacherProfile::query()
            ->where('institution_id', $institutionId)
            ->where('portfolio_status', PortfolioStatus::Published->value)
            ->with(['user', 'photo', 'classes']);

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('headline', 'like', $term)
                    ->orWhere('bio', 'like', $term)
                    ->orWhere('qualifications', 'like', $term)
                    ->orWhere('subjects', 'like', $term)
                    ->orWhereHas('user', function (Builder $userQ) use ($term) {
                        $userQ->where('name', 'like', $term);
                    });
            });
        }

        if ($this->selectedSubject !== 'all') {
            $query->where('subjects', 'like', '%"'.$this->selectedSubject.'"%');
        }

        $lecturers = $query->latest('published_at')->paginate($this->perPage);

        // Gather all distinct subjects from published portfolios
        $allSubjects = TeacherProfile::where('institution_id', $institutionId)
            ->where('portfolio_status', PortfolioStatus::Published->value)
            ->pluck('subjects')
            ->filter()
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        return view('livewire.public.lecturer-directory', [
            'lecturers' => $lecturers,
            'availableSubjects' => $allSubjects,
        ]);
    }
}
