<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $post1 = Post::create(['title' => 'First post']);
        $post2 = Post::create(['title' => 'Second post']);
        $post3 = Post::create(['title' => 'Third post']);

        Comment::create(['post_id' => $post1->id, 'body' => 'Great post!']);
        Comment::create(['post_id' => $post1->id, 'body' => 'Thanks for sharing.']);
        Comment::create(['post_id' => $post2->id, 'body' => 'Interesting read.']);
    }
}
