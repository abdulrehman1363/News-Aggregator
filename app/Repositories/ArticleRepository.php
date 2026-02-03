<?php

namespace App\Repositories;

use App\Exceptions\RepositoryException;
use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(
        protected Article $model
    ) {}

    public function findOrFail(int $id): Model
    {
        try {
            return $this->model
                ->with(['source', 'category', 'author'])
                ->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw RepositoryException::notFound('Article', $id);
        } catch (\Exception $e) {
            Log::error('Failed to find article', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::queryFailed('ArticleRepository', 'findOrFail', $e);
        }
    }

    public function bulkInsert(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        try {
            // Insert in chunks to avoid memory issues
            $chunks = array_chunk($data, 500);

            DB::beginTransaction();

            foreach ($chunks as $chunk) {
                DB::table('articles')->insert($chunk);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to bulk insert articles', [
                'count' => count($data),
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::createFailed('Articles (bulk)', $e);
        }
    }

    public function getExistingUrls(array $urls): array
    {
        try {
            return $this->model
                ->whereIn('url', $urls)
                ->pluck('url')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get existing URLs', [
                'count' => count($urls),
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::queryFailed('ArticleRepository', 'getExistingUrls', $e);
        }
    }

    public function search(array $filters): LengthAwarePaginator
    {
        try {
            $query = $this->model->with(['source', 'category', 'author']);
            dd($filters['keyword']);
            // Search by keyword using PostgreSQL full-text search
            $query->when(!empty($filters['keyword']), function ($q) use ($filters) {
                $keyword = $filters['keyword'];

                $q->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$keyword]);
            });

            // Filter by date range
            $query->when(!empty($filters['from']), function ($q) use ($filters) {
                $q->where('published_at', '>=', $filters['from']);
            });

            $query->when(!empty($filters['to']), function ($q) use ($filters) {
                $q->where('published_at', '<=', $filters['to']);
            });

            // Filter by sources
            $query->when(!empty($filters['sources']) && is_array($filters['sources']), function ($q) use ($filters) {
                $q->whereHas('source', function ($subQuery) use ($filters) {
                    $subQuery->whereIn('id', $filters['sources']);
                });
            });

            // Filter by categories
            $query->when(!empty($filters['categories']) && is_array($filters['categories']), function ($q) use ($filters) {
                $q->whereHas('category', function ($subQuery) use ($filters) {
                    $subQuery->whereIn('id', $filters['categories']);
                });
            });

            // Filter by authors
            $query->when(!empty($filters['authors']) && is_array($filters['authors']), function ($q) use ($filters) {
                $q->whereHas('author', function ($subQuery) use ($filters) {
                    $subQuery->whereIn('id', $filters['authors']);
                });
            });

            // Sort by published date (newest first)
            $query->orderBy('published_at', 'desc');

            // Paginate results
            $perPage = $filters['per_page'] ?? 15;
            return $query->paginate($perPage);
        } catch (\Exception $e) {
            Log::error('Failed to search articles', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::queryFailed('ArticleRepository', 'search', $e);
        }
    }
}
