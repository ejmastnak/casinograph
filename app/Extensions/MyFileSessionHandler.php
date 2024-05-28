<?php

namespace App\Extensions;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use SessionHandlerInterface;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Log;

/**
 *  A custom file-based Session driver based on Laravel's built-in
 *  FileSessionHandler, used to remove a user's SVG position graphs when the
 *  user's session ends.
 */
class MyFileSessionHandler implements \SessionHandlerInterface
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The path where sessions should be stored.
     *
     * @var string
     */
    protected $path;

    /**
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected $minutes;

    /**
     * Create a new file driven handler instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $path
     * @param  int  $minutes
     * @return void
     */
    public function __construct(Filesystem $files, $path, $minutes)
    {
        $this->path = $path;
        $this->files = $files;
        $this->minutes = $minutes;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return string|false
     */
    public function read($sessionId): string|false
    {
        if ($this->files->isFile($path = $this->path.'/'.$sessionId) &&
            $this->files->lastModified($path) >= Carbon::now()->subMinutes($this->minutes)->getTimestamp()) {
            return $this->files->sharedGet($path);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        $this->files->put($this->path.'/'.$sessionId, $data, true);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        $userId = $this->extractUserIdFromSessionFile($this->path.'/'.$sessionId);
        if ($userId) {
            Log::info("Cleaning position graph SVG files for user {$userId} from MyFileSessionHandle destroy method.");
            $this->cleanupPositionGraphSvgsForUser($userId);       
        }
        $this->files->delete($this->path.'/'.$sessionId);
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function gc($lifetime): int
    {
        $files = Finder::create()
                    ->in($this->path)
                    ->files()
                    ->ignoreDotFiles(true)
                    ->date('<= now - '.$lifetime.' seconds');

        $deletedSessions = 0;

        foreach ($files as $file) {
            $userId = $this->extractUserIdFromSessionFile($file->getRealPath());
            if ($userId) {
                Log::info("Cleaning position graph SVG files for user {$userId} from MyFileSessionHandle gc method.");
                $this->cleanupPositionGraphSvgsForUser($userId);       
            }
            $this->files->delete($file->getRealPath());
            $deletedSessions++;
        }

        return $deletedSessions;
    }

    /**
     *  Input contents of a session file. Returns the session's user id if
     *  present, and null otherwise.
     */
    private function extractUserIdFromSessionFile($filePath) {
        $fileContents = file_get_contents($filePath);
        $sessionData = unserialize(trim($fileContents));
        $userIdPrefix = "login_web";
        foreach ($sessionData as $key => $value) {
            if (strpos($key, $userIdPrefix) === 0) return $value;
        }
        return null;
    }

    /**
     * Delete a user's position graph SVGs from file system when the user's
     * session ends, to avoid taking up too much memory with SVGs, which are
     * meant to be generated on the fly anyway.
     */
    private function cleanupPositionGraphSvgsForUser($userId) {
        $svgDir = public_path(config('misc.graphs.position_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId));
        if (is_dir($svgDir)) {
            $svgFiles = glob($svgDir . '/*.svg');
            foreach ($svgFiles as $file) {
                if (is_file($file)) unlink($file);
            }
        }
    }

}
