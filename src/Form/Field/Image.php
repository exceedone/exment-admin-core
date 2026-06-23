<?php

namespace ExmentAdminCore\Admin\Form\Field;

use ExmentAdminCore\Admin\Form\Field;
use ExmentAdminCore\Admin\Form\Field\Traits\ImageField;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Image extends File
{
    use ImageField;

    /**
     * @inheritdoc
     */
    protected $view = 'admin::form.file';

    /**
     *  Validation rules.
     *
     * @var string
     */
    protected $rules = 'image';

    protected function setType($type = 'image')
    {
        $this->options['type'] = $type;
    }

    /**
     * @param array<mixed>|UploadedFile|null|string $image
     */
    public function prepare($file)
    {
        // Nếu có thuộc tính picker và true, gọi prepare cha
        if (property_exists($this, 'picker') && $this->picker) {
            return parent::prepare($file);
        }

        // Nếu $file là string có prefix TMP và có getTmp callback
        if (is_string($file) && strpos($file, File::TMP_FILE_PREFIX) === 0 && $this->getTmp) {
            $file = call_user_func($this->getTmp, $file);
        }

        // Nếu request có flag xóa file
        if (request()->has($this->column . Field::FILE_DELETE_FLAG) || request()->has(static::FILE_DELETE_FLAG)) {
            $this->destroy();
            return '';
        }

        // Nếu $file là null thì trả về null
        if (is_null($file)) {
            return null;
        }

        // Nếu $file không rỗng
        if (!empty($file)) {
            $this->name = $this->getStoreName($file);

            $this->callInterventionMethods($file->getRealPath());

            $path = $this->uploadAndDeleteOriginal($file);

            $this->uploadAndDeleteOriginalThumbnail($file);

            return $path;
        }

        return false;
    }



    /**
     * force file type to image.
     *
     * @param $file
     *
     * @return array|bool|int[]|string[]
     */
    public function guessPreviewType($file)
    {
        $extra = parent::guessPreviewType($file);
        $extra['type'] = 'image';

        return $extra;
    }

    /**
     * Render file upload field.
     */
    public function render()
    {
        $this->options([
            'preferIconicPreview' => false,
        ]);
        $this->filetype('image');
        return parent::render();
    }
}
