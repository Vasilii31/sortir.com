<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploadService
{
    private string $uploadDirectory;
    private SluggerInterface $slugger;

    public function __construct(string $uploadDirectory, SluggerInterface $slugger)
    {
        $this->uploadDirectory = $uploadDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file, ?string $oldFileName = null): string
    {
        // Supprimer l'ancienne image si elle existe
        if ($oldFileName) {
            $this->delete($oldFileName);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->uploadDirectory, $fileName);
        } catch (FileException $e) {
            throw new \Exception('Erreur lors du téléchargement de l\'image');
        }

        return $fileName;
    }

    public function delete(?string $fileName): void
    {
        if ($fileName && file_exists($this->uploadDirectory . '/' . $fileName)) {
            unlink($this->uploadDirectory . '/' . $fileName);
        }
    }

    public function isValidImageFile(UploadedFile $file): bool
    {
        // Vérifier la taille (max 2MB)
        if ($file->getSize() > 2097152) {
            return false;
        }

        // Vérifier le type MIME
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        return in_array($file->getMimeType(), $allowedMimes);
    }

    public function getUploadDirectory(): string
    {
        return $this->uploadDirectory;
    }
}