<?php


namespace Commons\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait StoreFile
{
    protected function getFolderPath()
    {
        return '/uploads';
    }

    protected function getPrivateFolderPath()
    {
        return storage_path('/private/uploads');
    }

    /**
     * @param $url
     */
    protected function deleteFileIfExists($url)
    {
        if ($url) {
            Storage::delete($this->getFileName($url));
        }
    }

    /**
     * @param $url
     * @return string
     */
    protected function getFileName($url)
    {
        $defaultDisk = config("filesystems.default");
        $diskUrl = config("filesystems.disks.$defaultDisk.url");
        return str_replace($diskUrl, '', $url);
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function putFile(UploadedFile $file)
    {
        $fileName = Storage::put($this->getFolderPath(), $file, 'public');
        return Storage::url($fileName);
    }

    /**
     * Returns the path to the file.
     *
     * Different function name since this doesn't return the url.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function saveFilePrivately(UploadedFile $file)
    {
        // Laravel, by default, chooses the 'local' folder.
        $fileName = Storage::put($this->getPrivateFolderPath(), $file);
        return Storage::path($fileName);
    }
}
