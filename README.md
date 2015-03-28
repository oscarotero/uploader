# uploader

Basic php library to upload files

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/oscarotero/uploader/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/oscarotero/uploader/?branch=master)

Created by Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>

## Usage

```php
//Init a Uploader instance:
$uploader = new Uploader\Uploader('/base/path/to/uploads');

//Save an uploaded file:
$uploader
	->with($_FILES['my-file'])
	->save();

//Save a file from an url
$uploader
	->with('http://example.com/files/file1.jpg')
	->save();

//Save from base64 value
$uploader
	->with('data:image/png;base64,...')
	->save();
```

## Options

You can define the following options before save:

* **prefix** (string|Closure) Custom filename prefix.
* **overwrite** (boolean|Closure) Whether or not overwrite existing files
* **destination** (string|Closure) The destination file (excluding the base path)
* **directory** (string|Closure) To change only the directory of the destination
* **filename** (string|Closure) To change only the filename of the destination
* **extension** (string|Closure) To change only the extension of the destination

Example:

```php
$uploader = new Uploader\Uploader(__DIR__.'/my-uploads');

$upload = $uploader
	->with($_FILES['my-file'])
	->setPrefix(uniqid())
	->overwrite(true)
	->setDirectory('files')
	->setExtension(function ($upload) {
		return strtolower($upload->getExtension());
	});

try {
	$upload->save();

	echo 'The file has been saved in '.$upload->getDestination();
} catch (Exception $e) {
	echo $e->getMessage();
}
```

The method `with` creates clones the instance with the current configuration, so you can configure the upload instance first and then use `with` for each individual upload. Example:

```php
$uploader = new Uploader\Uploader(__DIR__.'/my-uploads');

//Set configuration
$uploader
	->setPrefix(function () {
		return uniqid();
	}),
	->setDirectory('uploads');

//Saves all upload with this configuration
foreach ($_FILES as $file) {
	$upload = $uploader->with($file)->save();

	echo 'Saved file '.$upload->getDestination();
}
```