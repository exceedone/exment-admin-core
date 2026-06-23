<?php

namespace ExmentAdminCore\Admin\Traits;

use Illuminate\Support\Arr;

trait FormTrait
{
    /**
     * unique class name for class selector
     * 
     * @var string
     */
    protected $uniqueName;

    /**
     * If the form horizontal layout.
     *
     * @var bool
     */
    protected $horizontal = true;

    /**
     * Set unique class name for class selector
     * @param string $uniqueName
     *
     * @return  $this
     */ 
    public function setUniqueName($uniqueName)
    {
        $this->uniqueName = $uniqueName;
        return $this;
    }

    /**
     * Get unique class name for class selector
     *
     * @return  string
     */ 
    public function getUniqueName()
    {
        if(!$this->uniqueName){
            $this->uniqueName = 'form-' . mb_substr(md5(uniqid()), 0, 32);
        }
        return $this->uniqueName;
    }

    /**
     * @return bool
     */
    public function getHorizontal()
    {
        return $this->horizontal;
    }

    /**
     * @return $this
     */
    public function setHorizontal(bool $horizontal)
    {
        $this->horizontal = $horizontal;
        
        return $this; 
    }
    
}
