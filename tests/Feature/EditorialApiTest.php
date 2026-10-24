<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditorialApiTest extends TestCase
{
    use DatabaseTransactions;
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_editorial_config_returns_publication_settings(): void
    {
        $response = $this->getJson('/api/editorial-config');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'brand_name',
                'tagline',
                'description',
                'navigation',
                'socials',
            ]);
    }

    public function test_public_articles_returns_only_published_articles(): void
    {
        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'excerpt',
                        'image_url',
                        'category',
                        'author',
                        'status',
                        'published_at',
                    ],
                ],
                'links',
                'meta',
            ]);

        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertEquals('published', $item['status']);
        }
    }

    public function test_public_article_by_slug_works_for_published_article(): void
    {
        $published = Article::published()->first();
        $this->assertNotNull($published, 'A published article should exist from seeders.');

        $response = $this->getJson("/api/articles/{$published->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('article.slug', $published->slug)
            ->assertJsonStructure(['article', 'related']);
    }

    public function test_public_article_detail_returns_404_for_unpublished_draft_to_guest(): void
    {
        $draft = Article::where('status', 'draft')->first();
        $this->assertNotNull($draft, 'A draft article should exist from seeders.');

        $response = $this->getJson("/api/articles/{$draft->slug}");

        $response->assertStatus(404);
    }

    public function test_registration_strictly_forces_reader_role(): void
    {
        $email = 'test_reader_' . time() . '@atelier.local';
        $response = $this->postJson('/api/register', [
            'name' => 'Attempted Hacker',
            'email' => $email,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'admin', // Malicious client attempt
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('user.role', 'reader')
            ->assertJsonStructure(['token', 'user']);

        $user = User::where('email', $email)->first();
        $this->assertEquals('reader', $user->role);
    }

    public function test_login_authenticates_and_issues_token(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'contributor@atelier.local',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user'])
            ->assertJsonPath('user.email', 'contributor@atelier.local');
    }

    public function test_contributor_can_create_draft_with_image(): void
    {
        $contributor = User::where('email', 'contributor@atelier.local')->first();
        $category = Category::first();

        $image = UploadedFile::fake()->create('architecture_monolith.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($contributor, 'sanctum')->postJson('/api/articles', [
            'title' => 'Tectonic Explorations in Slate and Cedar',
            'excerpt' => 'A deep dive into local slate shingles and charred cedar cladding.',
            'content' => '## Material Notes on Slate\n\nExploring textures in high humidity climates...',
            'category_id' => $category->id,
            'image' => $image,
            'image_alt' => 'Charred cedar facade against slate stone paving',
            'action' => 'draft',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('article.status', 'draft')
            ->assertJsonPath('article.author.id', $contributor->id);

        $this->assertDatabaseHas('articles', [
            'title' => 'Tectonic Explorations in Slate and Cedar',
            'status' => 'draft',
            'author_id' => $contributor->id,
        ]);
    }

    public function test_contributor_cannot_publish_article_directly(): void
    {
        $contributor = User::where('email', 'contributor@atelier.local')->first();
        $draft = Article::where('status', 'draft')->where('author_id', $contributor->id)->first();

        $response = $this->actingAs($contributor, 'sanctum')
            ->postJson("/api/editor/articles/{$draft->id}/publish");

        $response->assertStatus(403);
    }

    public function test_editor_can_review_and_publish_pending_article(): void
    {
        $editor = User::where('email', 'editor@atelier.local')->first();
        $pending = Article::where('status', 'pending')->first();
        $this->assertNotNull($pending, 'A pending article should exist from seeders.');

        $response = $this->actingAs($editor, 'sanctum')
            ->postJson("/api/editor/articles/{$pending->id}/publish");

        $response->assertStatus(200)
            ->assertJsonPath('article.status', 'published');

        $this->assertDatabaseHas('articles', [
            'id' => $pending->id,
            'status' => 'published',
        ]);
    }

    public function test_editor_can_reject_article_with_notes(): void
    {
        $editor = User::where('email', 'editor@atelier.local')->first();
        $pending = Article::where('status', 'pending')->first();

        // If no pending, create one for test
        if (!$pending) {
            $contributor = User::where('role', 'contributor')->first();
            $category = Category::first();
            $pending = Article::create([
                'title' => 'Temporary Pending Article',
                'slug' => 'temporary-pending-article-' . time(),
                'excerpt' => 'Temporary excerpt',
                'content' => 'Temporary content',
                'category_id' => $category->id,
                'author_id' => $contributor->id,
                'image' => 'articles/monolithic-concrete.jpg',
                'image_alt' => 'Alt text',
                'status' => 'pending',
            ]);
        }

        $note = 'Please expand section 2 with specific references to Japanese timber joinery.';

        $response = $this->actingAs($editor, 'sanctum')
            ->postJson("/api/editor/articles/{$pending->id}/reject", [
                'rejection_note' => $note,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('article.status', 'rejected')
            ->assertJsonPath('article.rejection_note', $note);
    }

    public function test_reader_can_bookmark_and_prevent_duplicates(): void
    {
        $reader = User::where('email', 'reader@atelier.local')->first();
        $article = Article::published()->first();

        $response = $this->actingAs($reader, 'sanctum')
            ->postJson("/api/articles/{$article->id}/bookmark");

        $response->assertStatus(200)
            ->assertJsonPath('is_bookmarked', true);

        // Try duplicate bookmark - should gracefully handle and remain true
        $secondResponse = $this->actingAs($reader, 'sanctum')
            ->postJson("/api/articles/{$article->id}/bookmark");

        $secondResponse->assertStatus(200)
            ->assertJsonPath('is_bookmarked', true);
    }

    public function test_reader_can_post_comment_on_published_article(): void
    {
        $reader = User::where('email', 'reader@atelier.local')->first();
        $article = Article::published()->first();

        $response = $this->actingAs($reader, 'sanctum')
            ->postJson("/api/articles/{$article->id}/comments", [
                'content' => 'The acoustic qualities of mineral plaster mentioned in this piece are extraordinary.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('comment.status', 'approved')
            ->assertJsonPath('comment.user.name', $reader->name);
    }
}
