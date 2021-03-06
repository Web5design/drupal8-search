<?php

/**
 * @file
 * Contains \Drupal\image\Tests\ImageStyleFlushTest.
 */

namespace Drupal\image\Tests;

/**
 * Tests flushing of image styles.
 */
class ImageStyleFlushTest extends ImageFieldTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Image style flushing',
      'description' => 'Tests flushing of image styles.',
      'group' => 'Image',
    );
  }

  /**
   * Given an image style and a wrapper, generate an image.
   */
  function createSampleImage($style, $wrapper) {
    static $file;

    if (!isset($file)) {
      $files = $this->drupalGetTestFiles('image');
      $file = reset($files);
    }

    // Make sure we have an image in our wrapper testing file directory.
    $source_uri = file_unmanaged_copy($file->uri, $wrapper . '://');
    // Build the derivative image.
    $derivative_uri = image_style_path($style->id(), $source_uri);
    $derivative = image_style_create_derivative($style, $source_uri, $derivative_uri);

    return $derivative ? $derivative_uri : FALSE;
  }

  /**
   * Count the number of images currently created for a style in a wrapper.
   */
  function getImageCount($style, $wrapper) {
    return count(file_scan_directory($wrapper . '://styles/' . $style->id(), '/.*/'));
  }

  /**
   * General test to flush a style.
   */
  function testFlush() {

    // Setup a style to be created and effects to add to it.
    $style_name = strtolower($this->randomName(10));
    $style_label = $this->randomString();
    $style_path = 'admin/config/media/image-styles/manage/' . $style_name;
    $effect_edits = array(
      'image_resize' => array(
        'data[width]' => 100,
        'data[height]' => 101,
      ),
      'image_scale' => array(
        'data[width]' => 110,
        'data[height]' => 111,
        'data[upscale]' => 1,
      ),
    );

    // Add style form.
    $edit = array(
      'name' => $style_name,
      'label' => $style_label,
    );
    $this->drupalPost('admin/config/media/image-styles/add', $edit, t('Create new style'));

    // Add each sample effect to the style.
    foreach ($effect_edits as $effect => $edit) {
      // Add the effect.
      $this->drupalPost($style_path, array('new' => $effect), t('Add'));
      if (!empty($edit)) {
        $this->drupalPost(NULL, $edit, t('Add effect'));
      }
    }

    // Load the saved image style.
    $style = entity_load('image_style', $style_name);

    // Create an image for the 'public' wrapper.
    $image_path = $this->createSampleImage($style, 'public');
    // Expecting to find 2 images, one is the sample.png image shown in
    // image style preview.
    $this->assertEqual($this->getImageCount($style, 'public'), 2, format_string('Image style %style image %file successfully generated.', array('%style' => $style->label(), '%file' => $image_path)));

    // Create an image for the 'private' wrapper.
    $image_path = $this->createSampleImage($style, 'private');
    $this->assertEqual($this->getImageCount($style, 'private'), 1, format_string('Image style %style image %file successfully generated.', array('%style' => $style->label(), '%file' => $image_path)));

    // Remove the 'image_scale' effect and updates the style, which in turn
    // forces an image style flush.
    $style_path = 'admin/config/media/image-styles/manage/' . $style->id();
    $ieids = array();
    foreach ($style->effects as $ieid => $effect) {
      $ieids[$effect['name']] = $ieid;
    }
    $this->drupalPost($style_path . '/effects/' . $ieids['image_scale'] . '/delete', array(), t('Delete'));
    $this->assertResponse(200);
    $this->drupalPost($style_path, array(), t('Update style'));
    $this->assertResponse(200);

    // Post flush, expected 1 image in the 'public' wrapper (sample.png).
    $this->assertEqual($this->getImageCount($style, 'public'), 1, format_string('Image style %style flushed correctly for %wrapper wrapper.', array('%style' => $style->label(), '%wrapper' => 'public')));

    // Post flush, expected no image in the 'private' wrapper.
    $this->assertEqual($this->getImageCount($style, 'private'), 0, format_string('Image style %style flushed correctly for %wrapper wrapper.', array('%style' => $style->label(), '%wrapper' => 'private')));
  }
}
