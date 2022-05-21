<?php

namespace Tests\Feature\Articles;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginateArticlesTest extends TestCase
{
    use RefreshDatabase;

    /**  @test */
    public function can_paginate_articles()
    {
        $articles = Article::factory()->count(6)->create();

        // articles?page[size]=2&page[number]=2

        $url = route('api.v1.articles.index', [
            'page' => [
                'size' => 2,
                'number' => 2
            ]
        ]);

        $response = $this->getJson($url);

        $response->assertSee([
            $articles[2]->title,
            $articles[3]->title,
        ]);
        
        $response->assertDontSee([
            $articles[0]->title,
            $articles[1]->title,
            $articles[4]->title,
            $articles[5]->title
        ]);
        
        $response->assertJsonStructure([
            'links' => ['first', 'last', 'prev', 'next']
        ]);

        $firstLink = urldecode($response->json('links.first'));

        $this->assertStringContainsString('page[size]=2', $firstLink);
        $this->assertStringContainsString('page[number]=1', $firstLink);
    }
}
