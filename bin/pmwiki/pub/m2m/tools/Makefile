CXX = g++ -g

all: set-mediabox wellformer

set-mediabox: set-mediabox.cpp
	$(CXX) -o$@ $< -I/usr/include/pcre -lpcrecpp -lpcre
	strip $@

wellformer: wellformer.cpp
	$(CXX) -o$@ $<
	strip $@

clean:
	rm set-mediabox wellformer
