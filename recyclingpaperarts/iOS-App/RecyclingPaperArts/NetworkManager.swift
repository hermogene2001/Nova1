import Foundation

class NetworkManager {
    private let session = URLSession.shared
    private let baseURL = "https://recyclingpaperarts.com/"
    
    enum NetworkError: LocalizedError {
        case invalidURL
        case networkError(Error)
        case decodingError(Error)
        case httpError(Int)
        
        var errorDescription: String? {
            switch self {
            case .invalidURL:
                return "The URL provided is invalid."
            case .networkError(let error):
                return "Network error: \(error.localizedDescription)"
            case .decodingError(let error):
                return "Failed to decode response: \(error.localizedDescription)"
            case .httpError(let statusCode):
                return "HTTP Error: Status code \(statusCode)"
            }
        }
    }
    
    func fetchWebsite(from urlString: String, completion: @escaping (Result<[WebContent], NetworkError>) -> Void) {
        guard let url = URL(string: urlString) else {
            completion(.failure(.invalidURL))
            return
        }
        
        var request = URLRequest(url: url)
        request.timeoutInterval = 30
        request.setValue("Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15", forHTTPHeaderField: "User-Agent")
        
        session.dataTask(with: request) { data, response, error in
            if let error = error {
                completion(.failure(.networkError(error)))
                return
            }
            
            guard let httpResponse = response as? HTTPURLResponse else {
                completion(.failure(.networkError(NSError(domain: "Unknown", code: -1))))
                return
            }
            
            guard (200...299).contains(httpResponse.statusCode) else {
                completion(.failure(.httpError(httpResponse.statusCode)))
                return
            }
            
            guard let data = data else {
                completion(.failure(.networkError(NSError(domain: "NoData", code: -1))))
                return
            }
            
            do {
                let htmlString = String(data: data, encoding: .utf8) ?? ""
                let contents = parseHTML(htmlString)
                completion(.success(contents))
            } catch {
                completion(.failure(.decodingError(error)))
            }
        }.resume()
    }
    
    private func parseHTML(_ htmlString: String) -> [WebContent] {
        var contents: [WebContent] = []
        
        // Parse main content sections from the website
        let patterns: [(regex: String, titleKey: String, descKey: String)] = [
            ("<h[1-6][^>]*>([^<]+)</h[1-6]>", "title", ""),
            ("<p[^>]*>([^<]+)</p>", "description", ""),
        ]
        
        // Extract title from page
        if let titleMatch = htmlString.range(of: "<title>([^<]+)</title>", options: .regularExpression) {
            let titleRange = htmlString[titleMatch]
            let title = titleRange.replacingOccurrences(of: "<[^>]*>", with: "", options: .regularExpression)
            
            // Create main content item
            let mainContent = WebContent(
                title: title.trimmingCharacters(in: .whitespaces),
                description: "Content from Recycling Paper Arts - Discover creative recycling and paper art projects.",
                url: "https://recyclingpaperarts.com/",
                imageURL: nil
            )
            contents.append(mainContent)
        }
        
        // Extract h2 titles as products/articles
        let headingPattern = "<h2[^>]*>([^<]+)</h2>"
        if let regex = try? NSRegularExpression(pattern: headingPattern) {
            let nsString = htmlString as NSString
            let matches = regex.matches(in: htmlString, range: NSRange(location: 0, length: nsString.length))
            
            for match in matches {
                if let range = Range(match.range(at: 1), in: htmlString) {
                    let title = String(htmlString[range]).trimmingCharacters(in: .whitespaces)
                    if !title.isEmpty {
                        let content = WebContent(
                            title: title,
                            description: "Article or project from Recycling Paper Arts",
                            url: "https://recyclingpaperarts.com/",
                            imageURL: nil
                        )
                        contents.append(content)
                    }
                }
            }
        }
        
        // If no content was parsed, return a placeholder
        if contents.isEmpty {
            contents.append(WebContent(
                title: "Recycling Paper Arts",
                description: "Visit our website for creative recycling and paper art projects. Connect with us to learn more about sustainable crafting.",
                url: "https://recyclingpaperarts.com/",
                imageURL: nil
            ))
        }
        
        return contents
    }
}
