<?php

namespace App\Traits;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Log, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait GenericRepository
{
    /**
     * Retrieve all records from the entity.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->entity->get();
    }
    /**
     * Retrieve a paginated list of records from the entity.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage)
    {
        return $this->entity->paginate($perPage);
    }

    /**
     * Retrieve the entity record by ID.
     *
     * @param string $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getById(string $id)
    {
        return $this->entity->where('id', $id)->first();
    }

    /**
     * Retrieve the profile by the provided profile ID, or the authenticated user's.
     *
     * @param mixed $profileId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null
     */
    public function getProfile($profileId)
    {
        if (!$profileId) {
            $user = Auth()->user();

            if ($user && property_exists($user, 'profile') && $user->profile) {
                $profileId = $user->profile->id;
            } else {
                return null;
            }
        }

        return $this->entity->where('profile_id', $profileId);
    }

    /**
     * Retrieve the user profile by the provided user ID, or the authenticated user's.
     *
     * @param mixed $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getProfileUser($userId)
    {
        if (!$userId) {
            $userId = Auth()->user()->id;
        }

        return $this->entity->where('user_id', $userId);
    }
    /**
    * Store a new record in the entity with the provided values.
    *
    * @param array $data
    * @param UploadedFile|null $file
    * @param string $folder
    * @return \Illuminate\Database\Eloquent\Model
    * @throws Exception
    */
    public function store(array $data, ?UploadedFile $file = null, $folder = null): Model
    {
        try {
            if ($file) {
                $originalFileName = $file->getClientOriginalName();
                $extension        = $file->getClientOriginalExtension();

                $fileName = Str::snake(pathinfo($originalFileName, PATHINFO_FILENAME), '-');

                $fileName = $fileName . '.' . $extension;

                if (Storage::disk(config('epass.storage.drive'))->exists($folder . '/' . $fileName)) {
                    $fileName = $this->generateUniqueFileName($folder, $fileName);
                }
                $filePath = Storage::disk(config('epass.storage.drive'))->putFileAs($folder, $file, $fileName);

                $data['file_path'] = $filePath;
            }

            $created = $this->entity->creates($data);

            return $created;

        } catch (FileNotFoundException $exception) {
            Log::channel('general')->error('Erro de arquivo não encontrado: ' . $exception->getMessage());
            Log::channel('discord')->warning("Ocorreu uma falha ao criar o registro. \nVerifique os logs (general) para mais detalhes.");

            throw new Exception(__("File not found error: ") . $exception->getMessage());
        } catch (QueryException $exception) {
            Log::channel('general')->error('Erro de consulta SQL: ' . $exception->getMessage());
            Log::channel('discord')->warning("Ocorreu uma falha ao criar o registro. \nVerifique os logs (general) para mais detalhes.");

            throw new Exception(__("SQL query error: ") . $exception->getMessage());
        } catch (ValidationException $exception) {
            Log::channel('general')->error('Erro de validação: ' . json_encode($exception->errors()));
            Log::channel('discord')->warning("Ocorreu uma falha ao criar o registro. \nVerifique os logs (general) para mais detalhes.");

            throw new Exception(__("Validation error: ") . json_encode($exception->errors()));
        } catch (Exception $exception) {
            Log::channel('general')->error('Erro desconhecido: ' . $exception->getMessage());
            Log::channel('discord')->warning("Ocorreu uma falha ao criar o registro. \nVerifique os logs (general) para mais detalhes.");

            throw new Exception(__("Error: ") . $exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Update the provided record with the given values.
     *
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model $record
     * @param UploadedFile|null $file
     * @param string $folder
     * @return \Illuminate\Database\Eloquent\Model
     * @throws Exception
     */
    public function update(array $data, $record = null, ?UploadedFile $file = null, $folder = null)
    {
        try {
            if ($file) {
                $originalFileName = $file->getClientOriginalName();
                $extension        = $file->getClientOriginalExtension();

                $fileName = Str::snake(pathinfo($originalFileName, PATHINFO_FILENAME), '-');

                $fileName = $fileName . '.' . $extension;

                if (Storage::disk(config('epass.storage.drive'))->exists($folder . '/' . $fileName)) {

                    if ($fileName !== $record->file_path) {
                        $fileName = $this->generateUniqueFileName($folder, $fileName);
                    }
                }
                $filePath = Storage::disk(config('epass.storage.drive'))->putFileAs($folder, $file, $fileName);

                if (!$filePath) {
                    return false; // @phpstan-ignore-line
                }

                if ($filePath !== $record->file_path) {
                    Storage::disk(config('epass.storage.drive'))->delete($record->file_path);
                }
                $data['file_path'] = $filePath;

            }

            $record->update($data); // @phpstan-ignore-line

            return $record;

        } catch (\Exception $exception) {
            Log::channel('general')->info('Ocorreu uma falha ao processar o arquivo. ' . $exception->getMessage());
            Log::channel('discord')->warning("Ocorreu uma falha nao processar o arquivo verifique. \nVerifique os logs (general) para mais detalhes.");

            throw new Exception(__("Error: ") . $exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Delete the provided record.
     *
     * @param \Illuminate\Database\Eloquent\Model $record
     * @return void
     * @throws Exception
     */
    public function delete($record)
    {
        try {
            if ($record->file_path) {
                Storage::disk(config('epass.storage.drive'))->delete($record->file_path);
            }

            $record->delete();

        } catch (\Exception $exception) {
            Log::channel('general')->info('Ocorreu uma falha ao processar o arquivo. ' . $exception->getMessage());
            Log::channel('discord')->warning("Ocorreu uma falha nao processar o arquivo. \nVerifique os log (general) para mais detalhes.");

            throw new Exception(__("Error: ") . $exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Generate a unique file name by adding a number suffix if the filejá existe.
     *
     * @param string $folder
     * @param string $fileName
     * @return string
     */
    private function generateUniqueFileName($folder, $fileName)
    {
        $counter          = 1;
        $originalFileName = pathinfo($fileName, PATHINFO_FILENAME);
        $extension        = pathinfo($fileName, PATHINFO_EXTENSION);

        while (Storage::disk(config('epass.storage.drive'))->exists($folder . '/' . $fileName)) {
            $fileName = $originalFileName . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $fileName;
    }

}
