<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bug: EloquentPropertyNameCollectionMetadataFactory includes HasMany
 * relations in the property name collection. This causes to-many relations
 * to leak into the serialized output as IRI arrays, and in SQLite test
 * environments (where column discovery fails) the "id" property is lost
 * entirely, preventing the item route from being registered.
 *
 * Fix: api-platform/core commit 47a710c30
 */
class Bug2HasManySerializationTest extends TestCase
{
    use RefreshDatabase;

    public function test_hasmany_relation_excluded_from_property_names(): void
    {
        $factory = app(\ApiPlatform\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface::class);
        $properties = iterator_to_array($factory->create(Post::class));

        $this->assertNotContains('comments', $properties, 'HasMany relation should not appear in property names');
    }

    public function test_get_post_item_returns_200(): void
    {
        $post = Post::create(['title' => 'Hello']);
        Comment::create(['post_id' => $post->id, 'body' => 'A comment']);

        $response = $this->get('/api/posts/' . $post->id, [
            'Accept' => 'application/ld+json',
        ]);

        $response->assertOk();
        $this->assertArrayNotHasKey('comments', $response->json());
    }
}
