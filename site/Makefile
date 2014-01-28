HOST=wwucuneiform.com
PORT=2222
USERNAME=www
PASSWORD=famished!

install:
	find  -regex ".*\.\(php\|css\|eot\|svg\|ttf\|woff\|js\)" -exec curl -u $(USERNAME):$(PASSWORD) --ftp-create-dirs -T {} ftp://$(HOST)/{} \;

