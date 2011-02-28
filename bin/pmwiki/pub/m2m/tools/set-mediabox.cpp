/************************************************************************
** Simple tool to change the size of the MediaBox of a given PDF file. **
** (c) 2006 Martin Gieseking                                           **
************************************************************************/
#include <cstring>
#include <iostream>
#include <fstream>
#include <sstream>
#include <string>
#include <pcrecpp.h>


using namespace std;
using namespace pcrecpp;


const int BUFSIZE = 4096;

class Line
{
	public:
		Line () : content(0), size(0)  {}
		~Line ()                   {delete [] content;}
		void operator = (string str);
		void read (istream &is);
		void write (ostream &os) const;
		bool contains (string str) const;
		bool equals (string str) const;
		size_t length () const     {return size+1;}
		string toString () const;

	protected:
		Line (const Line &line) {}
		
	private:
		char *content;
		size_t size;
		static const int BUFSIZE = 4096;
};


static string trim (string str) {
	size_t first=0, last=str.length()-1;
	while (str[first] == ' ' || str[first] == '\n' || str[first] == '\t')
		first++;
	while (str[last] == ' ' || str[last] == '\n' || str[last] == '\t')
		last--;
	return str.substr(first, last-first+1);
}

#ifdef __WIN32__
static char* memmem(const char* haystack, size_t hl, const char* needle, size_t nl) {
  int i;
  if (nl>hl) return 0;
  for (i=hl-nl+1; i; --i) {
    if (!memcmp(haystack,needle,nl))
      return (char*)haystack;
    ++haystack;
  }
  return 0;
}
#endif

void Line::operator = (string str) {
	delete [] content;
	size = str.length();
	content = new char[size+1];
	memcpy(content, str.c_str(), size);	
	content[size] = 0;
}


void Line::read (istream &is) {
	delete [] content;
	content = 0;
	size = 0;

	char buf[BUFSIZE];
	is.getline(buf, BUFSIZE);
	if (is.gcount() > 0) {
		size = is.gcount()-1;   // getline swallows the newline character...
		content = new char[size+1];
		memcpy(content, buf, size);
		content[size] = 0; // for simple conversion to string
	}
}

void Line::write (ostream &os) const {
	if (content)
		os.write(content, size) << '\n';
}


bool Line::contains (string str) const {	
	if (!content || str.length() > size)
		return false;
	return memmem(content, str.length(), str.c_str(), str.length()) != 0;
}


bool Line::equals (string str) const {
	if (!content || size != str.length())
		return false;
	return memcmp(content, str.c_str(), size) == 0;
}


string Line::toString () const {
	if (!content)
		return "";
	return content;
}


void set_bbox (string bbox, istream &is, ostream &os) {
	bool inobj = false, inpageobj = false, instream = false;
	bool do_count = true;
	unsigned count=0, checkoffs=0;
	int diff=0;
	Line line;
	while (is) {
		line.read(is);
/*		if (!instream && line.equals("stream")) {
			instream = true;
			cout << "openstream -- ";
		}
		else if (instream) {
			instream = line.contains("endstream");
			line.write(os);
			continue;
		}*/
		string linestr = line.toString();
		if (!inobj && RE("^\\d+\\s+\\d+\\s+obj\\s*").PartialMatch(linestr)) 
			inobj = true;
		else if (inobj && RE("^endobj\\b").PartialMatch(linestr)) 
			inobj = inpageobj = false;
		else if (inobj && RE("/Type\\s*/Page\\b").PartialMatch(linestr))
			inpageobj = true;
		else if (line.equals("xref")) {
			line.write(os);
			line.read(is);
			while (!line.equals("trailer")) {
				int offset;
				string rest;
				if (RE("^(\\d{10}) (\\d{5} \\w )").PartialMatch(line.toString(), &offset, &rest) && offset >= checkoffs) {
					char buf[32];
					sprintf(buf, "%010d %s", offset+diff, rest.c_str());
					line = buf;
				}
				line.write(os);
				line.read(is);
			}
			do_count = false;
		}
		else if (line.equals("startxref")) {
			os << "startxref\n" << count << "\n%%EOF";
			break;
		}

		string a, b, c;
		if (inpageobj && RE("^(.*?/MediaBox\\s*)\\[(.*?)\\](.*?)$").FullMatch(linestr, &a, &b, &c)) {
			checkoffs = count + line.length();
			ostringstream oss;
			oss << a << '[' << bbox << ']' << c;
			line = oss.str();
			diff = bbox.length() - b.length();
		}
		if (do_count)
			count += line.length();
		line.write(os);
	}
}


int main (int argc, char *argv[]) {
#if 0
	Line line;
	ifstream ifs("test.pdf");
	line.read(ifs);
	cout << line.length() << endl;
#else
	if (argc < 3) {
		cerr << "Syntax: " << argv[0] << " \"x1 y1 x2 y2\" infile outfile" << endl;
		return 0;
	}
	ifstream ifs(argv[2], ios::binary);
	ofstream ofs(argv[3], ios::binary);
	set_bbox(argv[1], ifs, ofs);
	return 0;
#endif
}
