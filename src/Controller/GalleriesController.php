<?php
namespace PhotoGallery\Controller;

use PhotoGallery\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use AppCore\Lib\ImageUploader;
use AppCore\Lib\ImageUploaderConfig;

/**
 * Galleries Controller
 *
 * @property \PhotoGallery\Model\Table\GalleriesTable $Galleries
 */
class GalleriesController extends AppController
{
  public $helpers = ['AppCore.Form', 'DefaultAdminTheme.PanelMenu'];

  /**
   * [index description]
   * @return [type] [description]
   */
  public function index()
  {
    $this->Galleries = TableRegistry::get('PhotoGallery.Galleries');
    $this->set('tableHeaders', ['Imagem', 'Nome', 'Status', 'Opções']);
    $this->set('data', $this->Galleries->getAllGalleries());
  }

  /**
   * [add description]
   */
  public function add()
  {
    if($this->request->is('post')) {
        $this->Galleries = TableRegistry::get('PhotoGallery.Galleries');
        $data = $this->request->data;

        if(Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.use_image')) {
          $uploader = new ImageUploader();
          if($uploader->setData($data['cover'])) {
            $uploader->setPath('galleries');
            $uploader->setConfig(new ImageUploaderConfig(
              Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.gallery_cover_width'),
              Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.gallery_cover_height'),
              Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.gallery_cover_resize_mode')
            ));

            $image = $uploader->upload();
            $data['cover'] = '';
            $data['cover_thumbnail'] = '';

            if($image) {
                $data['cover'] = $image;

                $uploader->setConfig(new ImageUploaderConfig(
                  Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.gallery_cover_thumbnail_width'),
                  Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.gallery_cover_thumbnail_height'),
                  Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.gallery_cover_thumbnail_resize_mode')
                ));

                $coverThumbnail = $uploader->thumbnail();
                if($coverThumbnail) {
                  $data['cover_thumbnail'] = $coverThumbnail;
                }
            }

            $uploader->close();
          }
          else {
            $data['cover'] = '';
            $data['cover_thumbnail'] = '';
          }
        }
        else {
          $data['cover'] = '';
          $data['cover_thumbnail'] = '';
        }

        $result = $this->Galleries->insertNewGallery($data);

        if($result) {
            $this->Flash->set('Nova galeria adicionada!', ['element' => 'AppCore.alert_success']);
            $this->request->data = [];
        }
        else {
            $this->Flash->set('Erro ao tentar adicionar uma nova galeria.', ['element' => 'AppCore.alert_danger']);
        }
      }
      $categoriesTable = TableRegistry::get('PhotoGallery.Categories');
      $this->set('options', Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options'));
      $this->set('categoriesList', $categoriesTable->getCategoriesAsList());
  }

  public function edit($id)
  {
      $this->Galleries = TableRegistry::get('PhotoGallery.Galleries');
      $categoriesTable = TableRegistry::get('PhotoGallery.Categories');

      if($this->request->is('post')) {
        $this->Galleries = TableRegistry::get('PhotoGallery.Galleries');
        $data = $this->request->data;

        if(Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.use_image')) {
          $uploader = new ImageUploader();
          if($uploader->setData($data['cover'])) {
            $uploader->setPath('galleries');
            $uploader->setConfig(new ImageUploaderConfig(
              Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.gallery_cover_width'),
              Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.gallery_cover_height'),
              Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.gallery_cover_resize_mode')
            ));

            $image = $uploader->upload();
            unset($data['cover']);
            if($image) {
              $data['cover'] = $image;

              $uploader->setConfig(new ImageUploaderConfig(
                Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.gallery_cover_thumbnail_width'),
                Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.gallery_cover_thumbnail_height'),
                Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options.gallery_cover_thumbnail_resize_mode')
              ));

              $coverThumbnail = $uploader->thumbnail();
              if($coverThumbnail) {
                $data['cover_thumbnail'] = $coverThumbnail;
              }
            }
            $uploader->close();
          }
          else {
            unset($data['cover']);
          }
        }
        else {
          unset($data['cover']);
        }

        $result = $this->Galleries->updateGallery($id, $data);

        if($result) {
          $this->Flash->set('Galeria editada!', ['element' => 'AppCore.alert_success']);
        }
        else {
          $this->Flash->set('Erro ao tentar adicionar uma nova galeria.', ['element' => 'AppCore.alert_danger']);
        }
      }
      $gallery = $this->Galleries->get($id);
      $this->set('gallery', $gallery);
      $this->set('options', Configure::read('WebImobApp.Plugins.PhotoGallery.Settings.Options'));
      $this->set('categoriesList', $categoriesTable->getCategoriesAsList());
  }

  public function delete($id)
  {
    $this->Galleries = TableRegistry::get('PhotoGallery.Galleries');
    $result = $this->Galleries->deleteGallery($id);
    if($result) {
      $this->Flash->set('Galeria removida!', ['element' => 'AppCore.alert_success']);
    }
    else {
      $this->Flash->set('Erro ao tentar adicionar uma nova galeria.', ['element' => 'AppCore.alert_danger']);
    }
    $this->redirect(['action' => 'index']);
  }
}
