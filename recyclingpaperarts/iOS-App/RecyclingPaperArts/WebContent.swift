import Foundation

struct WebContent: Identifiable, Codable {
    let id: UUID
    let title: String
    let description: String
    let url: String
    let imageURL: String?
    let dateAdded: Date
    
    init(title: String, description: String, url: String, imageURL: String? = nil) {
        self.id = UUID()
        self.title = title
        self.description = description
        self.url = url
        self.imageURL = imageURL
        self.dateAdded = Date()
    }
}

class WebContentViewModel: ObservableObject {
    @Published var contents: [WebContent] = []
    @Published var isLoading = false
    @Published var errorMessage: String?
    
    private let networkManager = NetworkManager()
    
    func fetchWebContent() {
        isLoading = true
        errorMessage = nil
        
        networkManager.fetchWebsite(from: "https://recyclingpaperarts.com/") { [weak self] result in
            DispatchQueue.main.async {
                self?.isLoading = false
                switch result {
                case .success(let contents):
                    self?.contents = contents
                case .failure(let error):
                    self?.errorMessage = error.localizedDescription
                }
            }
        }
    }
}
