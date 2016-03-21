import java.util.*;
import java.io.*;
import java.net.*;

public class CoursesUMBC
{
	public static void main(String[] args)
	{
		Vector <String> prefix = readCoursePrefix("prefix.txt");
		String url1 = "", currentPrefix = "";
		for (int i=0; i < prefix.size(); i++) {
			currentPrefix = prefix.elementAt(i);
			url1 = "https://highpoint-prd.ps.umbc.edu/app/catalog/listCoursesBySubject/UMBC1/" + currentPrefix.charAt(0) + "/" + currentPrefix + "/UGRD/";
			getSourceCode(url1);
		}
	}
	public static void getSourceCode(String urlpage) {
		String sourcecode = "";
		//System.out.println(urlpage);
		try {
			URL coursesUMBC = new URL(urlpage);
			BufferedReader in = new BufferedReader(new InputStreamReader(coursesUMBC.openStream()));
			String buffer;
			while(!(null==(buffer=in.readLine()))) {
				sourcecode += buffer;
			}
    		in.close();
	    } catch(Exception ex) { System.out.println(ex.toString()); }
		//System.out.println("Accessed!" + sourcecode.length());
		String currentID = "", currentLink = "", currentCourse = "", currentName = "", moreData = "";
		int truncateIndex = 0, truncateIndex2 = 0, index1 = 0, index2 = 0, index3 = 0, index4 = 0;
		String placeholder1 = "https://highpoint-prd.ps.umbc.edu/app/catalog/showCourse/UMBC1/";
		String placeholder2 = "/UGRD\"  >";
		String placeholder3 = "<div class=\"strong section-body\">";
		String placeholder4 = "</div>";
		String placeholder5 = "<div class=\"section-body\"  >";
		while (true) {
			try {
				truncateIndex = sourcecode.indexOf(placeholder1, truncateIndex);
				if (truncateIndex > 1) {
					truncateIndex2 = sourcecode.indexOf(placeholder2, truncateIndex);
					currentID = sourcecode.substring(truncateIndex + placeholder1.length(), truncateIndex2);
					currentLink = placeholder1 + "" + currentID + "/UGRD/";
					index1 = sourcecode.indexOf(placeholder3, truncateIndex2);
					index2 = sourcecode.indexOf(placeholder4, index1);
					currentCourse = sourcecode.substring(index1 + placeholder3.length(), index2);
					currentCourse = currentCourse.replaceAll(" ", "");
					index3 = sourcecode.indexOf(placeholder5, index2);
					index4 = sourcecode.indexOf(placeholder4, index3);
					currentName = sourcecode.substring(index3 + placeholder5.length(), index4);
					moreData = getDescription(currentLink);
					//moreData = "	";
					System.out.println(currentID + "	" + currentCourse + "	" + currentName + "	" + moreData);
					truncateIndex = truncateIndex2;
				}
				else {
					break;
				}
			} catch(Exception ex) { break; }	
		}
	}
	public static String getDescription(String urlpage) {
		String returnValue = "";
		String sourcecode = "";
		try {
			URL coursesUMBC = new URL(urlpage);
			BufferedReader in = new BufferedReader(new InputStreamReader(coursesUMBC.openStream()));
			String buffer;
			while(!(null==(buffer=in.readLine()))) {
				sourcecode += buffer;
			}
    		in.close();
	    } catch(Exception ex) {}
		
		String currentDescription = "", currentRequirement = "";
		int truncateIndex = 0, truncateIndex2 = 0, index1 = 0, index2 = 0;
		String placeholder1 = "<div class=\"section-body\"  >";
		String placeholder2 = "</div>";
		String placeholder3 = "<div>You must have completed";
		try {
			truncateIndex = sourcecode.indexOf(placeholder1, truncateIndex);
			if (truncateIndex > 1) {
				truncateIndex2 = sourcecode.indexOf(placeholder2, truncateIndex);
				currentDescription = sourcecode.substring(truncateIndex + placeholder1.length(), truncateIndex2);
				
				try {
					index1 = sourcecode.indexOf(placeholder3, truncateIndex2);
					if  (index1 > 0) {
						index2 = sourcecode.indexOf(placeholder2, index1);
						currentRequirement = sourcecode.substring(index1 + placeholder2.length() - 1, index2);
					}
				} catch(Exception ex) { currentRequirement = ""; }
			}
		} catch(Exception ex) { currentDescription = ""; }
		returnValue = currentRequirement + "	" + currentDescription;
		return returnValue;
	}
	public static Vector <String> readCoursePrefix(String filename) {
		Vector <String> dictionaryWords = new Vector <String>();
		 try {
			  String line  = "";
			  FileInputStream fstream = new FileInputStream(filename);
			  BufferedReader br = new BufferedReader(new InputStreamReader(fstream));
			  while((line = br.readLine()) != null) {
					line.trim();
					if(!line.equals("")) {
						dictionaryWords.add(line);
					}
				}
				br.close();
		  } catch (Exception ex) {
			  //System.out.println(ex.toString());
		  }
		return dictionaryWords;
	}
}