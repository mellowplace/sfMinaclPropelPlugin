  public function executeNew(sfWebRequest $request)
  {
    $this->form = new <?php echo $this->getModelClass().'Form' ?>('<?php echo $this->getSingularName() ?>', '<?php echo $this->getFormTemplate() ?>');
  }
