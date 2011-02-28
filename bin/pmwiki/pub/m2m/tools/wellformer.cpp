/***********************************************************
** Simple program to correct invalid nested XML documents **
** (c) 2007 Martin Gieseking                              **
***********************************************************/
#include <algorithm>
#include <fstream>
#include <iostream>
#include <list>

using namespace std;


class WellFormer
{
	public:
		WellFormer () : numNestingErrors(0), numSkippedTags(0) {}
		void runOnStream (istream &is, ostream &os);
		unsigned nestingErrors () const {return numNestingErrors;}
		unsigned skippedTags () const {return numSkippedTags;}

	
	protected:
		void openElement (istream &is, ostream &os);
		void closeElement (istream &is, ostream &os);
		
	private:
		list<string> elementStack;
		unsigned numNestingErrors;
		unsigned numSkippedTags;
};


void WellFormer::runOnStream (istream &is, ostream &os) {
	while (is) {
		int c = is.get();
		if (c == '<') {
			c = is.get();
			if (c == '/')
				closeElement(is, os);
			else if (c != '!' && c != '?') {
				is.unget(); 
				openElement(is, os);
			}
			else {
				os.put('<');
				os.put(c);
			}
		}
		else if (is)
			os.put(c);
	}
	while (!elementStack.empty()) {
		os << "</" << elementStack.front() << ">";
		elementStack.pop_front();
	}
}


void WellFormer::openElement (istream &is, ostream &os) {
	string name;
	os.put('<');
	int c = is.get();
	while (is && (isalnum(c) || c == '-' || c == '_')) {
		os.put(c);
		name += c;
		c = is.get();
	}
	int attr_delim = 0;
	bool slash = false;
	while (is && (attr_delim != 0 || c != '>')) {
		switch (c) {
			case '"': 
			case '\'':
				if (attr_delim == 0) 
					attr_delim = c;
				else if (attr_delim == c)
					attr_delim = 0;
				break;
			case '/':
				if (attr_delim == 0)
					slash = true;
				break;
			default:
				slash = false;
		}
		os.put(c);
		c = is.get();
	}
	os.put('>');
	if (!slash)
		elementStack.push_front(name);
}


void WellFormer::closeElement (istream &is, ostream &os) {
	string name;
	int c = is.get();
	while (is && (isalnum(c) || c == '-' || c == '_')) {
		name += c;
		c = is.get();
	}
	while (is && c != '>') {
		c = is.get();
	}
	if (find(elementStack.begin(), elementStack.end(), name) != elementStack.end()) {
		string popped;
		int count = 0;
		while (!elementStack.empty() && popped != name) {
			os << "</" << elementStack.front() << ">";
			popped = elementStack.front();
			elementStack.pop_front();
			count++;
		}
		if (--count > 0)
			numNestingErrors += count;
	}
	else
		numSkippedTags++;
}


int main (int argc, char *argv[]) {
	if (argc < 2) {
		cerr << "Syntax: " << argv[0] << " infile [outfile]" << endl;
		return 0;
	}
	ifstream ifs(argv[1]);
	if (ifs) {
		WellFormer wf;
		if (argc == 2)
			wf.runOnStream(ifs, cout);
		else {
			ofstream ofs(argv[2]);
			wf.runOnStream(ifs, ofs);
		}
		cerr << "nesting errors: " << wf.nestingErrors() << endl
			  << "skipped tags: " << wf.skippedTags() << endl;
	}
	return 0;
}
