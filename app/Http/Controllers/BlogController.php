<?php

namespace App\Http\Controllers;

use App\Http\Requests\{StoreBlogRequest, UpdateBlogRequest};
use App\Http\Resources\{BlogCollection, BlogResource};
use App\Models\Blog;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{JsonResponse, Request, Response};
use Illuminate\Support\Facades\{Cache, DB, Log, Schema};

class BlogController extends Controller
{

    /**
     * Exibe uma listagem de blogs.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request): JsonResponse
    {
        try {
            // Obtém parâmetros de consulta para filtragem e paginação
            $perPage = $request->query('per_page', 15);

            // Coluna de classificação
            $sortBy = $request->query('sort_by', 'created_at');
            $validSortColumns = ['created_at', 'updated_at']; // Adicione outras colunas para ordens de classificação
            if (!in_array(strtolower($sortBy), $validSortColumns)) {
                $sortBy = 'created_at'; // Correção: atribui a $sortBy, não a $sortOrder
            }

            // Ordem de classificação
            $sortOrder = $request->query('sort_order', 'desc');
            $validSortOrders = ['asc', 'desc'];
            if (!in_array(strtolower($sortOrder), $validSortOrders)) {
                $sortOrder = 'desc';
            }

            // Chave de cache única baseada nos parâmetros de consulta
            $cacheKey = "blogs_page{$perPage}_{$sortBy}_{$sortOrder}";

            // Tenta encontrar a coleção de blogs usando o cache
            $blogs = Cache::remember($cacheKey, 3600, function () use ($perPage, $sortBy, $sortOrder) {
                $query = Blog::query();
                return $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
            });

            // Verificar se algum resultado foi encontrado
            if ($blogs->isEmpty()) {
                return response()->json([
                    'message' => __('messages.no_results_found', ['model' => 'blogs']),
                    'data' => []
                ], Response::HTTP_OK);
            }

            // Retorna coleção de recursos
            return (new BlogCollection($blogs))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar blogs: ' . $e->getMessage());
            return response()->json([
                'message' => __('messages.failed_to_fetch', ['model' => 'blogs']),
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Armazena um novo blog no banco de dados.
     *
     * @param StoreBlogRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreBlogRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();

            // Armazena múltiplos arquivos (se existirem)
            if ($request->hasFile('files')) {
                $imagePaths = store_files($request, 'files', 'blogs', 'public');
                $validatedData['files'] = json_encode($imagePaths);
            }

            $blog = Blog::create($validatedData);

            DB::commit();

            // Limpar cache após criar um blog - uso de pattern para limpar todos os caches relacionados
            Cache::flush('blogs*');

            return (new BlogResource($blog))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao criar blog: ' . $e->getMessage());
            return response()->json([
                'message' => __('messages.failed_to_create', ['model' => 'blog']),
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exibe o blog especificado.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            //Tenta encontrar o blog usando o cache
            $blog = Cache::remember('blog_' . $id, 3600, function () use ($id) {
                return Blog::findOrFail($id);
            });

            return (new BlogResource($blog))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => __('messages.model_not_found', ['model' => 'Blog'])
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar blog: ' . $e->getMessage());
            return response()->json([
                'message' => __('messages.failed_to_fetch', ['model' => 'blog']),
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualiza o blog especificado no banco de dados.
     *
     * @param UpdateBlogRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateBlogRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $blog = Blog::findOrFail($id);
            $validatedData = $request->validated();

            // Armazena múltiplos arquivos (se existirem)
            if ($request->hasFile('files')) {
                $newFilePaths = store_files($request, 'files', 'blogs', 'public');

                // Combinar com arquivos existentes (se houver)
                $existingFiles = [];
                if (!empty($blog->files)) {
                    $existingFiles = json_decode($blog->files, true) ?: [];
                }

                $allFiles = array_merge($existingFiles, $newFilePaths);
                $validatedData['files'] = json_encode($allFiles);
            }

            // Remover arquivos específicos se solicitado
            if ($request->has('remove_files') && is_array($request->input('remove_files'))) {
                $existingFiles = !empty($blog->files)
                    ? json_decode($blog->files, true)
                    : [];

                if (!empty($existingFiles)) {
                    $filesToRemove = [];
                    $remainingFiles = [];

                    foreach ($existingFiles as $file) {
                        if (in_array($file, $request->input('remove_files'))) {
                            $filesToRemove[] = $file;
                        } else {
                            $remainingFiles[] = $file;
                        }
                    }

                    // Excluir os arquivos físicos
                    if (!empty($filesToRemove)) {
                        delete_files($filesToRemove);
                    }

                    // Atualizar a lista de arquivos
                    $validatedData['files'] = json_encode($remainingFiles);
                }
            }

            // Atualizar o blog
            $blog->update($validatedData);

            DB::commit();

            // Limpar cache
            Cache::forget('blog_' . $id);
            Cache::flush('blogs*');

            return (new BlogResource($blog))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => __('messages.model_not_found', ['model' => 'Blog'])
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao atualizar blog: ' . $e->getMessage());
            return response()->json([
                'message' => __('messages.failed_to_update', ['model' => 'blog']),
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove o blog especificado do banco de dados.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $blog = Blog::findOrFail($id);

            // Excluir arquivos adicionais se existirem
            if (!empty($blog->files)) {
                $files = json_decode($blog->files, true) ?: [];
                delete_files($files);
            }

            $blog->delete();

            DB::commit();

            // Limpar cache
            Cache::forget('blog_' . $id);
            Cache::flush('blogs*');

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => __('messages.model_not_found', ['model' => 'Blog'])
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao excluir blog: ' . $e->getMessage());
            return response()->json([
                'message' => __('messages.failed_to_delete', ['model' => 'blog']),
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Pesquisa blogs por qualquer coluna.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            // Validar parâmetros de busca
            $request->validate([
                'q' => 'required|string|min:1',
                'column' => 'required|string',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $query = strtolower($request->query('q'));
            $column = strtolower($request->query('column'));
            $perPage = $request->query('per_page', 15);

            // Obtém as colunas disponíveis na tabela
            $availableColumns = Schema::getColumnListing('blogs');

            // Adiciona opção especial 'all'
            $availableColumns[] = 'all';

            // Verifica se a coluna solicitada está disponível
            if (!in_array($column, $availableColumns)) {
                return response()->json([
                    'message' => __('messages.invalid_column'),
                    'available_columns' => $availableColumns
                ], Response::HTTP_BAD_REQUEST);
            }

            $blogQuery = Blog::query();

            if ($column !== 'all') {
                // Verifica se a coluna solicitada é válida (medida de segurança para SQL Injection)
                if (!in_array($column, Schema::getColumnListing('blogs'))) {
                    return response()->json([
                        'message' => __('messages.invalid_column')
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Busca na coluna específica usando LOWER para case insensitivity
                $blogQuery->whereRaw("LOWER({$column}) LIKE ?", ['%' . $query . '%']);
            } else {
                // Busca em todas as colunas de texto (exclui colunas não pesquisáveis)
                $searchableColumns = array_filter($availableColumns, function($col) {
                    // Exclui colunas que geralmente não são usadas para busca de texto
                    $excludedColumns = ['id', 'created_at', 'updated_at', 'deleted_at'];
                    return !in_array($col, $excludedColumns);
                });

                $blogQuery->where(function($q) use ($searchableColumns, $query) {
                    foreach ($searchableColumns as $col) {
                        if (in_array($col, Schema::getColumnListing('blogs'))) {
                            $q->orWhereRaw("LOWER({$col}) LIKE ?", ['%' . $query . '%']);
                        }
                    }
                });
            }

            $blogs = $blogQuery->paginate($perPage);

            // Verificar se algum resultado foi encontrado
            if ($blogs->isEmpty()) {
                return response()->json([
                    'message' => __('messages.no_results_found', ['model' => 'blogs']),
                    'data' => []
                ], Response::HTTP_OK);
            }

            return (new BlogCollection($blogs))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => __('messages.validation_error'),
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Erro ao pesquisar blogs: ' . $e->getMessage());
            return response()->json([
                'message' => __('messages.failed_to_search', ['model' => 'blogs']),
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
