<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Enums\RoleName;
use App\Exceptions\FileUploadException;
use App\Livewire\Teacher\QuestionBank;
use App\Models\Institution;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\FileService;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionMediaTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $teacher;

    private User $student;

    private User $otherInstitutionStudent;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::fake('public');

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->institution = Institution::factory()->create();
        app(InstitutionContext::class)->set($this->institution);

        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => 'secret123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->student = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => 'secret123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->student->assignRole(RoleName::Student->value);

        $otherInstitution = Institution::factory()->create();
        $this->otherInstitutionStudent = User::factory()->create([
            'institution_id' => $otherInstitution->id,
            'status' => 'active',
            'two_factor_secret' => 'secret123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->otherInstitutionStudent->assignRole(RoleName::Student->value);
    }

    public function test_genuine_mp3_wav_ogg_m4a_files_upload_successfully_with_audio_kind(): void
    {
        $service = app(FileService::class);

        // 1. MP3 with ID3v2 header
        $mp3Content = "ID3\x03\x00\x00\x00\x00\x00\x00".str_repeat("\x00", 30);
        $mp3File = UploadedFile::fake()->createWithContent('prompt.mp3', $mp3Content);
        $mp3Asset = $service->store($mp3File, 'question_asset', ['mp3', 'wav', 'ogg', 'm4a'], $this->teacher);

        $this->assertTrue($mp3Asset->exists);
        $this->assertSame('audio', $mp3Asset->kind);
        $this->assertSame('audio/mpeg', $mp3Asset->mime_verified);
        $this->assertTrue($mp3Asset->isAudio());
        Storage::disk('private')->assertExists($mp3Asset->path);

        // 2. MP3 with frame sync header (\xFF\xFB)
        $mp3SyncContent = "\xFF\xFB\x90\x64".str_repeat("\x00", 30);
        $mp3SyncFile = UploadedFile::fake()->createWithContent('sync.mp3', $mp3SyncContent);
        $mp3SyncAsset = $service->store($mp3SyncFile, 'question_asset', ['mp3'], $this->teacher);
        $this->assertSame('audio', $mp3SyncAsset->kind);
        $this->assertTrue($mp3SyncAsset->isAudio());

        // 3. WAV file (RIFF....WAVEfmt )
        $wavContent = 'RIFF'.pack('V', 36).'WAVEfmt '.str_repeat("\x00", 20);
        $wavFile = UploadedFile::fake()->createWithContent('listen.wav', $wavContent);
        $wavAsset = $service->store($wavFile, 'question_asset', ['wav'], $this->teacher);
        $this->assertSame('audio', $wavAsset->kind);
        $this->assertSame('audio/wav', $wavAsset->mime_verified);
        $this->assertTrue($wavAsset->isAudio());

        // 4. OGG file (OggS....)
        $oggContent = "OggS\x00\x02".str_repeat("\x00", 30);
        $oggFile = UploadedFile::fake()->createWithContent('speech.ogg', $oggContent);
        $oggAsset = $service->store($oggFile, 'question_asset', ['ogg'], $this->teacher);
        $this->assertSame('audio', $oggAsset->kind);
        $this->assertSame('audio/ogg', $oggAsset->mime_verified);
        $this->assertTrue($oggAsset->isAudio());

        // 5. M4A file (ftypM4A )
        $m4aContent = "\x00\x00\x00\x20ftypM4A \x00\x00\x00\x00".str_repeat("\x00", 20);
        $m4aFile = UploadedFile::fake()->createWithContent('audiobook.m4a', $m4aContent);
        $m4aAsset = $service->store($m4aFile, 'question_asset', ['m4a'], $this->teacher);
        $this->assertSame('audio', $m4aAsset->kind);
        $this->assertSame('audio/mp4', $m4aAsset->mime_verified);
        $this->assertTrue($m4aAsset->isAudio());
    }

    public function test_fake_audio_file_disguised_with_audio_extension_is_rejected(): void
    {
        $service = app(FileService::class);

        $fakeMp3 = UploadedFile::fake()->createWithContent('malicious.mp3', '<?php phpinfo(); ?>');

        $this->expectException(FileUploadException::class);
        $service->store($fakeMp3, 'question_asset', ['mp3'], $this->teacher);
    }

    public function test_get_inline_url_generates_signed_url_with_inline_flag(): void
    {
        $service = app(FileService::class);
        $mp3Content = "ID3\x03\x00\x00\x00\x00\x00\x00".str_repeat("\x00", 30);
        $file = UploadedFile::fake()->createWithContent('sample.mp3', $mp3Content);
        $asset = $service->store($file, 'question_asset', ['mp3'], $this->teacher);

        $inlineUrl = $asset->getInlineUrl();

        $this->assertStringContainsString('inline=1', $inlineUrl);
        $this->assertStringContainsString('signature=', $inlineUrl);
    }

    public function test_image_and_audio_question_assets_stream_inline_to_authorized_users(): void
    {
        $service = app(FileService::class);

        // Upload question image asset
        $imageFile = UploadedFile::fake()->image('diagram.png', 100, 100);
        $imageAsset = $service->store($imageFile, 'question_asset', ['png', 'jpg'], $this->teacher);

        // Upload question audio asset
        $mp3Content = "ID3\x03\x00\x00\x00\x00\x00\x00".str_repeat("\x00", 30);
        $audioFile = UploadedFile::fake()->createWithContent('pronunciation.mp3', $mp3Content);
        $audioAsset = $service->store($audioFile, 'question_asset', ['mp3'], $this->teacher);

        // 1. Teacher can stream image inline
        $imageResponse = $this->actingAs($this->teacher)->get($imageAsset->getInlineUrl());
        $imageResponse->assertStatus(200);
        $this->assertStringContainsString('image/png', (string) $imageResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', (string) $imageResponse->headers->get('Content-Disposition'));

        // 2. Enrolled student in same institution can stream audio inline
        $audioResponse = $this->actingAs($this->student)->get($audioAsset->getInlineUrl());
        $audioResponse->assertStatus(200);
        $this->assertStringContainsString('audio/mpeg', (string) $audioResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', (string) $audioResponse->headers->get('Content-Disposition'));

        // 3. Student from different institution is forbidden (403)
        $forbiddenResponse = $this->actingAs($this->otherInstitutionStudent)->get($audioAsset->getInlineUrl());
        $forbiddenResponse->assertStatus(403);
    }

    public function test_teacher_can_create_question_with_image_and_audio_uploads(): void
    {
        $imageFile = UploadedFile::fake()->image('cell_structure.png', 400, 300);
        $mp3Content = "ID3\x03\x00\x00\x00\x00\x00\x00".str_repeat("\x00", 50);
        $audioFile = UploadedFile::fake()->createWithContent('cell_audio.mp3', $mp3Content);

        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('openCreateModal')
            ->set('title', 'Biology Cell Diagram')
            ->set('question_text', 'Identify the organelle shown in the diagram and described in the audio clip.')
            ->set('type', QuestionType::SingleChoice->value)
            ->set('difficulty', QuestionDifficulty::Medium->value)
            ->set('default_marks', 2.0)
            ->set('questionImage', $imageFile)
            ->set('questionAudio', $audioFile)
            ->set('options.0.text', 'Mitochondria')
            ->set('options.0.is_correct', true)
            ->set('options.1.text', 'Chloroplast')
            ->set('options.1.is_correct', false)
            ->set('options.2.text', 'Nucleus')
            ->set('options.2.is_correct', false)
            ->set('options.3.text', 'Ribosome')
            ->set('options.3.is_correct', false)
            ->call('saveQuestion')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $question = Question::where('title', 'Biology Cell Diagram')->firstOrFail();
        $this->assertNotNull($question->image_file_id);
        $this->assertNotNull($question->audio_file_id);
        $this->assertTrue($question->hasImage());
        $this->assertTrue($question->hasAudio());
        $this->assertNotNull($question->getImageUrl());
        $this->assertNotNull($question->getAudioUrl());

        $this->assertSame('cell_structure.png', $question->imageFile->original_name);
        $this->assertSame('cell_audio.mp3', $question->audioFile->original_name);
        $this->assertSame('question_asset', $question->imageFile->purpose);
    }

    public function test_teacher_can_create_picture_choice_question_with_uploaded_option_images(): void
    {
        $imgA = UploadedFile::fake()->image('paris.jpg', 200, 200);
        $imgB = UploadedFile::fake()->image('london.jpg', 200, 200);
        $imgC = UploadedFile::fake()->image('tokyo.jpg', 200, 200);

        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('openCreateModal')
            ->set('type', QuestionType::PictureChoice->value)
            ->set('title', 'Identify the Capital of France')
            ->set('question_text', 'Select the image depicting Paris.')
            ->set('options.0.text', 'Paris')
            ->set('options.0.is_correct', true)
            ->set('optionImages.0', $imgA)
            ->set('options.1.text', 'London')
            ->set('options.1.is_correct', false)
            ->set('optionImages.1', $imgB)
            ->set('options.2.text', 'Tokyo')
            ->set('options.2.is_correct', false)
            ->set('optionImages.2', $imgC)
            ->call('saveQuestion')
            ->assertHasNoErrors();

        $question = Question::where('title', 'Identify the Capital of France')->with('options.imageFile')->firstOrFail();
        $this->assertCount(3, $question->options);

        $correctOption = $question->options->where('is_correct', true)->firstOrFail();
        $this->assertSame('Paris', $correctOption->option_text);
        $this->assertTrue($correctOption->hasImage());
        $this->assertNotNull($correctOption->getImageUrl());
        $this->assertSame('paris.jpg', $correctOption->imageFile->original_name);
    }

    public function test_teacher_can_create_matching_question_with_premise_and_target_images(): void
    {
        $flagImage = UploadedFile::fake()->image('japan_flag.png', 150, 100);
        $mapImage = UploadedFile::fake()->image('japan_map.png', 150, 100);

        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('openCreateModal')
            ->set('type', QuestionType::Matching->value)
            ->set('title', 'Country Match Japanese Geography')
            ->set('question_text', 'Match the flag and country map to their corresponding labels.')
            ->set('options.0.left', 'Japan Flag')
            ->set('options.0.right', 'Japan Map')
            ->set('optionImages.0', $flagImage)
            ->set('optionMatchImages.0', $mapImage)
            ->set('options.1.left', 'France Flag')
            ->set('options.1.right', 'France Map')
            ->set('options.2.left', 'Italy Flag')
            ->set('options.2.right', 'Italy Map')
            ->call('saveQuestion')
            ->assertHasNoErrors();

        $question = Question::where('title', 'Country Match Japanese Geography')
            ->with(['options.imageFile', 'options.matchImageFile'])
            ->firstOrFail();

        $firstPair = $question->options->first();
        $this->assertTrue($firstPair->hasImage());
        $this->assertTrue($firstPair->hasMatchImage());
        $this->assertSame('japan_flag.png', $firstPair->imageFile->original_name);
        $this->assertSame('japan_map.png', $firstPair->matchImageFile->original_name);
    }

    public function test_teacher_can_remove_media_during_question_edit(): void
    {
        $service = app(FileService::class);
        $imageFile = UploadedFile::fake()->image('remove_me.png', 100, 100);
        $asset = $service->store($imageFile, 'question_asset', ['png'], $this->teacher);

        $question = Question::factory()
            ->has(QuestionOption::factory()->count(2)->state(new Sequence(
                ['option_text' => 'Option A', 'is_correct' => true],
                ['option_text' => 'Option B', 'is_correct' => false],
            )), 'options')
            ->create([
                'institution_id' => $this->institution->id,
                'created_by' => $this->teacher->id,
                'image_file_id' => $asset->id,
                'title' => 'Question to remove image from',
            ]);

        $this->assertTrue($question->hasImage());

        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('editQuestion', $question->id)
            ->assertSet('existingImageId', $asset->id)
            ->call('removeQuestionImage')
            ->assertSet('existingImageId', null)
            ->call('saveQuestion')
            ->assertHasNoErrors();

        $question->refresh();
        $this->assertNull($question->image_file_id);
        $this->assertFalse($question->hasImage());
    }

    public function test_question_duplication_preserves_media_attachments(): void
    {
        $service = app(FileService::class);
        $img = UploadedFile::fake()->image('flower.png', 100, 100);
        $imgAsset = $service->store($img, 'question_asset', ['png'], $this->teacher);

        $mp3Content = "ID3\x03\x00\x00\x00\x00\x00\x00".str_repeat("\x00", 30);
        $audio = UploadedFile::fake()->createWithContent('narration.mp3', $mp3Content);
        $audioAsset = $service->store($audio, 'question_asset', ['mp3'], $this->teacher);

        $question = Question::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Botany Quiz Original',
            'image_file_id' => $imgAsset->id,
            'audio_file_id' => $audioAsset->id,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('duplicateQuestion', $question->id);

        $copy = Question::where('title', 'Botany Quiz Original (Copy)')->firstOrFail();
        $this->assertSame($imgAsset->id, $copy->image_file_id);
        $this->assertSame($audioAsset->id, $copy->audio_file_id);
        $this->assertTrue($copy->hasImage());
        $this->assertTrue($copy->hasAudio());
    }
}
