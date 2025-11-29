<?php

namespace App\Services;

use App\Models\User\User;
use App\Models\Media\Media;
use App\Services\Media\MovieService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FavoriteService
{
    // add a favorite item for the authenticated user
    public function addFavorite(array $itemData): array
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ['success' => false, 'message' => 'User not authenticated'];
            }

            // validate required fields
            $requiredFields = ['title', 'type'];
            foreach ($requiredFields as $field) {
                if (empty($itemData[$field])) {
                    return ['success' => false, 'message' => "Missing required field: {$field}"];
                }
            }

            $type = $itemData['type'];
            $title = $itemData['title'];
            $creator = $itemData['creator'] ?? null;
            $releaseYear = $itemData['releaseYear'] ?? null;
            $coverImage = $itemData['coverImage'] ?? null;

            // for movies, if creator is null, fetch the director information
            if ($type === 'movie' && $creator === null && isset($itemData['id'])) {
                $movieService = new MovieService();
                $creator = $movieService->getMovieWithCredits($itemData['id']);
            }

            // check if media already exists
            $mediaId = DB::table('media')
                ->where('title', $title)
                ->where('creator', $creator)
                ->value('id');

            // if it doesn't exist, create it
            if (!$mediaId) {
                switch ($type) {
                    case 'book':
                        $result = DB::select('SELECT fn_create_book(?, ?, ?, ?) as id', [
                            $title,
                            $creator,
                            $releaseYear,
                            $coverImage
                        ]);
                        $mediaId = $result[0]->id;
                        break;

                    case 'movie':
                        $result = DB::select('SELECT fn_create_film(?, ?, ?, ?) as id', [
                            $title,
                            $creator,
                            $releaseYear,
                            $coverImage
                        ]);
                        $mediaId = $result[0]->id;
                        break;

                    case 'music':
                        $result = DB::select('SELECT fn_create_music(?, ?, ?, ?) as id', [
                            $title,
                            $creator,
                            $releaseYear,
                            $coverImage
                        ]);
                        $mediaId = $result[0]->id;
                        break;

                    default:
                        return ['success' => false, 'message' => 'Invalid media type'];
                }
            }

            // update user's favorite field
            $column = $this->getFavoriteColumnName($type);
            if (!$column) {
                return ['success' => false, 'message' => 'Invalid favorite type'];
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update([$column => $mediaId]);

            return ['success' => true, 'message' => 'Favorite added successfully', 'media_id' => $mediaId];
        } catch (\Exception $e) {
            Log::error('Error adding favorite: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while adding favorite'];
        }
    }

    // get the database column name for a favorite type  
    private function getFavoriteColumnName(string $type): ?string
    {
        switch ($type) {
            case 'book':
                return 'favoritebook';
            case 'movie':
                return 'favoritefilm';
            case 'music':
                return 'favoritesong';
            default:
                return null;
        }
    }

    // get user's favorites by type
    public function getUserFavorites(int $userId, string $type = null): array
    {
        $user = User::with(['favoriteFilmMedia', 'favoriteBookMedia', 'favoriteSongMedia'])
            ->find($userId);

        if (!$user) {
            return [];
        }

        $favorites = [];

        if (!$type || $type === 'movie') {
            if ($user->favoriteFilmMedia) {
                $favorites[] = [
                    'type' => 'movie',
                    'media' => $user->favoriteFilmMedia
                ];
            }
        }

        if (!$type || $type === 'book') {
            if ($user->favoriteBookMedia) {
                $favorites[] = [
                    'type' => 'book',
                    'media' => $user->favoriteBookMedia
                ];
            }
        }

        if (!$type || $type === 'music') {
            if ($user->favoriteSongMedia) {
                $favorites[] = [
                    'type' => 'music',
                    'media' => $user->favoriteSongMedia
                ];
            }
        }

        return $favorites;
    }

    // remove a favorite
    public function removeFavorite(string $type): array
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ['success' => false, 'message' => 'User not authenticated'];
            }

            $column = $this->getFavoriteColumnName($type);
            if (!$column) {
                return ['success' => false, 'message' => 'Invalid favorite type'];
            }

            // Update the user's favorite to null
            DB::table('users')
                ->where('id', $user->id)
                ->update([$column => null]);

            return ['success' => true, 'message' => 'Favorite removed successfully'];
        } catch (\Exception $e) {
            Log::error('Error removing favorite: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while removing favorite'];
        }
    }
}
