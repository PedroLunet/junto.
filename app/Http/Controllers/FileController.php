<?php

namespace App\Http\Controllers;

use App\Models\Post\StandardPost;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public static $default = 'default.jpg';

    public static $diskName = 'FileStorage';

    public static $systemTypes = [
        'profile' => ['png', 'jpg', 'jpeg', 'gif'],
        'post' => ['png', 'jpg', 'jpeg', 'gif'],
    ];

    private static function isValidType(string $type)
    {
        return array_key_exists($type, self::$systemTypes);
    }

    private static function defaultAsset(string $type)
    {
        return asset($type . '/' . self::$default);
    }

    private static function getFileName(string $type, int $id)
    {
        $fileName = null;

        switch ($type) {
            case 'profile':
                $user = User::find($id);
                $fileName = $user ? $user->profilepicture : null;
                break;
            case 'post':
                $post = StandardPost::where('postid', $id)->first();
                $fileName = $post ? $post->imageurl : null;
                break;
        }

        return $fileName;
    }

    public static function get(string $type, int $id)
    {
        // Validation: upload type
        if (! self::isValidType($type)) {
            return self::defaultAsset($type);
        }

        // Validation: file exists
        $fileName = self::getFileName($type, $id);
        if ($fileName && Storage::disk(self::$diskName)->exists($type . '/' . $fileName)) {
            return asset($type . '/' . $fileName);
        }

        // Not found: returns default asset
        return self::defaultAsset($type);
    }

    public function upload(Request $request)
    {
        // Validate request has file
        if (! $request->hasFile('file')) {
            return redirect()->back()->with('error', 'No file provided');
        }

        // Parameters
        $file = $request->file('file');
        $type = $request->type;
        $id = $request->id;
        $extension = $file->getClientOriginalExtension();

        // Validation: type exists
        if (! self::isValidType($type)) {
            return redirect()->back()->with('error', 'Invalid file type');
        }

        // Validation: extension is valid for this type
        if (! in_array(strtolower($extension), self::$systemTypes[$type])) {
            return redirect()->back()->with('error', 'Invalid file extension for ' . $type);
        }

        // Validation: file is a valid image
        $validator = \Validator::make($request->all(), [
            'file' => 'required|image|mimes:' . implode(',', self::$systemTypes[$type]) . '|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Invalid image file');
        }

        try {
            // Delete old file if exists
            $oldFileName = self::getFileName($type, $id);
            if ($oldFileName && $oldFileName !== self::$default) {
                Storage::disk(self::$diskName)->delete($type . '/' . $oldFileName);
            }

            // Hashing - generate a random unique id
            $fileName = $file->hashName();

            // Save in correct folder and disk
            $file->storeAs($type, $fileName, self::$diskName);

            // Update database
            self::updateDatabase($type, $id, $fileName);

            return redirect()->back()->with('success', 'File uploaded successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }

    private static function updateDatabase(string $type, int $id, string $fileName)
    {
        switch ($type) {
            case 'profile':
                $user = User::find($id);
                if ($user) {
                    $user->profilepicture = $fileName;
                    $user->save();
                }
                break;
            case 'post':
                $post = StandardPost::where('postid', $id)->first();
                if ($post) {
                    $post->imageurl = $fileName;
                    $post->save();
                }
                break;
        }
    }

    public function delete(Request $request)
    {
        $type = $request->type;
        $id = $request->id;

        // Validation
        if (! self::isValidType($type)) {
            return redirect()->back()->with('error', 'Invalid file type');
        }

        try {
            $fileName = self::getFileName($type, $id);

            if ($fileName && $fileName !== self::$default) {
                // Delete from storage
                Storage::disk(self::$diskName)->delete($type . '/' . $fileName);

                // Update database
                self::updateDatabase($type, $id, null);

                return redirect()->back()->with('success', 'File deleted successfully');
            }

            return redirect()->back()->with('info', 'No file to delete');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting file: ' . $e->getMessage());
        }
    }
}
