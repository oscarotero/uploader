# uploader

Basic php library to manage uploaded files

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/oscarotero/uploader/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/oscarotero/uploader/?branch=master)

Created by Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>

## Basic usage

```php
//Init a Uploader instance:
$uploader = new Uploader\Uploader('/base/path/to/uploads');

//Save an uploaded file:
$file = $uploader($_FILES['my-file']);

//Save a psr7 UploadedFileInterface instance
$file = $uploader($uploadedFile);

//Save a file from an url
$file = $uploader('http://example.com/files/file1.jpg');

//Save from base64 value
$file = $uploader('data:image/png;base64,...');
```

## Advanced usage
```php
//Init a Uploader instance:
$uploader = new Uploader\Uploader('/base/path/to/uploads');

//Add some configuration
$uploader
	->setPrefix(function () {
		return uniqid();
	})
	->setDirectory('images');

//Assign an input
$imageUpload = $uploader->with($_FILES['my-image']);

//Save
$imageUpload->save();

//Get the relative path
echo $imageUpload->getDestination();
```


## API

The following methods configure how the uploaded file will be saved

Setter | Getter | Description
-------|--------|------------
`setPrefix(string|Closure)` | `getPrefix()` | Custom filename prefix.
`setOverwrite(boolean|Closure)` | `getOverwrite()` | Whether or not overwrite existing files
`setDestination(boolean|Closure)` | `getDestination(bool $absolute = false)` | The destination file. If `$absolute` is `true`, returns the path with the cwd
`setDirectory(string|Closure)` | `getDirectory()` | To change only the directory of the destination
`setFilename(string|Closure)` | `getFilename()` | To change only the filename of the destination
`setExtension(string|Closure)` | `getExtension()` | To change only the file extension of the destination
`setCwd(string|Closure)` | `getCwd()` | To change the base path of the destination
`setCreateDir(boolean|Closure)` | `getCreateDir()` | Whether or not create the destination directory if it does not exist


Example:

```php
$uploader = new Uploader\Uploader(__DIR__.'/my-uploads');

$upload = $uploader
	->with($_FILES['my-file'])
	->setPrefix(uniqid())
	->setOverwrite(true)
	->setCreateDir(true)
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
