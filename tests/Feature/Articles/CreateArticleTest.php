<?php

namespace Tests\Feature\Articles;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateArticleTest extends TestCase
{
    use RefreshDatabase;

    /**  @test */
    public function can_create_articles()
    {
        $this->withoutExceptionHandling();

        $response = $this->postJson(route('api.v1.articles.store'), [
            'title' => 'Nuevo articulo',
            'slug' => 'nuevo-articulo',
            'content' => 'Contenido del articulo',
        ]);

        $response->assertCreated();

        $article = Article::first();

        $response->assertHeader('location', route('api.v1.articles.show', $article));

        $response->assertExactJson([
            'data' => [
                'type' => 'articles',
                'id' => (string) $article->getRouteKey(),
                'attributes' => [
                    'title' => 'Nuevo articulo',
                    'slug' => 'nuevo-articulo',
                    'content' => 'Contenido del articulo',
                ],
                'links' => [
                    'self' => route('api.v1.articles.show', $article),
                ],
            ],
        ]);
    }

    /**  @test */
    public function title_is_required()
    {
        $this->postJson(route('api.v1.articles.store'), [
            'slug' => 'nuevo-articulo',
            'content' => 'Contenido del articulo',

        ])->assertJsonApiValidationErrors('title');
    }

    /**  @test */
    public function slug_is_required()
    {
        $this->postJson(route('api.v1.articles.store'), [
            'title' => 'Nuevo articulo',
            'content' => 'Contenido del articulo'
        ])->assertJsonApiValidationErrors('slug');
    }

    /**  @test */
    public function slug_must_be_unique()
    {
        $article = Article::factory()->create();

        $this->postJson(route('api.v1.articles.store'), [
            'title' => 'Nuevo articulo',
            'slug' => $article->slug,
            'content' => 'Contenido del articulo'
        ])->assertJsonApiValidationErrors('slug');
    }

    /**  @test */
    public function slug_must_only_contain_letters_numbers_and_dashes()
    {
        $this->postJson(route('api.v1.articles.store'), [
            'title' => 'Nuevo articulo',
            'slug' => '$%^&',
            'content' => 'Contenido del articulo'
        ])->assertJsonApiValidationErrors('slug');
    }

    /**  @test */
    public function slug_must_not_contain_underscores()
    {
        $this->postJson(route('api.v1.articles.store'), [
            'title' => 'Nuevo articulo',
            'slug' => 'with_underscores',
            'content' => 'Contenido del articulo'
        ])->assertSee(trans('validation.no_underscores', [
            'attribute' => 'data.attributes.slug'
        ]))->assertJsonApiValidationErrors('slug');
    }

    /**  @test */
    public function slug_must_not_start_with_dashes()
    {
        $this->postJson(route('api.v1.articles.store'), [
            'title' => 'Nuevo articulo',
            'slug' => '-start-with-dashes',
            'content' => 'Contenido del articulo'
        ])->assertSee(trans('validation.no_starting_dashes', [
            'attribute' => 'data.attributes.slug'
        ]))->assertJsonApiValidationErrors('slug');
    }

    /**  @test */
    public function slug_must_not_end_with_dashes()
    {
        $this->postJson(route('api.v1.articles.store'), [
            'title' => 'Nuevo articulo',
            'slug' => 'end-with-dashes-',
            'content' => 'Contenido del articulo'
        ])->assertSee(trans('validation.no_ending_dashes', [
            'attribute' => 'data.attributes.slug'
        ]))->assertJsonApiValidationErrors('slug');
    }

    /**  @test */
    public function content_is_required()
    {
        $this->postJson(route('api.v1.articles.store'), [
            'title' => 'Nuevo articulo',
            'slug' => 'nuevo-articulo',
        ])->assertJsonApiValidationErrors('content');
    }
}
