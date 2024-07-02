<h1>Image Upload Processor</h1>
<p>
	Upload an image, and then process that image into webp, along with various
	smaller sizes. Split them into chunks, and upload each chunk individually. 
</p>
<p>
	The php validation for the inputs should be good, but always put your 
	own eyes on it.
</p>
<p>
	Configure the variable: <code>imageChunkSize</code> to create different size chunks
	that are appropriate for your server. The bigger the chunks can be, the 
	faster the upload process will be. 
</p>
<p>
	You should be able to clone this, and then run it on a php server. Simply
	visit the directory and use the form. You should add your own functionallity 
	for failed uploads, depending on your use case.
</p>
<h2>Enjoy</h2>